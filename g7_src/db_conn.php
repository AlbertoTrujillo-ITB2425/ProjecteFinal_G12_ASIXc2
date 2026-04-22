<?php

$host = getenv('DB_HOST') ?: 's4_mariadb';
$db   = getenv('DB_NAME') ?: 'cyberpyme';
$user = getenv('DB_USER') ?: 'cyberpyme_admin';
$pass = getenv('DB_PASSWORD') ?: 'TuPasswordSeguro123!';

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
