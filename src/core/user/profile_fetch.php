<?php
// core/user/profile_fetch.php
require_once __DIR__ . '/../db.php';

// Verificación de seguridad
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

$userId = $_SESSION['user_id'];

// 1. Datos frescos del usuario
$stmt = $pdo->prepare("SELECT *, DATE_FORMAT(created_at, '%d %b %Y') as member_since FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// 2. Historial de escaneos
try {
    $stmtScans = $pdo->prepare("SELECT target, type, status, created_at FROM scans WHERE user_id = ? ORDER BY created_at DESC LIMIT 6");
    $stmtScans->execute([$userId]);
    $lastScans = $stmtScans->fetchAll();
} catch (Exception $e) {
    $lastScans = [];
}

$userName  = $user['name'] ?? 'Auditor';
$userEmail = $user['email'] ?? 'No disponible';
$userRole  = $user['role'] ?? 'user';
$avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($userName) . "&background=0ea5e9&color=fff&bold=true&size=128";
