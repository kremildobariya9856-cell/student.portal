<?php
// Initialize variables for form data and errors
$name = $email = $mobile = $password = $confirmPassword = $course = $year = $gender = "";
$nameErr = $emailErr = $mobileErr = $passwordErr = $confirmPasswordErr = $courseErr = $yearErr = $genderErr = $termsErr = "";
$successMsg = "";

// Helper function to sanitize user inputs
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// 1. Process Form on POST submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 2. Server-side Validation & Sanitization

    // Full Name
    if (empty($_POST["fullName"])) {
        $nameErr = "Name is required";
    } else {
        $name = test_input($_POST["fullName"]);
        if (!preg_match("/^[a-zA-Z ]*$/", $name)) {
            $nameErr = "Only letters and spaces allowed";
        }
    }

    // Email
    if (empty($_POST["email"])) {
        $emailErr = "Email is required";
    } else {
        $email = test_input($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format";
        }
    }

    // Mobile Number
    if (empty($_POST["mobile"])) {
        $mobileErr = "Mobile number is required";
    } else {
        $mobile = test_input($_POST["mobile"]);
        if (!preg_match("/^[0-9]{10}$/", $mobile)) {
            $mobileErr = "Mobile must be exactly 10 digits";
        }
    }

    // Password
    if (empty($_POST["password"])) {
        $passwordErr = "Password is required";
    } else {
        $password = test_input($_POST["password"]);
        if (strlen($password) < 6) {
            $passwordErr = "Password must be at least 6 characters";
        }
    }

    // Confirm Password
    if (empty($_POST["confirmPassword"])) {
        $confirmPasswordErr = "Please confirm password";
    } else {
        $confirmPassword = test_input($_POST["confirmPassword"]);
        if ($password != $confirmPassword) {
            $confirmPasswordErr = "Passwords do not match";
        }
    }

    // Course
    if (empty($_POST["course"])) {
        $courseErr = "Please select course";
    } else {
        $course = test_input($_POST["course"]);
    }

    // Year
    if (empty($_POST["year"])) {
        $yearErr = "Please select year";
    } else {
        $year = test_input($_POST["year"]);
    }

    // Gender
    if (empty($_POST["gender"])) {
        $genderErr = "Please select gender";
    } else {
        $gender = test_input($_POST["gender"]);
    }

    // Terms & Conditions
    if (!isset($_POST["terms"])) {
        $termsErr = "You must accept terms & conditions";
    }

    // 3. Safe File Writing: Save to CSV and JSON if validation passes
    if (empty($nameErr) && empty($emailErr) && empty($mobileErr) && empty($passwordErr) && empty($confirmPasswordErr) && empty($courseErr) && empty($yearErr) && empty($genderErr) && empty($termsErr)) {
        
        // (A) Save to CSV file safely
        $csvFile = __DIR__ . "/students.csv";
        $isNewCsv = !file_exists($csvFile);
        $file = fopen($csvFile, "a");
        if ($file) {
            if ($isNewCsv) {
                fputcsv($file, ["Full Name", "Email", "Mobile", "Course", "Year", "Gender"]);
            }
            fputcsv($file, [$name, $email, $mobile, $course, $year, $gender]);
            fclose($file);
        }

        // (B) Save to JSON file safely
        $jsonFile = __DIR__ . "/students.json";
        $dataArray = [];
        if (file_exists($jsonFile)) {
            $jsonContent = file_get_contents($jsonFile);
            if (!empty($jsonContent)) {
                $decoded = json_decode($jsonContent, true);
                if (is_array($decoded)) {
                    $dataArray = $decoded;
                }
            }
        }
        $dataArray[] = [
            "name"   => $name,
            "email"  => $email,
            "mobile" => $mobile,
            "course" => $course,
            "year"   => $year,
            "gender" => $gender
        ];
        file_put_contents($jsonFile, json_encode($dataArray, JSON_PRETTY_PRINT));

        // Success message & clear form
        $successMsg = "Registration successful! Record has been saved to CSV and JSON files.";
        $name = $email = $mobile = $password = $confirmPassword = $course = $year = $gender = "";
    }
}

// Read CSV data to display stored records
$records = [];
$csvPath = __DIR__ . "/students.csv";
if (file_exists($csvPath)) {
    if (($handle = fopen($csvPath, "r")) !== false) {
        $isHeader = true;
        while (($row = fgetcsv($handle)) !== false) {
            if ($isHeader) {
                $isHeader = false;
                continue;
            }
            $records[] = $row;
        }
        fclose($handle);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | CHARUSAT Student Hub</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <a href="#main-content" class="skip-link" style="display:none;">Skip to main content</a>

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
                <li><a href="registration.php" aria-current="page">Register</a></li>
                <li><a href="login.html">Login</a></li>
                <li><a href="dashboard.html">Dashboard</a></li>
                <li><a href="events.html">Events</a></li>
                <li><a href="profile.html">Profile</a></li>
                <li><a href="contact.html">Contact</a></li>
                <li><a href="admin.html">Admin</a></li>
                <li><a href="faq.html">FAQ</a></li>
                <li><a href="feedback.html">Feedback</a></li>
            </ul>
        </nav>
    </header>

    <!-- Sidebar -->
    <aside>
        <h2>Quick Links</h2>
        <ul>
            <li><a href="index.html">Home</a></li>
            <li><a href="registration.php">New Registration</a></li>
            <li><a href="login.html">Student Login</a></li>
            <li><a href="events.html">Upcoming Events</a></li>
            <li><a href="contact.html">Help & Support</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main id="main-content">
        <h2>Student Registration</h2>
        <p>Practical 7: PHP Form Processing with Server-Side Validation & File Storage</p>

        <!-- Success Message -->
        <?php if (!empty($successMsg)): ?>
            <div class="alert alert-success">
                <strong>Success:</strong> <?php echo $successMsg; ?>
            </div>
        <?php endif; ?>

        <!-- Registration Form -->
        <section>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" novalidate>

                <div class="field">
                    <label for="fullName">Full Name</label>
                    <input type="text" id="fullName" name="fullName" value="<?php echo $name; ?>">
                    <span class="error"><?php echo $nameErr; ?></span>
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo $email; ?>">
                    <span class="error"><?php echo $emailErr; ?></span>
                </div>

                <div class="field">
                    <label for="mobile">Mobile Number</label>
                    <input type="tel" id="mobile" name="mobile" maxlength="10" value="<?php echo $mobile; ?>">
                    <span class="error"><?php echo $mobileErr; ?></span>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password">
                    <span class="error"><?php echo $passwordErr; ?></span>
                </div>

                <div class="field">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword">
                    <span class="error"><?php echo $confirmPasswordErr; ?></span>
                </div>

                <div class="field">
                    <label for="course">Course</label>
                    <select id="course" name="course">
                        <option value="">Select Course</option>
                        <option value="CE" <?php if ($course == "CE") echo "selected"; ?>>Computer Engineering</option>
                        <option value="IT" <?php if ($course == "IT") echo "selected"; ?>>Information Technology</option>
                        <option value="ME" <?php if ($course == "ME") echo "selected"; ?>>Mechanical Engineering</option>
                        <option value="CIVIL" <?php if ($course == "CIVIL") echo "selected"; ?>>Civil Engineering</option>
                    </select>
                    <span class="error"><?php echo $courseErr; ?></span>
                </div>

                <div class="field">
                    <label for="year">Year</label>
                    <select id="year" name="year">
                        <option value="">Select Year</option>
                        <option value="1" <?php if ($year == "1") echo "selected"; ?>>1st Year</option>
                        <option value="2" <?php if ($year == "2") echo "selected"; ?>>2nd Year</option>
                        <option value="3" <?php if ($year == "3") echo "selected"; ?>>3rd Year</option>
                        <option value="4" <?php if ($year == "4") echo "selected"; ?>>4th Year</option>
                    </select>
                    <span class="error"><?php echo $yearErr; ?></span>
                </div>

                <fieldset class="field">
                    <legend>Gender</legend>
                    <label><input type="radio" name="gender" value="male" <?php if ($gender == "male") echo "checked"; ?>> Male</label>
                    <label><input type="radio" name="gender" value="female" <?php if ($gender == "female") echo "checked"; ?>> Female</label>
                    <label><input type="radio" name="gender" value="other" <?php if ($gender == "other") echo "checked"; ?>> Other</label>
                    <span class="error"><?php echo $genderErr; ?></span>
                </fieldset>

                <div class="field">
                    <label>
                        <input type="checkbox" id="terms" name="terms"> I accept the terms and conditions
                    </label>
                    <span class="error"><?php echo $termsErr; ?></span>
                </div>

                <button type="submit">Register</button>
            </form>
        </section>

        <!-- Stored Records Display -->
        <section>
            <h3>Stored Records (from CSV/JSON storage)</h3>
            <?php if (!empty($records)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Course</th>
                            <th>Year</th>
                            <th>Gender</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $r): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r[0] ?? ""); ?></td>
                                <td><?php echo htmlspecialchars($r[1] ?? ""); ?></td>
                                <td><?php echo htmlspecialchars($r[2] ?? ""); ?></td>
                                <td><?php echo htmlspecialchars($r[3] ?? ""); ?></td>
                                <td><?php echo htmlspecialchars($r[4] ?? ""); ?></td>
                                <td><?php echo htmlspecialchars($r[5] ?? ""); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No records stored yet. Fill and submit the form above.</p>
            <?php endif; ?>
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
