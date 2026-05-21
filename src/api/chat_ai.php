<?php
// src/api/chat_ai.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';
$history = $input['history'] ?? []; // Historial de conversación opcional

if (empty($userMessage)) {
    http_response_code(400);
    echo json_encode(["error" => "Mensaje vacío"]);
    exit;
}

// --- CARGA SEGURA DE API KEY ---
$apiKey = getenv('GROQ_API_KEY');

if (!$apiKey && file_exists(__DIR__ . '/config.local.php')) {
    $localConfig = include(__DIR__ . '/config.local.php');
    $apiKey = $localConfig['GROQ_API_KEY'] ?? '';
}

if (!$apiKey) {
    http_response_code(500);
    echo json_encode(["error" => "No se ha configurado la API Key de Groq."]);
    exit;
}

$model = "llama-3.3-70b-versatile"; // El mismo modelo

// Construir mensajes para el contexto
$messages = [
    ["role" => "system", "content" => "Eres CyberPyme SOC Assistant, un experto en ciberseguridad. Responde de forma concisa, técnica y útil. Idioma: Español."]
];

// Añadir historial si existe
foreach ($history as $msg) {
    $messages[] = ["role" => $msg['role'], "content" => $msg['content']];
}

// Añadir mensaje actual
$messages[] = ["role" => "user", "content" => $userMessage];

$payload = [
    "model" => $model,
    "messages" => $messages,
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
    echo json_encode(["response" => "Error de conexión: " . $error]);
} elseif ($httpCode !== 200) {
    echo json_encode(["response" => "Error API Groq: " . $response]);
} else {
    $resDecoded = json_decode($response, true);
    $textResponse = $resDecoded['choices'][0]['message']['content'] ?? 'No se pudo generar respuesta.';
    echo json_encode(["response" => $textResponse]);
}
?>
