<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$message = "";
$messageType = "";

// Handle Event Registration via POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["event_id"]) && isset($_POST["student_id"])) {
    $eventId = (int)$_POST["event_id"];
    $studentId = (int)$_POST["student_id"];

    if ($eventId > 0 && $studentId > 0) {
        try {
            // Check if already registered
            $checkStmt = $conn->prepare("SELECT registration_id FROM registrations WHERE student_id = ? AND event_id = ?");
            $checkStmt->bind_param("ii", $studentId, $eventId);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows > 0) {
                $message = "You are already registered for this event!";
                $messageType = "danger";
            } else {
                $regStmt = $conn->prepare("INSERT INTO registrations (student_id, event_id, status) VALUES (?, ?, 'Confirmed')");
                $regStmt->bind_param("ii", $studentId, $eventId);
                $regStmt->execute();
                $regStmt->close();
                $message = "Successfully registered for the event!";
                $messageType = "success";
            }
            $checkStmt->close();
        } catch (mysqli_sql_exception $e) {
            $message = "Database Error: " . $e->getMessage();
            $messageType = "danger";
        }
    } else {
        $message = "Please select a valid student and event.";
        $messageType = "danger";
    }
}

// Fetch events from MySQL using MySQLi
$events = [];
try {
    $result = $conn->query("SELECT * FROM events ORDER BY event_date ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
    }
} catch (mysqli_sql_exception $e) {
    $events = [];
}

// Fetch students for dropdown
$studentsList = [];
try {
    $sRes = $conn->query("SELECT student_id, full_name, email FROM students ORDER BY full_name ASC");
    if ($sRes) {
        while ($row = $sRes->fetch_assoc()) {
            $studentsList[] = $row;
        }
    }
} catch (mysqli_sql_exception $e) {
    $studentsList = [];
}

// Fetch registrations
$allRegistrations = [];
try {
    $rRes = $conn->query("SELECT r.registration_id, s.full_name AS student_name, s.email, e.title AS event_title, e.event_date, r.registration_date, r.status 
                          FROM registrations r 
                          JOIN students s ON r.student_id = s.student_id 
                          JOIN events e ON r.event_id = e.event_id 
                          ORDER BY r.registration_date DESC");
    if ($rRes) {
        while ($row = $rRes->fetch_assoc()) {
            $allRegistrations[] = $row;
        }
    }
} catch (mysqli_sql_exception $e) {
    $allRegistrations = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events | CHARUSAT Student Hub</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .event-card-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        .event-box {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
            display: flex;
            flex-direction: column;
        }
        .event-poster-hero {
            width: 100%;
            height: 180px;
            object-fit: cover;
            background: #003366;
            display: block;
        }
        .event-box-content {
            padding: 16px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header>
        <div class="header-brand">
            <img src="CHARUSAT.jpg" alt="CHARUSAT University Banner" class="banner-img">
            <h1>CHARUSAT Student Hub</h1>
        </div>
        <?= renderRoleBasedNav('events.php') ?>
    </header>

    <!-- Sidebar -->
    <?= renderRoleBasedSidebar('events.php') ?>

    <!-- Main Content -->
    <main id="main-content">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <div>
                <h2>Campus Events & Hackathons</h2>
                <p>Explore upcoming university events, tech fests, workshops, and sports tournaments.</p>
            </div>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <div>
                    <a href="admin-events.php" class="btn-success btn-sm" style="text-decoration:none; padding:8px 14px; font-weight:bold; border-radius:4px;">+ Admin Event Management</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Event Cards Grid -->
        <section>
            <h3>All Scheduled Events</h3>
            <div class="event-card-container">
                <?php if (!empty($events)): ?>
                    <?php foreach ($events as $ev): ?>
                        <div class="event-box">
                            <?php if (!empty($ev['poster']) && file_exists(__DIR__ . '/uploads/' . $ev['poster'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($ev['poster']); ?>" alt="<?php echo htmlspecialchars($ev['title']); ?> Poster" class="event-poster-hero">
                            <?php else: ?>
                                <div style="height:140px; background:#e2e8f0; display:flex; align-items:center; justify-content:center; color:#64748b; font-weight:bold;">
                                    <?php echo htmlspecialchars($ev['category']); ?> Event
                                </div>
                            <?php endif; ?>

                            <div class="event-box-content">
                                <div>
                                    <div style="display:flex; justify-content:space-between; align-items:start; gap:8px;">
                                        <h4 style="margin:0; font-size:1.15rem; color:#003366;"><?php echo htmlspecialchars($ev['title']); ?></h4>
                                        <span class="badge badge-<?php echo strtolower(htmlspecialchars($ev['status'])); ?>">
                                            <?php echo htmlspecialchars($ev['status']); ?>
                                        </span>
                                    </div>

                                    <p class="event-meta" style="margin:6px 0;">
                                        <strong>Category:</strong> <?php echo htmlspecialchars($ev['category']); ?><br>
                                        <strong>Date & Time:</strong> <?php echo htmlspecialchars($ev['event_date']); ?> (<?php echo htmlspecialchars($ev['event_time']); ?>)<br>
                                        <strong>Venue:</strong> <?php echo htmlspecialchars($ev['venue']); ?> | <strong>Seats:</strong> <?php echo htmlspecialchars($ev['capacity']); ?>
                                    </p>

                                    <p style="font-size:14px; color:#444; margin-bottom:12px;"><?php echo htmlspecialchars($ev['description']); ?></p>
                                </div>

                                <!-- Quick Register Form -->
                                <form action="events.php" method="POST" style="margin-top:auto; padding-top:10px; border-top:1px solid #eee;">
                                    <input type="hidden" name="event_id" value="<?php echo $ev['event_id']; ?>">
                                    <div class="field" style="margin-bottom:8px;">
                                        <label for="sid_<?php echo $ev['event_id']; ?>" style="font-size:12px;">Select Student to Register:</label>
                                        <select name="student_id" id="sid_<?php echo $ev['event_id']; ?>" required style="padding:6px; font-size:13px;">
                                            <option value="">-- Choose Student --</option>
                                            <?php foreach ($studentsList as $stu): ?>
                                                <option value="<?php echo $stu['student_id']; ?>">
                                                    <?php echo htmlspecialchars($stu['full_name']); ?> (<?php echo htmlspecialchars($stu['email']); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn-primary" style="width:100%; padding:8px; font-size:13px;">Register for Event</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No events found in database.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Registrations Table -->
        <section>
            <h3>Confirmed Event Registrations</h3>
            <?php if (!empty($allRegistrations)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Reg ID</th>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Event Title</th>
                            <th>Event Date</th>
                            <th>Registration Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allRegistrations as $reg): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($reg['registration_id']); ?></td>
                                <td><strong><?php echo htmlspecialchars($reg['student_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($reg['email']); ?></td>
                                <td><?php echo htmlspecialchars($reg['event_title']); ?></td>
                                <td><?php echo htmlspecialchars($reg['event_date']); ?></td>
                                <td><?php echo htmlspecialchars($reg['registration_date']); ?></td>
                                <td><span style="color:#28a745; font-weight:bold;"><?php echo htmlspecialchars($reg['status']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No registrations recorded yet.</p>
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

</body>
</html>
