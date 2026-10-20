<?php
require_once __DIR__ . '/auth.php';

// Enforce admin-only access (RBAC)
requireAdmin();

$adminName = $_SESSION['user_name'] ?? 'Administrator';
$adminEmail = $_SESSION['user_email'] ?? '';
$adminRole = $_SESSION['user_role'] ?? 'admin';

// -------------------------------------------------------------------------
// 1. SQL Aggregations: Dynamic Dashboard Statistics
// -------------------------------------------------------------------------
$stats = [
    'students' => 0,
    'admins' => 0,
    'total_events' => 0,
    'active_events' => 0,
    'registrations' => 0,
    'audit_logs' => 0
];

try {
    // Total Students
    $r = $conn->query("SELECT COUNT(*) AS c FROM students WHERE role = 'student'");
    $stats['students'] = $r->fetch_assoc()['c'] ?? 0;

    // Total Admins
    $r = $conn->query("SELECT COUNT(*) AS c FROM students WHERE role = 'admin'");
    $stats['admins'] = $r->fetch_assoc()['c'] ?? 0;

    // Total Events
    $r = $conn->query("SELECT COUNT(*) AS c FROM events");
    $stats['total_events'] = $r->fetch_assoc()['c'] ?? 0;

    // Active (Upcoming/Ongoing) Events
    $r = $conn->query("SELECT COUNT(*) AS c FROM events WHERE status IN ('Upcoming', 'Ongoing')");
    $stats['active_events'] = $r->fetch_assoc()['c'] ?? 0;

    // Total Registrations
    $r = $conn->query("SELECT COUNT(*) AS c FROM registrations");
    $stats['registrations'] = $r->fetch_assoc()['c'] ?? 0;

    // Total Audit Logs
    $r = $conn->query("SELECT COUNT(*) AS c FROM audit_logs");
    $stats['audit_logs'] = $r->fetch_assoc()['c'] ?? 0;

} catch (mysqli_sql_exception $e) {
    $dbError = $e->getMessage();
}

// -------------------------------------------------------------------------
// 2. Latest Event Registrations (JOIN query)
// -------------------------------------------------------------------------
$latestRegistrations = [];
try {
    $sql = "SELECT r.registration_id, s.full_name AS student_name, s.email, s.course, e.title AS event_title, e.event_date, r.registration_date, r.status 
            FROM registrations r 
            JOIN students s ON r.student_id = s.student_id 
            JOIN events e ON r.event_id = e.event_id 
            ORDER BY r.registration_date DESC LIMIT 5";
    $res = $conn->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $latestRegistrations[] = $row;
        }
    }
} catch (mysqli_sql_exception $e) {
    $latestRegistrations = [];
}

// -------------------------------------------------------------------------
// 3. Recent Audit Logs
// -------------------------------------------------------------------------
$recentLogs = [];
try {
    $lRes = $conn->query("SELECT * FROM audit_logs ORDER BY log_id DESC LIMIT 5");
    if ($lRes) {
        while ($row = $lRes->fetch_assoc()) {
            $recentLogs[] = $row;
        }
    }
} catch (mysqli_sql_exception $e) {
    $recentLogs = [];
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

    <!-- Header with Dynamic Role-Based Navigation -->
    <header>
        <div class="header-brand">
            <img src="CHARUSAT.jpg" alt="CHARUSAT University Banner" class="banner-img">
            <h1>CHARUSAT Student Hub</h1>
        </div>
        <nav aria-label="Primary">
            <?php echo renderRoleBasedNav('admin-dashboard.php'); ?>
        </nav>
    </header>

    <nav aria-label="Breadcrumb">
        <ol>
            <li><a href="index.html">Home</a></li>
            <li aria-current="page">Admin Dashboard</li>
        </ol>
    </nav>

    <!-- Sidebar with Dynamic Role-Based Controls -->
    <aside>
        <?php echo renderRoleBasedSidebar('admin-dashboard.php'); ?>
    </aside>

    <!-- Main Content -->
    <main id="main-content">
        <h2>Administrator Analytics & Monitoring Dashboard</h2>
        <p>Live administrative overview: real-time database aggregations, registration tracking, and security audit trail.</p>

        <!-- Admin Info Card -->
        <div class="user-status-card">
            <div>
                <strong>Active Session:</strong> <?php echo htmlspecialchars($adminName); ?>
                <span class="badge badge-admin"><?php echo htmlspecialchars($adminRole); ?></span>
                <br>
                <small style="color:#555;">Email: <?php echo htmlspecialchars($adminEmail); ?> | Role-Based Access Control: <strong>Enforced</strong></small>
            </div>
            <div>
                <a href="admin.php" class="btn-primary btn-sm" style="text-decoration:none; padding:7px 12px; margin-right:6px;">Manage Students</a>
                <a href="admin-events.php" class="btn-success btn-sm" style="text-decoration:none; padding:7px 12px; margin-right:6px;">Manage Events</a>
                <a href="logout.php" class="btn-danger">Sign Out</a>
            </div>
        </div>

        <!-- 1. DYNAMIC STATS / KPI CARDS (SQL AGGREGATION) -->
        <section aria-labelledby="kpi-heading" style="background:transparent; box-shadow:none; padding:0; margin-bottom:15px;">
            <h3 id="kpi-heading" style="margin-bottom:12px; color:#003366;">System Metrics & Statistics</h3>
            
            <div class="stat-grid">
                <div class="stat-card stat-students">
                    <div class="stat-label">Enrolled Students</div>
                    <div class="stat-number"><?php echo $stats['students']; ?></div>
                    <div class="stat-footer">
                        <a href="admin.php" style="color:#003366; text-decoration:none; font-weight:bold;">View student records &rarr;</a>
                    </div>
                </div>

                <div class="stat-card stat-events">
                    <div class="stat-label">Campus Events</div>
                    <div class="stat-number"><?php echo $stats['total_events']; ?></div>
                    <div class="stat-footer">
                        <strong><?php echo $stats['active_events']; ?></strong> active / upcoming events
                    </div>
                </div>

                <div class="stat-card stat-regs">
                    <div class="stat-label">Event Registrations</div>
                    <div class="stat-number"><?php echo $stats['registrations']; ?></div>
                    <div class="stat-footer">
                        Across all campus workshops
                    </div>
                </div>

                <div class="stat-card stat-admins">
                    <div class="stat-label">Admin Actions Logged</div>
                    <div class="stat-number"><?php echo $stats['audit_logs']; ?></div>
                    <div class="stat-footer">
                        <a href="audit-logs.php" style="color:#d9534f; text-decoration:none; font-weight:bold;">Trace audit history &rarr;</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- 2. LATEST REGISTRATIONS TABLE -->
        <section aria-labelledby="latest-reg-heading">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:12px;">
                <h3 id="latest-reg-heading" style="margin:0;">Recent Event Registrations</h3>
                <small style="color:#666;">Showing 5 latest student signups</small>
            </div>

            <?php if (!empty($latestRegistrations)): ?>
                <table>
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Student Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Course</th>
                            <th scope="col">Event Title</th>
                            <th scope="col">Event Date</th>
                            <th scope="col">Registration Date</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($latestRegistrations as $reg): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($reg['registration_id']); ?></td>
                                <td><strong><?php echo htmlspecialchars($reg['student_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($reg['email']); ?></td>
                                <td><?php echo htmlspecialchars($reg['course']); ?></td>
                                <td><?php echo htmlspecialchars($reg['event_title']); ?></td>
                                <td><?php echo htmlspecialchars($reg['event_date']); ?></td>
                                <td><?php echo htmlspecialchars($reg['registration_date']); ?></td>
                                <td>
                                    <span style="color:#28a745; font-weight:bold;">
                                        <?php echo htmlspecialchars($reg['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="padding:15px; color:#666; background:#f9f9f9; border-radius:4px;">No student registrations recorded yet.</p>
            <?php endif; ?>
        </section>

        <!-- 3. RECENT AUDIT LOG ACTIVITY -->
        <section aria-labelledby="audit-heading">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:12px;">
                <h3 id="audit-heading" style="margin:0;">Security & Management Audit Trail</h3>
                <a href="audit-logs.php" class="btn-sm btn-primary" style="text-decoration:none;">View Full Audit History &rarr;</a>
            </div>

            <?php if (!empty($recentLogs)): ?>
                <table>
                    <thead>
                        <tr>
                            <th scope="col">Log ID</th>
                            <th scope="col">Timestamp</th>
                            <th scope="col">User</th>
                            <th scope="col">Action Type</th>
                            <th scope="col">Action Details</th>
                            <th scope="col">IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentLogs as $log): 
                            $act = strtolower($log['action']);
                            $badgeClass = 'badge-log-login';
                            if (strpos($act, 'add') !== false || strpos($act, 'create') !== false) $badgeClass = 'badge-log-add';
                            elseif (strpos($act, 'update') !== false || strpos($act, 'edit') !== false) $badgeClass = 'badge-log-update';
                            elseif (strpos($act, 'delete') !== false) $badgeClass = 'badge-log-delete';
                            elseif (strpos($act, 'logout') !== false) $badgeClass = 'badge-log-logout';
                        ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($log['log_id']); ?></td>
                                <td><?php echo htmlspecialchars($log['created_at']); ?></td>
                                <td><strong><?php echo htmlspecialchars($log['user_name']); ?></strong></td>
                                <td>
                                    <span class="badge-log <?php echo $badgeClass; ?>">
                                        <?php echo htmlspecialchars($log['action']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($log['details']); ?></td>
                                <td><code><?php echo htmlspecialchars($log['ip_address']); ?></code></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="padding:15px; color:#666; background:#f9f9f9; border-radius:4px;">No audit logs recorded yet.</p>
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
