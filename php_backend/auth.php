<?php
// auth.php
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    if (empty($email) || empty($password)) {
        sendJson("Email and password are required", 400);
    }

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        sendJson("User not found", 400);
    }

    // Checking plain password as per Spring Boot implementation
    if ($user['password'] !== $password) {
        sendJson("Wrong password", 400);
    }

    sendJson($user);
} else {
    sendJson("Method not allowed", 405);
}
?>
