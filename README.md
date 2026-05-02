# 🛡️ CyberAudit SaaS - Enterprise Security for Small Businesses

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?logo=docker)](https://www.docker.com/)
[![Solana](https://img.shields.io/badge/Payments-Solana-14F195?logo=solana)](https://solana.com/)
[![AWS](https://img.shields.io/badge/Cloud-AWS-FF9900?logo=amazon-aws)](https://aws.amazon.com/)
[![Status](https://img.shields.io/badge/status-production-success.svg)]()

> **Final Degree Project - Network Computer Systems Administration (ASIR)**  
> Institut Tecnològic de Barcelona - Academic Year 2024/2025

---

## 📋 Tabla de Contenidos

- [Descripción](#-descripción)
- [Características principales](#-características-principales)
- [Diferenciadores clave](#-diferenciadores-clave)
- [Quick Start](#-quick-start)
- [Estructura del proyecto](#️-estructura-del-proyecto)
- [Stack de tecnologías](#️-stack-de-tecnologías)
- [Documentación](#-documentación)
- [Modelo de negocio](#-modelo-de-negocio)
- [Equipo](#-equipo)
- [Licencia](#-licencia)

---

## 📋 Descripción

**CyberAudit SaaS** es una plataforma cloud-native de monitorización y auditoría de ciberseguridad diseñada específicamente para **pymes (pequeñas y medianas empresas)** con menos de 250 empleados que carecen de departamento IT o buscan optimizar sus costes de seguridad.

### 🎯 El problema

El **43% de los ciberataques se dirigen a pequeñas empresas**, y sin embargo:
- Solo el 14% tiene medidas de ciberseguridad adecuadas
- Coste medio de una brecha de datos: **€133.000** para pymes
- El 60% de las pequeñas empresas cierra en 6 meses tras un ciberataque
- El **87% de las pymes no tiene departamento IT dedicado**
- Las soluciones empresariales tradicionales cuestan **€500–€2.000/mes** (inaccesibles para la mayoría)

### 💡 Nuestra solución

Plataforma de seguridad cloud llave en mano con:
- **Monitorización automatizada 24/7** y respuesta a incidentes en tiempo real
- **Escaneo de vulnerabilidades** con recomendaciones automáticas de parcheo
- **Web Application Firewall (WAF)** protegiendo contra OWASP Top 10 (BunkerWeb)
- **SIEM** con Wazuh para correlación de eventos de seguridad
- **Reporting de compliance** (GDPR, PCI-DSS, ISO 27001)
- **Pagos blockchain** via Solana (EURC/USDC stablecoins)
- **Sin conocimientos IT requeridos** — servicio completamente gestionado

---

## ✨ Características principales

- 🔒 **WAF** (BunkerWeb) con ModSecurity CRS — protección contra SQLi, XSS, CSRF, RFI
- 🚨 **IDS/IPS** (Snort) en modo inline con ruleset Emerging Threats (30.000+ reglas)
- 📊 **SIEM** (Wazuh) con alertas en tiempo real y dashboards Grafana/Kibana
- 🔍 **Escáner de vulnerabilidades** integrado con API Shodan
- 🛡️ **Protección DDoS** via Cloudflare CDN (Layer 3/4/7)
- 💳 **Pagos crypto** Solana (EURC/USDC) con comisiones del 0,025%
- 🔐 **Autenticación centralizada** con OpenLDAP + PHP LDAP (SSO)
- 📦 **Totalmente dockerizado** — despliegue en 5 minutos
- ☁️ **Infraestructura AWS** (EC2, RDS, ElastiCache, S3)
- 🤖 **Módulo IA** para análisis forense y detección de amenazas
- 📧 **Notificaciones** multi-canal (email, SMS, Slack, PagerDuty)
- 📋 **Informes de compliance** automáticos en PDF

---

## 🏆 Diferenciadores clave

| Característica | CyberAudit | Soluciones tradicionales |
|----------------|------------|--------------------------|
| **Coste mensual** | €29,99 – €99,99 | €500 – €2.000 |
| **Tiempo de configuración** | 5 minutos | 2–4 semanas |
| **Conocimientos IT requeridos** | Ninguno | Avanzado |
| **Comisiones de pago** | 0,025% (crypto) | 2,9% + €0,30 |
| **Limitaciones geográficas** | Ninguna (global) | Restricciones bancarias |
| **Duración del contrato** | Mensual (sin permanencia) | Anual mínimo |

---

## 🚀 Quick Start

### Requisitos previos

| Herramienta | Versión mínima |
|-------------|----------------|
| Docker | 20.10+ |
| Docker Compose | 2.0+ |
| Git | 2.30+ |

### Instalación en 5 minutos

```bash
# 1. Clonar el repositorio
git clone https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7.git
cd ProjecteFinal_G7

# 2. Ejecutar el script de configuración inicial
bash scripts/project_setup.sh

# 3. Iniciar los servicios
docker-compose up -d

# 4. Verificar el estado de los contenedores
docker-compose ps
```

**Activar HTTPS (Let's Encrypt):**
```bash
bash scripts/enable_https.sh
```

**Instalar el módulo de IA:**
```bash
bash scripts/install_ai.sh
```

> 📖 Guía de despliegue completa → [docs/desplegament_inicial.md](docs/desplegament_inicial.md)  
> ☁️ Configuración VPC en AWS → [docs/guia_vpc.md](docs/guia_vpc.md)  
> 🐳 Verificar contenedores → [docs/comprobación_de_cada_contenedor.md](docs/comprobación_de_cada_contenedor.md)

---

## 🏗️ Estructura del proyecto

```
ProjecteFinal_G7/
│
├── README.md
├── docker-compose.yml              # Orquestación de todos los servicios
├── Dockerfile                      # Imagen principal de la aplicación
├── Dockerfile.s10_s11              # Imagen para entornos S10/S11
│
├── src/                            # Código fuente PHP
│   ├── index.php                   # Panel principal
│   ├── login.php                   # Página de login
│   ├── auth_handler.php            # Lógica de autenticación LDAP
│   ├── scanner.php                 # Escáner de vulnerabilidades
│   ├── forensics.php               # Análisis forense
│   ├── db_conn.php                 # Conexión a MariaDB
│   ├── translator.php              # Internacionalización
│   ├── utils.php                   # Utilidades comunes
│   ├── api/                        # Endpoints REST
│   ├── assets/                     # CSS, JavaScript, imágenes
│   └── includes/                   # Componentes reutilizables (header, footer)
│
├── config/                         # Ficheros de configuración del sistema
│   ├── nginx/default.conf          # → config/nginx/default.conf
│   ├── php-sessions.ini            # → config/php-sessions.ini
│   ├── logs-wallet.json            # Wallet Solana para logs
│   └── system-wallet.json          # Wallet Solana del sistema
│
├── scripts/                        # Scripts de automatización
│   ├── project_setup.sh            # Instalación y configuración inicial
│   ├── enable_https.sh             # Provisión de certificados SSL
│   └── install_ai.sh               # Instalación del módulo de IA
│
├── setup/                          # Inicialización de servicios
│   ├── db/                         # Esquemas SQL y datos iniciales
│   └── ldap/                       # Configuración y directorio OpenLDAP
│
├── docs/                           # Documentación técnica
│   ├── desplegament_inicial.md     # Guía de instalación paso a paso
│   ├── guia_vpc.md                 # VPC Peering en AWS
│   ├── desplegament_ia.md          # Despliegue del módulo IA
│   └── comprobación_de_cada_contenedor.md  # Verificación de contenedores
│
└── snort_logs/                     # Logs generados por el IDS Snort
```

---

## 🛠️ Stack de tecnologías

### Infraestructura y plataforma

| Componente | Tecnología | Notas |
|------------|------------|-------|
| **Cloud** | AWS (EC2, RDS, ElastiCache, S3) | Región eu-west-1 (Irlanda, GDPR) |
| **Contenedores** | Docker + Docker Compose | Orquestación local y en EC2 |
| **Sistema operativo** | Ubuntu Server 22.04 LTS | Base de todos los contenedores |
| **CDN / DDoS** | Cloudflare | DNS, SSL, protección Layer 3/4/7 |

### Servicios de la aplicación

| Componente | Tecnología | Versión |
|------------|------------|---------|
| **Web Server** | Nginx | 1.24 |
| **Backend** | PHP-FPM | 8.2 |
| **Base de datos** | MariaDB | 10.11 LTS |
| **Caché / Sesiones** | Redis | 7.2-alpine |
| **Autenticación** | OpenLDAP | 2.6 |
| **Email** | Postfix + SendGrid | — |

### Seguridad

| Componente | Tecnología | Función |
|------------|------------|---------|
| **WAF** | BunkerWeb + ModSecurity CRS | Protección web Layer 7 |
| **IDS/IPS** | Snort | Detección y bloqueo de intrusiones |
| **SIEM** | Wazuh + ELK Stack | Correlación y análisis de eventos |
| **Monitorización** | Grafana + Prometheus | Dashboards en tiempo real |
| **Pagos** | Solana Blockchain | EURC / USDC |
| **IA** | Módulo personalizado | Análisis forense y detección |

> 🔧 Ver configuración Nginx → [config/nginx/default.conf](config/nginx/default.conf)  
> 🔧 Ver configuración PHP → [config/php-sessions.ini](config/php-sessions.ini)

---

## 📚 Documentación

| Documento | Descripción |
|-----------|-------------|
| [📦 Despliegue inicial](docs/desplegament_inicial.md) | Guía completa de instalación en Ubuntu Server con script automatizado |
| [☁️ Guía VPC AWS](docs/guia_vpc.md) | Configuración de VPC Peering entre cuentas AWS |
| [🤖 Despliegue IA](docs/desplegament_ia.md) | Instalación y configuración del módulo de inteligencia artificial |
| [🐳 Verificación de contenedores](docs/comprobación_de_cada_contenedor.md) | Cómo comprobar el estado y funcionamiento de cada servicio Docker |

---

## 💰 Modelo de negocio

### Planes de suscripción

| Plan | EURC/mes | USDC/mes | Anual (–20%) | Perfil objetivo |
|------|----------|----------|--------------|-----------------|
| **Basic** | 29,99 | $32,99 | 287,90 EURC | 1 sitio web, 1–10 empleados |
| **Professional** | 59,99 | $65,99 | 575,90 EURC | Múltiples sitios, 10–50 empleados |
| **Business** | 99,99 | $109,99 | 959,90 EURC | 50–250 empleados, compliance |
| **Enterprise** | Personalizado | Personalizado | Personalizado | 250+ empleados |

### Ahorro estimado para el cliente

| Concepto | Enfoque tradicional | CyberAudit SaaS | Ahorro mensual |
|----------|---------------------|-----------------|----------------|
| **Consultor IT** | €3.000 – €6.000/mes | €0 | €3.000 – €6.000 |
| **Licencias software** | €200 – €800/mes | Incluido | €200 – €800 |
| **Herramientas de monitorización** | €150 – €400/mes | Incluido | €150 – €400 |
| **Auditorías compliance** | €2.000 – €5.000/año | €99/informe | €1.800 – €4.900 |
| **Coste mensual total** | €3.500 – €7.200 | €29,99 – €99,99 | **€3.400 – €7.100** |

### Mercado objetivo

- **España**: 540.000 pymes · Mercado potencial: **€194,4M/año**
- **Unión Europea**: 4,4 millones de pymes · Mercado potencial: **€1.580M/año**
- **CAGR ciberseguridad pyme**: 15,2% (2024–2030)
- **Catalizadores**: Directiva NIS2, GDPR, transformación digital, ciberseguros

---

## 👥 Equipo

| Nombre | Rol | GitHub | Email | Contribución |
|--------|-----|--------|-------|--------------|
| **Alberto Trujillo** | Project Lead / DevOps | [@AlbertoTrujillo-ITB2425](https://github.com/AlbertoTrujillo-ITB2425) | alberto.trujillo.7e6@itb.cat | 60% — Arquitectura, AWS, Documentación |
| **Joel Muñoz** | Backend Developer | [@JoelMunoz-ITB2425](https://github.com/JoelMunoz-ITB2425) | joel.munoz.7e8@itb.cat | 30% — PHP, API, Smart Contracts |
| **Luka Ukleba** | Security Specialist | [@LukaUkleba-ITB2425](https://github.com/LukaUkleba-ITB2425) | luka.ukleba.7e8@itb.cat | 10% — Pentesting, SIEM, WAF |

**Institución**: Institut Tecnològic de Barcelona (ITB)  
**Programa**: ASIR — Administración de Sistemas Informáticos en Red  
**Curso**: 2º año (2024–2025) · **Defensa**: 27 de junio de 2026

---

## 🤝 Contribuciones

1. Haz un fork del repositorio
2. Crea una rama para tu funcionalidad: `git checkout -b feature/nueva-funcionalidad`
3. Realiza tus cambios y haz commit: `git commit -m 'Añade nueva funcionalidad'`
4. Sube la rama: `git push origin feature/nueva-funcionalidad`
5. Abre un Pull Request

---

## 📄 Licencia

MIT License — Copyright (c) 2026 Grupo 7, ITB  
Ver fichero [LICENSE](LICENSE) para más detalles.

---

<div align="center">

**Made with ❤️ by Group 7**  
Alberto Trujillo • Joel Muñoz • Luka Ukleba

**Institut Tecnològic de Barcelona — 2026**

[![GitHub](https://img.shields.io/github/stars/AlbertoTrujillo-ITB2425/ProjecteFinal_G7?style=social)](https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7)

</div>
