<?php
require_once 'config.php';

try {
    $conn = getConnection();
    
    // Create cart table
    $sql = "CREATE TABLE IF NOT EXISTS cart (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id VARCHAR(255) NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        UNIQUE KEY unique_user_product (user_id, product_id)
    )";
    
    $conn->exec($sql);
    echo "Cart table created successfully!\n";
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
