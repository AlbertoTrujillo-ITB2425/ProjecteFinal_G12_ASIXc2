# 🛡️ CyberAudit SaaS - Enterprise Security for Small Businesses

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?logo=docker)](https://www.docker.com/)
[![Solana](https://img.shields.io/badge/Payments-Solana-14F195?logo=solana)](https://solana.com/)
[![AWS](https://img.shields.io/badge/Cloud-AWS-FF9900?logo=amazon-aws)](https://aws.amazon.com/)
[![Status](https://img.shields.io/badge/status-production-success.svg)]()

> **Final Degree Project - Network Computer Systems Administration (ASIR)**  
> Institut Tecnològic de Barcelona - Academic Year 2025/2026

---

## 📋 Table of Contents

- [Executive Summary](#-executive-summary)
- [Key Features](#-key-features)
- [Quick Start](#-quick-start)
- [Technology Stack](#-technology-stack)
- [Project Structure](#-project-structure)
- [Documentation](#-documentation)
- [Team](#-team)
- [License](#-license)

---

## 📋 Executive Summary

**CyberAudit SaaS** is a comprehensive cybersecurity monitoring and auditing platform engineered for **SMEs (Small and Medium Enterprises)** with fewer than 250 employees that lack dedicated IT departments or want to reduce security costs.

**The Problem**: 43% of cyberattacks target small businesses, yet only 14% have adequate cybersecurity. Traditional enterprise security costs €500–€2,000/month — unaffordable for most SMEs.

**Our Solution**: A turnkey, cloud-native security platform providing:
- 24/7 automated threat monitoring and real-time incident response
- Vulnerability scanning with automated patch recommendations
- Web Application Firewall (WAF) protecting against OWASP Top 10
- SIEM with Wazuh for centralized security event management
- Compliance reporting (GDPR, PCI-DSS, ISO 27001)
- Blockchain-based payments via Solana (EURC/USDC stablecoins) — 97% cheaper than Stripe

| Feature | CyberAudit | Traditional Solutions |
|---------|-----------|----------------------|
| **Monthly Cost** | €29.99 – €99.99 | €500 – €2,000 |
| **Setup Time** | 5 minutes | 2–4 weeks |
| **IT Expertise Required** | None | Advanced |
| **Payment Fees** | 0.0008% (crypto) | 3.9% (Stripe) |
| **Contract Length** | Monthly (no lock-in) | Annual minimum |

> 📊 See [BUSINESS_MODEL.md](./docs/md/BUSINESS_MODEL.md) for full market analysis, pricing, financial projections, and use cases.

---

## ✨ Key Features

- 🔥 **BunkerWeb WAF** — First-line defense against OWASP Top 10, SQL injection, XSS, DDoS
- 🛡️ **Snort IPS** — Inline intrusion prevention with 30,000+ rules, updated daily
- 📊 **Wazuh SIEM** — Centralized security monitoring, FIM, compliance checks (GDPR, PCI-DSS)
- 📈 **Grafana Dashboards** — System health, application performance, security overview, business metrics
- 🔍 **Shodan Integration** — Weekly vulnerability scans of all customer domains
- ☁️ **AWS Multi-AZ** — High-availability infrastructure with auto-scaling (99.95% uptime)
- 💳 **Solana Payments** — EURC/USDC stablecoin subscriptions, 400ms finality, near-zero fees
- 🔐 **OpenLDAP SSO** — Centralized authentication with TLS-encrypted directory queries
- 📦 **Automated Backups** — Daily encrypted database backups to S3, 30-day retention
- 📋 **Compliance Reports** — Automated GDPR, PCI-DSS, and ISO 27001 report generation

---

## ⚡ Quick Start

```bash
# 1. Clone the repository
git clone https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7.git
cd ProjecteFinal_G7

# 2. Create environment file with secure random passwords
cat > .env << EOF
DB_ROOT_PASSWORD=$(openssl rand -base64 32)
DB_NAME=cyberaudit
DB_USER=cyberaudit_user
DB_PASSWORD=$(openssl rand -base64 32)
REDIS_PASSWORD=$(openssl rand -base64 32)
LDAP_ADMIN_PASSWORD=$(openssl rand -base64 32)
EOF

# 3. Build and start all services
docker-compose up -d --build

# 4. Verify the application is running
curl -I http://localhost:8080
```

**Prerequisites**: Docker 20.10+, Docker Compose 2.0+, Git 2.30+

> 🚀 See [DEPLOYMENT.md](./docs/md/DEPLOYMENT.md) for the full deployment guide including production (AWS) setup.

---

## 🛠️ Technology Stack

| Layer | Technology | Purpose |
|-------|-----------|---------|
| **WAF** | BunkerWeb + ModSecurity CRS | Web application firewall |
| **IDS/IPS** | Snort 3 | Network intrusion detection/prevention |
| **Web Server** | Nginx 1.24 | Reverse proxy, static files |
| **Backend** | PHP 8.2-FPM | API and business logic |
| **Database** | MariaDB 10.11 | Persistent data storage |
| **Cache/Sessions** | Redis 7.2 | Session storage, rate limiting |
| **Authentication** | OpenLDAP 2.6 | Centralized user directory |
| **Email** | Postfix + SendGrid | Transactional email delivery |
| **SIEM** | Wazuh | Security information & event management |
| **Monitoring** | Grafana + Prometheus | Metrics dashboards |
| **Log Analysis** | ELK Stack | Log aggregation and visualization |
| **Container** | Docker + Docker Compose | Service orchestration |
| **Cloud** | AWS (EC2, RDS, ELB, S3) | Production infrastructure |
| **IaC** | Terraform | Infrastructure as code |
| **CI/CD** | GitHub Actions | Automated testing and deployment |
| **Payments** | Solana (EURC/USDC) | Subscription billing |
| **DDoS** | Cloudflare | CDN, DDoS protection, DNS |

---

## 📁 Project Structure

```
ProjecteFinal_G7/
├── README.md                   # This file
├── ARCHITECTURE.md             # Technical architecture & services
├── DEPLOYMENT.md               # Installation & deployment guide
├── SECURITY.md                 # Security specs & pentest reports
├── BUSINESS_MODEL.md           # Market analysis & business model
├── DEVELOPMENT.md              # Local development guide
├── Dockerfile                  # PHP-FPM multi-stage build
├── docker-compose.yml          # Service orchestration
├── src/                        # Application source code
├── config/                     # Nginx, Redis, PHP configuration
├── scripts/                    # Automation scripts (backup, deploy, monitor)
├── setup/                      # Database schema and initial setup
├── infrastructure/terraform/   # AWS Terraform configurations
├── docs/                       # Additional documentation
└── snort_logs/                 # IDS/IPS log storage
```

---

## 📚 Documentation

| Document | Description |
|---------|-------------|
| [ARCHITECTURE.md](./docs/md/ARCHITECTURE.md) | System design, network topology, service stack, monitoring |
| [DEPLOYMENT.md](./docs/md/DEPLOYMENT.md) | Local and production deployment, environment variables, troubleshooting |
| [SECURITY.md](./docs/md/SECURITY.md) | Security architecture, pentesting reports, compliance |
| [BUSINESS_MODEL.md](./docs/md/BUSINESS_MODEL.md) | Market analysis, pricing, financial projections, use cases |
| [DEVELOPMENT.md](./docs/md/DEVELOPMENT.md) | Dev environment setup, CI/CD pipeline, automation scripts |
| [docs/admin_manual.md](./docs/admin_manual.md) | Detailed admin and configuration manual |
| [docs/client_manual.md](./docs/client_manual.md) | End-user guide and FAQs |
| [docs/api/openapi.yaml](./docs/api/openapi.yaml) | REST API specification |

---

## 🎓 Academic Context

**Institution**: Institut Tecnològic de Barcelona  
**Program**: ASIR — Network Computer Systems Administration  
**Type**: Final Degree Project 
**Defense**: May 18, 2026  

**Modules Demonstrated**: OS Implementation, Network Administration, Security & High Availability, Web Services, Database Management, IT Security, Enterprise Deployment

---

## 👥 Team

| Name | Role | GitHub | Email |
|------|------|--------|-------|
| **Alberto Trujillo** | Project Lead, DevOps | [@AlbertoTrujillo-ITB2425](https://github.com/AlbertoTrujillo-ITB2425) | alberto.trujillo.7e6@itb.cat |
| **Joel Muñoz** | Backend Developer | [@JoelMunoz-ITB2425](https://github.com/JoelMunoz-ITB2425) | joel.munoz.7e8@itb.cat |
| **Luka Ukleba** | Security Specialist | [@LukaUkleba-ITB2425](https://github.com/LukaUkleba-ITB2425) | luka.ukleba.7e8@itb.cat |

---

## 📞 Support & Contact

| Audience | Contact |
|---------|---------|
| **Customers** | support@cyberaudit.local (< 24h response, ES/EN) |
| **ITB Professors** | alberto.trujillo.7e6@itb.cat · [Demo](https://cyberpyme.free.nf/l) |
| **Developers** | [GitHub Issues](https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7/issues) |

---

## 📄 License

MIT License — Copyright (c) 2026 Group 7, ITB

See [LICENSE](./LICENSE) for details.

---

## 🙏 Acknowledgments

- **Institut Tecnològic de Barcelona** — Academic guidance and AWS credits
- **Open Source Community** — Docker, BunkerWeb, Wazuh, Solana
- **Circle** — EURC/USDC stablecoin infrastructure

---

<div align="center">

## 🚀 Protecting Small Businesses in the Digital Age

[🏠 Website](https://cyberpyme.es) • [🎮 Demo](https://cyberpyme.free.nf/) • [📚 Docs](./docs)

**EURC** • **USDC** • **Solana Network** — *Fast • Secure • Low Fees*

**Made by Group 7** — Alberto Trujillo • Joel Muñoz • Luka Ukleba  
**Institut Tecnològic de Barcelona - 2026**

[![GitHub](https://img.shields.io/github/stars/AlbertoTrujillo-ITB2425/ProjecteFinal_G7?style=social)](https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7)

</div>
