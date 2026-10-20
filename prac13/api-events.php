<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$method = $_SERVER['REQUEST_METHOD'];
$uploadDir = __DIR__ . '/uploads';

// Require admin for write operations (add, update, delete)
if (in_array($action, ['add', 'update', 'delete'])) {
    if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'Unauthorized: Administrator privileges required.'
        ]);
        exit();
    }
}

// Helper: Handle Poster Upload with Validation
function validateAndSavePoster($fileInput, &$uploadError, $uploadDir) {
    if (!isset($_FILES[$fileInput]) || $_FILES[$fileInput]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$fileInput];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadError = "Upload error code: " . $file['error'];
        return false;
    }

    // 2MB size limit
    if ($file['size'] > 2 * 1024 * 1024) {
        $uploadError = "Poster file size exceeds the 2MB limit.";
        return false;
    }

    $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExts)) {
        $uploadError = "Invalid file type. Only JPG, JPEG, PNG, WEBP, and GIF are allowed.";
        return false;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedMimes)) {
        $uploadError = "Security check failed: File is not a valid image.";
        return false;
    }

    $newFilename = uniqid('poster_', true) . '.' . $ext;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $newFilename)) {
        return $newFilename;
    }

    $uploadError = "Failed to write file to upload directory.";
    return false;
}

try {
    // ---------------------------------------------------------
    // 1. GET: LIST EVENTS (with search & filter)
    // ---------------------------------------------------------
    if ($action === 'list' && $method === 'GET') {
        $search = trim($_GET['search'] ?? '');
        $category = trim($_GET['category'] ?? '');
        $status = trim($_GET['status'] ?? '');

        $where = [];
        $types = "";
        $params = [];

        if (!empty($search)) {
            $where[] = "(title LIKE ? OR venue LIKE ?)";
            $like = "%" . $search . "%";
            $types .= "ss";
            $params[] = $like;
            $params[] = $like;
        }

        if (!empty($category)) {
            $where[] = "category = ?";
            $types .= "s";
            $params[] = $category;
        }

        if (!empty($status)) {
            $where[] = "status = ?";
            $types .= "s";
            $params[] = $status;
        }

        $sql = "SELECT * FROM events";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY event_date ASC, event_id DESC";

        $stmt = $conn->prepare($sql);
        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $events = [];
        while ($row = $result->fetch_assoc()) {
            $row['has_poster'] = (!empty($row['poster']) && file_exists($uploadDir . '/' . $row['poster']));
            $events[] = $row;
        }
        $stmt->close();

        echo json_encode([
            'status' => 'success',
            'total' => count($events),
            'data' => $events
        ]);
        exit();
    }

    // ---------------------------------------------------------
    // 2. GET: SINGLE EVENT BY ID
    // ---------------------------------------------------------
    elseif ($action === 'get' && $method === 'GET') {
        $id = intval($_GET['id'] ?? 0);
        $stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc()) {
            $row['has_poster'] = (!empty($row['poster']) && file_exists($uploadDir . '/' . $row['poster']));
            echo json_encode([
                'status' => 'success',
                'data' => $row
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Event not found.'
            ]);
        }
        $stmt->close();
        exit();
    }

    // ---------------------------------------------------------
    // 3. POST: ADD NEW EVENT
    // ---------------------------------------------------------
    elseif ($action === 'add' && $method === 'POST') {
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $eventDate = trim($_POST['event_date'] ?? '');
        $eventTime = trim($_POST['event_time'] ?? '');
        $venue = trim($_POST['venue'] ?? '');
        $capacity = intval($_POST['capacity'] ?? 100);
        $status = trim($_POST['status'] ?? 'Upcoming');

        if (empty($title) || empty($category) || empty($eventDate) || empty($eventTime) || empty($venue)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Validation error: Title, Category, Date, Time, and Venue are required.'
            ]);
            exit();
        }

        $uploadError = '';
        $posterFilename = validateAndSavePoster('poster', $uploadError, $uploadDir);

        if ($posterFilename === false) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'File error: ' . $uploadError
            ]);
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO events (title, category, description, event_date, event_time, venue, capacity, poster, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssiss", $title, $category, $description, $eventDate, $eventTime, $venue, $capacity, $posterFilename, $status);
        $stmt->execute();
        $newEventId = $stmt->insert_id;
        $stmt->close();

        // Fetch newly created record
        $getStmt = $conn->prepare("SELECT * FROM events WHERE event_id = ?");
        $getStmt->bind_param("i", $newEventId);
        $getStmt->execute();
        $createdEvent = $getStmt->get_result()->fetch_assoc();
        $createdEvent['has_poster'] = (!empty($createdEvent['poster']) && file_exists($uploadDir . '/' . $createdEvent['poster']));
        $getStmt->close();

        echo json_encode([
            'status' => 'success',
            'message' => "Event '{$title}' created successfully!",
            'data' => $createdEvent
        ]);
        exit();
    }

    // ---------------------------------------------------------
    // 4. POST: UPDATE EXISTING EVENT
    // ---------------------------------------------------------
    elseif ($action === 'update' && $method === 'POST') {
        $id = intval($_POST['event_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $eventDate = trim($_POST['event_date'] ?? '');
        $eventTime = trim($_POST['event_time'] ?? '');
        $venue = trim($_POST['venue'] ?? '');
        $capacity = intval($_POST['capacity'] ?? 100);
        $status = trim($_POST['status'] ?? 'Upcoming');

        if ($id <= 0 || empty($title) || empty($category) || empty($eventDate) || empty($eventTime) || empty($venue)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Validation error: Valid Event ID and all event fields are required.'
            ]);
            exit();
        }

        $uploadError = '';
        $newPoster = validateAndSavePoster('poster', $uploadError, $uploadDir);

        if ($newPoster === false) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'File error: ' . $uploadError
            ]);
            exit();
        }

        if ($newPoster !== null) {
            // Delete old poster if exists
            $oldStmt = $conn->prepare("SELECT poster FROM events WHERE event_id = ?");
            $oldStmt->bind_param("i", $id);
            $oldStmt->execute();
            $oldRes = $oldStmt->get_result()->fetch_assoc();
            $oldStmt->close();

            if (!empty($oldRes['poster']) && file_exists($uploadDir . '/' . $oldRes['poster'])) {
                @unlink($uploadDir . '/' . $oldRes['poster']);
            }

            $stmt = $conn->prepare("UPDATE events SET title = ?, category = ?, description = ?, event_date = ?, event_time = ?, venue = ?, capacity = ?, poster = ?, status = ? WHERE event_id = ?");
            $stmt->bind_param("ssssssissi", $title, $category, $description, $eventDate, $eventTime, $venue, $capacity, $newPoster, $status, $id);
        } else {
            // Retain existing poster
            $stmt = $conn->prepare("UPDATE events SET title = ?, category = ?, description = ?, event_date = ?, event_time = ?, venue = ?, capacity = ?, status = ? WHERE event_id = ?");
            $stmt->bind_param("ssssssisi", $title, $category, $description, $eventDate, $eventTime, $venue, $capacity, $status, $id);
        }

        $stmt->execute();
        $stmt->close();

        // Fetch updated record
        $getStmt = $conn->prepare("SELECT * FROM events WHERE event_id = ?");
        $getStmt->bind_param("i", $id);
        $getStmt->execute();
        $updatedEvent = $getStmt->get_result()->fetch_assoc();
        $updatedEvent['has_poster'] = (!empty($updatedEvent['poster']) && file_exists($uploadDir . '/' . $updatedEvent['poster']));
        $getStmt->close();

        echo json_encode([
            'status' => 'success',
            'message' => "Event '{$title}' updated successfully!",
            'data' => $updatedEvent
        ]);
        exit();
    }

    // ---------------------------------------------------------
    // 5. POST: DELETE EVENT
    // ---------------------------------------------------------
    elseif ($action === 'delete' && $method === 'POST') {
        // Support POST parameter or JSON payload
        $id = intval($_POST['event_id'] ?? 0);
        if ($id <= 0) {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = intval($input['event_id'] ?? 0);
        }

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid event ID provided for deletion.'
            ]);
            exit();
        }

        // Get poster filename before deletion
        $pStmt = $conn->prepare("SELECT poster FROM events WHERE event_id = ?");
        $pStmt->bind_param("i", $id);
        $pStmt->execute();
        $pRow = $pStmt->get_result()->fetch_assoc();
        $pStmt->close();

        if (!empty($pRow['poster']) && file_exists($uploadDir . '/' . $pRow['poster'])) {
            @unlink($uploadDir . '/' . $pRow['poster']);
        }

        $del = $conn->prepare("DELETE FROM events WHERE event_id = ?");
        $del->bind_param("i", $id);
        $del->execute();
        $affected = $del->affected_rows;
        $del->close();

        if ($affected > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => "Event #{$id} and its associated poster were deleted successfully!",
                'event_id' => $id
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Event not found or already deleted.'
            ]);
        }
        exit();
    }

    // Default: Invalid Action
    else {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => "Invalid API action or request method. (Action: '{$action}', Method: '{$method}')"
        ]);
        exit();
    }

} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit();
}
?>
