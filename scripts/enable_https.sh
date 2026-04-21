#!/bin/bash

# Colores
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${BLUE}==============================================${NC}"
echo -e "${BLUE}   CyberAudit Hub - Inyector SSL Interno      ${NC}"
echo -e "${BLUE}==============================================${NC}"

# 1. Parámetros de configuración
read -p "Introduce tu dominio (ej: cyberaudit.com): " DOMAIN
read -p "Introduce tu email: " EMAIL

if [[ -z "$DOMAIN" || -z "$EMAIL" ]]; then
    echo -e "${RED}[ERROR] Faltan datos necesarios.${NC}"
    exit 1
fi

# 2. Crear carpetas temporales en el host para generar los certificados
mkdir -p ./temp_certbot/conf ./temp_certbot/www

# 3. Configurar Nginx temporalmente para el reto de Certbot
# Creamos el archivo y lo "empujamos" dentro del contenedor
echo -e "${YELLOW}[+] Inyectando configuración de reto ACME en s1_nginx...${NC}"
cat <<EOF > ./temp_nginx_http.conf
server {
    listen 80;
    server_name $DOMAIN;
    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
    }
    location / {
        return 301 https://\$host\$request_uri;
    }
}
EOF

# Aseguramos que el directorio existe dentro del contenedor y copiamos
docker exec s1_nginx mkdir -p /var/www/certbot
docker cp ./temp_nginx_http.conf s1_nginx:/etc/nginx/conf.d/default.conf

# Recargamos Nginx para que empiece a servir el reto
docker exec s1_nginx nginx -s reload

# 4. Ejecutar Certbot para obtener los certificados
echo -e "${GREEN}[+] Solicitando certificados a Let's Encrypt...${NC}"
docker run --rm \
    -v "$(pwd)/temp_certbot/conf:/etc/letsencrypt" \
    -v "$(pwd)/temp_certbot/www:/var/www/certbot" \
    certbot/certbot certonly --webroot \
    --webroot-path=/var/www/certbot \
    --email $EMAIL --agree-tos --no-eff-email -d $DOMAIN

if [ $? -ne 0 ]; then
    echo -e "${RED}[ERROR] Certbot falló. Revisa que el dominio apunte a esta IP.${NC}"
    exit 1
fi

# 5. Mover los certificados al interior del contenedor s1_nginx
echo -e "${YELLOW}[+] Inyectando certificados SSL dentro del contenedor...${NC}"
docker exec s1_nginx mkdir -p /etc/letsencrypt
docker cp ./temp_certbot/conf/. s1_nginx:/etc/letsencrypt/

# 6. Inyectar la configuración final HTTPS
echo -e "${YELLOW}[+] Aplicando configuración SSL final internamente...${NC}"
cat <<EOF > ./temp_nginx_https.conf
server {
    listen 80;
    server_name $DOMAIN;
    return 301 https://\$host\$request_uri;
}

server {
    listen 443 ssl;
    server_name $DOMAIN;

    ssl_certificate /etc/letsencrypt/live/$DOMAIN/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/$DOMAIN/privkey.pem;

    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers on;

    location / {
        proxy_pass http://s2_node:9000;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
}
EOF

docker cp ./temp_nginx_https.conf s1_nginx:/etc/nginx/conf.d/default.conf

# 7. Limpieza de archivos temporales en el host
rm ./temp_nginx_http.conf ./temp_nginx_https.conf

# 8. Reiniciar Nginx para cargar el puerto 443
# NOTA: Para que el puerto 443 funcione, debe estar declarado en el docker-compose.yml
echo -e "${YELLOW}[+] Verificando configuración y reiniciando...${NC}"
docker exec s1_nginx nginx -t
docker restart s1_nginx

echo -e "${BLUE}==============================================${NC}"
echo -e "${GREEN}✅ ¡S1_NGINX configurado y cifrado!${NC}"
echo -e "${BLUE}URL:${NC} https://$DOMAIN"
echo -e "${YELLOW}Aviso: Si no puedes acceder, asegúrate de que el puerto 443"
echo -e "esté abierto en tu docker-compose y en el firewall (AWS/Azure).${NC}"
echo -e "${BLUE}==============================================${NC}"
