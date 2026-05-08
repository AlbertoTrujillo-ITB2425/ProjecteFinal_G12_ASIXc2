<?php
session_start();
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesión no válida']);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'update_name':
            $name = trim($_POST['name'] ?? '');
            if (empty($name)) throw new Exception("El nombre no puede estar vacío");
            
            $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
            if ($stmt->execute([$name, $userId])) {
                $_SESSION['user_name'] = $name; // Actualizar sesión
                echo json_encode(['status' => 'success', 'newName' => $name]);
            }
            break;

        case 'update_password':
            $pass = $_POST['password'] ?? '';
            if (strlen($pass) < 4) throw new Exception("Contraseña demasiado corta");
            
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hash, $userId]);
            echo json_encode(['status' => 'success']);
            break;

        case 'clear_data':
            // Elimina escaneos pero mantiene la cuenta
            $stmt = $pdo->prepare("DELETE FROM scans WHERE user_id = ?");
            $stmt->execute([$userId]);
            echo json_encode(['status' => 'success', 'message' => 'Historial de la nube borrado']);
            break;

        case 'logout_devices':
            // Regenerar ID de sesión invalida las cookies de sesión antiguas en muchos entornos
            session_regenerate_id(true);
            echo json_encode(['status' => 'success']);
            break;

        case 'delete_account':
            $pdo->prepare("DELETE FROM scans WHERE user_id = ?")->execute([$userId]);
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
            session_destroy();
            echo json_encode(['status' => 'success']);
            break;

        default:
            throw new Exception("Acción desconocida: " . $action);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
