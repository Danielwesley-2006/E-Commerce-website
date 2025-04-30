<?php
function testEndpoint($method, $endpoint, $data = null) {
    $ch = curl_init();
    
    $url = "http://localhost:8000/api/" . $endpoint;
    if ($method === 'GET' && $data) {
        $url .= '?' . http_build_query($data);
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($data && $method !== 'GET') {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json'
    ));
    
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo "\nError testing $method $endpoint: " . curl_error($ch) . "\n";
    } else {
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        echo "\nTesting $method $endpoint:\n";
        echo "Status Code: $httpCode\n";
        echo "Response: " . ($response ? $response : "No response") . "\n";
    }
    
    curl_close($ch);
    echo "------------------------\n";
}

// Test user registration
testEndpoint('POST', 'auth.php', array(
    'action' => 'register',
    'username' => 'testuser',
    'email' => 'test@example.com',
    'password' => 'password123'
));

// Test user login
testEndpoint('POST', 'auth.php', array(
    'action' => 'login',
    'email' => 'test@example.com',
    'password' => 'password123'
));

// Test adding a product
testEndpoint('POST', 'products.php', array(
    'name' => 'Test Product',
    'price' => 99.99,
    'description' => 'A test product',
    'stock' => 10
));

// Test getting products
testEndpoint('GET', 'products.php');

// Test adding to cart
testEndpoint('POST', 'cart.php', array(
    'user_id' => 1,
    'product_id' => 1,
    'quantity' => 2
));

// Test getting cart items
testEndpoint('GET', 'cart.php', array('user_id' => 1));

// Test creating an order
testEndpoint('POST', 'orders.php', array(
    'user_id' => 1,
    'total_amount' => 199.98,
    'items' => array(
        array(
            'product_id' => 1,
            'quantity' => 2,
            'price' => 99.99
        )
    )
));

// Test getting orders
testEndpoint('GET', 'orders.php', array('user_id' => 1));
