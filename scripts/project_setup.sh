#!/bin/bash

# Colores para la terminal
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # Sin color

echo -e "${BLUE}===========================================${NC}"
echo -e "${BLUE}   CyberAudit Hub - Instalador Automático  ${NC}"
echo -e "${BLUE}        Despliegue en Puerto 8080          ${NC}"
echo -e "${BLUE}===========================================${NC}"

# 1. Verificar dependencias
for cmd in docker git; do
    if ! [ -x "$(command -v $cmd)" ]; then
      echo -e "${RED}Error: $cmd no está instalado.${NC}" >&2
      exit 1
    fi
done

# 2. Verificar si el puerto 8080 está ocupado
if lsof -Pi :8080 -sTCP:LISTEN -t >/dev/null ; then
    echo -e "${YELLOW}Aviso: El puerto 8080 ya está en uso. Revisa tus servicios.${NC}"
fi

# 3. Gestión del repositorio
REPO_URL="https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7.git"
TARGET_DIR="ProjecteFinal_G7"

if [ -d "$TARGET_DIR" ]; then
    echo -e "${BLUE}[+] Entrando en la carpeta existente...${NC}"
    cd "$TARGET_DIR" || exit
    git pull origin main
else
    echo -e "${GREEN}[+] Clonando repositorio...${NC}"
    git clone $REPO_URL
    cd "$TARGET_DIR" || exit
fi

# 4. Arreglar permisos y preparar directorios
echo -e "${GREEN}[+] Configurando entorno y permisos...${NC}"
mkdir -p redis_data ldap_config config/nginx g7_src/views
sudo chown -R $USER:$USER .

# 5. Levantar Docker
echo -e "${GREEN}[+] Iniciando contenedores (Build)...${NC}"
docker-compose up -d --build

# 6. Finalización
echo -e "${BLUE}===========================================${NC}"
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ ¡CyberAudit Hub está operativo!${NC}"
    echo -e "${BLUE}Acceso:${NC} http://localhost:8080"
    echo -e "${YELLOW}Nota:${NC} Si no carga, revisa el mapeo de puertos en docker-compose.yml"
    echo -e "${BLUE}Servicios actuales:${NC}"
    docker-compose ps
else
    echo -e "${RED}❌ El despliegue ha fallado.${NC}"
fi
echo -e "${BLUE}===========================================${NC}"
