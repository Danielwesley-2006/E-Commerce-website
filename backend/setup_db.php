<?php
$host = 'localhost';
$username = 'root';
$password = 'Daniel2006';

try {
    // Create connection without database
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read and execute SQL file
    $sql = file_get_contents(__DIR__ . '/database.sql');
    
    // Split into individual queries
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($queries as $query) {
        if (!empty($query)) {
            $conn->exec($query);
            echo "Executed: " . substr($query, 0, 50) . "...\n";
        }
    }
    
    echo "\nDatabase setup completed successfully!\n";
    
} catch(PDOException $e) {
    die("ERROR: " . $e->getMessage() . "\n");
}
?>
