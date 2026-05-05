<?php
header('Content-Type: application/json');

/**
 * CYBERPYME SOC - Scan Engine v6.9.0
 * Soporta GET y POST para máxima compatibilidad.
 */

// 1. Captura de datos (Soporta ambos métodos)
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $target = $data['target'] ?? '';
    $type = $data['type'] ?? $data['action'] ?? 'quick';
} else {
    $target = $_GET['target'] ?? '';
    $type = $_GET['type'] ?? 'quick';
}

// 2. Validación Robusta (IP o Dominio)
if (empty($target)) {
    die(json_encode(["status" => "error", "message" => "Objetivo vacío"]));
}

// Regex que permite dominios locales (.cat, .local) e IPs
if (!filter_var($target, FILTER_VALIDATE_IP) && !preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $target)) {
    die(json_encode(["status" => "error", "message" => "Objetivo inválido: " . $target]));
}

$output = "";

// 3. Lógica Shodan
if ($type === 'shodan') {
    $apiKey = 'cTZNVRd5Qf4GOpq7TVUoe3K9TdMN1ubt';
    // Resolver IP si es un dominio
    $ip = filter_var($target, FILTER_VALIDATE_IP) ? $target : gethostbyname($target);
    
    $url = "https://api.shodan.io/shodan/host/$ip?key=$apiKey";
    
    // Usar timeout para evitar bloqueos
    $ctx = stream_context_create(['http' => ['timeout' => 5]]);
    $shodan_data = @file_get_contents($url, false, $ctx);
    
    if ($shodan_data) {
        $json = json_decode($shodan_data, true);
        $ports = isset($json['ports']) ? implode(', ', $json['ports']) : 'Ninguno';
        $vulns = isset($json['vulns']) ? count($json['vulns']) : 0;
        
        $output .= "--- OSINT REPORT (SHODAN) ---\n";
        $output .= "IP: {$json['ip_str']}\n";
        $output .= "ORG: " . ($json['org'] ?? 'N/A') . "\n";
        $output .= "Vulnerabilidades detectadas: $vulns\n";
        $output .= "Puertos abiertos: $ports\n";
    } else {
        die(json_encode(["status" => "error", "message" => "Shodan: No hay datos o API Key sin créditos."]));
    }

} else {
    // 4. Lógica NMAP Real
    // -F (Fast), -A (Aggressive/Vulnerability)
    $cmd_base = ($type === 'quick') ? "nmap -F -T4 " : "nmap -A -T4 ";
    $safe_target = escapeshellarg($target);
    
    // Ejecución y captura de errores
    $output = shell_exec($cmd_base . $safe_target . " 2>&1");
    
    if (!$output) {
        die(json_encode(["status" => "error", "message" => "Error de ejecución: Nmap no devolvió datos."]));
    }
}

// 5. Respuesta final (El JS espera el texto puro o el JSON dependiendo de tu fetch)
// Para que tu scan.js actual funcione, devolvemos el resultado directamente
echo $output; 
?>
