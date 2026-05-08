#!/bin/bash

# Colores para una interfaz limpia
BLUE='\033[0;34m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${BLUE}==========================================${NC}"
echo -e "${BLUE}   SOC CONFIGURATOR - GENERADOR .ENV      ${NC}"
echo -e "${BLUE}==========================================${NC}"

# Función para preguntar con valor por defecto
ask_value() {
    local prompt=$1
    local default=$2
    local var_name=$3
    
    read -p "$(echo -e ${YELLOW}"$prompt [Default: $default]: "${NC})" input
    if [ -z "$input" ]; then
        eval "$var_name=\"$default\""
    else
        eval "$var_name=\"$input\""
    fi
}

# --- 1. CONFIGURACIÓN DE BASE DE DATOS ---
echo -e "\n${GREEN}[1/4] Base de Datos & Redis${NC}"
ask_value "Nombre de la base de datos" "cyberaudit" DB_NAME
ask_value "Usuario de la base de datos" "cyberuser" DB_USER
ask_value "Contraseña del usuario" "superpassword" DB_PASSWORD
ask_value "Contraseña Root MariaDB" "rootpassword" DB_ROOT_PASSWORD
ask_value "Contraseña de Redis" "redispass" REDIS_PASSWORD
ask_value "Contraseña Admin LDAP" "adminpass" LDAP_ADMIN_PASSWORD

# --- 2. APIs EXTERNAS ---
echo -e "\n${GREEN}[2/4] APIs Externas${NC}"
ask_value "Shodan API Key" "" SHODAN_API_KEY

# --- 3. GOOGLE AUTH ---
echo -e "\n${GREEN}[3/4] Google OAuth${NC}"
ask_value "Google Client ID" "tu_google_id" GOOGLE_CLIENT_ID
ask_value "Google Client Secret" "tu_google_secret" GOOGLE_CLIENT_SECRET
ask_value "Google Redirect URI" "https://cyberpyme.es/core/auth_handler.php" GOOGLE_REDIRECT_URI

# --- 4. MICROSOFT AUTH ---
echo -e "\n${GREEN}[4/4] Microsoft Azure Auth${NC}"
ask_value "Microsoft Client ID" "tu_ms_id" MS_CLIENT_ID
ask_value "Microsoft Tenant ID" "tu_ms_tenant" MS_TENANT_ID
ask_value "Microsoft Client Secret" "tu_ms_secret" MS_CLIENT_SECRET
ask_value "Microsoft Redirect URI" "https://cyberpyme.es/core/auth_handler.php" MS_REDIRECT_URI

# --- GENERACIÓN DEL ARCHIVO ---
cat <<EOF > .env
# --- GENERADO AUTOMÁTICAMENTE EL $(date) ---

# Database & Infra
DB_NAME="$DB_NAME"
DB_USER="$DB_USER"
DB_PASSWORD="$DB_PASSWORD"
DB_ROOT_PASSWORD="$DB_ROOT_PASSWORD"
REDIS_PASSWORD="$REDIS_PASSWORD"
LDAP_ADMIN_PASSWORD="$LDAP_ADMIN_PASSWORD"

# External APIs
SHODAN_API_KEY="$SHODAN_API_KEY"

# Google Auth
GOOGLE_CLIENT_ID="$GOOGLE_CLIENT_ID"
GOOGLE_CLIENT_SECRET="$GOOGLE_CLIENT_SECRET"
GOOGLE_REDIRECT_URI="$GOOGLE_REDIRECT_URI"

# Microsoft Auth
MS_CLIENT_ID="$MS_CLIENT_ID"
MS_TENANT_ID="$MS_TENANT_ID"
MS_CLIENT_SECRET="$MS_CLIENT_SECRET"
MS_REDIRECT_URI="$MS_REDIRECT_URI"
MS_SCOPES="openid profile email User.Read"
EOF

echo -e "\n${BLUE}==========================================${NC}"
echo -e "${GREEN}[OK] Archivo .env generado correctamente.${NC}"
echo -e "${BLUE}==========================================${NC}"
