#!/bin/bash
# Navegar al directorio del proyecto
cd /home/ubuntu/ProjecteFinal_G7 || exit

# Obtener fecha y hora
FECHA=$(date +"%Y-%m-%d %H:%M:%S")

# Ejecutar Git
git add .
git commit -m "Auto-backup: $FECHA"
git push
