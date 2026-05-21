#!/bin/bash

# ===========================================
#   CyberPyme Hub - Instal·lador
# ===========================================

REPO_DIR="/home/ubuntu/ProjecteFinal_G7"
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${BLUE}===========================================${NC}"
echo -e "${BLUE}   CyberPyme Hub - Instal·lador Automàtic  ${NC}"
echo -e "${BLUE}           Desplegament de Seguretat       ${NC}"
echo -e "${BLUE}===========================================${NC}"

# 1. DETECTAR SISTEMA OPERATIU
detect_os() {
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        if [[ "$ID" != "ubuntu" ]]; then
            echo -e "${RED}[ERROR] Aquest instal·lador només funciona en entorns Ubuntu.${NC}"
            exit 1
        fi
    fi
}

# 2. INSTAL·LAR DEPENDÈNCIES DEL SISTEMA
install_dependencies() {
    echo -e "${YELLOW}[+] Instal·lant dependències del sistema (Git, Docker)...${NC}"

    sudo apt-get update -y
    sudo apt-get install -y git curl lsof ca-certificates gnupg

    # Instal·lació oficial del motor de Docker
    sudo install -m 0755 -d /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    sudo chmod a+r /etc/apt/keyrings/docker.gpg

    echo \
    "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \
    https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" \
    | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

    sudo apt-get update -y
    sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

    sudo usermod -aG docker $USER

    echo -e "${GREEN}[+] Docker i Git s'han instal·lat correctament.${NC}"
}

apply_docker_group() {
    newgrp docker <<EONG
echo "Grup docker aplicat amb èxit"
EONG
}

# 3. DETECTAR IP PÚBLICA DEL SERVIDOR
detect_public_ip() {
    PUBLIC_IP=$(curl -s ifconfig.me || echo "")
    if [[ -z "$PUBLIC_IP" ]]; then
        ACCESS_URL="http://127.0.0.1:8080"
    else
        ACCESS_URL="http://$PUBLIC_IP:8080"
    fi
}

# ===========================================
#              EXECUCIÓ PRINCIPAL
# ===========================================

detect_os

# Comprovació de binaris bàsics
for cmd in docker git; do
    if ! command -v $cmd >/dev/null 2>&1; then
        install_dependencies
        apply_docker_group
        break
    fi
done

# Control i sincronització del Repositori Git
REPO_URL="https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7.git"
TARGET_DIR="ProjecteFinal_G7"

if [[ "$PWD" == *"$TARGET_DIR"* ]]; then
    echo -e "${BLUE}[+] Ja estàs dins de la carpeta del projecte. Actualitzant Git...${NC}"
    git pull origin main
else
    if [ -d "$TARGET_DIR" ]; then
        echo -e "${BLUE}[+] Carpeta detectada. Sincronitzant amb el repositori remot...${NC}"
        cd "$TARGET_DIR" && git pull origin main
    else
        echo -e "${GREEN}[+] Clonant el projecte des de GitHub...${NC}"
        git clone $REPO_URL
        cd "$TARGET_DIR"
    fi
fi

# ===========================================
#         PROVISIÓ D'INFRAESTRUCTURA
# ===========================================
echo -e "${BLUE}[SOC-SYSTEM]${NC} Configurant l'entorn local d'infraestructura de CyberPyme..."

# Creació de directoris per a persistència de dades (Volums Docker)
echo -e "${GREEN}[1/4]${NC} Generant volums de dades i directoris de logs..."
mkdir -p db_data redis_data ldap_data ldap_config ollama_data grafana_data certbot/www
mkdir -p snort_logs mail_logs logs/nginx logs/php

# Assignació de permisos crítics per a la recollida de logs
chmod -R 777 snort_logs mail_logs logs

# FIX de permisos específic per a Grafana (UID 472 intern del contenidor)
echo -e "      ${BLUE}[FIX]${NC} Aplicant permisos de seguretat per a Grafana (UID 472)..."
sudo chown -R 472:472 grafana_data
chmod -R 775 grafana_data

# Generació d'esquemes d'inicialització (Init SQL i LDAP)
echo -e "${GREEN}[2/4]${NC} Generant fitxers d'inicialització (Init SQL/LDAP)..."
mkdir -p config/init

# Esquema automàtic per a MariaDB (Corregit a cyberpyme)
cat <<EOF > config/init/db_init.sql
CREATE DATABASE IF NOT EXISTS cyberpyme;
USE cyberpyme;
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
EOF

# Esquema automàtic per a OpenLDAP (Corregit a cyberpyme.local)
cat <<EOF > config/init/ldap_init.ldif
dn: ou=users,dc=cyberpyme,dc=local
objectClass: organizationalUnit
ou: users

dn: ou=groups,dc=cyberpyme,dc=local
objectClass: organizationalUnit
ou: groups
EOF

# Validació de variables d'entorn (.env) securitzades
echo -e "${GREEN}[3/4]${NC} Verificant configuració de l'entorn (.env)..."
if [ ! -f .env ]; then
    echo -e "      ${YELLOW}[!]${NC} Fitxer .env no detectat."
    if [ -f scripts/generate_env.sh ]; then
        bash scripts/generate_env.sh
    else
        echo -e "      ${RED}[ERROR]${NC} No s'ha trobat scripts/generate_env.sh per generar les claus."
        exit 1
    fi
else
    echo -e "      ${GREEN}OK:${NC} Fitxer .env detectat correctament."
fi

# Configuració de persistència de sessions distribuïdes (PHP -> Redis)
echo -e "${GREEN}[4/4]${NC} Configurant pont de sessió PHP -> Redis..."
mkdir -p src/includes
cat <<EOF > src/includes/session_redis.php
<?php
/**
 * Configuració de persistència de sessió en clúster Redis
 * Autogenerat per project_setup.sh (Tot en Un)
 */
ini_set('session.save_handler', 'redis');
\$redis_pass = getenv('REDIS_PASSWORD') ?: 'redispass';
ini_set('session.save_path', "tcp://s5_redis:6379?auth=\$redis_pass");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
EOF

# ===========================================
#         DESPLEGAMENT DE CONTENIDORS
# ===========================================
echo -e "${GREEN}[+] Aixecant l'ecosistema de contenidors en segon pla (Build)...${NC}"
docker compose up -d --build

# Diagnòstic final
detect_public_ip

echo -e "${BLUE}===========================================${NC}"
echo -e "${GREEN}✅ ¡CyberPyme està completament operatiu!${NC}"
echo -e "${BLUE}Accés Web:${NC} $ACCESS_URL"
echo -e "${BLUE}Estat actual dels serveis:${NC}"
docker compose ps
echo -e "${BLUE}===========================================${NC}"
