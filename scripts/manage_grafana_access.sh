#!/bin/bash

# --- CONFIGURACIÓN ---
# Ruta relativa al directorio raíz del proyecto
IP_FILE="config/nginx/grafana_ips.conf"
NGINX_CONTAINER="s1_nginx"

# --- COLORES ---
BLUE='\033[0;34m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; RED='\033[0;31m'; NC='\033[0m'

# Asegurar que el archivo existe
touch $IP_FILE

function reload_nginx() {
    echo -e "${YELLOW}[*]${NC} Validando configuración y recargando Nginx..."
    docker exec $NGINX_CONTAINER nginx -t > /dev/null 2>&1
    if [ $? -eq 0 ]; then
        docker exec $NGINX_CONTAINER nginx -s reload
        echo -e "${GREEN}[OK]${NC} Configuración aplicada con éxito."
    else
        echo -e "${RED}[ERROR]${NC} Error en la sintaxis de Nginx. Revisa el archivo de IPs."
    fi
}

echo -e "${BLUE}======================================================${NC}"
echo -e "          SOC FIREWALL: GESTIÓN DE ACCESO             "
echo -e "${BLUE}======================================================${NC}"
echo -e "${GREEN}1)$${NC} Autorizar mi IP actual ($(curl -s ifconfig.me))"
echo -e "${GREEN}2)$${NC} Autorizar una IP manual"
echo -e "${RED}3)$${NC} BLOQUEO TOTAL (Vaciar whitelist)"
echo -e "${YELLOW}4)$${NC} Listar IPs autorizadas"
echo -e "5) Salir"
read -p "Seleccione opción: " opt

case $opt in
    1)
        MY_IP=$(curl -s ifconfig.me)
        # Evitar duplicados
        if grep -q "$MY_IP" "$IP_FILE"; then
            echo -e "${YELLOW}[!]${NC} La IP $MY_IP ya está autorizada."
        else
            echo "\"$MY_IP\" 1;" >> $IP_FILE
            echo -e "${GREEN}[OK]${NC} IP $MY_IP añadida."
            reload_nginx
        fi
        ;;
    2)
        read -p "Introduce la IP: " MANUAL_IP
        echo "\"$MANUAL_IP\" 1;" >> $IP_FILE
        echo -e "${GREEN}[OK]${NC} IP $MANUAL_IP añadida."
        reload_nginx
        ;;
    3)
        echo -n "" > $IP_FILE
        echo -e "${RED}[!]${NC} Lista de acceso vaciada."
        reload_nginx
        ;;
    4)
        echo -e "${BLUE}--- IPs con acceso a Grafana ---${NC}"
        if [ ! -s $IP_FILE ]; then
            echo "La lista está vacía (Bloqueo total activo)."
        else
            cat $IP_FILE
        fi
        ;;
    5)
        exit 0
        ;;
    *)
        echo "Opción no válida."
        ;;
esac
