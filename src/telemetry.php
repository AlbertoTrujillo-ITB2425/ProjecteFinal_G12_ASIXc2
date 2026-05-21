<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

require_once "includes/components.php";

header('Content-Type: application/json');

// Primera muestra de CPU
$stat1 = getRealCPUUsage();
usleep(100000); // 100ms de diferencia controlada
$stat2 = getRealCPUUsage();

$diff_total = $stat2['total'] - $stat1['total'];
$diff_idle = $stat2['idle'] - $stat1['idle'];

$cpuPercent = 12; // Valor base por defecto
if ($diff_total > 0) {
    $cpuPercent = round((($diff_total - $diff_idle) / $diff_total) * 100);
}

// Obtener logs frescos
$freshLogs = getSecurityLogs();

echo json_encode([
    "cpu" => min(max($cpuPercent, 0), 100) . "%",
    "logs" => $freshLogs
]);
