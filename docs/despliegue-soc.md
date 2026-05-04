# 🛡️ SOC G7 — Despliegue completo: S0 Firewall + socmail.php

> **Proyecto:** CyberPyme G7  
> **Servidor:** AWS EC2 — `ubuntu@3.215.30.52`  
> **Objetivo:** Añadir S0 (Firewall), actualizar docker-compose y desplegar la interfaz de Mail + Snort

---

## 📋 Índice

1. [Conectarse al servidor](#1--conectarse-al-servidor)
2. [Crear ficheros nuevos](#2--crear-ficheros-nuevos)
3. [Actualizar docker-compose.yml](#3--actualizar-docker-composeyml)
4. [Subir socmail.php](#4--subir-socmailphp)
5. [Levantar los contenedores](#5--levantar-los-contenedores)
6. [Verificar el despliegue](#6--verificar-el-despliegue)
7. [Abrir la web](#7--abrir-la-web)
8. [Resumen de ficheros](#-resumen-de-ficheros)

---

## 1 — Conectarse al servidor

> 💻 **Ejecutar desde: tu PC local**

```bash
ssh ubuntu@3.215.30.52
```

---

## 2 — Crear ficheros nuevos

> 🖥️ **Ejecutar desde: servidor AWS (dentro del SSH)**

```bash
cd ~/ProjecteFinal_G7
```

### `Dockerfile.s0`

```bash
cat > Dockerfile.s0 << 'EOF'
FROM ubuntu:22.04
ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get update && apt-get install -y \
    iptables iproute2 net-tools nftables \
    iputils-ping curl && rm -rf /var/lib/apt/lists/*
COPY firewall.sh /firewall.sh
RUN chmod +x /firewall.sh
CMD ["/firewall.sh"]
EOF
```

### `firewall.sh`

```bash
cat > firewall.sh << 'EOF'
#!/bin/bash
echo 1 > /proc/sys/net/ipv4/ip_forward
iptables -t nat -A POSTROUTING -o eth0 -j MASQUERADE
iptables -A FORWARD -i eth1 -o eth0 -j ACCEPT
iptables -A FORWARD -i eth0 -o eth1 -m state --state RELATED,ESTABLISHED -j ACCEPT
echo "S0 Firewall activo"
tail -f /dev/null
EOF
chmod +x firewall.sh
```

### Carpetas de volúmenes

```bash
mkdir -p mail_logs snort_logs
```

---

## 3 — Actualizar `docker-compose.yml`

> 🖥️ **Ejecutar desde: servidor AWS**

```bash
nano ~/ProjecteFinal_G7/docker-compose.yml
```

> Borra todo el contenido y pega lo siguiente. Guardar: `Ctrl+O` → `Enter` → `Ctrl+X`

```yaml
services:

  # S0: Firewall (OPNsense equivalent)
  s0_firewall:
    build:
      context: .
      dockerfile: Dockerfile.s0
    container_name: s0_firewall
    cap_add:
      - NET_ADMIN
      - NET_RAW
      - SYS_ADMIN
    sysctls:
      - net.ipv4.ip_forward=1
      - net.ipv4.conf.all.forwarding=1
    networks:
      - net_public
      - net_private
    restart: always

  # S1: Gateway & Load Balancer
  s1_nginx:
    image: nginx:1.24-alpine
    container_name: s1_nginx
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./g7_src:/var/www/html
      - ./config/nginx/default.conf:/etc/nginx/conf.d/default.conf
      - /etc/letsencrypt:/etc/letsencrypt:ro
      - ./certbot/www:/var/www/certbot
    networks:
      - net_public
      - net_private
    restart: always
    depends_on:
      - s0_firewall
      - s2_node
      - s3_node

  # S2 & S3: Web Nodes (PHP-FPM)
  s2_node:
    build: .
    container_name: s2_node
    volumes:
      - ./g7_src:/var/www/html
      - ./mail_logs:/var/mail:ro        # Lee buzón de Postfix
      - ./snort_logs:/var/log/snort:ro  # Lee alertas de Snort
    networks:
      - net_public
      - net_private
    restart: always

  s3_node:
    build: .
    container_name: s3_node
    volumes:
      - ./g7_src:/var/www/html
      - ./mail_logs:/var/mail:ro
      - ./snort_logs:/var/log/snort:ro
    networks:
      - net_public
      - net_private
    restart: always

  # S4: Database
  s4_mariadb:
    image: mariadb:10.11
    container_name: s4_mariadb
    environment:
      - MYSQL_ROOT_PASSWORD=${DB_ROOT_PASSWORD}
      - MYSQL_DATABASE=${DB_NAME}
      - MYSQL_USER=${DB_USER}
      - MYSQL_PASSWORD=${DB_PASSWORD}
    volumes:
      - ./db_data:/var/lib/mysql
    networks:
      - net_private
    restart: always

  # S5: Redis
  s5_redis:
    image: redis:7-alpine
    container_name: s5_redis
    command: redis-server --requirepass ${REDIS_PASSWORD}
    networks:
      - net_private
    restart: always

  # S6: LDAP
  s6_openldap:
    image: osixia/openldap:1.5.0
    container_name: s6_openldap
    environment:
      - LDAP_ORGANISATION="CyberAudit Group 7"
      - LDAP_DOMAIN="g7.local"
      - LDAP_ADMIN_PASSWORD=${LDAP_ADMIN_PASSWORD}
    volumes:
      - ./ldap_data:/var/lib/ldap
    networks:
      - net_private
    restart: always

  # S7: SIEM (Wazuh)
  s7_wazuh:
    image: wazuh/wazuh-manager:4.7.2
    container_name: s7_wazuh
    ports:
      - "1514:1514"
      - "55000:55000"
    networks:
      - net_private
    restart: always

  # S8: Grafana
  s8_grafana:
    image: grafana/grafana:10.2.3
    container_name: s8_grafana
    ports:
      - "3000:3000"
    environment:
      - GF_SECURITY_ADMIN_PASSWORD=${GRAFANA_PASS:-admin123}
    networks:
      - net_private
    restart: always

  # S9: Scanner
  s9_scanner:
    build: .
    container_name: s9_scanner
    volumes:
      - ./g7_src:/var/www/html
    env_file: .env
    networks:
      - net_public
      - net_private
    cap_add:
      - NET_RAW
      - NET_ADMIN
    restart: always

  # S10: Postfix
  s10_postfix:
    build:
      context: .
      dockerfile: Dockerfile.s10_s11
    container_name: s10_postfix
    ports:
      - "25:25"
      - "587:587"
    volumes:
      - ./mail_logs:/var/log/mail
      - ./mail_logs:/var/mail           # Expone buzón mbox para PHP
    networks:
      - net_private
    restart: always

  # S11: Snort
  s11_snort:
    build:
      context: .
      dockerfile: Dockerfile.s10_s11
    container_name: s11_snort
    command: snort -A fast -i eth0 -c /etc/snort/snort.conf -l /var/log/snort
    volumes:
      - ./snort_logs:/var/log/snort
    cap_add:
      - NET_ADMIN
      - NET_RAW
    networks:
      - net_private
    restart: always

  # S12: Ollama AI Engine
  s12_ollama:
    image: ollama/ollama:latest
    container_name: s12_ollama
    volumes:
      - ./ollama_data:/root/.ollama
    networks:
      - net_private
    restart: always

networks:
  net_public:
    driver: bridge
  net_private:
    driver: bridge
```

### Cambios aplicados respecto al original

| Servicio | Cambio |
|---|---|
| **S0** | Servicio nuevo — firewall perimetral |
| **S1** | `depends_on: s0_firewall` añadido |
| **S2 / S3** | 2 volúmenes `:ro` nuevos para leer mail y snort |
| **S10** | Volumen extra `./mail_logs:/var/mail` para exponer el buzón |
| **S11** | `-A console` → `-A fast -l /var/log/snort` para escribir el fichero `alert` |

---

## 4 — Subir `socmail.php`

### Opción A — Desde tu PC local (recomendado)

> 💻 **Ejecutar desde: tu PC local** (en otra terminal, sin cerrar la del servidor)

```bash
scp /ruta/local/socmail.php ubuntu@3.215.30.52:~/ProjecteFinal_G7/g7_src/socmail.php
```

> **Windows con PowerShell:**
> ```powershell
> scp C:\Users\TuUsuario\Downloads\socmail.php ubuntu@3.215.30.52:~/ProjecteFinal_G7/g7_src/socmail.php
> ```

### Opción B — Crear directamente en el servidor

> 🖥️ **Ejecutar desde: servidor AWS**

```bash
nano ~/ProjecteFinal_G7/g7_src/socmail.php
# Pega el contenido → Ctrl+O → Enter → Ctrl+X
```

---

## 5 — Levantar los contenedores

> 🖥️ **Ejecutar desde: servidor AWS**

```bash
cd ~/ProjecteFinal_G7

# Parar todo
docker compose down

# Reconstruir y levantar
docker compose up -d --build
```

---

## 6 — Verificar el despliegue

> 🖥️ **Ejecutar desde: servidor AWS**

```bash
# Todos los contenedores corriendo
docker ps --format "table {{.Names}}\t{{.Status}}"

# s2_node ve los ficheros de mail y snort
docker exec s2_node ls -la /var/mail
docker exec s2_node ls -la /var/log/snort

# Snort está escribiendo el fichero alert
docker exec s11_snort ls -la /var/log/snort/

# S0 firewall activo
docker logs s0_firewall
```

### ✅ Resultado esperado

```
# docker ps
s0_firewall    Up X seconds
s1_nginx       Up X seconds
s2_node        Up X seconds
s3_node        Up X seconds
s10_postfix    Up X seconds
s11_snort      Up X seconds

# /var/mail
root   (fichero del buzón de Postfix)

# /var/log/snort
alert  (fichero de alertas de Snort)

# logs s0_firewall
S0 Firewall activo
```

---

## 7 — Abrir la web

> 💻 **Desde tu navegador (PC local)**

```
# 1. Hacer login
http://3.215.30.52/auth.php

# 2. Abrir la interfaz de Mail + Snort
http://3.215.30.52/socmail.php
```

---

## 📁 Resumen de ficheros

| Fichero | Ubicación en el servidor | Acción |
|---|---|---|
| `Dockerfile.s0` | `~/ProjecteFinal_G7/` | 🆕 Nuevo |
| `firewall.sh` | `~/ProjecteFinal_G7/` | 🆕 Nuevo |
| `docker-compose.yml` | `~/ProjecteFinal_G7/` | ✏️ Modificado |
| `socmail.php` | `~/ProjecteFinal_G7/g7_src/` | 🆕 Nuevo |
| `mail_logs/` | `~/ProjecteFinal_G7/` | 📁 Carpeta nueva |
| `snort_logs/` | `~/ProjecteFinal_G7/` | 📁 Carpeta nueva |

---

> 📝 **Nota:** Si `socmail.php` muestra error de sesión, asegúrate de que `auth.php` y `socmail.php` comparten el mismo `session_start()` y están en el mismo dominio/contenedor PHP.
