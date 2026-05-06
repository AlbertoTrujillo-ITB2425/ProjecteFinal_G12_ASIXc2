<?php
session_start();
require_once __DIR__ . '/db.php';

// Establecemos que la respuesta siempre sea JSON
header('Content-Type: application/json');

// Verificación de seguridad: ¿Está el usuario logueado?
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesión no autorizada']);
    exit;
}

$action = $_POST['action'] ?? '';
$userId = $_SESSION['user_id'];

// --- ACCIÓN: ACTUALIZAR NOMBRE ---
if ($action === 'update_name') {
    $newName = trim($_POST['name'] ?? '');

    if (empty($newName)) {
        echo json_encode(['status' => 'error', 'message' => 'El nombre no puede estar vacío']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
        if ($stmt->execute([$newName, $userId])) {
            // ¡Importante! Actualizamos la sesión para que el cambio sea global e inmediato
            $_SESSION['user_name'] = $newName;
            echo json_encode(['status' => 'success', 'new_name' => $newName]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar en la base de datos']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error de servidor: ' . $e->getMessage()]);
    }
    exit;
}

// --- ACCIÓN: ACTUALIZAR CONTRASEÑA (Opcional pero recomendado) ---
if ($action === 'update_password') {
    $newPass = $_POST['password'] ?? '';

    if (strlen($newPass) < 6) {
        echo json_encode(['status' => 'error', 'message' => 'La contraseña debe tener al menos 6 caracteres']);
        exit;
    }

    try {
        // Encriptamos la nueva contraseña antes de guardarla
        $hashedPass = password_hash($newPass, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        
        if ($stmt->execute([$hashedPass, $userId])) {
            echo json_encode(['status' => 'success', 'message' => 'Password actualizada']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar password']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Si no coincide ninguna acción
echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
