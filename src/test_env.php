<?php
$path = __DIR__ . '/../.env';
echo "Buscando en: " . realpath($path) . "\n";
$config = parse_ini_file($path);
if ($config) {
    echo "ID de Google: " . ($config['GOOGLE_CLIENT_ID'] ?? 'NO ENCONTRADO EN ARRAY') . "\n";
} else {
    echo "ERROR: No se puede leer el archivo .env\n";
}
