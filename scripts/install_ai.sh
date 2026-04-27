#!/bin/bash

echo "[*] Levantando contenedor de Ollama..."
docker compose up -d s12_ollama

echo "[*] Esperando a que el servicio inicie..."
sleep 5

# He elegido qwen2.5:1.5b por ser el equilibrio perfecto para un servidor cloud
echo "[*] Descargando modelo Qwen 2.5 (1.5B)..."
docker exec -it s12_ollama ollama pull qwen2.5:1.5b

echo "[V] Ollama con Qwen 2.5 listo."
