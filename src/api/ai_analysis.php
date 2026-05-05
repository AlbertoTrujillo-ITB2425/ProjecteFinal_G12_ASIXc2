<?php
/**
 * CYBERPYME SOC - AI Proxy for Ollama (OPTIMIZED VERSION)
 */

// Reducimos el timeout para no dejar colgado al usuario si falla la IA
set_time_limit(30); 
header('Content-Type: application/json');

 $input = file_get_contents('php://input');
 $data = json_decode($input, true);
 $scan_result = $data['scan_data'] ?? '';

if (empty($scan_result)) {
    echo json_encode(['error' => 'No scan data provided']);
    exit;
}

// --- MEJORA 1: LIMPIEZA DE PROMPT (OPTIMIZACIÓN CLAVE) ---
// En lugar de enviar miles de caracteres, filtramos solo las líneas importantes
 $lines = explode("\n", $scan_result);
 $relevant_lines = [];

foreach ($lines as $line) {
    // Solo guardar líneas que contengan puertos, versiones o vulnerabilidades
    if (preg_match('/(open|tcp|udp|vuln|http|ssh|ftp|smb)/i', $line)) {
        $relevant_lines[] = $line;
    }
}

// Si el filtro falla o está vacío, usar las últimas 20 líneas (resumen)
if (count($relevant_lines) < 2) {
    $relevant_lines = array_slice($lines, -20);
}

// Unimos y limitamos la longitud para no saturar al modelo pequeño
 $clean_context = implode("\n", $relevant_lines);
 $clean_context = substr($clean_context, 0, 1500); // Max 1500 caracteres para velocidad máxima

 $prompt = "Eres un experto SOC. Resume esto en 3 puntos: Riesgo, Puertos Críticos, Acción Inmediata.\n\nDATA:\n" . $clean_context;

// --- MEJORA 2: CONFIGURACIÓN DE PAYLOAD ---
 $payload = [
    "model" => "qwen2.5:0.5b", 
    "prompt" => $prompt,
    "stream" => false, 
    "options" => [
        "temperature" => 0.1, // Muy bajo para respuestas deterministas y rápidas
        "num_predict" => 200, // Solo 200 tokens, suficiente para un resumen
        "num_ctx" => 2048     // Contexto pequeño para el modelo 0.5b
    ],
    "keep_alive" => -1 // MEJORA 3: Mantiene el modelo en RAM (-1 = para siempre o 300 = 5 min)
];

 $url = "http://s12_ollama:11434/api/generate";

 $ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); // Tiempo máximo para conectar
curl_setopt($ch, CURLOPT_TIMEOUT, 15);      // Tiempo máximo para generar respuesta (15s es mucho para 0.5b)

 $response = curl_exec($ch);
 $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
 $curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    // Si falla la IA, devolvemos un mensaje por defecto rápido para no bloquear la app
    echo json_encode([
        "status" => "warning", 
        "response" => "IA Ocupada: Análisis rápido indica puertos abiertos detectados. Revisión manual recomendada."
    ]);
} elseif ($httpCode !== 200) {
    echo json_encode(["error" => "API Ollama Error", "code" => $httpCode]);
} else {
    $ollamaData = json_decode($response, true);
    echo json_encode([
        "status" => "success",
        "response" => $ollamaData['response'] ?? 'Análisis completado sin texto.'
    ]);
}
