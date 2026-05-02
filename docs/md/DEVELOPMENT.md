# 💻 Development Guide - CyberAudit SaaS

Guide for setting up a local development environment and contributing to CyberAudit SaaS.

---

## Table of Contents

- [Development Environment](#development-environment)
- [Docker Configuration](#docker-configuration)
- [CI/CD Pipeline](#cicd-pipeline)
- [Automation Scripts](#automation-scripts)
- [Project Structure](#project-structure)
- [Development Phases](#development-phases)

---

## Development Environment

### Option 1: VirtualBox (Recommended)

```bash
# Create Ubuntu Server 22.04 VM
VBoxManage createvm --name "cyberaudit-dev" --ostype "Ubuntu_64" --register
VBoxManage modifyvm "cyberaudit-dev" \
  --memory 4096 \
  --cpus 2 \
  --nic1 nat \
  --nic2 hostonly --hostonlyadapter2 vboxnet0

# Configure storage (50GB)
VBoxManage storagectl "cyberaudit-dev" --name "SATA" --add sata --controller IntelAhci
VBoxManage createhd --filename ~/VirtualBox\ VMs/cyberaudit-dev/cyberaudit-dev.vdi --size 50000
VBoxManage storageattach "cyberaudit-dev" --storagectl "SATA" --port 0 --device 0 --type hdd \
  --medium ~/VirtualBox\ VMs/cyberaudit-dev/cyberaudit-dev.vdi
```

### Option 2: Isard VDI (Remote)
- Remote desktop infrastructure accessible from anywhere (ITB lab, home)
- Centralized resource management
- Ubuntu Server 22.04 LTS instances

### Install Dependencies (Ubuntu 22.04)

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER
newgrp docker

# Install Docker Compose
sudo apt install -y docker-compose-plugin

# Install development tools
sudo apt install -y git vim curl wget jq openssl
```

---

## Docker Configuration

### Service Architecture

The development stack uses `docker-compose.yml` with two isolated networks:
- **`net_public`**: External-facing services (BunkerWeb, Nginx)
- **`net_private`**: Internal services only (PHP-FPM, MariaDB, Redis, OpenLDAP)

### Key Services

| Service | Image | Port | Network |
|---------|-------|------|---------|
| BunkerWeb WAF | `bunkerity/bunkerweb:latest` | 8080 | public |
| Nginx | `nginx:1.24-alpine` | — | public + private |
| PHP-FPM | Custom build | 9000 | private |
| MariaDB | `mariadb:10.11` | — | private |
| Redis | `redis:7-alpine` | — | private |
| OpenLDAP | `osixia/openldap:latest` | — | private |

### Build PHP-FPM Image

The `Dockerfile` uses multi-stage builds:

```bash
# Build the image
docker build -t cyberaudit-php-fpm .

# Run tests inside container
docker run --rm cyberaudit-php-fpm php -m
```

The Dockerfile installs extensions: `mysqli`, `pdo_mysql`, `redis`, `ldap`

See [`Dockerfile`](./Dockerfile) for the full multi-stage build configuration.

### Common Docker Commands

```bash
# Start all services
docker-compose up -d

# View logs for a specific service
docker-compose logs -f php-fpm

# Restart a single service
docker-compose restart nginx

# Run a command inside a container
docker exec -it g7-backend bash

# Stop and remove everything (including volumes)
docker-compose down -v

# Rebuild after code changes
docker-compose up -d --build php-fpm
```

---

## CI/CD Pipeline

The CI/CD pipeline uses **GitHub Actions** (`.github/workflows/deploy.yml`):

### Workflow Triggers
- **`push` to `main`**: Full test + build + deploy
- **Pull Requests to `main`**: Test only

### Pipeline Stages

```
Push to main
    │
    ├─► Test Job
    │   ├─ Checkout code
    │   ├─ Setup PHP 8.2
    │   ├─ Install Composer dependencies
    │   ├─ Run unit tests (PHPUnit)
    │   └─ Run integration tests
    │
    └─► Build & Deploy Job (on main only)
        ├─ Configure AWS credentials
        ├─ Login to Amazon ECR
        ├─ Build + tag + push Docker images
        ├─ Update ECS service (rolling deployment)
        ├─ Wait for deployment to stabilize
        └─ Notify Slack
```

### Required GitHub Secrets

| Secret | Description |
|--------|-------------|
| `AWS_ACCESS_KEY_ID` | AWS IAM access key |
| `AWS_SECRET_ACCESS_KEY` | AWS IAM secret key |
| `SLACK_WEBHOOK` | Slack notification webhook |

### Running Tests Locally

```bash
# Install Composer dependencies
composer install --prefer-dist --no-progress

# Run unit tests
vendor/bin/phpunit tests/Unit

# Run integration tests (requires running DB)
vendor/bin/phpunit tests/Integration

# Run all tests
vendor/bin/phpunit
```

---

## Automation Scripts

All scripts are in the `scripts/` directory.

### `scripts/deploy.sh` — Production Deployment

```bash
./scripts/deploy.sh
```

Performs: build → tag → push to ECR → update ECS → run DB migrations

### `scripts/backup_database.sh` — Database Backup

```bash
./scripts/backup_database.sh
```

Performs: full MySQL dump → gzip → upload to S3 (AES256) → cleanup old backups (>30 days)

**Cron schedule**:
```
0 2 * * *   /opt/scripts/backup_database.sh   # Daily full backup
0 * * * *   /opt/scripts/backup_binlogs.sh    # Hourly incremental
0 3 * * 0   aws ec2 create-snapshot ...        # Weekly EC2 snapshot
```

### `scripts/monitor.sh` — Real-Time Monitoring

```bash
./scripts/monitor.sh
```

Shows: Docker container status, CPU/memory usage, DB connections, Redis memory, recent security events (refreshes every 5 seconds)

---

## Project Structure

```
ProjecteFinal_G7/
├── README.md                   # Project overview
├── ARCHITECTURE.md             # Technical architecture
├── DEPLOYMENT.md               # Deployment guide
├── SECURITY.md                 # Security specifications
├── BUSINESS_MODEL.md           # Market analysis & business model
├── DEVELOPMENT.md              # This file
├── Dockerfile                  # PHP-FPM multi-stage build
├── Dockerfile.s10_s11          # Postfix/Snort services
├── docker-compose.yml          # Service orchestration
├── package.json                # Node.js dependencies (frontend)
├── .env.example                # Environment variables template
│
├── src/                        # Application source code
│   ├── api/                    # REST API endpoints
│   ├── controllers/            # MVC controllers
│   ├── models/                 # Database models
│   └── views/                  # Frontend templates
│
├── config/                     # Service configurations
│   ├── nginx/default.conf      # Nginx server config
│   ├── redis.conf              # Redis configuration
│   └── php-sessions.ini        # PHP session settings
│
├── scripts/                    # Automation scripts
│   ├── deploy.sh               # Production deployment
│   ├── backup_database.sh      # Database backup
│   └── monitor.sh              # Service monitoring
│
├── setup/                      # Initial setup files
│   └── database/schema.sql     # Database schema
│
├── infrastructure/
│   └── terraform/              # AWS IaC configurations
│       ├── main.tf
│       ├── variables.tf
│       └── outputs.tf
│
├── docs/                       # Additional documentation
│   ├── admin_manual.md
│   ├── client_manual.md
│   ├── comprobación_de_cada_contenedor.md
│   ├── desplegament_ia.md
│   ├── desplegament_inicial.md
│   ├── guia_vpc.md
│   └── api/
│       └── openapi.yaml
│
└── snort_logs/                 # IDS/IPS logs
```

---

## Development Phases

### Timeline

```
March 2026        April 2026         May 2026          June 2026
│                 │                  │                 │
├─ Phase 1 ───────┤                  │                 │ Planning
│                 ├─ Phase 2 ────────┤                 │ Infrastructure
│                 │                  ├─ Phase 3 ───────┤ Development
│                 │                  │                 ├─ Phase 4 ─┤ Testing/Defense
└─────────────────┴──────────────────┴─────────────────┴────────────┘
  Week 1-2         Week 3-6          Week 7-10         Week 11-17
```

### Phase Status

| Phase | Status | Period |
|-------|--------|--------|
| Phase 1: Planning & Research | ✅ Complete | March 1–14, 2026 |
| Phase 2: Infrastructure Setup | 🔄 85% (In Progress) | March 15 – April 11, 2026 |
| Phase 3: Application Development | 📅 Planned | April 12 – May 9, 2026 |
| Phase 4: Security Hardening | 📅 Planned | May 10–23, 2026 |
| Phase 5: Monitoring & Reporting | 📅 Planned | May 24 – June 6, 2026 |
| Phase 6: Documentation & Defense | 📅 Planned | June 7–27, 2026 |

### Phase 3 Planned API Endpoints

```
/api/auth               POST login, register, logout
/api/customers          GET/POST/PUT/DELETE customers
/api/subscriptions      GET/POST/PUT manage plans
/api/security-events    GET security logs
/api/reports            POST generate PDF reports
/api/payment/webhook    POST Solana payment confirmation
```

### Project Management

- **Planning**: GitHub Projects (Kanban board)
- **Communication**: Slack, Discord
- **Meetings**: Tuesdays 16:00–17:00 (ITB Lab or Google Meet)
- **Version Control**: Git with feature branches → main via PR

### Risk Register

| Risk | Probability | Impact | Mitigation | Status |
|------|------------|--------|-----------|--------|
| AWS budget overrun | Medium | High | Daily cost monitoring | ⚠️ 60% used |
| SSL cert delay | Low | Medium | Started DNS config early | 🟢 On track |
| Team member sick | Low | Medium | Cross-training, docs | 🟢 Low risk |
| Scope creep | High | High | Change control process | 🟢 Controlled |
