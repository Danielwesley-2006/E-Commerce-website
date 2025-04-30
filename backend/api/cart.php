<?php
require_once '../cors.php';
header('Content-Type: application/json');

require_once '../config.php';

try {
    $conn = getConnection();

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if (!isset($_GET['user_id'])) {
                throw new Exception('User ID is required');
            }
            
            $stmt = $conn->prepare("
                SELECT c.*, p.name, p.price, p.image_url 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ?
            ");
            $stmt->execute([$_GET['user_id']]);
            $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'cart_items' => $cart_items]);
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['user_id']) || !isset($data['product_id']) || !isset($data['quantity'])) {
                throw new Exception('User ID, product ID, and quantity are required');
            }
            
            if (!is_numeric($data['quantity']) || $data['quantity'] < 1) {
                throw new Exception('Invalid quantity');
            }
            
            // Check if product exists and has enough stock
            $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
            $stmt->execute([$data['product_id']]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product) {
                throw new Exception('Product not found');
            }
            
            if ($product['stock'] < $data['quantity']) {
                throw new Exception('Not enough stock available');
            }
            
            // Check if item already exists in cart
            $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$data['user_id'], $data['product_id']]);
            $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing_item) {
                // Update quantity
                $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt->execute([$existing_item['quantity'] + $data['quantity'], $existing_item['id']]);
            } else {
                // Add new item
                $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt->execute([$data['user_id'], $data['product_id'], $data['quantity']]);
            }
            echo json_encode(['success' => true, 'message' => 'Cart updated successfully']);
            break;
            
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['user_id']) || !isset($data['product_id'])) {
                throw new Exception('User ID and product ID are required');
            }
            
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$data['user_id'], $data['product_id']]);
            
            if ($stmt->rowCount() === 0) {
                throw new Exception('Item not found in cart');
            }
            
            echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
            break;
            
        default:
            throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
