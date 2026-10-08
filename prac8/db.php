<?php
// ==========================================================
// Practical 8: Database Connection using PDO & Error Handling
// ==========================================================

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbname = 'studenthub';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use real native prepared statements
];

try {
    // Attempt connecting to the database
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // If the database does not exist yet, create it and initialize tables
    if ($e->getCode() == 1049 || strpos($e->getMessage(), 'Unknown database') !== false) {
        try {
            $tempPdo = new PDO("mysql:host=$host;charset=$charset", $user, $pass, $options);
            $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo = new PDO($dsn, $user, $pass, $options);
            
            // Auto initialize schema
            $schemaFile = __DIR__ . '/schema.sql';
            if (file_exists($schemaFile)) {
                $sql = file_get_contents($schemaFile);
                $pdo->exec($sql);
            }
        } catch (PDOException $ex) {
            die("<div style='color:red; font-family:sans-serif; padding:20px; border:1px solid red; background:#fff3f3; max-width:600px; margin:30px auto; border-radius:5px;'>
                <h3>Database Connection Error</h3>
                <p>Could not connect to MySQL server. Please make sure MySQL is started in XAMPP / WAMP.</p>
                <small><strong>Error Details:</strong> " . htmlspecialchars($ex->getMessage()) . "</small>
            </div>");
        }
    } else {
        // Other connection errors (e.g. MySQL not running)
        die("<div style='color:red; font-family:sans-serif; padding:20px; border:1px solid red; background:#fff3f3; max-width:600px; margin:30px auto; border-radius:5px;'>
            <h3>Database Connection Error</h3>
            <p>Could not connect to MySQL. Please ensure that MySQL is running in your XAMPP Control Panel.</p>
            <small><strong>Error Details:</strong> " . htmlspecialchars($e->getMessage()) . "</small>
        </div>");
    }
}
?>
