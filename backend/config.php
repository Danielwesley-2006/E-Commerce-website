<?php
// Database configuration
define('DB_HOST', 'localhost');     // Replace with your host
define('DB_USERNAME', 'root');          // Database username
define('DB_PASSWORD', 'Daniel2006');    // Database password
define('DB_NAME', 'amazon_clone');  // Database name

// Create connection
function getConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USERNAME,
            DB_PASSWORD
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}
?>
