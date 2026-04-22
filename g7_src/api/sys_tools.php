<?php
header('Content-Type: application/json');
$target = $_GET['target'] ?? '';
$tool = $_GET['tool'] ?? '';

// 1. SANITIZAR EL OBJETIVO. Expresión regular que solo permite IPs, dominios y guiones.
if (!preg_match('/^[a-zA-Z0-9.-]+$/', $target)) {
    echo json_encode(['error' => 'Target inválido. Evita caracteres especiales.']);
    exit;
}

// 2. ESCAPAR EL COMANDO (La regla de oro)
$safe_target = escapeshellarg($target);

$output = "";
switch ($tool) {
    case 'ping':
        // -c 4 en Linux, -n 4 en Windows
        $output = shell_exec("ping -c 4 $safe_target 2>&1");
        break;
    case 'whois':
        $output = shell_exec("whois $safe_target 2>&1");
        break;
    case 'dig':
        $output = shell_exec("dig +short $safe_target 2>&1");
        break;
    default:
        $output = "Herramienta no soportada.";
}

echo json_encode(['output' => $output]);
?>
