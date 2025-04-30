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
                SELECT o.*, oi.product_id, oi.quantity, oi.price, p.name 
                FROM orders o 
                JOIN order_items oi ON o.id = oi.order_id 
                JOIN products p ON oi.product_id = p.id 
                WHERE o.user_id = ?
                ORDER BY o.created_at DESC
            ");
            $stmt->execute([$_GET['user_id']]);
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'orders' => $orders]);
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['user_id']) || !isset($data['items']) || !isset($data['total_amount'])) {
                throw new Exception('User ID, items, and total amount are required');
            }
            
            if (!is_array($data['items']) || empty($data['items'])) {
                throw new Exception('Items must be a non-empty array');
            }
            
            if (!is_numeric($data['total_amount']) || $data['total_amount'] <= 0) {
                throw new Exception('Invalid total amount');
            }
            
            try {
                $conn->beginTransaction();
                
                // Create order
                $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount) VALUES (?, ?)");
                $stmt->execute([$data['user_id'], $data['total_amount']]);
                $order_id = $conn->lastInsertId();
                
                // Add order items and update stock
                $stmt_items = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
                
                foreach ($data['items'] as $item) {
                    if (!isset($item['product_id']) || !isset($item['quantity']) || !isset($item['price'])) {
                        throw new Exception('Invalid item format');
                    }
                    
                    // Update stock
                    $result = $stmt_stock->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
                    if ($stmt_stock->rowCount() === 0) {
                        throw new Exception("Not enough stock for product ID: {$item['product_id']}");
                    }
                    
                    // Add order item
                    $stmt_items->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
                }
                
                // Clear cart
                $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
                $stmt->execute([$data['user_id']]);
                
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Order created successfully', 'order_id' => $order_id]);
            } catch (Exception $e) {
                $conn->rollBack();
                throw $e;
            }
            break;
            
        default:
            throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
