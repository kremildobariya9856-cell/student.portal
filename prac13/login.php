<?php
require_once __DIR__ . '/auth.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}

$email = $password = "";
$emailErr = $passwordErr = $loginMsg = "";
$msgType = "danger";

// Check for status messages
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'timeout') {
        $loginMsg = "Your session has expired due to 10 minutes of inactivity. Please log in again.";
        $msgType = "warning";
    } elseif ($_GET['msg'] === 'unauthorized') {
        $loginMsg = "Please log in to access your dashboard.";
        $msgType = "warning";
    } elseif ($_GET['msg'] === 'logout') {
        $loginMsg = "You have been logged out securely.";
        $msgType = "success";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");
    $remember = isset($_POST["remember"]);

    if (empty($email)) {
        $emailErr = "Email is required.";
    }
    if (empty($password)) {
        $passwordErr = "Password is required.";
    }

    if (empty($emailErr) && empty($passwordErr)) {
        try {
            $stmt = $conn->prepare("SELECT student_id, full_name, email, password, role FROM students WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($user = $result->fetch_assoc()) {
                // Verify password against hash
                if (password_verify($password, $user['password'])) {
                    // Regenerate session ID to prevent session fixation
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['student_id'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['last_activity'] = time();

                    // Handle Remember-Me token
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        $updateStmt = $conn->prepare("UPDATE students SET remember_token = ? WHERE student_id = ?");
                        $updateStmt->bind_param("si", $token, $user['student_id']);
                        $updateStmt->execute();
                        $updateStmt->close();

                        // Set cookie for 30 days
                        setcookie('remember_token', $token, time() + (30 * 86400), '/', '', false, true);
                    } else {
                        $clearStmt = $conn->prepare("UPDATE students SET remember_token = NULL WHERE student_id = ?");
                        $clearStmt->bind_param("i", $user['student_id']);
                        $clearStmt->execute();
                        $clearStmt->close();

                        setcookie('remember_token', '', time() - 3600, '/');
                    }

                    // Role-based redirection
                    if ($user['role'] === 'admin') {
                        header("Location: admin.php");
                    } else {
                        header("Location: dashboard.php");
                    }
                    exit();

                } else {
                    $loginMsg = "Invalid password! The password does not match.";
                    $msgType = "danger";
                }
            } else {
                $loginMsg = "No account found with this email address.";
                $msgType = "danger";
            }
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            $loginMsg = "Database Error: " . $e->getMessage();
            $msgType = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CHARUSAT Student Hub</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

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
                <li><a href="login.php" aria-current="page">Login</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="events.php">Events</a></li>
                <li><a href="profile.html">Profile</a></li>
                <li><a href="contact.html">Contact</a></li>
                <li><a href="admin.php">Admin</a></li>
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
            <li><a href="login.php">Login Portal</a></li>
            <li><a href="events.php">Browse Events</a></li>
            <li><a href="contact.html">Contact Support</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main id="main-content">
        <h2>Secure User Login</h2>
        <p>Sign in to access your role-based dashboard with session management.</p>

        <?php if (!empty($loginMsg)): ?>
            <div class="alert alert-<?php echo $msgType; ?>">
                <?php echo htmlspecialchars($loginMsg); ?>
            </div>
        <?php endif; ?>

        <section>
            <h3>Sign in to your account</h3>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="field">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    <span class="error"><?php echo $emailErr; ?></span>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <span class="error"><?php echo $passwordErr; ?></span>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="remember" name="remember" value="1">
                    <label for="remember">Remember me on this device (30 days)</label>
                </div>

                <button type="submit">Sign in</button>
            </form>

            <p style="margin-top:15px;">Don't have an account? <a href="registration.php">Register here</a>.</p>
        </section>

        <section style="background:#f9fbfe; border:1px solid #d8e5f2;">
            <h3>Demo Test Accounts</h3>
            <p>You can test role-based access control with these pre-seeded accounts:</p>
            <ul style="margin-left: 20px; margin-top: 8px;">
                <li><strong>Student Account:</strong> <code>25dce022@charusat.edu.in</code> | Password: <code>password123</code> (Redirects to Student Dashboard)</li>
                <li><strong>Admin Account:</strong> <code>admin@charusat.edu.in</code> | Password: <code>admin123</code> (Redirects to Admin Dashboard)</li>
            </ul>
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

</body>
</html>
