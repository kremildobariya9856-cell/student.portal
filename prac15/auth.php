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
        // Record timeout in audit logs
        recordAuditLog($conn, 'TIMEOUT', 'User session expired due to 10 minutes of inactivity', $_SESSION['user_id'], $_SESSION['user_name']);

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

        recordAuditLog($conn, 'LOGIN_REMEMBER', 'User auto-logged in via Remember-Me token', $user['student_id'], $user['full_name']);
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

// -------------------------------------------------------------
// Dynamic Role-Based Navigation Menu Renderers
// -------------------------------------------------------------
function renderRoleBasedNav($activePage = '') {
    $role = $_SESSION['user_role'] ?? 'guest';
    $html = '<ul>';

    if ($role === 'admin') {
        $links = [
            'admin-dashboard.php' => 'Admin Dashboard',
            'admin.php' => 'Manage Students',
            'admin-events.php' => 'Manage Events',
            'audit-logs.php' => 'Audit Logs',
            'events.php' => 'Public Events',
            'dashboard.php' => 'Student View',
            'logout.php' => 'Logout'
        ];
    } elseif ($role === 'student') {
        $links = [
            'index.html' => 'Home',
            'dashboard.php' => 'Dashboard',
            'events.php' => 'Events',
            'profile.html' => 'Profile',
            'contact.html' => 'Contact',
            'logout.php' => 'Logout'
        ];
    } else {
        $links = [
            'index.html' => 'Home',
            'about.html' => 'About',
            'registration.php' => 'Register',
            'login.php' => 'Login',
            'events.php' => 'Events',
            'contact.html' => 'Contact',
            'faq.html' => 'FAQ',
            'feedback.html' => 'Feedback'
        ];
    }

    $currentScript = basename($_SERVER['PHP_SELF']);
    foreach ($links as $url => $label) {
        $isCurrent = ($activePage === $url || $currentScript === $url);
        $aria = $isCurrent ? ' aria-current="page"' : '';
        $style = ($url === 'logout.php') ? ' style="background:#c9302c; color:white;"' : '';
        $html .= "<li><a href=\"{$url}\"{$aria}{$style}>{$label}</a></li>";
    }

    $html .= '</ul>';
    return $html;
}

function renderRoleBasedSidebar($activePage = '') {
    $role = $_SESSION['user_role'] ?? 'guest';
    $html = '';

    if ($role === 'admin') {
        $html .= '<h2>Admin Controls</h2><ul>';
        $links = [
            'admin-dashboard.php' => 'Dashboard Overview',
            'admin.php' => 'Student Management (CRUD)',
            'admin-events.php' => 'Event Management (AJAX)',
            'audit-logs.php' => 'Security Audit Logs',
            'events.php' => 'Public Events View',
            'dashboard.php' => 'Switch to Student View',
            'logout.php' => 'Logout'
        ];
    } elseif ($role === 'student') {
        $html .= '<h2>Student Menu</h2><ul>';
        $links = [
            'dashboard.php' => 'Dashboard Home',
            'profile.html' => 'My Profile',
            'events.php' => 'Upcoming Events',
            'contact.html' => 'Contact Support',
            'logout.php' => 'Logout'
        ];
    } else {
        $html .= '<h2>Quick Links</h2><ul>';
        $links = [
            'index.html' => 'Home',
            'registration.php' => 'New Registration',
            'login.php' => 'Student / Admin Login',
            'events.php' => 'Browse Events',
            'contact.html' => 'Contact Support'
        ];
    }

    foreach ($links as $url => $label) {
        $html .= "<li><a href=\"{$url}\">{$label}</a></li>";
    }

    $html .= '</ul>';
    return $html;
}
?>
