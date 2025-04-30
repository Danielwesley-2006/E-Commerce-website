<?php
require_once '../cors.php';
header('Content-Type: application/json');

require_once '../config.php';

try {
    $conn = getConnection();

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $stmt = $conn->query("SELECT * FROM products");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'products' => $products]);
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['name']) || !isset($data['price'])) {
                throw new Exception('Name and price are required');
            }
            
            if (!is_numeric($data['price']) || $data['price'] < 0) {
                throw new Exception('Invalid price');
            }
            
            $stmt = $conn->prepare("INSERT INTO products (name, price, image_url, description, stock) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['name'],
                $data['price'],
                $data['image_url'] ?? null,
                $data['description'] ?? null,
                $data['stock'] ?? 0
            ]);
            echo json_encode(['success' => true, 'message' => 'Product added successfully']);
            break;
            
        default:
            throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
