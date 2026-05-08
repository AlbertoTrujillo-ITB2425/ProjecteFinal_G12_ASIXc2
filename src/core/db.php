<?php
// core/db.php
$host = 's4_mariadb';
$db   = 'cyberaudit';
$user = 'root';              // Cambiado de cyberuser a root
$pass = 'rootpassword';      // Cambiado de superpassword a rootpassword

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Esto es vital: si falla, queremos ver el error en el navegador para debugear
    die("❌ Error de conexión: " . $e->getMessage());
}
