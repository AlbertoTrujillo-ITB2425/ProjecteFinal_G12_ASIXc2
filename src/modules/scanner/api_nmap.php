<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// Leer el cuerpo de la petición (necesario para fetch con JSON)
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

// Extraer target y tipo
$target = $input['target'] ?? $_REQUEST['target'] ?? null;
$type = $input['type'] ?? 'quick';

if (!$target) {
    echo json_encode([
        "status" => "error",
        "message" => "No se recibió el objetivo (IP/Dominio)."
    ]);
    exit;
}

// Sanitización
$target = preg_replace('/[^A-Za-z0-9\.\-\/]/', '', $target);

/**
 * CONFIGURACIÓN DE COMANDOS
 * Añadimos -Pn para saltar el descubrimiento de host y evitar falsos "Host down"
 */
switch ($type) {
    case 'full':
        $params = "-sV -T4 -Pn"; // Escaneo de servicios y versiones
        break;
    case 'vuln':
        $params = "-Pn --script vuln"; // Scripts de vulnerabilidades
        break;
    case 'quick':
    default:
        $params = "-F -Pn"; // Escaneo rápido de los 100 puertos principales
        break;
}

$command = "nmap $params " . escapeshellarg($target) . " 2>&1";

// Ejecutar
exec($command, $output, $return_var);

if ($return_var !== 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Nmap falló. Código: $return_var",
        "result" => implode("\n", $output)
    ]);
} else {
    echo json_encode([
        "status" => "success",
        "result" => implode("\n", $output)
    ]);
}
