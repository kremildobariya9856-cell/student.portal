<?php
// ==========================================================
// Practical 9: Database Connection using MySQLi
// ==========================================================

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbname = 'studenthub';

// Enable mysqli error reporting for exceptions
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // 1. Connect to MySQL Server
    $conn = new mysqli($host, $user, $pass);

    // 2. Create database if it does not exist
    $conn->query("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // 3. Select database
    $conn->select_db($dbname);

    // 4. Create students table if it does not exist
    $tableSql = "CREATE TABLE IF NOT EXISTS `students` (
        `student_id` INT AUTO_INCREMENT PRIMARY KEY,
        `full_name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(100) NOT NULL UNIQUE,
        `mobile` VARCHAR(15) NOT NULL,
        `password` VARCHAR(255) NOT NULL,
        `course` VARCHAR(50) NOT NULL,
        `year` INT NOT NULL,
        `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $conn->query($tableSql);

    // 5. Ensure the specific 3 students exist with hashed passwords
    $checkData = $conn->query("SELECT COUNT(*) AS total FROM `students` WHERE `email` = '25dce022@charusat.edu.in'");
    $row = $checkData->fetch_assoc();
    
    if ($row['total'] == 0) {
        // Clean out old/unwanted sample data if any
        $conn->query("DELETE FROM `students` WHERE `email` LIKE '%@example.com'");

        $seedSql = "INSERT INTO `students` (`student_id`, `full_name`, `email`, `mobile`, `password`, `course`, `year`, `gender`) VALUES
            (1, 'Kremil', '25dce022@charusat.edu.in', '9727207234', '" . password_hash('password123', PASSWORD_DEFAULT) . "', 'CE', 2, 'Male'),
            (2, 'Shubham Desai', '25dce020@charusat.edu.in', '9876594934', '" . password_hash('password123', PASSWORD_DEFAULT) . "', 'CE', 2, 'Male'),
            (3, 'Shan Dabhi', '25dce018@charusat.edu.in', '8401792927', '" . password_hash('password123', PASSWORD_DEFAULT) . "', 'CE', 2, 'Male')
            ON DUPLICATE KEY UPDATE `full_name` = VALUES(`full_name`), `mobile` = VALUES(`mobile`), `course` = VALUES(`course`), `year` = VALUES(`year`), `password` = VALUES(`password`)";
        
        $conn->query($seedSql);
    }

} catch (mysqli_sql_exception $e) {
    die("<div style='color:red; font-family:sans-serif; padding:20px; border:1px solid red; background:#fff3f3; max-width:600px; margin:30px auto; border-radius:5px;'>
        <h3>MySQLi Database Connection Error</h3>
        <p>Could not connect to MySQL server. Please ensure MySQL is started in your XAMPP Control Panel.</p>
        <small><strong>Error Details:</strong> " . htmlspecialchars($e->getMessage()) . "</small>
    </div>");
}
?>
