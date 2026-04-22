<?php
// g7_src/api/scan_async.php
require_once '../utils.php';

$ip = $_GET['ip'] ?? '';
$apiKey = getEnvVar('SHODAN_API_KEY');

$results = [
    'nmap' => '',
    'recomendaciones' => []
];

if ($ip) {
    // 1. Ejecutar Nmap (Básico)
    $results['nmap'] = shell_exec("nmap -F " . escapeshellarg($ip));

    // 2. Consultar Shodan si tenemos la Key
    if ($apiKey) {
        $url = "https://api.shodan.io/shodan/host/{$ip}?key={$apiKey}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $shodanData = json_decode($response, true);
        curl_close($ch);

        if (isset($shodanData['ports'])) {
            $results['nmap'] .= "\n[SHODAN INFO] Puertos detectados en histórico: " . implode(', ', $shodanData['ports']);
            
            // Añadir recomendaciones basadas en Shodan
            foreach ($shodanData['data'] as $service) {
                if (!empty($service['vuls'])) {
                    $results['recomendaciones'][] = [
                        'riesgo' => 'CRÍTICO',
                        'msg' => "CVE detectado en puerto {$service['port']}: " . implode(', ', array_keys($service['vuls']))
                    ];
                }
            }
        }
    }
}

header('Content-Type: application/json');
echo json_encode($results);
