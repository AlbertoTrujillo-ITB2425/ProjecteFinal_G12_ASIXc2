# 🛡️ CyberAudit SaaS - Projecte Final G7

[![Version](https://img.shields.io/badge/version-1.0.0--20260414-blue.svg)](https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7/tags)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?logo=docker&logoColor=white)](https://www.docker.com/)
[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?logo=php&logoColor=white)](https://www.php.net/)

> **Choose your language / Tria el teu idioma / Elige tu idioma**
> - [🇪🇸 Castellano](#-castellano)
> - [🇬🇧 English](#-english)
> - [🇪🇸 Català](#-català)

---

## 🇪🇸 Castellano

### 📋 Descripción del Proyecto

**CyberAudit SaaS** es una plataforma web de ciberseguridad diseñada específicamente para **pequeñas y medianas empresas** (small caps) como panaderías, farmacias, carnicerías, tiendas locales y comercios tradicionales que no disponen de departamentos IT dedicados.

#### 🎯 ¿Qué hace CyberAudit?

- **🔍 Análisis de Amenazas**: Monitorización continua de posibles vulnerabilidades en sistemas conectados
- **📊 Panel de Control**: Dashboard intuitivo con métricas de seguridad en tiempo real
- **🚨 Alertas Proactivas**: Notificaciones automáticas ante actividades sospechosas
- **📈 Monitorización 24/7**: Vigilancia constante de tus servicios digitales
- **📝 Informes Periódicos**: Reportes mensuales sobre el estado de seguridad
- **🛡️ Protección WAF**: Firewall de aplicaciones web integrado

#### 💼 ¿Para quién es?

Perfecta para negocios que:
- Tienen **presencia online** (web, e-commerce, redes sociales)
- Manejan **datos de clientes** (emails, pedidos, pagos)
- No tienen **presupuesto para un equipo IT completo**
- Necesitan **cumplir con RGPD** y normativas de protección de datos
- Quieren **dormir tranquilos** sabiendo que están protegidos

### 🏗️ Arquitectura del Sistema

```
┌─────────────────────────────────────────────────┐
│          🌐 Internet (Puerto 8080)              │
└────────────────┬────────────────────────────────┘
                 │
        ┌────────▼────────┐
        │   🛡️ BunkerWeb  │ ◄── WAF + Reverse Proxy
        │   (Firewall)    │
        └────────┬────────┘
                 │
        ┌────────▼────────┐
        │  🐘 PHP-FPM     │ ◄── Backend de Aplicación
        │  (g7-backend)   │
        └───┬─────┬───┬───┘
            │     │   │
    ┌───────▼─┐ ┌─▼───────┐ ┌─▼──────────┐
    │ 💾 Redis│ │🗄️ MariaDB│ │👥 OpenLDAP │
    │Sessions │ │  (DB)    │ │  (Users)   │
    └─────────┘ └──────────┘ └────────────┘
```

#### 🔧 Componentes Técnicos

| Componente | Tecnología | Función |
|------------|------------|---------|
| **WAF** | BunkerWeb | Protección contra ataques web (SQL injection, XSS, DDoS) |
| **Backend** | PHP 8.x + FPM | Lógica de aplicación y procesamiento |
| **Sesiones** | Redis 7 | Gestión rápida y segura de sesiones de usuario |
| **Base de Datos** | MariaDB 10.11 | Almacenamiento de auditorías y configuraciones |
| **Autenticación** | OpenLDAP | Directorio centralizado de usuarios y permisos |
| **Orquestación** | Docker Compose | Despliegue y gestión de contenedores |

### 🚀 Instalación Rápida

#### Prerrequisitos
```bash
# Verificar instalaciones
docker --version          # Docker 20.10+
docker-compose --version  # Docker Compose 2.0+
git --version            # Git 2.30+
```

#### Paso 1: Clonar el Repositorio
```bash
git clone https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7.git
cd ProjecteFinal_G7
```

#### Paso 2: Configurar Variables de Entorno
```bash
# Crear archivo .env con credenciales seguras
cat > .env << EOF
# Credenciales de Base de Datos
DB_ROOT_PASSWORD=$(openssl rand -base64 32)

# Contraseña de Redis
REDIS_PASSWORD=$(openssl rand -base64 32)

# Contraseña de LDAP Admin
LDAP_ADMIN_PASSWORD=$(openssl rand -base64 32)
EOF

# ⚠️ IMPORTANTE: Guardar estas credenciales en un lugar seguro
cat .env
```

#### Paso 3: Desplegar la Aplicación
```bash
# Construir y levantar todos los servicios
docker-compose up -d --build

# Verificar que todos los contenedores están corriendo
docker-compose ps
```

#### Paso 4: Acceder a la Plataforma
```bash
# La aplicación estará disponible en:
# http://localhost:8080
# http://127.0.0.1:8080

# Ver logs en tiempo real
docker-compose logs -f
```

### 📊 Verificación del Sistema

```bash
# Estado de todos los servicios
docker-compose ps

# Salud del firewall
docker logs cyberaudit-firewall | tail -20

# Estado del backend PHP
docker logs g7-backend | tail -20

# Test de conectividad
curl -I http://localhost:8080
```

**Salida esperada:**
```
HTTP/1.1 200 OK
Date: ...
Content-Type: text/html; charset=utf-8
X-Content-Type-Options: nosniff
```

### 🔒 Características de Seguridad

- ✅ **Headers de Seguridad**: CSP, HSTS, X-Frame-Options configurados
- ✅ **Aislamiento de Red**: Redes privadas para servicios internos
- ✅ **Sesiones Cifradas**: Redis con autenticación por contraseña
- ✅ **Contraseñas Hasheadas**: Nunca se almacenan en texto plano
- ✅ **WAF Activo**: Protección contra OWASP Top 10
- ✅ **Volúmenes Persistentes**: Datos seguros ante reinicios

### 🛠️ Comandos Útiles

```bash
# Reiniciar todos los servicios
docker-compose restart

# Parar la aplicación
docker-compose down

# Parar y eliminar volúmenes (⚠️ BORRA DATOS)
docker-compose down -v

# Ver logs de un servicio específico
docker-compose logs -f cyberaudit-firewall
docker-compose logs -f g7-backend
docker-compose logs -f mariadb

# Acceder a un contenedor
docker exec -it g7-backend /bin/bash
docker exec -it cyberaudit-db mysql -u root -p

# Backup de la base de datos
docker exec cyberaudit-db mysqldump -u root -p${DB_ROOT_PASSWORD} --all-databases > backup_$(date +%Y%m%d).sql

# Restaurar backup
docker exec -i cyberaudit-db mysql -u root -p${DB_ROOT_PASSWORD} < backup_20260414.sql
```

### 📁 Estructura del Proyecto

```
ProjecteFinal_G7/
│
├── 📄 docker-compose.yml      # Orquestación de servicios
├── 📄 Dockerfile              # Imagen PHP personalizada
├── 📄 .env                    # Variables de entorno (NO COMMITEAR)
├── 📄 .gitignore              # Archivos ignorados por Git
│
├── 📂 g7_src/                 # Código fuente de la aplicación
│   ├── index.php
│   ├── config.php
│   └── ...
│
├── 📂 config/                 # Configuraciones
│   └── php-sessions.ini       # Configuración de sesiones PHP
│
├── 📂 db_data/                # Datos de MariaDB (persistentes)
├── 📂 ldap_data/              # Datos de OpenLDAP (persistentes)
│
└── 📄 README.md               # Este archivo
```

### 🐛 Solución de Problemas

#### ❌ El puerto 8080 ya está en uso
```bash
# Cambiar el puerto en docker-compose.yml
ports:
  - 8081:8080  # Usar 8081 en su lugar
```

#### ❌ Los contenedores no arrancan
```bash
# Ver errores detallados
docker-compose logs

# Reconstruir imágenes
docker-compose build --no-cache
docker-compose up -d
```

#### ❌ Error de permisos en volúmenes
```bash
# Ajustar permisos (Linux/Mac)
sudo chown -R $(whoami):$(whoami) db_data/ ldap_data/
```

#### ❌ No puedo conectar a la base de datos
```bash
# Verificar que la contraseña en .env es correcta
cat .env

# Probar conexión manual
docker exec -it cyberaudit-db mysql -u root -p
```

### 📈 Roadmap

- [x] Infraestructura base con Docker
- [x] Integración de WAF (BunkerWeb)
- [x] Sistema de sesiones con Redis
- [ ] Panel de administración completo
- [ ] Sistema de alertas por email/SMS
- [ ] Integración con APIs de threat intelligence
- [ ] App móvil para notificaciones
- [ ] Certificados SSL automatizados
- [ ] Backup automático cloud

### 👥 Equipo de Desarrollo

**Grupo 7 - ITB (Institut Tecnològic de Barcelona)**

- **Alberto Trujillo** - Project Lead & DevOps
- [Añadir más miembros del equipo]

### 📄 Licencia

Este proyecto está bajo la licencia MIT. Ver el archivo [LICENSE](LICENSE) para más detalles.

### 🤝 Contribuir

```bash
# Fork el proyecto
# Crear una rama
git checkout -b feature/nueva-funcionalidad

# Commit cambios
git commit -m "Add: nueva funcionalidad increíble"

# Push a la rama
git push origin feature/nueva-funcionalidad

# Abrir Pull Request
```

### 📞 Soporte

- 📧 Email: [alberto@cyberaudit.local](mailto:alberto@cyberaudit.local)
- 🐛 Issues: [GitHub Issues](https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7/issues)
- 📚 Wiki: [Documentación completa](https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7/wiki)

---

## 🇬🇧 English

### 📋 Project Description

**CyberAudit SaaS** is a cybersecurity web platform specifically designed for **small and medium-sized businesses** (SMBs) such as bakeries, pharmacies, butcher shops, local stores, and traditional retailers that don't have dedicated IT departments.

#### 🎯 What does CyberAudit do?

- **🔍 Threat Analysis**: Continuous monitoring of potential vulnerabilities in connected systems
- **📊 Control Panel**: Intuitive dashboard with real-time security metrics
- **🚨 Proactive Alerts**: Automatic notifications for suspicious activities
- **📈 24/7 Monitoring**: Constant surveillance of your digital services
- **📝 Periodic Reports**: Monthly reports on security status
- **🛡️ WAF Protection**: Integrated web application firewall

#### 💼 Who is it for?

Perfect for businesses that:
- Have **online presence** (website, e-commerce, social media)
- Handle **customer data** (emails, orders, payments)
- Don't have **budget for a full IT team**
- Need to **comply with GDPR** and data protection regulations
- Want to **sleep peacefully** knowing they're protected

### 🏗️ System Architecture

```
┌─────────────────────────────────────────────────┐
│          🌐 Internet (Port 8080)                │
└────────────────┬────────────────────────────────┘
                 │
        ┌────────▼────────┐
        │   🛡️ BunkerWeb  │ ◄── WAF + Reverse Proxy
        │   (Firewall)    │
        └────────┬────────┘
                 │
        ┌────────▼────────┐
        │  🐘 PHP-FPM     │ ◄── Application Backend
        │  (g7-backend)   │
        └───┬─────┬───┬───┘
            │     │   │
    ┌───────▼─┐ ┌─▼───────┐ ┌─▼──────────┐
    │ 💾 Redis│ │🗄️ MariaDB│ │👥 OpenLDAP │
    │Sessions │ │  (DB)    │ │  (Users)   │
    └─────────┘ └──────────┘ └────────────┘
```

### 🚀 Quick Start

#### Prerequisites
```bash
# Verify installations
docker --version          # Docker 20.10+
docker-compose --version  # Docker Compose 2.0+
git --version            # Git 2.30+
```

#### Step 1: Clone Repository
```bash
git clone https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7.git
cd ProjecteFinal_G7
```

#### Step 2: Configure Environment Variables
```bash
# Create .env file with secure credentials
cat > .env << EOF
# Database Credentials
DB_ROOT_PASSWORD=$(openssl rand -base64 32)

# Redis Password
REDIS_PASSWORD=$(openssl rand -base64 32)

# LDAP Admin Password
LDAP_ADMIN_PASSWORD=$(openssl rand -base64 32)
EOF

# ⚠️ IMPORTANT: Save these credentials in a secure location
cat .env
```

#### Step 3: Deploy Application
```bash
# Build and start all services
docker-compose up -d --build

# Verify all containers are running
docker-compose ps
```

#### Step 4: Access Platform
```bash
# Application will be available at:
# http://localhost:8080
# http://127.0.0.1:8080

# View real-time logs
docker-compose logs -f
```

### 🔒 Security Features

- ✅ **Security Headers**: CSP, HSTS, X-Frame-Options configured
- ✅ **Network Isolation**: Private networks for internal services
- ✅ **Encrypted Sessions**: Redis with password authentication
- ✅ **Hashed Passwords**: Never stored in plain text
- ✅ **Active WAF**: Protection against OWASP Top 10
- ✅ **Persistent Volumes**: Secure data across restarts

### 📞 Support

- 📧 Email: [alberto@cyberaudit.local](mailto:alberto@cyberaudit.local)
- 🐛 Issues: [GitHub Issues](https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7/issues)
- 📚 Wiki: [Full Documentation](https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7/wiki)

---

## 🇪🇸 Català

### 📋 Descripció del Projecte

**CyberAudit SaaS** és una plataforma web de ciberseguretat dissenyada específicament per a **petites i mitjanes empreses** (small caps) com flequeries, farmàcies, carnisseries, botigues locals i comerços tradicionals que no disposen de departaments IT dedicats.

#### 🎯 Què fa CyberAudit?

- **🔍 Anàlisi d'Amenaces**: Monitorització contínua de possibles vulnerabilitats en sistemes connectats
- **📊 Panell de Control**: Dashboard intuïtiu amb mètriques de seguretat en temps real
- **🚨 Alertes Proactives**: Notificacions automàtiques davant activitats sospitoses
- **📈 Monitorització 24/7**: Vigilància constant dels teus serveis digitals
- **📝 Informes Periòdics**: Reports mensuals sobre l'estat de seguretat
- **🛡️ Protecció WAF**: Firewall d'aplicacions web integrat

#### 💼 Per a qui és?

Perfecta per a negocis que:
- Tenen **presència online** (web, e-commerce, xarxes socials)
- Gestionen **dades de clients** (emails, comandes, pagaments)
- No tenen **pressupost per un equip IT complet**
- Necessiten **complir amb RGPD** i normatives de protecció de dades
- Volen **dormir tranquils** sabent que estan protegits

### 🚀 Instal·lació Ràpida

#### Pas 1: Clonar el Repositori
```bash
git clone https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7.git
cd ProjecteFinal_G7
```

#### Pas 2: Configurar Variables d'Entorn
```bash
# Crear fitxer .env amb credencials segures
cat > .env << EOF
# Credencials de Base de Dades
DB_ROOT_PASSWORD=$(openssl rand -base64 32)

# Contrasenya de Redis
REDIS_PASSWORD=$(openssl rand -base64 32)

# Contrasenya de LDAP Admin
LDAP_ADMIN_PASSWORD=$(openssl rand -base64 32)
EOF

# ⚠️ IMPORTANT: Desar aquestes credencials en un lloc segur
cat .env
```

#### Pas 3: Desplegar l'Aplicació
```bash
# Construir i aixecar tots els serveis
docker-compose up -d --build

# Verificar que tots els contenidors estan funcionant
docker-compose ps
```

#### Pas 4: Accedir a la Plataforma
```bash
# L'aplicació estarà disponible a:
# http://localhost:8080
# http://127.0.0.1:8080

# Veure logs en temps real
docker-compose logs -f
```

### 📞 Suport

- 📧 Email: [alberto@cyberaudit.local](mailto:alberto@cyberaudit.local)
- 🐛 Issues: [GitHub Issues](https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7/issues)
- 📚 Wiki: [Documentació completa](https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7/wiki)

---

<div align="center">

**[⬆ Tornar a dalt](#-cyberaudit-saas---projecte-final-g7)**

Made with ❤️ by Team G7 - ITB 2024/2025

</div>
