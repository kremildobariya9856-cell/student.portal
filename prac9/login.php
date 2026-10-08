<?php
require_once __DIR__ . '/db.php';

$email = $password = "";
$emailErr = $passwordErr = $loginMsg = "";
$loginSuccess = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if (empty($email)) {
        $emailErr = "Email is required.";
    }
    if (empty($password)) {
        $passwordErr = "Password is required.";
    }

    if (empty($emailErr) && empty($passwordErr)) {
        try {
            // Fetch user record by email using MySQLi prepared statement
            $stmt = $conn->prepare("SELECT student_id, full_name, email, password FROM students WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($user = $result->fetch_assoc()) {
                // Verify the hashed password with password_verify()
                if (password_verify($password, $user['password'])) {
                    $loginSuccess = true;
                    $loginMsg = "Welcome back, " . htmlspecialchars($user['full_name']) . "! Password successfully verified against the bcrypt hash.";
                } else {
                    $loginMsg = "Invalid password! The password does not match the hashed password.";
                }
            } else {
                $loginMsg = "No account found with this email address.";
            }
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            $loginMsg = "Database Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CHARUSAT Student Hub (Practical 9)</title>
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
                <li><a href="dashboard.html">Dashboard</a></li>
                <li><a href="events.php">Events</a></li>
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
            <li><a href="login.php">Student Login</a></li>
            <li><a href="events.php">Browse Events</a></li>
            <li><a href="contact.html">Contact Support</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main id="main-content">
        <h2>Student Login (Password Verification)</h2>
        <p>Demonstrating secure password verification using <code>password_verify()</code></p>

        <?php if (!empty($loginMsg)): ?>
            <div class="alert alert-<?php echo $loginSuccess ? 'success' : 'danger'; ?>">
                <?php echo $loginMsg; ?>
            </div>
        <?php endif; ?>

        <section>
            <h3>Sign in to your account</h3>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    <span class="error"><?php echo $emailErr; ?></span>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <span class="error"><?php echo $passwordErr; ?></span>
                </div>

                <button type="submit">Sign in</button>
            </form>
            <p style="margin-top:15px;">Don't have an account? <a href="registration.php">Register here</a>.</p>
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
