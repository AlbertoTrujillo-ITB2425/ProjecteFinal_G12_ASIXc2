<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$target = $data['target'] ?? '';
$type = $data['action'] ?? 'quick';

// Validación
if (!filter_var($target, FILTER_VALIDATE_IP) && !preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $target)) {
    die(json_encode(["status" => "error", "message" => "Objetivo inválido"]));
}

$output = "";

if ($type === 'shodan') {
    // 1. REAL SHODAN API (Desde el Backend)
    $apiKey = 'cTZNVRd5Qf4GOpq7TVUoe3K9TdMN1ubt';
    $ip = filter_var($target, FILTER_VALIDATE_IP) ? $target : gethostbyname($target);
    
    $url = "https://api.shodan.io/shodan/host/$ip?key=$apiKey";
    $shodan_data = @file_get_contents($url);
    
    if ($shodan_data) {
        $json = json_decode($shodan_data, true);
        $ports = isset($json['ports']) ? implode(', ', $json['ports']) : 'Ninguno detectado';
        $vulns = isset($json['vulns']) ? count($json['vulns']) : 0;
        
        $output .= "--- DATOS OSINT (SHODAN) ---\n";
        $output .= "IP: {$json['ip_str']}\n";
        $output .= "ORG: " . ($json['org'] ?? 'N/A') . "\n";
        $output .= "OS: " . ($json['os'] ?? 'N/A') . "\n";
        $output .= "Puertos: $ports\n";
        $output .= "Vulnerabilidades: $vulns\n";
    } else {
        die(json_encode(["status" => "error", "message" => "Shodan HTTP 403: API Key sin créditos o IP no registrada."]));
    }

} else {
    // 2. REAL NMAP (Ejecución Local)
    $cmd = ($type === 'quick') ? "nmap -F -T4 " : "nmap -A -T4 ";
    $output = shell_exec($cmd . escapeshellarg($target));
    
    if (!$output) {
        die(json_encode(["status" => "error", "message" => "Fallo al ejecutar Nmap. Asegúrate de tenerlo instalado en el servidor."]));
    }
}

echo json_encode(["status" => "success", "result" => $output]);
?>
