<?php
error_reporting(0);
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$scan_data = $input['scan_data'] ?? 'Sin datos.';

// --- CARGA SEGURA DE API KEY ---
// 1. Intentamos leerla desde las variables de entorno del sistema.
// 2. Si no existe, buscamos en un archivo de configuración local (no trackeado por Git).
$apiKey = getenv('GROQ_API_KEY');

if (!$apiKey && file_exists(__DIR__ . '/config.local.php')) {
    $localConfig = include(__DIR__ . '/config.local.php');
    $apiKey = $localConfig['GROQ_API_KEY'] ?? '';
}

// Validación por si la clave no se encuentra configurada
if (!$apiKey) {
    echo json_encode(["response" => "⚠️ Error: No se ha configurado la API Key de Groq."]);
    exit;
}

$model = "llama-3.3-70b-versatile";

// Aumentamos un poco el contexto pero mantenemos seguridad
$truncated_data = substr(strip_tags($scan_data), -3000);

// --- PROMPT AVANZADO ---
$system_prompt = "Eres un Analista de Ciberseguridad Senior de un SOC (Security Operations Center). "
               . "Tu tarea es realizar un análisis forense técnico de los logs proporcionados. "
               . "Para cada hallazgo debes: "
               . "1. Identificar la vulnerabilidad (usando terminología MITRE ATT&CK si es posible). "
               . "2. Evaluar el impacto potencial en el negocio. "
               . "3. Proporcionar una solución técnica inmediata (comando, cambio de config o parche). "
               . "Responde en español, con un tono profesional, usando Markdown para resaltar puntos clave.";

$payload = [
    "model" => $model,
    "messages" => [
        ["role" => "system", "content" => $system_prompt],
        ["role" => "user", "content" => "Analiza el siguiente dump de seguridad y genera un informe ejecutivo técnico:\n\n" . $truncated_data]
    ],
    "temperature" => 0.1, // Bajamos la temperatura para que sea más preciso y menos creativo
    "max_tokens" => 800   // Aumentamos tokens para una respuesta más completa
];

// --- EJECUCIÓN DE LA CONSULTA (CURL) ---
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
curl_close($ch);

if ($httpCode !== 200) {
    echo json_encode(["response" => "⚠️ Error en la matriz de análisis (HTTP $httpCode)."]);
} else {
    $resDecoded = json_decode($response, true);
    $textResponse = $resDecoded['choices'][0]['message']['content'] ?? 'Error en procesado.';
    echo json_encode(["response" => $textResponse]);
}
