# 🚀 Deployment Guide - CyberAudit SaaS

Complete installation and deployment instructions for CyberAudit SaaS.

---

## Table of Contents

- [Prerequisites](#prerequisites)
- [Quick Start — Local Development](#quick-start--local-development)
- [Environment Variables](#environment-variables)
- [Production Deployment — AWS](#production-deployment--aws)
- [Verification](#verification)
- [Troubleshooting](#troubleshooting)

---

## Prerequisites

| Tool | Minimum Version | Purpose |
|------|----------------|---------|
| Docker | 20.10+ | Container runtime |
| Docker Compose | 2.0+ | Service orchestration |
| Git | 2.30+ | Version control |
| AWS CLI | 2.x | Cloud deployment (production only) |
| Terraform | 1.5+ | Infrastructure provisioning (production only) |

```bash
# Verify installations
docker --version          # Docker 20.10+
docker-compose --version  # Docker Compose 2.0+
git --version             # Git 2.30+
```

---

## Quick Start — Local Development

### 5-Minute Setup

```bash
# 1. Clone the repository
git clone https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7.git
cd ProjecteFinal_G7

# 2. Create environment file
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

# 4. Wait for services to be ready (~2 minutes)
sleep 120

# 5. Verify services are running
docker-compose ps
curl -I http://localhost:8080
```

**Expected output**:
```
HTTP/1.1 200 OK
Content-Type: text/html
X-Content-Type-Options: nosniff
```

### Access Points (Local)

| Service | URL | Credentials |
|---------|-----|------------|
| Web Application | http://localhost:8080 | See `.env` |
| Grafana | http://localhost:3000 | admin / admin |
| Kibana | http://localhost:5601 | — |
| phpMyAdmin | http://localhost:8081 | See `.env` |

---

## Environment Variables

Copy `.env.example` to `.env` and fill in the required values:

```bash
cp .env.example .env
```

| Variable | Description | Required |
|----------|-------------|---------|
| `DB_ROOT_PASSWORD` | MariaDB root password | ✅ |
| `DB_NAME` | Database name | ✅ |
| `DB_USER` | Application DB user | ✅ |
| `DB_PASSWORD` | Application DB password | ✅ |
| `REDIS_PASSWORD` | Redis authentication | ✅ |
| `LDAP_ADMIN_PASSWORD` | OpenLDAP admin password | ✅ |
| `SHODAN_API_KEY` | Shodan vulnerability scanning | ⚠️ Optional |
| `CIRCLE_API_KEY` | Circle fiat conversion | ⚠️ Optional |
| `SLACK_WEBHOOK` | Slack deployment notifications | ⚠️ Optional |

---

## Production Deployment — AWS

### Step 1: AWS Infrastructure (Terraform)

```bash
cd infrastructure/terraform

# Initialize Terraform
terraform init

# Review the plan
terraform plan -var="environment=production"

# Apply (provisions VPC, EC2, RDS, ElastiCache, ALB, S3)
terraform apply -var="environment=production"
```

Estimated provisioning time: **15–25 minutes**

### Step 2: Build and Push Docker Images

```bash
# Configure AWS credentials
aws configure

# Login to Amazon ECR
ECR_REPO="<account-id>.dkr.ecr.eu-west-1.amazonaws.com"
aws ecr get-login-password --region eu-west-1 | docker login --username AWS --password-stdin $ECR_REPO

# Build and push images
docker-compose build --no-cache
docker tag cyberaudit_nginx:latest ${ECR_REPO}/nginx:latest
docker tag cyberaudit_php-fpm:latest ${ECR_REPO}/php-fpm:latest
docker push ${ECR_REPO}/nginx:latest
docker push ${ECR_REPO}/php-fpm:latest
```

### Step 3: Deploy to ECS

```bash
# Update ECS service (triggers rolling deployment)
aws ecs update-service \
  --cluster cyberaudit-cluster \
  --service cyberaudit-service \
  --force-new-deployment \
  --region eu-west-1

# Wait for deployment to stabilize
aws ecs wait services-stable \
  --cluster cyberaudit-cluster \
  --services cyberaudit-service
```

### Step 4: Database Setup

```bash
# Run database migrations
docker run --rm \
  -e DB_HOST=${DB_HOST} \
  -e DB_USER=${DB_USER} \
  -e DB_PASSWORD=${DB_PASSWORD} \
  ${ECR_REPO}/php-fpm:latest \
  php /var/www/html/migrate.php
```

### Step 5: Configure Cloudflare DNS

1. Add your domain to Cloudflare
2. Set DNS records pointing to the AWS ALB endpoint
3. Enable: Always HTTPS, DNSSEC, HTTP/3, Brotli
4. Set minimum TLS version to 1.2

### Step 6: Enable Monitoring

```bash
# Install Wazuh agents on all EC2 instances
curl -so wazuh-agent-4.x.deb https://packages.wazuh.com/4.x/apt/pool/main/w/wazuh-agent/wazuh-agent_4.x_amd64.deb
sudo dpkg -i ./wazuh-agent-4.x.deb

# Set up cron jobs for backups
crontab -e
# Add:
# 0 2 * * * /opt/scripts/backup_database.sh
# 0 * * * * /opt/scripts/backup_binlogs.sh
# 0 3 * * 0 aws ec2 create-snapshot --volume-id <vol-id>
```

**Estimated total setup time**: 2–4 hours

---

## Verification

### Health Checks

```bash
# Check all containers are running
docker-compose ps

# Check application response
curl -I https://yourdomain.com

# Check database connectivity
docker exec cyberaudit-db mysql -u root -p${DB_ROOT_PASSWORD} -e "SHOW STATUS LIKE 'Uptime';"

# Check Redis
docker exec cyberaudit-redis redis-cli -a ${REDIS_PASSWORD} PING

# Check real-time monitoring
watch -n 5 'docker stats --no-stream --format "table {{.Name}}\t{{.CPUPerc}}\t{{.MemUsage}}"'
```

### Expected Security Headers

```
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Strict-Transport-Security: max-age=31536000; includeSubDomains
Content-Security-Policy: default-src 'self'
```

---

## Troubleshooting

### Services won't start
```bash
docker-compose logs --tail=50 <service_name>
```

### Database connection errors
```bash
# Verify MariaDB is ready
docker exec cyberaudit-db mysqladmin -u root -p${DB_ROOT_PASSWORD} status
```

### LDAP authentication issues
```bash
# Test LDAP connectivity
ldapsearch -x -H ldap://localhost -b "dc=cyberaudit,dc=local" -D "cn=admin,dc=cyberaudit,dc=local" -W "(objectClass=*)"
```

### Redis connection refused
```bash
# Check Redis is accepting connections
docker exec cyberaudit-redis redis-cli -a ${REDIS_PASSWORD} INFO server
```

### Logs location
| Service | Log Path |
|---------|---------|
| Nginx | `/var/log/nginx/access.log`, `error.log` |
| PHP-FPM | `/var/log/php-fpm/error.log` |
| MariaDB | `/var/log/mysql/error.log` |
| Wazuh | `/var/ossec/logs/ossec.log` |
| Snort | `snort_logs/` |
