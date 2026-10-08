<?php
require_once __DIR__ . '/db.php';

$message = "";
$messageType = "";

// Handle Event Registration via POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["event_id"]) && isset($_POST["student_id"])) {
    $eventId = (int)$_POST["event_id"];
    $studentId = (int)$_POST["student_id"];

    if ($eventId > 0 && $studentId > 0) {
        try {
            // Check if already registered
            $checkStmt = $pdo->prepare("SELECT registration_id FROM registrations WHERE student_id = :sid AND event_id = :eid");
            $checkStmt->execute([':sid' => $studentId, ':eid' => $eventId]);

            if ($checkStmt->rowCount() > 0) {
                $message = "You are already registered for this event!";
                $messageType = "danger";
            } else {
                // Insert registration with PDO Prepared Statement
                $regStmt = $pdo->prepare("INSERT INTO registrations (student_id, event_id, status) VALUES (:sid, :eid, 'Confirmed')");
                $regStmt->execute([':sid' => $studentId, ':eid' => $eventId]);
                $message = "Successfully registered for the event!";
                $messageType = "success";
            }
        } catch (PDOException $e) {
            $message = "Database Error: " . $e->getMessage();
            $messageType = "danger";
        }
    } else {
        $message = "Please select a valid student and event.";
        $messageType = "danger";
    }
}

// Fetch events from MySQL using PDO
$events = [];
try {
    $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date ASC");
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    $events = [];
}

// Fetch students for the dropdown
$studentsList = [];
try {
    $sStmt = $pdo->query("SELECT student_id, full_name, email FROM students ORDER BY full_name ASC");
    $studentsList = $sStmt->fetchAll();
} catch (PDOException $e) {
    $studentsList = [];
}

// Fetch registered events list
$allRegistrations = [];
try {
    $rStmt = $pdo->query("SELECT r.registration_id, s.full_name AS student_name, s.email, e.title AS event_title, e.event_date, r.registration_date, r.status 
                          FROM registrations r 
                          JOIN students s ON r.student_id = s.student_id 
                          JOIN events e ON r.event_id = e.event_id 
                          ORDER BY r.registration_date DESC");
    $allRegistrations = $rStmt->fetchAll();
} catch (PDOException $e) {
    $allRegistrations = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events | CHARUSAT Student Hub (Practical 8)</title>
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
                <li><a href="login.html">Login</a></li>
                <li><a href="dashboard.html">Dashboard</a></li>
                <li><a href="events.php" aria-current="page">Events</a></li>
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
            <li><a href="events.php">Browse Events</a></li>
            <li><a href="login.html">Student Login</a></li>
            <li><a href="contact.html">Contact Support</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main id="main-content">
        <h2>Campus Events (MySQL & PDO)</h2>
        <p>Fetched dynamically from MySQL `events` table using PDO</p>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Event Cards Grid -->
        <section>
            <h3>Upcoming Events</h3>
            <div class="events-grid">
                <?php if (!empty($events)): ?>
                    <?php foreach ($events as $ev): ?>
                        <div class="event-card">
                            <h4><?php echo htmlspecialchars($ev['title']); ?></h4>
                            <p class="event-meta">
                                <strong>Category:</strong> <?php echo htmlspecialchars($ev['category']); ?> | 
                                <strong>Date:</strong> <?php echo htmlspecialchars($ev['event_date']); ?> (<?php echo htmlspecialchars($ev['event_time']); ?>)
                            </p>
                            <p><strong>Venue:</strong> <?php echo htmlspecialchars($ev['venue']); ?> | <strong>Capacity:</strong> <?php echo htmlspecialchars($ev['capacity']); ?></p>
                            <p><?php echo htmlspecialchars($ev['description']); ?></p>

                            <!-- Quick Register Form -->
                            <form action="events.php" method="POST" style="margin-top:10px;">
                                <input type="hidden" name="event_id" value="<?php echo $ev['event_id']; ?>">
                                <div class="field" style="margin-bottom:8px;">
                                    <label for="sid_<?php echo $ev['event_id']; ?>" style="font-size:13px;">Select Student:</label>
                                    <select name="student_id" id="sid_<?php echo $ev['event_id']; ?>" required style="padding:6px; font-size:13px;">
                                        <option value="">-- Choose Student --</option>
                                        <?php foreach ($studentsList as $stu): ?>
                                            <option value="<?php echo $stu['student_id']; ?>">
                                                <?php echo htmlspecialchars($stu['full_name']); ?> (<?php echo htmlspecialchars($stu['email']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" style="padding:6px 12px; font-size:13px;">Register for Event</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No events found in database.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Registrations Table -->
        <section>
            <h3>Event Registrations (JOIN Query: Students & Events)</h3>
            <?php if (!empty($allRegistrations)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Reg ID</th>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Event Title</th>
                            <th>Event Date</th>
                            <th>Registration Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allRegistrations as $reg): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($reg['registration_id']); ?></td>
                                <td><?php echo htmlspecialchars($reg['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($reg['email']); ?></td>
                                <td><?php echo htmlspecialchars($reg['event_title']); ?></td>
                                <td><?php echo htmlspecialchars($reg['event_date']); ?></td>
                                <td><?php echo htmlspecialchars($reg['registration_date']); ?></td>
                                <td><span style="color:green; font-weight:bold;"><?php echo htmlspecialchars($reg['status']); ?></span></td>
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
