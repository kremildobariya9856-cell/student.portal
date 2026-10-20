<?php
require_once __DIR__ . '/auth.php';

// Enforce admin-only access
requireAdmin();

$adminName = $_SESSION['user_name'] ?? 'Administrator';
$adminEmail = $_SESSION['user_email'] ?? '';
$adminRole = $_SESSION['user_role'] ?? 'admin';

// Fetch registered users from database
$usersList = [];
try {
    $result = $conn->query("SELECT student_id, full_name, email, mobile, course, year, role, created_at FROM students ORDER BY student_id ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $usersList[] = $row;
        }
    }
} catch (mysqli_sql_exception $e) {
    $dbError = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | CHARUSAT Student Hub</title>
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
                <li><a href="admin.php" aria-current="page">Admin Panel</a></li>
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
            <li aria-current="page">Admin</li>
        </ol>
    </nav>

    <!-- Sidebar -->
    <aside>
        <h2>Admin Menu</h2>
        <ul>
            <li><a href="admin.php">Admin Overview</a></li>
            <li><a href="dashboard.php">Student View</a></li>
            <li><a href="events.php">Manage Events</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main id="main-content">
        <h2>Administrator Control Panel</h2>
        <p>Restricted Area: Manage registered users and site administration.</p>

        <div class="user-status-card">
            <div>
                <strong>Active Admin:</strong> <?php echo htmlspecialchars($adminName); ?>
                <span class="badge badge-admin"><?php echo htmlspecialchars($adminRole); ?></span>
                <br>
                <small style="color:#555;">Email: <?php echo htmlspecialchars($adminEmail); ?> | Role-Based Access: Verified</small>
            </div>
            <div>
                <a href="logout.php" class="btn-danger">Sign Out</a>
            </div>
        </div>

        <section aria-labelledby="admin-users-heading">
            <h3 id="admin-users-heading">Registered User Management</h3>
            <p>List of all registered accounts stored in the MySQL database.</p>

            <table>
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Mobile</th>
                        <th scope="col">Course / Year</th>
                        <th scope="col">Role</th>
                        <th scope="col">Registration Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($usersList)): ?>
                        <?php foreach ($usersList as $u): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($u['student_id']); ?></td>
                                <td><strong><?php echo htmlspecialchars($u['full_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><?php echo htmlspecialchars($u['mobile']); ?></td>
                                <td><?php echo htmlspecialchars($u['course']) . ' - Year ' . htmlspecialchars($u['year']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo htmlspecialchars($u['role']); ?>">
                                        <?php echo htmlspecialchars($u['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($u['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <aside aria-labelledby="admin-actions-heading" style="background:transparent; border:none; padding:0;">
            <h3 id="admin-actions-heading">Quick Actions</h3>
            <ul>
                <li><a href="dashboard.php">Switch to Student Dashboard View</a></li>
                <li><a href="registration.php">Register a New Account</a></li>
                <li><a href="logout.php">Terminate Admin Session</a></li>
            </ul>
        </aside>

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
