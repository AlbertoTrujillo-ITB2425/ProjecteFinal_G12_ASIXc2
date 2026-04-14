<?php
// g7_src/db_conn.php
$host = getenv('DB_HOST') ?: 's4_mariadb';
$db   = getenv('DB_NAME') ?: 'cyberaudit_db';
$user = getenv('DB_USER') ?: 'cyberaudit_admin';
$pass = getenv('DB_PASSWORD') ?: 'TuPasswordSeguro123!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    // LA CONSTANTE CORRECTA ES ESTA:
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
} catch (PDOException $e) {
}
?>
