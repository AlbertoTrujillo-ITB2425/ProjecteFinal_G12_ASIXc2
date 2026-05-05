<?php
// db_conn.php - Sincronizado con .env
$host = 's4_mariadb';
$db   = 'cyberaudit';      // Coincide con tu .env
$user = 'cyberuser';       // Coincide con tu .env
$pass = 'superpassword';   // Coincide con tu .env

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}
