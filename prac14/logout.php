<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear remember token and record audit log in database if user is logged in
if (isset($_SESSION['user_id'])) {
    recordAuditLog($conn, 'LOGOUT', "User '{$_SESSION['user_name']}' logged out", $_SESSION['user_id'], $_SESSION['user_name']);
    try {
        $stmt = $conn->prepare("UPDATE students SET remember_token = NULL WHERE student_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        // Continue logout even if db error occurs
    }
}

// Unset all session variables
$_SESSION = [];

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Delete remember-me cookie
setcookie('remember_token', '', time() - 3600, '/');

// Redirect to login page
header("Location: login.php?msg=logout");
exit();
?>
