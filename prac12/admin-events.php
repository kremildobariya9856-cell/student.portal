<?php
require_once __DIR__ . '/auth.php';

// Enforce admin-only access
requireAdmin();

$adminName = $_SESSION['user_name'] ?? 'Administrator';
$adminEmail = $_SESSION['user_email'] ?? '';
$adminRole = $_SESSION['user_role'] ?? 'admin';

$successMsg = "";
$errorMsg = "";
$uploadDir = __DIR__ . '/uploads';

// Allowed file types and max file size (2 MB)
$allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
$allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$maxFileSize = 2 * 1024 * 1024; // 2MB in bytes

// -------------------------------------------------------------------------
// Helper: Validate and upload event poster
// -------------------------------------------------------------------------
function handlePosterUpload($fileInputName, &$errorMsg, $uploadDir, $allowedExtensions, $allowedMimes, $maxFileSize) {
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // No file uploaded
    }

    $file = $_FILES[$fileInputName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = "File upload error code: " . $file['error'];
        return false;
    }

    // 1. Validate file size
    if ($file['size'] > $maxFileSize) {
        $sizeMB = round($file['size'] / (1024 * 1024), 2);
        $errorMsg = "Poster file size exceeds the 2MB limit. (Uploaded: {$sizeMB} MB)";
        return false;
    }

    // 2. Validate file extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExtensions)) {
        $errorMsg = "Invalid file type. Only JPG, JPEG, PNG, WEBP, and GIF images are allowed.";
        return false;
    }

    // 3. Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedMimes)) {
        $errorMsg = "Security validation failed: The uploaded file is not a valid image.";
        return false;
    }

    // Generate safe unique filename
    $newFileName = uniqid('poster_', true) . '.' . $ext;
    $targetPath = $uploadDir . '/' . $newFileName;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $newFileName;
    } else {
        $errorMsg = "Failed to save the uploaded poster file to the server.";
        return false;
    }
}

// -------------------------------------------------------------------------
// Handle POST Requests: Add, Update, Delete
// -------------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    // 1. ADD (Create Event)
    if ($action === "add") {
        $title = trim($_POST["title"] ?? "");
        $category = trim($_POST["category"] ?? "");
        $description = trim($_POST["description"] ?? "");
        $eventDate = trim($_POST["event_date"] ?? "");
        $eventTime = trim($_POST["event_time"] ?? "");
        $venue = trim($_POST["venue"] ?? "");
        $capacity = intval($_POST["capacity"] ?? 100);
        $status = trim($_POST["status"] ?? "Upcoming");

        if (empty($title) || empty($category) || empty($eventDate) || empty($eventTime) || empty($venue)) {
            $errorMsg = "All event details (Title, Category, Date, Time, Venue) are required.";
        } else {
            $uploadedPoster = handlePosterUpload('poster', $errorMsg, $uploadDir, $allowedExtensions, $allowedMimes, $maxFileSize);

            if ($uploadedPoster !== false) { // false means upload failed validation
                try {
                    $ins = $conn->prepare("INSERT INTO events (title, category, description, event_date, event_time, venue, capacity, poster, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $ins->bind_param("ssssssiss", $title, $category, $description, $eventDate, $eventTime, $venue, $capacity, $uploadedPoster, $status);
                    $ins->execute();
                    $ins->close();
                    $successMsg = "Event '$title' was added successfully with poster validation!";
                } catch (mysqli_sql_exception $e) {
                    $errorMsg = "Database error: " . $e->getMessage();
                }
            }
        }
    }

    // 2. UPDATE (Edit Event)
    elseif ($action === "update") {
        $eventId = intval($_POST["event_id"] ?? 0);
        $title = trim($_POST["title"] ?? "");
        $category = trim($_POST["category"] ?? "");
        $description = trim($_POST["description"] ?? "");
        $eventDate = trim($_POST["event_date"] ?? "");
        $eventTime = trim($_POST["event_time"] ?? "");
        $venue = trim($_POST["venue"] ?? "");
        $capacity = intval($_POST["capacity"] ?? 100);
        $status = trim($_POST["status"] ?? "Upcoming");

        if ($eventId <= 0 || empty($title) || empty($category) || empty($eventDate) || empty($eventTime) || empty($venue)) {
            $errorMsg = "All event details are required for updating.";
        } else {
            // Check if a new poster file was submitted
            $newPoster = handlePosterUpload('poster', $errorMsg, $uploadDir, $allowedExtensions, $allowedMimes, $maxFileSize);

            if ($newPoster !== false) {
                try {
                    if ($newPoster !== null) {
                        // Delete old poster file if exists
                        $oldStmt = $conn->prepare("SELECT poster FROM events WHERE event_id = ?");
                        $oldStmt->bind_param("i", $eventId);
                        $oldStmt->execute();
                        $oldRes = $oldStmt->get_result()->fetch_assoc();
                        $oldStmt->close();

                        if (!empty($oldRes['poster']) && file_exists($uploadDir . '/' . $oldRes['poster'])) {
                            @unlink($uploadDir . '/' . $oldRes['poster']);
                        }

                        $upd = $conn->prepare("UPDATE events SET title = ?, category = ?, description = ?, event_date = ?, event_time = ?, venue = ?, capacity = ?, poster = ?, status = ? WHERE event_id = ?");
                        $upd->bind_param("ssssssissi", $title, $category, $description, $eventDate, $eventTime, $venue, $capacity, $newPoster, $status, $eventId);
                    } else {
                        // Keep current poster
                        $upd = $conn->prepare("UPDATE events SET title = ?, category = ?, description = ?, event_date = ?, event_time = ?, venue = ?, capacity = ?, status = ? WHERE event_id = ?");
                        $upd->bind_param("ssssssisi", $title, $category, $description, $eventDate, $eventTime, $venue, $capacity, $status, $eventId);
                    }

                    $upd->execute();
                    $upd->close();
                    $successMsg = "Event '$title' updated successfully!";
                } catch (mysqli_sql_exception $e) {
                    $errorMsg = "Database error: " . $e->getMessage();
                }
            }
        }
    }

    // 3. DELETE (Delete Event)
    elseif ($action === "delete") {
        $eventId = intval($_POST["event_id"] ?? 0);
        if ($eventId > 0) {
            try {
                // Delete poster file from uploads
                $pStmt = $conn->prepare("SELECT poster FROM events WHERE event_id = ?");
                $pStmt->bind_param("i", $eventId);
                $pStmt->execute();
                $pRow = $pStmt->get_result()->fetch_assoc();
                $pStmt->close();

                if (!empty($pRow['poster']) && file_exists($uploadDir . '/' . $pRow['poster'])) {
                    @unlink($uploadDir . '/' . $pRow['poster']);
                }

                $del = $conn->prepare("DELETE FROM events WHERE event_id = ?");
                $del->bind_param("i", $eventId);
                $del->execute();
                if ($del->affected_rows > 0) {
                    $successMsg = "Event #$eventId and its poster were deleted successfully!";
                } else {
                    $errorMsg = "Event not found or already deleted.";
                }
                $del->close();
            } catch (mysqli_sql_exception $e) {
                $errorMsg = "Database error: " . $e->getMessage();
            }
        }
    }
}

// -------------------------------------------------------------------------
// Handle Edit Mode (Fetch event data)
// -------------------------------------------------------------------------
$editEvent = null;
if (isset($_GET["action"]) && $_GET["action"] === "edit" && isset($_GET["id"])) {
    $editId = intval($_GET["id"]);
    $stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $editEvent = $row;
    }
    $stmt->close();
}

// -------------------------------------------------------------------------
// Handle Filter and Search
// -------------------------------------------------------------------------
$search = trim($_GET["search"] ?? "");
$filterCategory = trim($_GET["category"] ?? "");
$filterStatus = trim($_GET["status"] ?? "");

$whereConditions = [];
$bindTypes = "";
$bindParams = [];

if (!empty($search)) {
    $whereConditions[] = "(title LIKE ? OR venue LIKE ?)";
    $likeSearch = "%" . $search . "%";
    $bindTypes .= "ss";
    $bindParams[] = $likeSearch;
    $bindParams[] = $likeSearch;
}

if (!empty($filterCategory)) {
    $whereConditions[] = "category = ?";
    $bindTypes .= "s";
    $bindParams[] = $filterCategory;
}

if (!empty($filterStatus)) {
    $whereConditions[] = "status = ?";
    $bindTypes .= "s";
    $bindParams[] = $filterStatus;
}

$querySql = "SELECT * FROM events";
if (!empty($whereConditions)) {
    $querySql .= " WHERE " . implode(" AND ", $whereConditions);
}
$querySql .= " ORDER BY event_date ASC, event_id DESC";

$eventsList = [];
try {
    $stmt = $conn->prepare($querySql);
    if (!empty($bindTypes)) {
        $stmt->bind_param($bindTypes, ...$bindParams);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $eventsList[] = $row;
    }
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    $errorMsg = "Error loading events: " . $e->getMessage();
}

$totalEvents = 0;
try {
    $cntRes = $conn->query("SELECT COUNT(*) AS total FROM events");
    if ($cntRow = $cntRes->fetch_assoc()) {
        $totalEvents = intval($cntRow['total']);
    }
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management | CHARUSAT Student Hub</title>
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
                <li><a href="dashboard.php">Student Dashboard</a></li>
                <li><a href="admin.php">Manage Students</a></li>
                <li><a href="admin-events.php" aria-current="page">Manage Events</a></li>
                <li><a href="events.php">Browse Events</a></li>
                <li><a href="contact.html">Contact</a></li>
                <li><a href="logout.php" style="background:#c9302c; color:white;">Logout</a></li>
            </ul>
        </nav>
    </header>

    <nav aria-label="Breadcrumb">
        <ol>
            <li><a href="index.html">Home</a></li>
            <li><a href="admin.php">Admin Panel</a></li>
            <li aria-current="page">Event Management CRUD</li>
        </ol>
    </nav>

    <!-- Sidebar -->
    <aside>
        <h2>Admin Menu</h2>
        <ul>
            <li><a href="admin-events.php">Manage Events (CRUD)</a></li>
            <li><a href="admin.php">Manage Students</a></li>
            <li><a href="events.php">Public Events View</a></li>
            <li><a href="dashboard.php">Student View</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main id="main-content">
        <h2>Event Management Module</h2>
        <p>Admin control to add, view, update, and delete campus events with validated poster uploads.</p>

        <!-- Active Admin Status Card -->
        <div class="user-status-card">
            <div>
                <strong>Active Admin:</strong> <?php echo htmlspecialchars($adminName); ?>
                <span class="badge badge-admin"><?php echo htmlspecialchars($adminRole); ?></span>
                <br>
                <small style="color:#555;">Email: <?php echo htmlspecialchars($adminEmail); ?> | Poster Validation: Enabled (Max 2MB, JPG/PNG/WEBP)</small>
            </div>
            <div>
                <a href="#add-event-section" class="btn-success btn-sm" style="text-decoration:none; padding:8px 14px; font-weight:bold; border-radius:4px;">+ Create New Event</a>
                <a href="logout.php" class="btn-danger">Sign Out</a>
            </div>
        </div>

        <!-- UI Feedback Alerts -->
        <?php if (!empty($successMsg)): ?>
            <div class="alert alert-success">
                <strong>Success:</strong> <?php echo htmlspecialchars($successMsg); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger">
                <strong>Error:</strong> <?php echo htmlspecialchars($errorMsg); ?>
            </div>
        <?php endif; ?>

        <!-- EDIT EVENT FORM (Visible when ?action=edit&id=X) -->
        <?php if ($editEvent): ?>
            <section class="crud-form-card" style="border-top-color:#ff9800;">
                <h3>Edit Event: <?php echo htmlspecialchars($editEvent['title']); ?> (#<?php echo $editEvent['event_id']; ?>)</h3>
                <form action="admin-events.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="event_id" value="<?php echo $editEvent['event_id']; ?>">

                    <div class="crud-form-grid">
                        <div class="field">
                            <label for="edit_title">Event Title</label>
                            <input type="text" id="edit_title" name="title" value="<?php echo htmlspecialchars($editEvent['title']); ?>" required>
                        </div>

                        <div class="field">
                            <label for="edit_category">Category</label>
                            <select id="edit_category" name="category" required>
                                <option value="Technical" <?php echo ($editEvent['category'] === 'Technical') ? 'selected' : ''; ?>>Technical</option>
                                <option value="Sports" <?php echo ($editEvent['category'] === 'Sports') ? 'selected' : ''; ?>>Sports</option>
                                <option value="Cultural" <?php echo ($editEvent['category'] === 'Cultural') ? 'selected' : ''; ?>>Cultural</option>
                                <option value="Academic" <?php echo ($editEvent['category'] === 'Academic') ? 'selected' : ''; ?>>Academic</option>
                                <option value="Career" <?php echo ($editEvent['category'] === 'Career') ? 'selected' : ''; ?>>Career</option>
                            </select>
                        </div>

                        <div class="field">
                            <label for="edit_date">Event Date</label>
                            <input type="date" id="edit_date" name="event_date" value="<?php echo htmlspecialchars($editEvent['event_date']); ?>" required>
                        </div>

                        <div class="field">
                            <label for="edit_time">Event Time</label>
                            <input type="text" id="edit_time" name="event_time" value="<?php echo htmlspecialchars($editEvent['event_time']); ?>" placeholder="e.g. 10:00 AM" required>
                        </div>

                        <div class="field">
                            <label for="edit_venue">Venue / Location</label>
                            <input type="text" id="edit_venue" name="venue" value="<?php echo htmlspecialchars($editEvent['venue']); ?>" required>
                        </div>

                        <div class="field">
                            <label for="edit_capacity">Seating Capacity</label>
                            <input type="number" id="edit_capacity" name="capacity" min="1" value="<?php echo htmlspecialchars($editEvent['capacity']); ?>" required>
                        </div>

                        <div class="field">
                            <label for="edit_status">Event Status</label>
                            <select id="edit_status" name="status" required>
                                <option value="Upcoming" <?php echo ($editEvent['status'] === 'Upcoming') ? 'selected' : ''; ?>>Upcoming</option>
                                <option value="Ongoing" <?php echo ($editEvent['status'] === 'Ongoing') ? 'selected' : ''; ?>>Ongoing</option>
                                <option value="Completed" <?php echo ($editEvent['status'] === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                                <option value="Cancelled" <?php echo ($editEvent['status'] === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>

                        <div class="field">
                            <label for="edit_poster">Change Poster Image <small style="font-weight:normal; color:#666;">(Optional, Max 2MB: JPG, PNG, WEBP)</small></label>
                            <input type="file" id="edit_poster" name="poster" accept=".jpg,.jpeg,.png,.webp,.gif">

                            <?php if (!empty($editEvent['poster']) && file_exists($uploadDir . '/' . $editEvent['poster'])): ?>
                                <div class="current-poster-preview">
                                    <img src="uploads/<?php echo htmlspecialchars($editEvent['poster']); ?>" alt="Current Poster" class="poster-thumb">
                                    <small style="color:#555;">Current: <code><?php echo htmlspecialchars($editEvent['poster']); ?></code> (Leave file input empty to keep current poster)</small>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="field" style="grid-column: 1 / -1;">
                            <label for="edit_desc">Event Description</label>
                            <textarea id="edit_desc" name="description" rows="3"><?php echo htmlspecialchars($editEvent['description']); ?></textarea>
                        </div>
                    </div>

                    <div style="margin-top:15px; display:flex; gap:10px;">
                        <button type="submit" class="btn-primary" style="background:#ff9800;">Update Event Details</button>
                        <a href="admin-events.php" class="btn-secondary">Cancel</a>
                    </div>
                </form>
            </section>
        <?php endif; ?>

        <!-- SEARCH AND FILTER MODULE -->
        <section class="search-filter-card">
            <h3 style="margin-bottom:12px; font-size:1.15rem; color:#003366;">Search and Filter Events</h3>
            <form action="admin-events.php" method="GET">
                <div class="search-filter-grid" style="grid-template-columns: 2fr 1fr 1fr auto;">
                    <div class="filter-group">
                        <label for="search">Keyword Search</label>
                        <input type="search" id="search" name="search" placeholder="Search by title or venue..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <div class="filter-group">
                        <label for="category">Category</label>
                        <select id="category" name="category">
                            <option value="">All Categories</option>
                            <option value="Technical" <?php echo ($filterCategory === 'Technical') ? 'selected' : ''; ?>>Technical</option>
                            <option value="Sports" <?php echo ($filterCategory === 'Sports') ? 'selected' : ''; ?>>Sports</option>
                            <option value="Cultural" <?php echo ($filterCategory === 'Cultural') ? 'selected' : ''; ?>>Cultural</option>
                            <option value="Academic" <?php echo ($filterCategory === 'Academic') ? 'selected' : ''; ?>>Academic</option>
                            <option value="Career" <?php echo ($filterCategory === 'Career') ? 'selected' : ''; ?>>Career</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="status">Event Status</label>
                        <select id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="Upcoming" <?php echo ($filterStatus === 'Upcoming') ? 'selected' : ''; ?>>Upcoming</option>
                            <option value="Ongoing" <?php echo ($filterStatus === 'Ongoing') ? 'selected' : ''; ?>>Ongoing</option>
                            <option value="Completed" <?php echo ($filterStatus === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                            <option value="Cancelled" <?php echo ($filterStatus === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn-primary">Filter</button>
                        <a href="admin-events.php" class="btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </section>

        <!-- VIEW / SELECT EVENTS LIST -->
        <section aria-labelledby="event-list-heading">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:10px;">
                <h3 id="event-list-heading" style="margin:0;">Registered Campus Events</h3>
                <span style="font-size:14px; color:#555;">
                    Showing <strong><?php echo count($eventsList); ?></strong> of <strong><?php echo $totalEvents; ?></strong> total events
                    <?php if (!empty($search) || !empty($filterCategory) || !empty($filterStatus)): ?>
                        (Filtered)
                    <?php endif; ?>
                </span>
            </div>

            <table>
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Poster</th>
                        <th scope="col">Title & Category</th>
                        <th scope="col">Date & Time</th>
                        <th scope="col">Venue</th>
                        <th scope="col">Capacity</th>
                        <th scope="col">Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($eventsList)): ?>
                        <?php foreach ($eventsList as $ev): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ev['event_id']); ?></td>
                                <td>
                                    <?php if (!empty($ev['poster']) && file_exists($uploadDir . '/' . $ev['poster'])): ?>
                                        <a href="uploads/<?php echo htmlspecialchars($ev['poster']); ?>" target="_blank" title="View Full Poster">
                                            <img src="uploads/<?php echo htmlspecialchars($ev['poster']); ?>" alt="Poster" class="poster-thumb">
                                        </a>
                                    <?php else: ?>
                                        <div class="poster-no-img">No Poster</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($ev['title']); ?></strong>
                                    <br>
                                    <small style="color:#666;"><?php echo htmlspecialchars($ev['category']); ?></small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($ev['event_date']); ?>
                                    <br>
                                    <small style="color:#555;"><?php echo htmlspecialchars($ev['event_time']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($ev['venue']); ?></td>
                                <td><?php echo htmlspecialchars($ev['capacity']); ?> Seats</td>
                                <td>
                                    <span class="badge badge-<?php echo strtolower(htmlspecialchars($ev['status'])); ?>">
                                        <?php echo htmlspecialchars($ev['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btn-group">
                                        <a href="admin-events.php?action=edit&id=<?php echo $ev['event_id']; ?>" class="btn-sm btn-edit">Edit</a>
                                        <form action="admin-events.php" method="POST" style="margin:0;" onsubmit="return confirm('Are you sure you want to delete event: <?php echo addslashes($ev['title']); ?>? This will also remove the poster file.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="event_id" value="<?php echo $ev['event_id']; ?>">
                                            <button type="submit" class="btn-sm btn-delete">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align:center; padding:25px; color:#777;">
                                No events found matching your search and filter criteria.
                                <br><a href="admin-events.php" style="color:#003366; text-decoration:underline; font-weight:bold; margin-top:8px; display:inline-block;">Reset Search & Filters</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <!-- ADD NEW EVENT FORM -->
        <section id="add-event-section" class="crud-form-card">
            <h3>Create New Event with Poster Upload</h3>
            <p>Fill out the event details and upload a promotional poster with automatic file type and size validation.</p>
            <form action="admin-events.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">

                <div class="crud-form-grid">
                    <div class="field">
                        <label for="add_title">Event Title</label>
                        <input type="text" id="add_title" name="title" placeholder="e.g. AI & Robotics Symposium" required>
                    </div>

                    <div class="field">
                        <label for="add_category">Category</label>
                        <select id="add_category" name="category" required>
                            <option value="Technical" selected>Technical</option>
                            <option value="Sports">Sports</option>
                            <option value="Cultural">Cultural</option>
                            <option value="Academic">Academic</option>
                            <option value="Career">Career</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="add_date">Event Date</label>
                        <input type="date" id="add_date" name="event_date" required>
                    </div>

                    <div class="field">
                        <label for="add_time">Event Time</label>
                        <input type="text" id="add_time" name="event_time" placeholder="e.g. 10:00 AM - 01:00 PM" required>
                    </div>

                    <div class="field">
                        <label for="add_venue">Venue / Auditorium</label>
                        <input type="text" id="add_venue" name="venue" placeholder="e.g. Seminar Hall 2, DEPSTAR" required>
                    </div>

                    <div class="field">
                        <label for="add_capacity">Seating Capacity</label>
                        <input type="number" id="add_capacity" name="capacity" min="1" value="100" required>
                    </div>

                    <div class="field">
                        <label for="add_status">Event Status</label>
                        <select id="add_status" name="status" required>
                            <option value="Upcoming" selected>Upcoming</option>
                            <option value="Ongoing">Ongoing</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="add_poster">Event Poster Image <small style="font-weight:normal; color:#666;">(Max 2MB: JPG, JPEG, PNG, WEBP)</small></label>
                        <input type="file" id="add_poster" name="poster" accept=".jpg,.jpeg,.png,.webp,.gif">
                    </div>

                    <div class="field" style="grid-column: 1 / -1;">
                        <label for="add_desc">Event Description</label>
                        <textarea id="add_desc" name="description" rows="3" placeholder="Provide event schedule, rules, and overview..."></textarea>
                    </div>
                </div>

                <div style="margin-top:15px;">
                    <button type="submit" class="btn-success">Publish & Save Event</button>
                </div>
            </form>
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
