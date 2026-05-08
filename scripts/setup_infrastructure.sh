#!/bin/bash

# --- CONFIGURACIÓN ---
REPO_DIR="/home/ubuntu/ProjecteFinal_G7"
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${BLUE}[SOC-SYSTEM]${NC} Iniciando provisión de infraestructura..."

# 1. VALIDACIÓN DE DIRECTORIO
cd "$REPO_DIR" || { echo -e "${RED}Error: No se encontró el directorio del proyecto${NC}"; exit 1; }

# 2. CREACIÓN DE PERSISTENCIA
echo -e "${GREEN}[1/4]${NC} Generando volúmenes de datos y directorios de logs..."
# Carpetas de datos para contenedores
mkdir -p db_data redis_data ldap_data ldap_config ollama_data grafana_data certbot/www
# Carpetas de registros del sistema
mkdir -p snort_logs mail_logs logs/nginx logs/php

# --- FIX DE PERMISOS (CRÍTICO) ---
# Permisos generales para logs (777 para que Snort y Nginx escriban sin problemas)
chmod -R 777 snort_logs mail_logs logs

# FIX específico para Grafana: El contenedor usa el UID 472
echo -e "      ${BLUE}[FIX]${NC} Aplicando permisos de seguridad para Grafana (UID 472)..."
sudo chown -R 472:472 grafana_data
chmod -R 775 grafana_data

echo -e "      ${GREEN}OK:${NC} Estructura de archivos y permisos preparados."

# 3. GENERACIÓN DE ESQUEMAS DE INICIALIZACIÓN
echo -e "${GREEN}[2/4]${NC} Generando archivos de inicialización (Init SQL/LDAP)..."
mkdir -p config/init

# Esquema de Base de Datos MariaDB
cat <<EOF > config/init/db_init.sql
CREATE DATABASE IF NOT EXISTS cyberaudit;
USE cyberaudit;
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
EOF

# Esquema de Directorio OpenLDAP
cat <<EOF > config/init/ldap_init.ldif
dn: ou=users,dc=g7,dc=local
objectClass: organizationalUnit
ou: users

dn: ou=groups,dc=g7,dc=local
objectClass: organizationalUnit
ou: groups
EOF
echo -e "      ${GREEN}OK:${NC} Scripts de configuración inyectados en config/init/."

# 4. VALIDACIÓN DE VARIABLES DE ENTORNO
echo -e "${GREEN}[3/4]${NC} Verificando configuración del entorno (.env)..."
if [ ! -f .env ]; then
    echo -e "      ${YELLOW}[!]${NC} Archivo .env no detectado."
    if [ -f scripts/generate_env.sh ]; then
        bash scripts/generate_env.sh
    else
        echo -e "      ${RED}[ERROR]${NC} No se encuentra scripts/generate_env.sh."
        exit 1
    fi
else
    echo -e "      ${GREEN}OK:${NC} Archivo .env detectado."
fi

# 5. PERSISTENCIA DE SESIÓN (REDIS)
echo -e "${GREEN}[4/4]${NC} Configurando puente de sesión PHP -> Redis..."
mkdir -p src/includes
cat <<EOF > src/includes/session_redis.php
<?php
/**
 * Configuración de persistencia de sesión en clúster Redis
 * Autogenerado por setup_infrastructure.sh
 */
ini_set('session.save_handler', 'redis');
\$redis_pass = getenv('REDIS_PASSWORD') ?: 'redispass';
ini_set('session.save_path', "tcp://s5_redis:6379?auth=\$redis_pass");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
EOF

echo -e "\n${BLUE}[SUCCESS]${NC} La infraestructura del SOC está lista."
echo -e "${BLUE}[INFO]${NC} Use '${YELLOW}docker compose up -d --build${NC}' para iniciar los servicios."
