#!/bin/bash

# --- CONFIGURACIÓN ---
REPO_DIR="/home/ubuntu/ProjecteFinal_G7"
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${BLUE}[SOC-SETUP]${NC} Iniciando provisión de infraestructura segura..."

cd "$REPO_DIR" || { echo -e "${RED}Error: No se encontró el directorio${NC}"; exit 1; }

# 1. CREAR CARPETAS DE PERSISTENCIA (DATOS)
echo -e "${GREEN}[1/5]${NC} Creando directorios de datos y logs..."
mkdir -p db_data redis_data ldap_data ldap_config ollama_data grafana_data certbot/www
mkdir -p snort_logs mail_logs logs/nginx logs/php
chmod -R 777 snort_logs mail_logs logs

# 2. MIGRACIÓN DE CARPETA SETUP (DB & LDAP INIT)
echo -e "${GREEN}[2/5]${NC} Generando scripts de inicialización (Init SQL/LDAP)..."
mkdir -p config/init

# Crear esquema de DB (Lo que estaba en setup/db/init.sql)
cat <<EOF > config/init/db_init.sql
CREATE DATABASE IF NOT EXISTS cyberaudit;
USE cyberaudit;
-- Aquí puedes añadir tus tablas de usuarios, logs de auditoría, etc.
CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50), password VARCHAR(255));
EOF

# Crear esquema de LDAP (Lo que estaba en setup/ldap/init.ldif)
cat <<EOF > config/init/ldap_init.ldif
dn: ou=users,dc=g7,dc=local
objectClass: organizationalUnit
ou: users

dn: ou=groups,dc=g7,dc=local
objectClass: organizationalUnit
ou: groups
EOF

# 3. GENERACIÓN DE .ENV INTERACTIVO
echo -e "${GREEN}[3/5]${NC} Configurando variables de entorno..."
if [ ! -f .env ]; then
    # Usamos el generador interactivo que hicimos antes o uno rápido aquí:
    cat <<EOF > .env
DB_NAME="cyberaudit"
DB_USER="cyberuser"
DB_PASSWORD="superpassword"
DB_ROOT_PASSWORD="rootpassword"
REDIS_PASSWORD="redispass"
LDAP_ADMIN_PASSWORD="adminpass"
# Añade aquí tus CLIENT_ID y SECRETS de Google/MS
EOF
    echo -e "${YELLOW}[AVISO]${NC} Se ha creado un .env básico. Edítalo con tus credenciales de Google/MS."
else
    echo -e "${BLUE}[SKIP]${NC} El archivo .env ya existe."
fi

# 4. CONFIGURACIÓN DE SESIÓN REDIS
echo -e "${GREEN}[4/5]${NC} Configurando persistencia de sesión PHP -> Redis..."
mkdir -p src/includes
cat <<EOF > src/includes/session_redis.php
<?php
ini_set('session.save_handler', 'redis');
\$redis_pass = getenv('REDIS_PASSWORD') ?: 'redispass';
ini_set('session.save_path', "tcp://s5_redis:6379?auth=\$redis_pass");
if (session_status() === PHP_SESSION_NONE) { session_start(); }
EOF

# 5. LIMPIEZA DE SEGURIDAD EN GIT (Borrar carpetas sensibles)
echo -e "${GREEN}[5/5]${NC} Eliminando rastros sensibles de Git (Certificados, Logs, Setup)..."
# Eliminamos físicamente la carpeta setup ya que su contenido ahora vive en config/init
rm -rf setup/

# Comandos Git para limpiar el historial (No borran los archivos de Ubuntu, solo de GitHub)
git rm -r --cached setup/ certs/ temp_certbot/ snort_logs/ logs/ db_data/ redis_data/ 2>/dev/null
git add .gitignore scripts/setup_infrastructure.sh config/init/

echo -e "\n${BLUE}[SUCCESS]${NC} Infraestructura lista y repositorio saneado."
echo -e "${YELLOW}[NEXT STEP]${NC} Ejecuta: ${BLUE}git commit -m \"Final Refactor: Setup removed and infra automated\" && git push${NC}"
