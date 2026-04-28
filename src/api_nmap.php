<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// Leer el cuerpo de la petición (necesario para fetch con JSON)
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

// Extraer target y tipo (buscamos en el JSON o en REQUEST por si acaso)
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

// Construir comando según tipo
$params = "-F"; // Default quick
if ($type === 'full') $params = "-sV -T4";
if ($type === 'vuln') $params = "--script vuln";

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
