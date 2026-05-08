<?php
// core/user/profile_actions.php
session_start();
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Acceso no autorizado']);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'update_name':
        $newName = trim($_POST['name'] ?? '');
        if (strlen($newName) < 3) {
            echo json_encode(['status' => 'error', 'message' => 'El nombre debe tener al menos 3 caracteres']);
            break;
        }
        $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
        if ($stmt->execute([$newName, $_SESSION['user_id']])) {
            $_SESSION['user_name'] = $newName;
            echo json_encode(['status' => 'success']);
        }
        break;

    case 'update_password':
        $pass = $_POST['password'] ?? '';
        if (strlen($pass) < 8) {
            echo json_encode(['status' => 'error', 'message' => 'Contraseña demasiado corta']);
            break;
        }
        $hashed = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        echo $stmt->execute([$hashed, $_SESSION['user_id']]) 
            ? json_encode(['status' => 'success']) 
            : json_encode(['status' => 'error', 'message' => 'Error en DB']);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no reconocida']);
}
