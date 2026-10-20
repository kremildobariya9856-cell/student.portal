<?php
require_once __DIR__ . '/auth.php';

// Enforce admin-only access
requireAdmin();

$adminName = $_SESSION['user_name'] ?? 'Administrator';
$adminEmail = $_SESSION['user_email'] ?? '';
$adminRole = $_SESSION['user_role'] ?? 'admin';

$successMsg = "";
$errorMsg = "";

// -------------------------------------------------------------------------
// Handle POST Requests: Add, Update, Delete
// -------------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    // 1. CREATE (Insert New Student)
    if ($action === "add") {
        $name = trim($_POST["full_name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $mobile = trim($_POST["mobile"] ?? "");
        $password = trim($_POST["password"] ?? "");
        $course = trim($_POST["course"] ?? "");
        $year = intval($_POST["year"] ?? 1);
        $gender = trim($_POST["gender"] ?? "Other");
        $role = trim($_POST["role"] ?? "student");

        if (empty($name) || empty($email) || empty($mobile) || empty($password) || empty($course)) {
            $errorMsg = "All fields are required to register a student.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMsg = "Please enter a valid email address.";
        } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {
            $errorMsg = "Mobile number must be exactly 10 digits.";
        } else {
            try {
                // Check duplicate email
                $chk = $conn->prepare("SELECT student_id FROM students WHERE email = ?");
                $chk->bind_param("s", $email);
                $chk->execute();
                $chk->store_result();

                if ($chk->num_rows > 0) {
                    $errorMsg = "A user with email '$email' already exists!";
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $ins = $conn->prepare("INSERT INTO students (full_name, email, mobile, password, course, year, gender, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $ins->bind_param("sssssiss", $name, $email, $mobile, $hashedPassword, $course, $year, $gender, $role);
                    $ins->execute();
                    $ins->close();
                    $successMsg = "Student '$name' was successfully added!";
                }
                $chk->close();
            } catch (mysqli_sql_exception $e) {
                $errorMsg = "Database error: " . $e->getMessage();
            }
        }
    }

    // 2. UPDATE (Edit Student)
    elseif ($action === "update") {
        $id = intval($_POST["student_id"] ?? 0);
        $name = trim($_POST["full_name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $mobile = trim($_POST["mobile"] ?? "");
        $password = trim($_POST["password"] ?? "");
        $course = trim($_POST["course"] ?? "");
        $year = intval($_POST["year"] ?? 1);
        $gender = trim($_POST["gender"] ?? "Other");
        $role = trim($_POST["role"] ?? "student");

        if ($id <= 0 || empty($name) || empty($email) || empty($mobile) || empty($course)) {
            $errorMsg = "All fields except password are required for updating.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMsg = "Please enter a valid email address.";
        } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {
            $errorMsg = "Mobile number must be exactly 10 digits.";
        } else {
            try {
                // Ensure email is not duplicate for another student
                $chk = $conn->prepare("SELECT student_id FROM students WHERE email = ? AND student_id != ?");
                $chk->bind_param("si", $email, $id);
                $chk->execute();
                $chk->store_result();

                if ($chk->num_rows > 0) {
                    $errorMsg = "The email '$email' is already registered with another user!";
                } else {
                    if (!empty($password)) {
                        // Update with new password hash
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $upd = $conn->prepare("UPDATE students SET full_name = ?, email = ?, mobile = ?, password = ?, course = ?, year = ?, gender = ?, role = ? WHERE student_id = ?");
                        $upd->bind_param("sssssissi", $name, $email, $mobile, $hashedPassword, $course, $year, $gender, $role, $id);
                    } else {
                        // Update without changing password
                        $upd = $conn->prepare("UPDATE students SET full_name = ?, email = ?, mobile = ?, course = ?, year = ?, gender = ?, role = ? WHERE student_id = ?");
                        $upd->bind_param("sssssisi", $name, $email, $mobile, $course, $year, $gender, $role, $id);
                    }
                    $upd->execute();
                    $upd->close();
                    $successMsg = "Student '$name' was successfully updated!";
                }
                $chk->close();
            } catch (mysqli_sql_exception $e) {
                $errorMsg = "Database error: " . $e->getMessage();
            }
        }
    }

    // 3. DELETE (Delete Student)
    elseif ($action === "delete") {
        $id = intval($_POST["student_id"] ?? 0);

        // Prevent admin from deleting their own active logged-in account
        if ($id === intval($_SESSION['user_id'])) {
            $errorMsg = "You cannot delete your own currently active administrator account!";
        } elseif ($id > 0) {
            try {
                $del = $conn->prepare("DELETE FROM students WHERE student_id = ?");
                $del->bind_param("i", $id);
                $del->execute();
                if ($del->affected_rows > 0) {
                    $successMsg = "Student record #$id was deleted successfully!";
                } else {
                    $errorMsg = "Student record not found or already deleted.";
                }
                $del->close();
            } catch (mysqli_sql_exception $e) {
                $errorMsg = "Database error: " . $e->getMessage();
            }
        }
    }
}

// -------------------------------------------------------------------------
// Handle Edit Mode (Fetch student to edit)
// -------------------------------------------------------------------------
$editStudent = null;
if (isset($_GET["action"]) && $_GET["action"] === "edit" && isset($_GET["id"])) {
    $editId = intval($_GET["id"]);
    $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $editStudent = $row;
    }
    $stmt->close();
}

// -------------------------------------------------------------------------
// Handle Search and Filter Query
// -------------------------------------------------------------------------
$search = trim($_GET["search"] ?? "");
$filterCourse = trim($_GET["course"] ?? "");
$filterYear = trim($_GET["year"] ?? "");
$filterGender = trim($_GET["gender"] ?? "");

$whereConditions = [];
$bindTypes = "";
$bindParams = [];

if (!empty($search)) {
    $whereConditions[] = "(full_name LIKE ? OR email LIKE ? OR course LIKE ?)";
    $likeSearch = "%" . $search . "%";
    $bindTypes .= "sss";
    $bindParams[] = $likeSearch;
    $bindParams[] = $likeSearch;
    $bindParams[] = $likeSearch;
}

if (!empty($filterCourse)) {
    $whereConditions[] = "course = ?";
    $bindTypes .= "s";
    $bindParams[] = $filterCourse;
}

if (!empty($filterYear)) {
    $whereConditions[] = "year = ?";
    $bindTypes .= "i";
    $bindParams[] = intval($filterYear);
}

if (!empty($filterGender)) {
    $whereConditions[] = "gender = ?";
    $bindTypes .= "s";
    $bindParams[] = $filterGender;
}

$querySql = "SELECT student_id, full_name, email, mobile, course, year, gender, role, created_at FROM students";
if (!empty($whereConditions)) {
    $querySql .= " WHERE " . implode(" AND ", $whereConditions);
}
$querySql .= " ORDER BY student_id DESC";

$studentsList = [];
try {
    $stmt = $conn->prepare($querySql);
    if (!empty($bindTypes)) {
        $stmt->bind_param($bindTypes, ...$bindParams);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $studentsList[] = $row;
    }
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    $errorMsg = "Error loading students: " . $e->getMessage();
}

// Get total count of students in table
$totalStudents = 0;
try {
    $cntRes = $conn->query("SELECT COUNT(*) AS total FROM students");
    if ($cntRow = $cntRes->fetch_assoc()) {
        $totalStudents = intval($cntRow['total']);
    }
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management | CHARUSAT Student Hub</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <a href="#main-content" class="skip-link">Skip to main content</a>

    <!-- Header -->
    <header>
        <div class="header-brand">
            <img src="CHARUSAT.jpg" alt="CHARUSAT University Banner" class="banner-img">
            <h1>CHARUSAT Student Hub</h1>
        </div>
        <nav aria-label="Primary">
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="about.html">About</a></li>
                <li><a href="registration.php">Register</a></li>
                <li><a href="dashboard.php">Student Dashboard</a></li>
                <li><a href="admin.php" aria-current="page">Manage Students</a></li>
                <li><a href="admin-events.php">Manage Events</a></li>
                <li><a href="events.php">Events</a></li>
                <li><a href="profile.html">Profile</a></li>
                <li><a href="contact.html">Contact</a></li>
                <li><a href="faq.html">FAQ</a></li>
                <li><a href="feedback.html">Feedback</a></li>
                <li><a href="logout.php" style="background:#c9302c; color:white;">Logout</a></li>
            </ul>
        </nav>
    </header>

    <nav aria-label="Breadcrumb">
        <ol>
            <li><a href="index.html">Home</a></li>
            <li aria-current="page">Student Management CRUD</li>
        </ol>
    </nav>

    <!-- Sidebar -->
    <aside>
        <h2>Admin Menu</h2>
        <ul>
            <li><a href="admin.php">Manage Students</a></li>
            <li><a href="admin-events.php">Manage Events (CRUD)</a></li>
            <li><a href="events.php">Browse Events</a></li>
            <li><a href="dashboard.php">Student Dashboard</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main id="main-content">
        <h2>Student Management Module</h2>
        <p>Complete data-driven CRUD operations with real-time search, filter, and confirmation.</p>

        <!-- Active Admin Status Card -->
        <div class="user-status-card">
            <div>
                <strong>Active Admin:</strong> <?php echo htmlspecialchars($adminName); ?>
                <span class="badge badge-admin"><?php echo htmlspecialchars($adminRole); ?></span>
                <br>
                <small style="color:#555;">Email: <?php echo htmlspecialchars($adminEmail); ?> | Role-Based Access: Verified</small>
            </div>
            <div>
                <a href="#add-student-section" class="btn-success btn-sm" style="text-decoration:none; padding:8px 14px; font-weight:bold; border-radius:4px;">+ Add New Student</a>
                <a href="logout.php" class="btn-danger">Sign Out</a>
            </div>
        </div>

        <!-- UI Feedback Alerts -->
        <?php if (!empty($successMsg)): ?>
            <div class="alert alert-success">
                <strong>Success:</strong> <?php echo htmlspecialchars($successMsg); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger">
                <strong>Error:</strong> <?php echo htmlspecialchars($errorMsg); ?>
            </div>
        <?php endif; ?>

        <!-- EDIT STUDENT FORM (Visible when ?action=edit&id=X) -->
        <?php if ($editStudent): ?>
            <section class="crud-form-card" style="border-top-color:#ff9800;">
                <h3>Edit Student Record: <?php echo htmlspecialchars($editStudent['full_name']); ?> (#<?php echo $editStudent['student_id']; ?>)</h3>
                <form action="admin.php" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="student_id" value="<?php echo $editStudent['student_id']; ?>">

                    <div class="crud-form-grid">
                        <div class="field">
                            <label for="edit_name">Full Name</label>
                            <input type="text" id="edit_name" name="full_name" value="<?php echo htmlspecialchars($editStudent['full_name']); ?>" required>
                        </div>

                        <div class="field">
                            <label for="edit_email">Email Address</label>
                            <input type="email" id="edit_email" name="email" value="<?php echo htmlspecialchars($editStudent['email']); ?>" required>
                        </div>

                        <div class="field">
                            <label for="edit_mobile">Mobile Number (10 digits)</label>
                            <input type="tel" id="edit_mobile" name="mobile" maxlength="10" value="<?php echo htmlspecialchars($editStudent['mobile']); ?>" required>
                        </div>

                        <div class="field">
                            <label for="edit_course">Course / Department</label>
                            <select id="edit_course" name="course" required>
                                <option value="CE" <?php echo ($editStudent['course'] === 'CE') ? 'selected' : ''; ?>>CE (Computer Engineering)</option>
                                <option value="IT" <?php echo ($editStudent['course'] === 'IT') ? 'selected' : ''; ?>>IT (Information Technology)</option>
                                <option value="CSE" <?php echo ($editStudent['course'] === 'CSE') ? 'selected' : ''; ?>>CSE (Computer Science)</option>
                                <option value="EC" <?php echo ($editStudent['course'] === 'EC') ? 'selected' : ''; ?>>EC (Electronics & Comm.)</option>
                                <option value="ME" <?php echo ($editStudent['course'] === 'ME') ? 'selected' : ''; ?>>ME (Mechanical Eng.)</option>
                                <option value="ADMIN" <?php echo ($editStudent['course'] === 'ADMIN') ? 'selected' : ''; ?>>ADMIN (Administration)</option>
                            </select>
                        </div>

                        <div class="field">
                            <label for="edit_year">Year of Study</label>
                            <select id="edit_year" name="year" required>
                                <option value="1" <?php echo ($editStudent['year'] == 1) ? 'selected' : ''; ?>>Year 1</option>
                                <option value="2" <?php echo ($editStudent['year'] == 2) ? 'selected' : ''; ?>>Year 2</option>
                                <option value="3" <?php echo ($editStudent['year'] == 3) ? 'selected' : ''; ?>>Year 3</option>
                                <option value="4" <?php echo ($editStudent['year'] == 4) ? 'selected' : ''; ?>>Year 4</option>
                            </select>
                        </div>

                        <div class="field">
                            <label for="edit_gender">Gender</label>
                            <select id="edit_gender" name="gender" required>
                                <option value="Male" <?php echo ($editStudent['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($editStudent['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo ($editStudent['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>

                        <div class="field">
                            <label for="edit_role">User Role</label>
                            <select id="edit_role" name="role" required>
                                <option value="student" <?php echo ($editStudent['role'] === 'student') ? 'selected' : ''; ?>>Student</option>
                                <option value="admin" <?php echo ($editStudent['role'] === 'admin') ? 'selected' : ''; ?>>Administrator</option>
                            </select>
                        </div>

                        <div class="field">
                            <label for="edit_password">New Password <small style="font-weight:normal; color:#666;">(Leave blank to keep existing password)</small></label>
                            <input type="password" id="edit_password" name="password" placeholder="Enter new password or leave blank">
                        </div>
                    </div>

                    <div style="margin-top:15px; display:flex; gap:10px;">
                        <button type="submit" class="btn-primary" style="background:#ff9800;">Update Student Record</button>
                        <a href="admin.php" class="btn-secondary">Cancel</a>
                    </div>
                </form>
            </section>
        <?php endif; ?>

        <!-- SEARCH AND FILTER MODULE -->
        <section class="search-filter-card">
            <h3 style="margin-bottom:12px; font-size:1.15rem; color:#003366;">Search and Filter Students</h3>
            <form action="admin.php" method="GET">
                <div class="search-filter-grid">
                    <div class="filter-group">
                        <label for="search">Search Keyword</label>
                        <input type="search" id="search" name="search" placeholder="Search by name, email, or course..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <div class="filter-group">
                        <label for="course">Course</label>
                        <select id="course" name="course">
                            <option value="">All Courses</option>
                            <option value="CE" <?php echo ($filterCourse === 'CE') ? 'selected' : ''; ?>>CE</option>
                            <option value="IT" <?php echo ($filterCourse === 'IT') ? 'selected' : ''; ?>>IT</option>
                            <option value="CSE" <?php echo ($filterCourse === 'CSE') ? 'selected' : ''; ?>>CSE</option>
                            <option value="EC" <?php echo ($filterCourse === 'EC') ? 'selected' : ''; ?>>EC</option>
                            <option value="ME" <?php echo ($filterCourse === 'ME') ? 'selected' : ''; ?>>ME</option>
                            <option value="ADMIN" <?php echo ($filterCourse === 'ADMIN') ? 'selected' : ''; ?>>ADMIN</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="year">Year</label>
                        <select id="year" name="year">
                            <option value="">All Years</option>
                            <option value="1" <?php echo ($filterYear === '1') ? 'selected' : ''; ?>>Year 1</option>
                            <option value="2" <?php echo ($filterYear === '2') ? 'selected' : ''; ?>>Year 2</option>
                            <option value="3" <?php echo ($filterYear === '3') ? 'selected' : ''; ?>>Year 3</option>
                            <option value="4" <?php echo ($filterYear === '4') ? 'selected' : ''; ?>>Year 4</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender">
                            <option value="">All Genders</option>
                            <option value="Male" <?php echo ($filterGender === 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($filterGender === 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($filterGender === 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn-primary">Apply Filter</button>
                        <a href="admin.php" class="btn-secondary">Clear</a>
                    </div>
                </div>
            </form>
        </section>

        <!-- VIEW / SELECT STUDENTS LIST -->
        <section aria-labelledby="student-list-heading">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:10px;">
                <h3 id="student-list-heading" style="margin:0;">Registered Student Records</h3>
                <span style="font-size:14px; color:#555;">
                    Showing <strong><?php echo count($studentsList); ?></strong> of <strong><?php echo $totalStudents; ?></strong> total records
                    <?php if (!empty($search) || !empty($filterCourse) || !empty($filterYear) || !empty($filterGender)): ?>
                        (Filtered)
                    <?php endif; ?>
                </span>
            </div>

            <table>
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Mobile</th>
                        <th scope="col">Course</th>
                        <th scope="col">Year</th>
                        <th scope="col">Gender</th>
                        <th scope="col">Role</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($studentsList)): ?>
                        <?php foreach ($studentsList as $u): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($u['student_id']); ?></td>
                                <td><strong><?php echo htmlspecialchars($u['full_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><?php echo htmlspecialchars($u['mobile']); ?></td>
                                <td><?php echo htmlspecialchars($u['course']); ?></td>
                                <td>Year <?php echo htmlspecialchars($u['year']); ?></td>
                                <td><?php echo htmlspecialchars($u['gender']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo htmlspecialchars($u['role']); ?>">
                                        <?php echo htmlspecialchars($u['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btn-group">
                                        <!-- EDIT BUTTON -->
                                        <a href="admin.php?action=edit&id=<?php echo $u['student_id']; ?>" class="btn-sm btn-edit">Edit</a>

                                        <!-- DELETE BUTTON WITH CONFIRMATION -->
                                        <?php if ($u['student_id'] == $_SESSION['user_id']): ?>
                                            <span style="font-size:11px; color:#888;">(Active Admin)</span>
                                        <?php else: ?>
                                            <form action="admin.php" method="POST" style="margin:0;" onsubmit="return confirm('Are you sure you want to delete student: <?php echo addslashes($u['full_name']); ?>? This action cannot be undone.');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="student_id" value="<?php echo $u['student_id']; ?>">
                                                <button type="submit" class="btn-sm btn-delete">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align:center; padding:25px; color:#777;">
                                No student records found matching your search and filter criteria.
                                <br><a href="admin.php" style="color:#003366; text-decoration:underline; font-weight:bold; margin-top:8px; display:inline-block;">Reset Search & Filters</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <!-- ADD NEW STUDENT FORM -->
        <section id="add-student-section" class="crud-form-card">
            <h3>Add New Student Record</h3>
            <p>Insert a new student directly into the MySQL database using prepared statements.</p>
            <form action="admin.php" method="POST">
                <input type="hidden" name="action" value="add">

                <div class="crud-form-grid">
                    <div class="field">
                        <label for="add_name">Full Name</label>
                        <input type="text" id="add_name" name="full_name" placeholder="e.g. Ramesh Patel" required>
                    </div>

                    <div class="field">
                        <label for="add_email">Email Address</label>
                        <input type="email" id="add_email" name="email" placeholder="e.g. ramesh@charusat.edu.in" required>
                    </div>

                    <div class="field">
                        <label for="add_mobile">Mobile Number (10 digits)</label>
                        <input type="tel" id="add_mobile" name="mobile" maxlength="10" placeholder="e.g. 9876543210" required>
                    </div>

                    <div class="field">
                        <label for="add_password">Password</label>
                        <input type="password" id="add_password" name="password" placeholder="Temporary password" required>
                    </div>

                    <div class="field">
                        <label for="add_course">Course / Department</label>
                        <select id="add_course" name="course" required>
                            <option value="CE">CE (Computer Engineering)</option>
                            <option value="IT">IT (Information Technology)</option>
                            <option value="CSE">CSE (Computer Science)</option>
                            <option value="EC">EC (Electronics & Comm.)</option>
                            <option value="ME">ME (Mechanical Eng.)</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="add_year">Year of Study</label>
                        <select id="add_year" name="year" required>
                            <option value="1">Year 1</option>
                            <option value="2">Year 2</option>
                            <option value="3">Year 3</option>
                            <option value="4">Year 4</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="add_gender">Gender</label>
                        <select id="add_gender" name="gender" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="add_role">User Role</label>
                        <select id="add_role" name="role" required>
                            <option value="student" selected>Student</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                </div>

                <div style="margin-top:15px;">
                    <button type="submit" class="btn-success">Submit & Save Student</button>
                </div>
            </form>
        </section>

    </main>

    <!-- Footer -->
    <footer>
        <nav aria-label="Footer">
            <ul>
                <li><a href="about.html">About</a></li>
                <li><a href="contact.html">Contact us</a></li>
                <li><a href="faq.html">FAQ</a></li>
                <li><a href="feedback.html">Feedback</a></li>
            </ul>
        </nav>
        <p>&copy; 2026 CHARUSAT Student Hub. All rights reserved.</p>
    </footer>

    <script src="script.js"></script>
</body>
</html>
