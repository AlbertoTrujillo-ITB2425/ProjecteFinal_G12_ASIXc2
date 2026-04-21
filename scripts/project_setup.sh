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

# FORZAR lectura desde la terminal real (pantalla)
exec < /dev/tty

echo -n "➤ Introduce tu dominio (ej: cyberaudit.com): "
read DOMAIN
echo -n "➤ Introduce tu email para el certificado: "
read EMAIL

if [[ -z "$DOMAIN" || -z "$EMAIL" ]]; then
    echo -e "${RED}[ERROR] No proporcionaste los datos por pantalla. Abortando.${NC}"
    exit 1
fi

# --- A partir de aquí el script sigue su proceso normal ---

echo -e "${YELLOW}[+] Iniciando configuración para: $DOMAIN${NC}"

# 1. Preparar configuración temporal de Nginx
cat <<EOF > ./temp_nginx_http.conf
server {
    listen 80;
    server_name $DOMAIN;
    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
    }
}
EOF

# 2. Inyectar en el contenedor s1_nginx
docker exec s1_nginx mkdir -p /var/www/certbot
docker cp ./temp_nginx_http.conf s1_nginx:/etc/nginx/conf.d/default.conf
docker exec s1_nginx nginx -s reload

# 3. Obtener Certificados con Certbot (Contenedor temporal)
docker run --rm \
    -v "$(pwd)/temp_certbot/conf:/etc/letsencrypt" \
    -v "$(pwd)/temp_certbot/www:/var/www/certbot" \
    certbot/certbot certonly --webroot \
    --webroot-path=/var/www/certbot \
    --email $EMAIL --agree-tos --no-eff-email -d $DOMAIN

if [ $? -ne 0 ]; then
    echo -e "${RED}[ERROR] No se pudo obtener el certificado. Revisa tu dominio.${NC}"
    exit 1
fi

# 4. Inyectar certificados y config final SSL en s1_nginx
docker exec s1_nginx mkdir -p /etc/letsencrypt
docker cp ./temp_certbot/conf/. s1_nginx:/etc/letsencrypt/

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
    location / {
        proxy_pass http://s2_node:9000;
        proxy_set_header Host \$host;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
}
EOF

docker cp ./temp_nginx_https.conf s1_nginx:/etc/nginx/conf.d/default.conf
docker exec s1_nginx nginx -s reload

echo -e "${GREEN}✅ ¡HTTPS activado internamente en s1_nginx para $DOMAIN!${NC}"
