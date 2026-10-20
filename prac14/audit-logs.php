<?php
require_once __DIR__ . '/auth.php';

// Enforce admin-only access
requireAdmin();

$adminName = $_SESSION['user_name'] ?? 'Administrator';

$search = trim($_GET['search'] ?? '');
$filterAction = trim($_GET['action_filter'] ?? '');

$where = [];
$types = "";
$params = [];

if (!empty($search)) {
    $where[] = "(user_name LIKE ? OR details LIKE ? OR ip_address LIKE ?)";
    $like = "%" . $search . "%";
    $types .= "sss";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if (!empty($filterAction)) {
    $where[] = "action = ?";
    $types .= "s";
    $params[] = $filterAction;
}

$sql = "SELECT * FROM audit_logs";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY log_id DESC";

$logs = [];
try {
    $stmt = $conn->prepare($sql);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $logs[] = $row;
    }
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    $logs = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs | CHARUSAT Student Hub</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <a href="#main-content" class="skip-link">Skip to main content</a>

    <!-- Header with Dynamic Role-Based Nav -->
    <header>
        <div class="header-brand">
            <img src="CHARUSAT.jpg" alt="CHARUSAT University Banner" class="banner-img">
            <h1>CHARUSAT Student Hub</h1>
        </div>
        <nav aria-label="Primary">
            <?php echo renderRoleBasedNav('audit-logs.php'); ?>
        </nav>
    </header>

    <nav aria-label="Breadcrumb">
        <ol>
            <li><a href="index.html">Home</a></li>
            <li><a href="admin-dashboard.php">Admin Dashboard</a></li>
            <li aria-current="page">Security Audit Logs</li>
        </ol>
    </nav>

    <!-- Sidebar with Dynamic Role-Based Controls -->
    <aside>
        <?php echo renderRoleBasedSidebar('audit-logs.php'); ?>
    </aside>

    <!-- Main Content -->
    <main id="main-content">
        <h2>Security & Administrative Audit Logs</h2>
        <p>Complete traceability and accountability log recording all critical user and administrative actions.</p>

        <!-- Search and Filter Form -->
        <section class="search-filter-card">
            <h3 style="margin-bottom:12px; font-size:1.15rem; color:#003366;">Filter Audit Trail</h3>
            <form action="audit-logs.php" method="GET">
                <div class="search-filter-grid" style="grid-template-columns: 2fr 1fr auto;">
                    <div class="filter-group">
                        <label for="search">Keyword Search</label>
                        <input type="search" id="search" name="search" placeholder="Search by user, description, or IP..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <div class="filter-group">
                        <label for="action_filter">Action Type</label>
                        <select id="action_filter" name="action_filter">
                            <option value="">All Actions</option>
                            <option value="LOGIN" <?php echo ($filterAction === 'LOGIN') ? 'selected' : ''; ?>>LOGIN</option>
                            <option value="LOGOUT" <?php echo ($filterAction === 'LOGOUT') ? 'selected' : ''; ?>>LOGOUT</option>
                            <option value="ADD_STUDENT" <?php echo ($filterAction === 'ADD_STUDENT') ? 'selected' : ''; ?>>ADD_STUDENT</option>
                            <option value="UPDATE_STUDENT" <?php echo ($filterAction === 'UPDATE_STUDENT') ? 'selected' : ''; ?>>UPDATE_STUDENT</option>
                            <option value="DELETE_STUDENT" <?php echo ($filterAction === 'DELETE_STUDENT') ? 'selected' : ''; ?>>DELETE_STUDENT</option>
                            <option value="ADD_EVENT" <?php echo ($filterAction === 'ADD_EVENT') ? 'selected' : ''; ?>>ADD_EVENT</option>
                            <option value="UPDATE_EVENT" <?php echo ($filterAction === 'UPDATE_EVENT') ? 'selected' : ''; ?>>UPDATE_EVENT</option>
                            <option value="DELETE_EVENT" <?php echo ($filterAction === 'DELETE_EVENT') ? 'selected' : ''; ?>>DELETE_EVENT</option>
                        </select>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn-primary">Apply Filter</button>
                        <a href="audit-logs.php" class="btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </section>

        <!-- Audit Logs Table -->
        <section aria-labelledby="logs-table-heading">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:12px;">
                <h3 id="logs-table-heading" style="margin:0;">Audit Trail Entries</h3>
                <span style="font-size:14px; color:#555;">
                    Total Records: <strong><?php echo count($logs); ?></strong>
                </span>
            </div>

            <?php if (!empty($logs)): ?>
                <table>
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Timestamp</th>
                            <th scope="col">User</th>
                            <th scope="col">Action</th>
                            <th scope="col">Action Details</th>
                            <th scope="col">IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): 
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
                <p style="padding:25px; text-align:center; color:#777; background:#f9f9f9; border-radius:4px;">
                    No audit log records found matching your filter criteria.
                    <br><a href="audit-logs.php" style="color:#003366; text-decoration:underline; font-weight:bold; margin-top:8px; display:inline-block;">Reset Filter</a>
                </p>
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
