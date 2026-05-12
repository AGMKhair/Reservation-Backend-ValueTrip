<?php
// users.php
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

switch ($method) {
    case 'GET':
        if ($id) {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            if ($user) {
                sendJson($user);
            } else {
                sendJson("User not found with id: $id", 400);
            }
        } else {
            $stmt = $pdo->query('SELECT * FROM users');
            sendJson($stmt->fetchAll());
        }
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? null;
        $password = $input['password'] ?? null;
        $fullName = $input['fullName'] ?? null; // using camelCase from JSON, assuming column is full_name
        $role = $input['role'] ?? null;
        $createdAt = date('Y-m-d H:i:s');

        $stmt = $pdo->prepare('INSERT INTO users (email, password, full_name, role, created_at) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$email, $password, $fullName, $role, $createdAt]);
        $newId = $pdo->lastInsertId();

        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$newId]);
        sendJson($stmt->fetch());
        break;

    case 'PUT':
        if (!$id) {
            sendJson("User ID is required", 400);
        }
        $input = json_decode(file_get_contents('php://input'), true);
        
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            sendJson("User not found with id: $id", 400);
        }

        $email = $input['email'] ?? null;
        $password = $input['password'] ?? null;
        $fullName = $input['fullName'] ?? null;
        $role = $input['role'] ?? null;

        $stmt = $pdo->prepare('UPDATE users SET email = ?, password = ?, full_name = ?, role = ? WHERE id = ?');
        $stmt->execute([$email, $password, $fullName, $role, $id]);

        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        sendJson($stmt->fetch());
        break;

    case 'DELETE':
        if (!$id) {
            sendJson("User ID is required", 400);
        }
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            sendJson("User not found with id: $id", 400);
        }

        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        sendJson("User deleted successfully");
        break;

    default:
        sendJson("Method not allowed", 405);
        break;
}
?>
