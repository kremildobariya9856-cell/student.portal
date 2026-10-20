<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbname = 'studenthub';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass);
    $conn->query("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->select_db($dbname);

    // 1. Students table
    $conn->query("CREATE TABLE IF NOT EXISTS `students` (
        `student_id` INT AUTO_INCREMENT PRIMARY KEY,
        `full_name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(100) NOT NULL UNIQUE,
        `mobile` VARCHAR(15) NOT NULL,
        `password` VARCHAR(255) NOT NULL,
        `course` VARCHAR(50) NOT NULL,
        `year` INT NOT NULL,
        `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
        `role` ENUM('student', 'admin') NOT NULL DEFAULT 'student',
        `remember_token` VARCHAR(255) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Ensure role and remember_token columns exist
    $roleCol = $conn->query("SHOW COLUMNS FROM `students` LIKE 'role'");
    if ($roleCol->num_rows == 0) {
        $conn->query("ALTER TABLE `students` ADD COLUMN `role` ENUM('student', 'admin') NOT NULL DEFAULT 'student'");
    }
    $tokenCol = $conn->query("SHOW COLUMNS FROM `students` LIKE 'remember_token'");
    if ($tokenCol->num_rows == 0) {
        $conn->query("ALTER TABLE `students` ADD COLUMN `remember_token` VARCHAR(255) DEFAULT NULL");
    }

    // 2. Events table
    $conn->query("CREATE TABLE IF NOT EXISTS `events` (
        `event_id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(150) NOT NULL,
        `category` VARCHAR(50) NOT NULL,
        `description` TEXT,
        `event_date` DATE NOT NULL,
        `event_time` VARCHAR(20) NOT NULL,
        `venue` VARCHAR(100) NOT NULL,
        `capacity` INT DEFAULT 100,
        `poster` VARCHAR(255) DEFAULT NULL,
        `status` ENUM('Upcoming', 'Ongoing', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Upcoming',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $posterCol = $conn->query("SHOW COLUMNS FROM `events` LIKE 'poster'");
    if ($posterCol->num_rows == 0) {
        $conn->query("ALTER TABLE `events` ADD COLUMN `poster` VARCHAR(255) DEFAULT NULL AFTER `capacity`");
    }
    $statusCol = $conn->query("SHOW COLUMNS FROM `events` LIKE 'status'");
    if ($statusCol->num_rows == 0) {
        $conn->query("ALTER TABLE `events` ADD COLUMN `status` ENUM('Upcoming', 'Ongoing', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Upcoming' AFTER `poster`");
    }

    // 3. Registrations table
    $conn->query("CREATE TABLE IF NOT EXISTS `registrations` (
        `registration_id` INT AUTO_INCREMENT PRIMARY KEY,
        `student_id` INT NOT NULL,
        `event_id` INT NOT NULL,
        `registration_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `status` ENUM('Confirmed', 'Pending', 'Cancelled') DEFAULT 'Confirmed',
        UNIQUE KEY `unique_student_event` (`student_id`, `event_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 4. Audit Logs table (Action traceability & monitoring)
    $conn->query("CREATE TABLE IF NOT EXISTS `audit_logs` (
        `log_id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT DEFAULT NULL,
        `user_name` VARCHAR(100) NOT NULL,
        `action` VARCHAR(100) NOT NULL,
        `details` TEXT NOT NULL,
        `ip_address` VARCHAR(45) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Uploads directory
    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    if (file_exists(__DIR__ . '/SPOURAL.jpg') && !file_exists($uploadDir . '/spoural.jpg')) {
        copy(__DIR__ . '/SPOURAL.jpg', $uploadDir . '/spoural.jpg');
    }

    // Seed default admin and students if needed
    $checkAdmin = $conn->query("SELECT COUNT(*) AS total FROM `students` WHERE `email` = 'admin@charusat.edu.in'");
    $adminRow = $checkAdmin->fetch_assoc();
    if ($adminRow['total'] == 0) {
        $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
        $passHash = password_hash('password123', PASSWORD_DEFAULT);
        $conn->query("INSERT INTO `students` (`full_name`, `email`, `mobile`, `password`, `course`, `year`, `gender`, `role`) VALUES
            ('Administrator', 'admin@charusat.edu.in', '9999988888', '$adminHash', 'ADMIN', 4, 'Other', 'admin'),
            ('Kremil', '25dce022@charusat.edu.in', '9727207234', '$passHash', 'CE', 2, 'Male', 'student'),
            ('Shubham Desai', '25dce020@charusat.edu.in', '9876594934', '$passHash', 'CE', 2, 'Male', 'student'),
            ('Shan Dabhi', '25dce018@charusat.edu.in', '8401792927', '$passHash', 'CE', 2, 'Male', 'student')");
    }

    // Seed default events if table is empty
    $checkEvents = $conn->query("SELECT COUNT(*) AS total FROM `events`");
    $evRow = $checkEvents->fetch_assoc();
    if ($evRow['total'] == 0) {
        $conn->query("INSERT INTO `events` (`title`, `category`, `description`, `event_date`, `event_time`, `venue`, `capacity`, `poster`, `status`) VALUES
            ('Tech Innovate 2026', 'Technical', 'Annual university hackathon and coding contest.', '2026-11-15', '10:00 AM', 'Seminar Hall 1', 120, NULL, 'Upcoming'),
            ('Spoural Annual Sports Fest', 'Sports', 'Inter-department sports tournament including Cricket, Football, and Badminton.', '2026-11-20', '08:30 AM', 'University Ground', 300, 'spoural.jpg', 'Upcoming'),
            ('Web Dev Workshop', 'Academic', 'Hands-on workshop on full-stack web development with PHP and MySQL.', '2026-12-05', '02:00 PM', 'Lab 204', 60, NULL, 'Ongoing'),
            ('Cognizance Cultural Night', 'Cultural', 'Music, dance, drama and cultural performances by students.', '2026-12-18', '06:00 PM', 'Auditorium', 500, NULL, 'Upcoming')");
    }

    // Seed sample audit logs if table is empty
    $checkLogs = $conn->query("SELECT COUNT(*) AS total FROM `audit_logs`");
    $logRow = $checkLogs->fetch_assoc();
    if ($logRow['total'] == 0) {
        $conn->query("INSERT INTO `audit_logs` (`user_id`, `user_name`, `action`, `details`, `ip_address`) VALUES
            (1, 'Administrator', 'LOGIN', 'Admin logged in successfully to management dashboard', '127.0.0.1'),
            (1, 'Administrator', 'ADD_EVENT', 'Created new event: Tech Innovate 2026', '127.0.0.1'),
            (1, 'Administrator', 'ADD_STUDENT', 'Enrolled new student: Kremil (CE)', '127.0.0.1')");
    }

} catch (mysqli_sql_exception $e) {
    die("<div style='color:red; font-family:sans-serif; padding:20px; border:1px solid red; background:#fff3f3; max-width:600px; margin:30px auto; border-radius:5px;'>
        <h3>Database Connection Error</h3>
        <p>Could not connect to MySQL. Please ensure MySQL is started in XAMPP Control Panel.</p>
        <small><strong>Details:</strong> " . htmlspecialchars($e->getMessage()) . "</small>
    </div>");
}

// -------------------------------------------------------------
// Helper: Record action in audit_logs table
// -------------------------------------------------------------
function recordAuditLog($conn, $action, $details, $userId = null, $userName = null) {
    try {
        if ($userId === null) {
            $userId = $_SESSION['user_id'] ?? null;
        }
        if ($userName === null) {
            $userName = $_SESSION['user_name'] ?? 'Guest';
        }
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $stmt = $conn->prepare("INSERT INTO `audit_logs` (`user_id`, `user_name`, `action`, `details`, `ip_address`) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $userId, $userName, $action, $details, $ip);
        $stmt->execute();
        $stmt->close();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>
