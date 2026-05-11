<?php
// scan_process.php - Motor de IA SOC con Gemma 2
header("Content-Type: application/json");

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);
$nmap_output = $input['scan_data'] ?? ''; 

if (empty($nmap_output)) {
    echo json_encode(["response" => "No hay datos de escaneo para analizar."]);
    exit;
}

// ENDPOINT de tu contenedor Docker
$ollama_url = "http://s12_ollama:11434/api/generate"; 

// MODELO: Debe ser idéntico al de tu 'ollama list'
$model_name = "gemma2:2b"; 

$prompt = "Eres un analista de seguridad en un SOC. 
Analiza estos resultados de Nmap y genera un informe ejecutivo:
1. Resumen de puertos abiertos.
2. Posibles vulnerabilidades detectadas.
3. Recomendación de mitigación.
Sé técnico, breve y responde en español.
Datos de Nmap:
$nmap_output";

$data = [
    "model" => $model_name,
    "prompt" => $prompt,
    "stream" => false,
    "options" => [
        "temperature" => 0.3, // Menos alucinación, más realidad técnica
        "num_ctx" => 4096     // Para que quepan logs de Nmap largos
    ]
];

$ch = curl_init($ollama_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 45); // Gemma2:2b es rápida, 45s es suficiente

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($http_code !== 200) {
    $error = curl_error($ch);
    echo json_encode(["response" => "Error de conexión con el contenedor s12_ollama (HTTP $http_code). $error"]);
    exit;
}

curl_close($ch);

$result = json_decode($response, true);
echo json_encode(["response" => $result['response'] ?? "Error: La IA no devolvió texto."]);
