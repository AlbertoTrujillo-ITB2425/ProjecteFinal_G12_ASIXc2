#!/bin/bash

# --- CONFIGURACIÓN DE GRADO MILITAR ---
REPO_DIR="/home/ubuntu/ProjecteFinal_G7"
LOG_FILE="$REPO_DIR/logs/backup_history.log"
LOCK_FILE="/tmp/soc_backup.lock"
VERSION_FILE="$REPO_DIR/.version_counter"
FECHA=$(date +"%Y-%m-%d %H:%M:%S")
TIMESTAMP=$(date +"%s")

# Colores SOC-Style
G='\033[0;32m' # Green
B='\033[0;34m' # Blue
Y='\033[1;33m' # Yellow
R='\033[0;31m' # Red
NC='\033[0m'    # No Color

# 1. EVITAR EJECUCIÓN DUPLICADA (Locking)
if [ -f "$LOCK_FILE" ]; then
    echo -e "${R}[CRITICAL]${NC} Otra instancia del backup ya está en ejecución. Abortando."
    exit 1
fi
touch "$LOCK_FILE"

# Función de salida segura
finish() {
    rm -f "$LOCK_FILE"
}
trap finish EXIT

echo -e "${B}[SOC-SYSTEM]${NC} Iniciando respaldo de alta integridad..."

# 2. VALIDACIÓN DE DIRECTORIO
cd "$REPO_DIR" || { echo -e "${R}[FAIL]${NC} Directorio no encontrado."; exit 1; }
mkdir -p logs # Asegurar que existe carpeta de logs

# 3. LIMPIEZA PRE-BACKUP (Mantenimiento de infraestructura)
echo -e "${G}[MAINTENANCE]${NC} Rotando logs internos y temporales..."
find ./snort_logs -type f -name "*.log.*" -mtime +7 -delete 2>/dev/null

# 4. SINCRONIZACIÓN INTELIGENTE (Anti-Rejection)
echo -e "${G}[SYNC]${NC} Trayendo cambios remotos (Pull Rebase)..."
# Intentamos 3 veces si la red de AWS/Cloudflare falla
RETRY=0
until [ $RETRY -ge 3 ]
do
    git pull --rebase --autostash origin main && break
    RETRY=$[$RETRY+1]
    echo -e "${Y}[RETRY]${NC} Error de red. Intento $RETRY de 3..."
    sleep 5
done

# 5. VERIFICACIÓN DE CAMBIOS LOCALES
if [ -z "$(git status --porcelain)" ]; then
    echo -e "${Y}[IDLE]${NC} No hay cambios exclusivos en Ubuntu. Sincronización completada."
    exit 0
fi

# 6. GESTIÓN DE VERSIÓN
if [ ! -f "$VERSION_FILE" ]; then echo "1.0.0" > "$VERSION_FILE"; fi
VERSION=$(cat "$VERSION_FILE")

# 7. INDEXACIÓN Y COMMIT
echo -e "${G}[INTEGRITY]${NC} Indexando nuevos vectores de datos..."
git add .

COMMIT_MSG="v$VERSION | SOC-Backup | $FECHA | Hash: $TIMESTAMP"
git commit -m "$COMMIT_MSG"

# 8. SUBIDA (PUSH) CON VERIFICACIÓN DE SALIDA
echo -e "${G}[UPLOAD]${NC} Transfiriendo a servidor seguro GitHub..."
if git push origin main; then
    # Incrementar versión solo si tuvo éxito
    NEXT_VERSION=$(echo $VERSION | awk -F. '{$NF = $NF + 1;} 1' OFS=.)
    echo "$NEXT_VERSION" > "$VERSION_FILE"
    
    echo -e "${G}[SUCCESS]${NC} Backup $VERSION completado y verificado."
    echo "[$FECHA] SUCCESS - v$VERSION - Push OK" >> "$LOG_FILE"
else
    echo -e "${R}[ERROR]${NC} Fallo crítico en el envío. Los cambios se mantienen localmente."
    echo "[$FECHA] ERROR - v$VERSION - Push Failed" >> "$LOG_FILE"
    exit 1
fi
