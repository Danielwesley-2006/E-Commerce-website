<?php
require_once '../cors.php';
header('Content-Type: application/json');

require_once '../config.php';

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password) {
    return strlen($password) >= 6;
}

try {
    $conn = getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['action'])) {
            switch ($data['action']) {
                case 'register':
                    if (!isset($data['username']) || !isset($data['email']) || !isset($data['password'])) {
                        throw new Exception('Missing required fields');
                    }
                    
                    if (!validateEmail($data['email'])) {
                        throw new Exception('Invalid email format');
                    }
                    
                    if (!validatePassword($data['password'])) {
                        throw new Exception('Password must be at least 6 characters long');
                    }
                    
                    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
                    $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
                    $stmt->execute([$data['username'], $data['email'], $password_hash]);
                    echo json_encode(['success' => true, 'message' => 'User registered successfully']);
                    break;
                    
                case 'login':
                    if (!isset($data['email']) || !isset($data['password'])) {
                        throw new Exception('Missing email or password');
                    }
                    
                    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
                    $stmt->execute([$data['email']]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($user && password_verify($data['password'], $user['password_hash'])) {
                        echo json_encode([
                            'success' => true,
                            'user' => [
                                'id' => $user['id'],
                                'username' => $user['username'],
                                'email' => $user['email']
                            ]
                        ]);
                    } else {
                        throw new Exception('Invalid credentials');
                    }
                    break;
                    
                default:
                    throw new Exception('Invalid action');
            }
        } else {
            throw new Exception('Action not specified');
        }
    } else {
        throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
