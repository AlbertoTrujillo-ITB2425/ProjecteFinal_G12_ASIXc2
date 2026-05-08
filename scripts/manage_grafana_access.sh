#!/bin/bash

# --- CONFIGURACIÓN ---
PORT=3000
DOCKER_CHAIN="DOCKER-USER" # Esta cadena es la que Docker respeta para filtros de usuario

# --- COLORES ---
BLUE='\033[0;34m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; RED='\033[0;31m'; NC='\033[0m'

echo -e "${BLUE}======================================================${NC}"
echo -e "      SOC FIREWALL: CONTROL REAL PUERTO $PORT          "
echo -e "${BLUE}======================================================${NC}"
echo -e "${GREEN}1)${NC} Permitir mi IP actual"
echo -e "${GREEN}2)${NC} Permitir una IP específica"
echo -e "${RED}3)${NC} BLOQUEO TOTAL (Cerrar puerto $PORT)"
echo -e "4) Ver reglas aplicadas"
echo -e "5) Salir"
read -p "Seleccione opción: " opt

case $opt in
    1)
        MY_IP=$(curl -s ifconfig.me)
        echo -e "${YELLOW}[!]${NC} Autorizando IP: $MY_IP"
        # Insertar la regla al principio de la cadena DOCKER-USER
        sudo iptables -I $DOCKER_CHAIN -p tcp --dport $PORT -s $MY_IP -j ACCEPT
        echo -e "${GREEN}[OK]${NC} Acceso permitido."
        ;;
    2)
        read -p "Introduce la IP: " MANUAL_IP
        sudo iptables -I $DOCKER_CHAIN -p tcp --dport $PORT -s $MANUAL_IP -j ACCEPT
        echo -e "${GREEN}[OK]${NC} Acceso permitido a $MANUAL_IP."
        ;;
    3)
        echo -e "${RED}[!]${NC} Ejecutando Hard Lockdown en puerto $PORT..."
        # Limpiamos reglas previas en esa cadena para evitar duplicados
        sudo iptables -F $DOCKER_CHAIN
        # Bloqueamos cualquier tráfico al puerto 3000 que no haya sido aceptado antes
        sudo iptables -A $DOCKER_CHAIN -p tcp --dport $PORT -j DROP
        echo -e "${RED}[OK]${NC} Puerto cerrado. Solo Nginx (interno) podrá llegar si se requiere."
        ;;
    4)
        echo -e "${BLUE}--- Reglas en DOCKER-USER ---${NC}"
        sudo iptables -L $DOCKER_CHAIN -n --line-numbers
        ;;
    5) exit 0 ;;
esac
