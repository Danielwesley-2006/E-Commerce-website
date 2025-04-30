<?php
require_once 'config.php';

try {
    $conn = getConnection();
    echo "Successfully connected to the database!\n";
    
    // Test if we can access the users table
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Number of users in database: " . $result['count'] . "\n";
    
    echo "Database connection test completed successfully!";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
