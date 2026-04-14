# 🛡️ CyberAudit SaaS - Projecte Final G7

[![Version](https://img.shields.io/badge/version-1.0.0--20260414-blue.svg)](https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7/tags)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

> **Choose your language / Tria el teu idioma / Elige tu idioma**
> - [Català](#català)
> - [Castellano](#castellano)
> - [English](#english)

---

## Català

### 🚀 Arquitectura del Sistema
Infraestructura contenidoritzada per a la plataforma d'auditoria **CyberAudit**, dissenyada per al desplegament segur de serveis web mitjançant Docker.

* **WAF:** BunkerWeb (Firewall d'aplicacions web).
* **Backend:** PHP-FPM (Processament de l'aplicació).
* **Sessions:** Redis 7 (Persistència de sessions).
* **Base de dades:** MariaDB 10.11.
* **Identitat:** OpenLDAP (Gestió d'usuaris).

### 📦 Desplegament
```bash
git clone [https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7.git](https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7.git)
docker compose up -d --build
