<?php
require_once __DIR__ . '/auth.php';

// Block unauthorized access
requireLogin();

$userName = $_SESSION['user_name'] ?? 'Student';
$userEmail = $_SESSION['user_email'] ?? '';
$userRole = $_SESSION['user_role'] ?? 'student';

// Check if student was redirected from unauthorized admin attempt
$forbiddenAlert = false;
if (isset($_GET['msg']) && $_GET['msg'] === 'forbidden') {
    $forbiddenAlert = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | CHARUSAT Student Hub</title>
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
                <li><a href="dashboard.php" aria-current="page">Dashboard</a></li>
                <?php if ($userRole === 'admin'): ?>
                    <li><a href="admin.php">Manage Students</a></li>
                    <li><a href="admin-events.php">Manage Events</a></li>
                <?php endif; ?>
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
            <li aria-current="page">Dashboard</li>
        </ol>
    </nav>

    <!-- Sidebar -->
    <aside>
        <h2>Student Menu</h2>
        <ul>
            <li><a href="dashboard.php">Dashboard Home</a></li>
            <li><a href="profile.html">My Profile</a></li>
            <li><a href="events.php">Upcoming Events</a></li>
            <?php if ($userRole === 'admin'): ?>
                <li><a href="admin.php">Manage Students</a></li>
                <li><a href="admin-events.php">Manage Events</a></li>
            <?php endif; ?>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main id="main-content">
        <h2>Student Dashboard</h2>
        <p>Welcome back. Here is a summary of your academic activity.</p>

        <?php if ($forbiddenAlert): ?>
            <div class="alert alert-danger">
                <strong>Access Denied:</strong> You do not have administrator privileges to access the Admin Panel.
            </div>
        <?php endif; ?>

        <div class="user-status-card">
            <div>
                <strong>Active Session:</strong> <?php echo htmlspecialchars($userName); ?>
                <span class="badge badge-<?php echo htmlspecialchars($userRole); ?>">
                    <?php echo htmlspecialchars($userRole); ?>
                </span>
                <br>
                <small style="color:#555;">Email: <?php echo htmlspecialchars($userEmail); ?> | Inactivity Timeout: 10 mins</small>
            </div>
            <div>
                <a href="logout.php" class="btn-danger">Sign Out</a>
            </div>
        </div>

        <section aria-labelledby="summary-heading">
            <h3 id="summary-heading">Summary</h3>
            <ul>
                <li>Attendance: 87%</li>
                <li>Pending assignments: 3</li>
                <li>Latest result: Semester 4 published</li>
            </ul>
        </section>

        <section aria-labelledby="notices-heading">
            <h3 id="notices-heading">Recent notices</h3>
            <table>
                <caption class="visually-hidden">Recent notices with dates</caption>
                <thead>
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Notice</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>02 Aug 2026</td>
                        <td>Mid-semester exam schedule released</td>
                    </tr>
                    <tr>
                        <td>28 Jul 2026</td>
                        <td>Library timings extended</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <aside aria-labelledby="shortcuts-heading" style="background:transparent; border:none; padding:0;">
            <h3 id="shortcuts-heading">Shortcuts</h3>
            <ul>
                <li><a href="profile.html">Edit profile</a></li>
                <li><a href="events.php">Upcoming events</a></li>
                <li><a href="logout.php">Secure Logout</a></li>
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
