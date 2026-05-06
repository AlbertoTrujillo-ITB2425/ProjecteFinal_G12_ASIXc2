#!/bin/bash

# --- CONFIGURACIÓN ---
REPO_DIR="/home/ubuntu/ProjecteFinal_G7"
# Formato: Día/Mes/Año - Hora:Min
FECHA=$(date +"%d/%m/%Y %H:%M")

# Colores para una terminal pro
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}[SOC-SYSTEM]${NC} Iniciando proceso de respaldo..."

# 1. Navegar al directorio y validar
cd "$REPO_DIR" || { echo -e "${RED}Error: No se encontró el directorio${NC}"; exit 1; }

# 2. Verificar si hay cambios antes de hacer nada
if [ -z "$(git status --porcelain)" ]; then
    echo -e "${YELLOW}[SKIP]${NC} No hay cambios detectados. Abortando."
    exit 0
fi

# 3. Lógica de Versión (Contador simple)
# Leemos el último número de versión de un archivo oculto o empezamos en 1
if [ ! -f .version_counter ]; then
    echo "1.0.0" > .version_counter
fi
VERSION=$(cat .version_counter)

# 4. Git Flow
echo -e "${GREEN}[STEP 1]${NC} Indexando archivos..."
git add .

# Creamos el mensaje con la Versión y la Fecha corregida
COMMIT_MSG="v$VERSION | Release: $FECHA | Automated Backup"

echo -e "${GREEN}[STEP 2]${NC} Creando commit: ${YELLOW}$COMMIT_MSG${NC}"
git commit -m "$COMMIT_MSG"

echo -e "${GREEN}[STEP 3]${NC} Subiendo a producción (Push)..."
git push

# 5. Incrementar versión para el próximo commit (ej: 1.0.1, 1.0.2...)
# Esta pequeña lógica incrementa el último dígito
NEXT_VERSION=$(echo $VERSION | awk -F. '{$NF = $NF + 1;} 1' OFS=.)
echo "$NEXT_VERSION" > .version_counter

echo -e "${BLUE}[SUCCESS]${NC} Backup completado correctamente."
