#!/bin/bash

# --- CONFIGURACIÓ LOCAL (OLLAMA) ---
echo "[*] Aixecant el contenidor d'Ollama..."
docker compose up -d s12_ollama

echo "[*] Esperant que el servei s'iniciï..."
sleep 5

echo "[*] Descarregant el model Llama 3 (8B) a Ollama..."
docker exec -it s12_ollama ollama pull llama3:8b
echo "[V] Ollama amb Llama 3 llest."

echo "--------------------------------------------------"

# --- CONFIGURACIÓ DE GROQ (Per a Llama-3.3-70b-versatile) ---
echo "[*] Obre aquest enllaç al teu navegador per obtenir la clau de Groq:"
echo "    --> https://console.groq.com/keys"
echo ""

echo "Enganxa aquí la teva API Key de Groq:"
read -r input_key

if [ -n "$input_key" ]; then
    # 1. Exportem a la sessió actual i al .bashrc de l'usuari ubuntu
    export GROQ_API_KEY="$input_key"
    echo "export GROQ_API_KEY=\"$input_key\"" >> ~/.bashrc
    
    # 2. Injecció directa al fitxer PHP des de la carpeta scripts/
    PHP_CONFIG_FILE="../src/api/config.local.php"

    echo "[*] Actualitzant l'API Key a $PHP_CONFIG_FILE..."
    
    if [ ! -f "$PHP_CONFIG_FILE" ]; then
        # Si el fitxer no existeix, el creem de zero amb l'etiqueta PHP
        echo -e "<?php\n\ndefine('GROQ_API_KEY', '$input_key');" > "$PHP_CONFIG_FILE"
        echo "[V] S'ha creat el fitxer de nou amb la clau."
    else
        # Si ja existeix, mirem si ja tenia la constant definida
        if grep -q "GROQ_API_KEY" "$PHP_CONFIG_FILE"; then
            # Si ja existia, la substituïm per la nova per no duplicar
            sed -i "s/define('GROQ_API_KEY',.*/define('GROQ_API_KEY', '$input_key');/g" "$PHP_CONFIG_FILE"
            echo "[V] S'ha actualitzat la clau existent al fitxer PHP."
        else
            # Si el fitxer existia però no tenia la clau, l'afegim abans del tancament o al final
            echo "define('GROQ_API_KEY', '$input_key');" >> "$PHP_CONFIG_FILE"
            echo "[V] S'ha afegit la clau al fitxer PHP existent."
        fi
    fi
else
    echo "[X] No s'ha introduït cap clau. El fitxer PHP no s'ha modificat."
fi

echo ""
echo "[V] Configuració completada correctament per a la carpeta scripts/."
