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

    // Create students table
    $tableSql = "CREATE TABLE IF NOT EXISTS `students` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->query($tableSql);

    // Ensure role column exists
    $roleCol = $conn->query("SHOW COLUMNS FROM `students` LIKE 'role'");
    if ($roleCol->num_rows == 0) {
        $conn->query("ALTER TABLE `students` ADD COLUMN `role` ENUM('student', 'admin') NOT NULL DEFAULT 'student'");
    }

    // Ensure remember_token column exists
    $tokenCol = $conn->query("SHOW COLUMNS FROM `students` LIKE 'remember_token'");
    if ($tokenCol->num_rows == 0) {
        $conn->query("ALTER TABLE `students` ADD COLUMN `remember_token` VARCHAR(255) DEFAULT NULL");
    }

    // Seed default accounts if not already present
    $checkData = $conn->query("SELECT COUNT(*) AS total FROM `students` WHERE `email` = '25dce022@charusat.edu.in'");
    $row = $checkData->fetch_assoc();
    
    if ($row['total'] == 0) {
        $passHash = password_hash('password123', PASSWORD_DEFAULT);
        $adminHash = password_hash('admin123', PASSWORD_DEFAULT);

        $seedSql = "INSERT INTO `students` (`student_id`, `full_name`, `email`, `mobile`, `password`, `course`, `year`, `gender`, `role`) VALUES
            (1, 'Kremil', '25dce022@charusat.edu.in', '9727207234', '$passHash', 'CE', 2, 'Male', 'student'),
            (2, 'Shubham Desai', '25dce020@charusat.edu.in', '9876594934', '$passHash', 'CE', 2, 'Male', 'student'),
            (3, 'Shan Dabhi', '25dce018@charusat.edu.in', '8401792927', '$passHash', 'CE', 2, 'Male', 'student'),
            (4, 'Administrator', 'admin@charusat.edu.in', '9999988888', '$adminHash', 'ADMIN', 4, 'Other', 'admin')
            ON DUPLICATE KEY UPDATE `full_name` = VALUES(`full_name`), `mobile` = VALUES(`mobile`), `course` = VALUES(`course`), `year` = VALUES(`year`), `password` = VALUES(`password`), `role` = VALUES(`role`)";
        
        $conn->query($seedSql);
    } else {
        // Ensure admin user exists even if students already existed
        $checkAdmin = $conn->query("SELECT COUNT(*) AS total FROM `students` WHERE `email` = 'admin@charusat.edu.in'");
        $adminRow = $checkAdmin->fetch_assoc();
        if ($adminRow['total'] == 0) {
            $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
            $conn->query("INSERT INTO `students` (`full_name`, `email`, `mobile`, `password`, `course`, `year`, `gender`, `role`) VALUES
                ('Administrator', 'admin@charusat.edu.in', '9999988888', '$adminHash', 'ADMIN', 4, 'Other', 'admin')");
        }
    }

} catch (mysqli_sql_exception $e) {
    die("<div style='color:red; font-family:sans-serif; padding:20px; border:1px solid red; background:#fff3f3; max-width:600px; margin:30px auto; border-radius:5px;'>
        <h3>Database Connection Error</h3>
        <p>Could not connect to MySQL. Please ensure MySQL is started in XAMPP Control Panel.</p>
        <small><strong>Details:</strong> " . htmlspecialchars($e->getMessage()) . "</small>
    </div>");
}
?>
