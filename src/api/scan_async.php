<?php
header('Content-Type: application/json');

/**
 * CYBERPYME SOC - Scan Engine v6.9.1 (Optimized for AI & Speed)
 */

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $target = $data['target'] ?? '';
    $type = $data['type'] ?? $data['action'] ?? 'quick';
} else {
    $target = $_GET['target'] ?? '';
    $type = $_GET['type'] ?? 'quick';
}

if (empty($target)) {
    die(json_encode(["status" => "error", "message" => "Objetivo vacío"]));
}

if (!filter_var($target, FILTER_VALIDATE_IP) && !preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $target)) {
    die(json_encode(["status" => "error", "message" => "Objetivo inválido: " . $target]));
}

$output = "";

if ($type === 'shodan') {
    $apiKey = 'cTZNVRd5Qf4GOpq7TVUoe3K9TdMN1ubt';
    $ip = filter_var($target, FILTER_VALIDATE_IP) ? $target : gethostbyname($target);
    $url = "https://api.shodan.io/shodan/host/$ip?key=$apiKey";
    $ctx = stream_context_create(['http' => ['timeout' => 5]]);
    $shodan_data = @file_get_contents($url, false, $ctx);
    
    if ($shodan_data) {
        $json = json_decode($shodan_data, true);
        $ports = isset($json['ports']) ? implode(', ', $json['ports']) : 'Ninguno';
        $vulns = isset($json['vulns']) ? count($json['vulns']) : 0;
        $output .= "--- OSINT REPORT (SHODAN) ---\n";
        $output .= "IP: {$json['ip_str']}\n";
        $output .= "Vulnerabilidades: $vulns\n";
        $output .= "Puertos: $ports\n";
    } else {
        die(json_encode(["status" => "error", "message" => "Shodan timeout o sin datos."]));
    }

} else {
    // --- LÓGICA NMAP CON TIMEOUT DE SEGURIDAD ---
    // Usamos 'timeout 50' para que el proceso no dure más de un minuto.
    // -T4 es agresivo para velocidad, --host-timeout evita colgarse en hosts lentos.
    $cmd_base = ($type === 'quick') ? "timeout 50 nmap -F -T4 --max-retries 1 " : "timeout 50 nmap -A -T4 --host-timeout 45s ";
    $safe_target = escapeshellarg($target);
    
    $output = shell_exec($cmd_base . $safe_target . " 2>&1");
    
    if (!$output || strpos($output, 'failed') !== false) {
        // Si el comando fue interrumpido por el timeout o falló
        $output = "[SCAN SKIPPED] El host $target tardó demasiado en responder y ha sido omitido para optimizar recursos de IA.";
    }
}

// Para compatibilidad con tu JS, devolvemos el texto puro
echo $output; 
?>
