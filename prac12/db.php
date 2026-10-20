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

    // 1. Create students table
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

    // Ensure role & remember_token columns exist
    $roleCol = $conn->query("SHOW COLUMNS FROM `students` LIKE 'role'");
    if ($roleCol->num_rows == 0) {
        $conn->query("ALTER TABLE `students` ADD COLUMN `role` ENUM('student', 'admin') NOT NULL DEFAULT 'student'");
    }
    $tokenCol = $conn->query("SHOW COLUMNS FROM `students` LIKE 'remember_token'");
    if ($tokenCol->num_rows == 0) {
        $conn->query("ALTER TABLE `students` ADD COLUMN `remember_token` VARCHAR(255) DEFAULT NULL");
    }

    // 2. Create events table with poster and status
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

    // Ensure poster & status columns exist in events table
    $posterCol = $conn->query("SHOW COLUMNS FROM `events` LIKE 'poster'");
    if ($posterCol->num_rows == 0) {
        $conn->query("ALTER TABLE `events` ADD COLUMN `poster` VARCHAR(255) DEFAULT NULL AFTER `capacity`");
    }
    $statusCol = $conn->query("SHOW COLUMNS FROM `events` LIKE 'status'");
    if ($statusCol->num_rows == 0) {
        $conn->query("ALTER TABLE `events` ADD COLUMN `status` ENUM('Upcoming', 'Ongoing', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Upcoming' AFTER `poster`");
    }

    // 3. Create registrations table
    $conn->query("CREATE TABLE IF NOT EXISTS `registrations` (
        `registration_id` INT AUTO_INCREMENT PRIMARY KEY,
        `student_id` INT NOT NULL,
        `event_id` INT NOT NULL,
        `registration_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `status` ENUM('Confirmed', 'Pending', 'Cancelled') DEFAULT 'Confirmed',
        UNIQUE KEY `unique_student_event` (`student_id`, `event_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Ensure uploads directory exists
    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Copy sample poster if available
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
            ('Shubham Desai', '25dce020@charusat.edu.in', '9876594934', '$passHash', 'CE', 2, 'Male', 'student')");
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

} catch (mysqli_sql_exception $e) {
    die("<div style='color:red; font-family:sans-serif; padding:20px; border:1px solid red; background:#fff3f3; max-width:600px; margin:30px auto; border-radius:5px;'>
        <h3>Database Connection Error</h3>
        <p>Could not connect to MySQL. Please ensure MySQL is started in XAMPP Control Panel.</p>
        <small><strong>Details:</strong> " . htmlspecialchars($e->getMessage()) . "</small>
    </div>");
}
?>
