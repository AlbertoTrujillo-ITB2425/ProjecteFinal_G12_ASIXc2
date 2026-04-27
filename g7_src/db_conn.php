<?php

$host = getenv('DB_HOST') ?: 's4_mariadb';
$db   = getenv('DB_NAME') ?: 'cyberaudit';
$user = getenv('DB_USER') ?: 'cyberuser';
$pass = getenv('DB_PASSWORD') ?: 'superpassword';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("❌ DB CONNECTION ERROR: " . $e->getMessage());
}
