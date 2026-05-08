#!/bin/bash

# ===========================
#  CyberAudit Hub Installer
# ===========================

GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${BLUE}===========================================${NC}"
echo -e "${BLUE}   CyberAudit Hub - Instalador Automático  ${NC}"
echo -e "${BLUE}        Despliegue en Puerto 8080          ${NC}"
echo -e "${BLUE}===========================================${NC}"

# Detectar si es Ubuntu
detect_os() {
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        if [[ "$ID" != "ubuntu" ]]; then
            echo -e "${RED}[ERROR] Este instalador solo funciona en Ubuntu.${NC}"
            exit 1
        fi
    fi
}

# Instalar dependencias en Ubuntu
install_dependencies() {
    echo -e "${YELLOW}[+] Instalando dependencias (Git, Docker)...${NC}"

    sudo apt-get update -y
    sudo apt-get install -y git curl lsof ca-certificates gnupg

    # Instalar Docker
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

    echo -e "${GREEN}[+] Docker y Git instalados correctamente.${NC}"
}

# Aplicar grupo docker sin cerrar sesión
apply_docker_group() {
    newgrp docker <<EONG
echo "Grupo docker aplicado"
EONG
}

# Crear archivo .env si no existe
create_env_file() {
    if [ ! -f .env ]; then
        echo -e "${YELLOW}[+] Creando archivo .env por defecto...${NC}"
        cat <<EOF > .env
DB_NAME=cyberaudit
DB_USER=cyberuser
DB_PASSWORD=superpassword
DB_ROOT_PASSWORD=rootpassword
REDIS_PASSWORD=redispass
LDAP_ADMIN_PASSWORD=adminpass
SHODAN_API_KEY=
EOF
    fi
}

# Detectar IP pública
detect_public_ip() {
    PUBLIC_IP=$(curl -s ifconfig.me || echo "")
    if [[ -z "$PUBLIC_IP" ]]; then
        ACCESS_URL="http://127.0.0.1:8080"
    else
        ACCESS_URL="http://$PUBLIC_IP:8080"
    fi
}

# ===========================
#  EJECUCIÓN PRINCIPAL
# ===========================

detect_os

# Instalar dependencias si faltan
for cmd in docker git; do
    if ! command -v $cmd >/dev/null 2>&1; then
        install_dependencies
        apply_docker_group
        break
    fi
done

# Clonar o actualizar repositorio
REPO_URL="https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7.git"
TARGET_DIR="ProjecteFinal_G7"

if [ -d "$TARGET_DIR" ]; then
    echo -e "${BLUE}[+] Actualizando repositorio...${NC}"
    cd "$TARGET_DIR" && git pull origin main
else
    echo -e "${GREEN}[+] Clonando repositorio...${NC}"
    git clone $REPO_URL
    cd "$TARGET_DIR"
fi

# Crear .env
create_env_file

# Crear directorios necesarios
mkdir -p redis_data ldap_config config/nginx g7_src/views
sudo chown -R $USER:$USER .

# Levantar contenedores
echo -e "${GREEN}[+] Iniciando contenedores (Build)...${NC}"
docker compose up -d --build

# Detectar IP pública
detect_public_ip

echo -e "${BLUE}===========================================${NC}"
echo -e "${GREEN}✅ ¡CyberAudit Hub está operativo!${NC}"
echo -e "${BLUE}Acceso:${NC} $ACCESS_URL"
echo -e "${BLUE}Servicios actuales:${NC}"
docker compose ps
echo -e "${BLUE}===========================================${NC}"
