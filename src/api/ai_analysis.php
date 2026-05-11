<?php
error_reporting(0); // Evitar que warnings rompan el JSON
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$scan_data = $input['scan_data'] ?? 'Sin datos.';

$apiKey = "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";
$model = "llama-3.3-70b-versatile";

$truncated_data = substr(strip_tags($scan_data), -2000);

$payload = [
    "model" => $model,
    "messages" => [
        ["role" => "system", "content" => "Eres un experto en ciberseguridad. Analiza los logs y resume los 3 riesgos más críticos en español."],
        ["role" => "user", "content" => "Logs técnicos:\n" . $truncated_data]
    ],
    "temperature" => 0.2,
    "max_tokens" => 500
];

$ch = curl_init("https://api.groq.com/openai/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $apiKey",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo json_encode(["response" => "Error de CURL: " . $error]);
} elseif ($httpCode !== 200) {
    echo json_encode(["response" => "Error API Groq (HTTP $httpCode): " . $response]);
} else {
    $resDecoded = json_decode($response, true);
    $textResponse = $resDecoded['choices'][0]['message']['content'] ?? 'Error: Estructura JSON de Groq no reconocida.';
    echo json_encode(["response" => $textResponse]);
}
