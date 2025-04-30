<?php
require_once 'config.php';

try {
    $conn = getConnection();
    
    // Check if cart table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'cart'");
    if ($stmt->rowCount() == 0) {
        echo "Cart table does not exist! Please run setup_cart_table.php first.\n";
        exit;
    }
    
    // Get all cart items with product details
    $stmt = $conn->query("
        SELECT c.*, u.username, p.name as product_name, p.price 
        FROM cart c 
        JOIN users u ON c.user_id = u.id 
        JOIN products p ON c.product_id = p.id
    ");
    
    echo "<h2>Cart Contents:</h2>";
    echo "<pre>";
    
    if ($stmt->rowCount() > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "User: " . $row['username'] . "\n";
            echo "Product: " . $row['product_name'] . "\n";
            echo "Quantity: " . $row['quantity'] . "\n";
            echo "Price: $" . $row['price'] . "\n";
            echo "Added at: " . $row['created_at'] . "\n";
            echo "------------------------\n";
        }
    } else {
        echo "No items in cart table.\n";
    }
    
    echo "</pre>";
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
