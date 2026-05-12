#!/bin/bash
# Este script simula logs de sistema que Wazuh monitoriza

CONTAINER="s7_wazuh"

echo "Generando eventos simulados para Wazuh..."

# 1. Simular intento de SSH fallido (Genera alerta de seguridad)
# Inyectamos una línea falsa en el log auth.log que Wazuh lee
docker exec $CONTAINER bash -c '
echo "$(date "+%b %d %H:%M:%S") sshd[12345]: Failed password for invalid user admin from 192.168.1.100 port 22 ssh2" >> /var/log/auth.log
'

# 2. Simular login exitoso
docker exec $CONTAINER bash -c '
echo "$(date "+%b %d %H:%M:%S") sshd[12346]: Accepted password for root from 192.168.1.101 port 22 ssh2" >> /var/log/auth.log
'

# 3. Forzar a Wazuh a leer los nuevos logs (si tiene filebeat/agent activo)
# En wazuh-manager, a veces hay que reiniciar el módulo de logs o esperar al ciclo
sleep 2

echo "Eventos inyectados en /var/log/auth.log del contenedor Wazuh."
echo "Espera unos segundos para que aparezcan en el dashboard."
