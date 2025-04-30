<?php
require_once 'config.php';

try {
    $conn = getConnection();
    
    // Create products table
    $sql = "CREATE TABLE IF NOT EXISTS products (
        id VARCHAR(255) PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        image_url VARCHAR(255),
        stock INT DEFAULT 100,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $conn->exec($sql);
    echo "Products table created successfully!\n";
    
    // Import products from products.js
    $productsJson = file_get_contents('../data/products.js');
    // Extract the array part from the JS file
    preg_match('/export const products = (\[.*?\]);/s', $productsJson, $matches);
    if (isset($matches[1])) {
        $products = json_decode($matches[1], true);
        
        // Prepare insert statement
        $stmt = $conn->prepare("INSERT IGNORE INTO products (id, name, price, image_url) VALUES (?, ?, ?, ?)");
        
        foreach ($products as $product) {
            $stmt->execute([
                $product['id'],
                $product['name'],
                $product['priceCents'] / 100, // Convert cents to dollars
                $product['image']
            ]);
        }
        echo "Products imported successfully!\n";
    }
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
