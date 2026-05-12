#!/bin/bash
# Este script inyecta alertas falsas en el log de Snort

CONTAINER="s11_snort"

echo "Generando alertas simuladas para Snort..."

# Formatos típicos de alerta de Snort
ALERT_1="[**] [1:1000001:1] ET SCAN Potential VNC Scan [**] [Classification: Attempted Information Leak] [Priority: 2] {TCP} 192.168.1.50:54321 -> 10.0.0.5:5900"
ALERT_2="[**] [1:2000002:1] GPL ATTACK_RESPONSE id check returned root [**] [Classification: Potentially Bad Traffic] [Priority: 2] {TCP} 10.0.0.5:22 -> 192.168.1.50:54322"
ALERT_3="[**] [1:3000003:1] SQL Injection Attempt Detected [**] [Classification: Web Application Attack] [Priority: 1] {TCP} 192.168.1.50:12345 -> 10.0.0.5:80"

# Inyectar las alertas con timestamp actual
docker exec $CONTAINER bash -c "
echo '$(date '+%Y-%m-%d %H:%M:%S.%N') $ALERT_1' >> /var/log/snort/alert
echo '$(date '+%Y-%m-%d %H:%M:%S.%N') $ALERT_2' >> /var/log/snort/alert
echo '$(date '+%Y-%m-%d %H:%M:%S.%N') $ALERT_3' >> /var/log/snort/alert
"

echo "Alertas inyectadas en /var/log/snort/alert del contenedor Snort."
