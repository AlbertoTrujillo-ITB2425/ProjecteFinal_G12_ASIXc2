<?php
header('Content-Type: application/json');
$target = $_GET['target'] ?? '';

// Validar que sea IP (Shodan Host API requiere IPs, si envías dominio, resuélvelo antes)
if (!filter_var($target, FILTER_VALIDATE_IP)) {
    echo json_encode(['error' => 'Shodan requiere una dirección IP válida.']);
    exit;
}

// Ocultamos la key aquí, LEJOS del usuario
$shodan_key = getenv('SHODAN_API_KEY') ?: 'TU_API_KEY_AQUI_SOLO_SI_NO_HAY_ENV';
$url = "https://api.shodan.io/shodan/host/{$target}?key={$shodan_key}";

// Usar cURL en lugar de file_get_contents para mejor manejo de errores
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 404) {
    echo json_encode(['error' => 'No hay datos en Shodan para esta IP.']);
} else {
    echo $response; // Devolvemos el JSON de Shodan directamente a nuestro frontend
}
?>
