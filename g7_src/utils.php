<?php
// g7_src/utils.php

function getEnvVar($key) {
    // Buscamos el archivo .env en la carpeta del proyecto (un nivel arriba de g7_src si es necesario)
    // Según tu ruta: ~/ProjecteFinal_G7/.env
    $envPath = __DIR__ . '/../.env'; 
    
    if (file_exists($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            list($name, $value) = explode('=', $line, 2);
            if (trim($name) === $key) {
                return trim($value);
            }
        }
    }
    return getenv($key); // Fallback a variables de sistema
}
