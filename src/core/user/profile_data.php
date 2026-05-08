<?php
// core/user/profile_data.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

// Obtener datos base del usuario
$stmt = $pdo->prepare("SELECT *, DATE_FORMAT(created_at, '%d %b %Y') as member_since FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Estadísticas de auditoría
$countScans = 0;
try {
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM scans WHERE user_id = ?");
    $stmtCount->execute([$_SESSION['user_id']]);
    $countScans = $stmtCount->fetchColumn();
} catch (Exception $e) { $countScans = 0; }

// Preparar variables para la vista
$userName  = $user['name'] ?? 'Auditor SOC';
$userEmail = $user['email'] ?? 'No disponible';
$userRole  = $user['role'] ?? 'user';
$avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($userName) . "&background=0ea5e9&color=fff&bold=true&size=128";
