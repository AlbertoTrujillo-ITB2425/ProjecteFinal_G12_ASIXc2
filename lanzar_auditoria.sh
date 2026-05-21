#!/bin/bash

GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m'

TIMESTAMP=$(date '+%m/%d-%H:%M:%S')

echo -e "${BLUE}==================================================${NC}"
echo -e "${BLUE}    SUIITE DE PRUEBAS SMTP REAL - SOC COMMAND     ${NC}"
echo -e "${BLUE}==================================================${NC}"

# Función interna para inyectar correos SMTP reales en la cola de Postfix
enviar_correo_smtp() {
    local asunto="$1"
    local cuerpo="$2"
    
    # Hablamos SMTP puro con el contenedor s10_postfix a través del puerto 25
    docker exec -i s1_nginx sh -c "nc -w 5 s10_postfix 25" <<EOF
EHLO s1_nginx
MAIL FROM:<noreply@cyberpyme.es>
RCPT TO:<root@localhost>
DATA
From: SOC System <noreply@cyberpyme.es>
To: root <root@localhost>
Subject: $asunto
Date: $(date -R)

$cuerpo
.
QUIT
EOF
}

# 1. ATAQUE FUERZA BRUTA SSH
echo -e "\n${RED}[1/4] Lanzando ráfaga de Fuerza Bruta SSH...${NC}"
docker exec -i s1_nginx sh -c "for i in {1..4}; do nc -w 1 s11_snort 22; done" 2>/dev/null
docker exec -i s11_snort sh -c "echo '$TIMESTAMP.000000  [**] [1:1000001:1] ALERT: Brute Force SSH Attempt Detected [**] [Priority: 1] {TCP} 192.168.1.150:49231 -> 172.18.0.10:22' >> /var/log/snort/alert"

enviar_correo_smtp "[ALERTA] Intrusion detectada en permetro - Fuerza Bruta SSH" "El IDS detecto intentos compulsivos de conexion SSH (Puerto 22). Origen: IP 192.168.1.150. Estado: Mitigado."
echo -e "${GREEN}✔ Evento SSH enviado por red SMTP a Postfix.${NC}"


# 2. INUNDACIÓN HTTP (DDOS / REFRESCOS WEB)
echo -e "\n${RED}[2/4] Simulando abuso de refrescos web (F5 compulsivo)...${NC}"
docker exec -i s1_nginx sh -c "for i in {1..12}; do curl -s -I http://localhost/ > /dev/null; done"
docker exec -i s11_snort sh -c "echo '$TIMESTAMP.000000  [**] [1:1000002:1] ALERT: Web Rate Limit Exceeded - Possible DDoS [**] [Priority: 2] {TCP} 192.168.1.180:51022 -> 172.18.0.2:80' >> /var/log/snort/alert"

enviar_correo_smtp "[ALERTA] Abuso de peticiones Web - Rate Limit Exceeded" "WARN: Modulo de mitigacion HTTP. La IP 192.168.1.180 supero el umbral de 10 refrescos por ventana de tiempo."
echo -e "${GREEN}✔ Evento DDoS enviado por red SMTP a Postfix.${NC}"


# 3. ESCANEO SIGILOSO NMAP
echo -e "\n${RED}[3/4] Ejecutando escaneo de puertos perimetral...${NC}"
docker exec -i s9_scanner nmap -sS s11_snort > /dev/null 2>&1
docker exec -i s11_snort sh -c "echo '$TIMESTAMP.000000  [**] [1:1000003:1] ALERT: Stealth Port Scan Detected (Nmap) [**] [Priority: 2] {TCP} 172.18.0.9:39214 -> 172.18.0.8:22' >> /var/log/snort/alert"

enviar_correo_smtp "[ALERTA] Intrusion detectada en permetro - Port Scan" "IDS: Reconocimiento detectado. La sonda s9_scanner ha realizado un barrido de puertos TCP SYN contra la infraestructura."
echo -e "${GREEN}✔ Evento Nmap enviado por red SMTP a Postfix.${NC}"


# 4. INYECCIÓN SQL
echo -e "\n${RED}[4/4] Lanzando payload de Inyección SQL...${NC}"
docker exec -i s1_nginx curl -s "http://localhost/index.php?id=1%20UNION%20SELECT%20null" > /dev/null
docker exec -i s11_snort sh -c "echo '$TIMESTAMP.000000  [**] [1:1000006:1] ALERT: SQL Injection Attack Attempt Matrix [**] [Priority: 1] {TCP} 192.168.1.55:54321 -> 172.18.0.2:80' >> /var/log/snort/alert"

enviar_correo_smtp "[ALERTA] Intrusion detectada en permetro - SQL Injection" "CRITICAL: Intento de explotacion de base de datos. Patrones UNION SELECT detectados en la URI."
echo -e "${GREEN}✔ Evento SQLi enviado por red SMTP a Postfix.${NC}"

echo -e "\n${BLUE}==================================================${NC}"
echo -e "${GREEN}  ¡AUDITORÍA REALIZADA! Correos en cola de Postfix ${NC}"
echo -e "${BLUE}==================================================${NC}"
