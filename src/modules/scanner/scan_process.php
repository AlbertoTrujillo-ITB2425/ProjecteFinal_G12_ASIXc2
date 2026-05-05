<?php
// Configuración de la IA
$ollama_url = "http://s12_ollama:11434/api/generate";
$model_name = "qwen2.5:1.5b"; // DEBE coincidir con tu 'ollama list'

$prompt = "Analiza estos puertos abiertos y detecta vulnerabilidades críticas: " . $nmap_output;

$data = [
    "model" => $model_name,
    "prompt" => $prompt,
    "stream" => false // Para que no te de fallos de buffer en PHP
];

$ch = curl_init($ollama_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($http_code !== 200) {
    die("Error AI: HTTP $http_code. Revisa si el modelo '$model_name' está cargado.");
}

$result = json_decode($response, true);
echo $result['response'];
