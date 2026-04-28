<?php
/**
 * CYBERPYME SOC - AI Proxy for Ollama (Ultra-Fast Mini Version)
 * Connects to s12_ollama container via net_private
 */

// Reduïm una mica el límit de temps ja que el model mini és molt ràpid
set_time_limit(90); 
header('Content-Type: application/json');

// 1. Obtenir les dades de la petició (JSON)
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$scan_result = $data['scan_data'] ?? '';

if (empty($scan_result)) {
    echo json_encode(['error' => 'No scan data provided for analysis']);
    exit;
}

// 2. Configurar el Prompt (Optimitzat per a un model més petit)
$prompt = "Ets un expert en ciberseguretat. Analitza aquest escaneig breument. 
Identifica ports crítics i dóna recomanacions ràpides.
FORMAT: Resum, Riscos i Conclusions.

RESULTATS:
" . $scan_result;

// 3. Preparar el Payload (IMPORTANT: model qwen2.5:0.5b)
$payload = [
    "model" => "qwen2.5:0.5b", 
    "prompt" => $prompt,
    "stream" => false, 
    "options" => [
        "temperature" => 0.3, // Temperatura baixa per a respostes més tècniques i menys creatives
        "num_predict" => 500  // Respostes més curtes per a màxima velocitat
    ]
];

// 4. Configuració de CURL
$url = "http://s12_ollama:11434/api/generate";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

// Configuració de temps per al model mini
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
curl_setopt($ch, CURLOPT_TIMEOUT, 60); 

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// 5. Gestió de sortida
if ($curlError) {
    http_response_code(500);
    echo json_encode([
        "error" => "Connexió fallida amb l'IA (Mini)",
        "details" => $curlError
    ]);
} elseif ($httpCode !== 200) {
    http_response_code($httpCode);
    echo json_encode([
        "error" => "L'IA ha tornat error $httpCode",
        "raw" => $response
    ]);
} else {
    // Èxit: Retornem la resposta d'Ollama al JavaScript
    echo $response;
}
