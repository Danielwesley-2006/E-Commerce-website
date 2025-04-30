<?php
$host = 'localhost';
$username = 'root';
$password = 'Daniel2006';

try {
    // Create connection without database selected
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read SQL file
    $sql = file_get_contents(__DIR__ . '/database.sql');
    
    // Execute SQL
    $conn->exec($sql);
    echo "Database and tables created successfully\n";
    
} catch(PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
?>
