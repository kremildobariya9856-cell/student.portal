<?php
require_once __DIR__ . '/db.php';

// Configure session cookie settings
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Session timeout: 10 minutes (600 seconds) of inactivity
$timeoutDuration = 600;

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeoutDuration)) {
        // Inactivity timeout reached
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
        setcookie('remember_token', '', time() - 3600, '/');
        header("Location: login.php?msg=timeout");
        exit();
    }
    $_SESSION['last_activity'] = time();
} elseif (isset($_COOKIE['remember_token'])) {
    // Auto-login via remember-me token
    $token = $_COOKIE['remember_token'];
    $stmt = $conn->prepare("SELECT student_id, full_name, email, role FROM students WHERE remember_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['student_id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['last_activity'] = time();
    } else {
        setcookie('remember_token', '', time() - 3600, '/');
    }
    $stmt->close();
}

// Restrict access to logged-in users only
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?msg=unauthorized");
        exit();
    }
}

// Restrict access to admin role only
function requireAdmin() {
    requireLogin();
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        header("Location: dashboard.php?msg=forbidden");
        exit();
    }
}
?>
