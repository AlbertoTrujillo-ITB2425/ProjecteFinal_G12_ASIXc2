# 🏗️ Architecture - CyberAudit SaaS

Technical architecture documentation for the CyberAudit SaaS platform.

---

## Table of Contents

- [System Design](#system-design)
- [Network Topology](#network-topology)
- [Infrastructure as Code](#infrastructure-as-code)
- [Core Services](#core-services)
- [Security Layer](#security-layer)
- [Monitoring & Observability](#monitoring--observability)

---

## System Design

Our architecture follows **microservices principles** with strict separation of concerns, designed for **high availability**, **scalability**, and **security**.

```
┌─────────────────────────────────────────────────────────────────┐
│                    INTERNET (Public Access)                     │
└────────────────────────────┬────────────────────────────────────┘
                             │
                    ┌────────▼────────┐
                    │  Cloudflare CDN │ ◄── DDoS Protection
                    │   DNS + WAF     │     SSL/TLS Termination
                    └────────┬────────┘     Rate Limiting
                             │
                    ┌────────▼────────┐
                    │   AWS ALB       │ ◄── Load Balancer
                    │ (Application)   │     Health Checks
                    └────────┬────────┘     SSL Offloading
                             │
        ┌────────────────────┼────────────────────┐
        │                    │                    │
   ┌────▼─────┐       ┌─────▼──────┐      ┌─────▼──────┐
   │   EC2    │       │    EC2     │      │    EC2     │
   │ Instance │       │  Instance  │      │  Instance  │
   │    #1    │       │     #2     │      │     #3     │
   └────┬─────┘       └─────┬──────┘      └─────┬──────┘
        │                   │                    │
        └───────────────────┼────────────────────┘
                            │
        ┌───────────────────┼───────────────────────────┐
        │                   │                           │
   ┌────▼─────────┐  ┌─────▼──────┐  ┌───────▼────────┐
   │  BunkerWeb   │  │  Wazuh     │  │    Grafana     │
   │  WAF/Proxy   │  │  SIEM      │  │   Monitoring   │
   └────┬─────────┘  └─────┬──────┘  └───────┬────────┘
        │                  │                  │
   ┌────▼──────────────────▼──────────────────▼────┐
   │         Docker Compose Orchestration          │
   ├───────────────────────────────────────────────┤
   │  ┌──────────┐  ┌──────────┐  ┌─────────────┐ │
   │  │  Nginx   │  │ PHP-FPM  │  │  MariaDB    │ │
   │  │  Web     │  │ Backend  │  │  Database   │ │
   │  └──────────┘  └──────────┘  └─────────────┘ │
   │  ┌──────────┐  ┌──────────┐  ┌─────────────┐ │
   │  │  Redis   │  │ OpenLDAP │  │  Postfix    │ │
   │  │ Sessions │  │   Auth   │  │   Email     │ │
   │  └──────────┘  └──────────┘  └─────────────┘ │
   └────────────────┬──────────────────────────────┘
                    │
        ┌───────────┼───────────┐
        │           │           │
   ┌────▼────┐ ┌───▼────┐ ┌────▼────┐
   │ AWS RDS │ │  AWS   │ │ AWS S3  │
   │ MariaDB │ │ElastiC-│ │ Backups │
   │ Cluster │ │ Cache  │ │  Logs   │
   └─────────┘ └────────┘ └─────────┘
```

---

## Network Topology

**Three-Tier Security Architecture** (Defense in Depth):

| Tier | Components | Subnet | Access |
|------|-----------|--------|--------|
| **DMZ** | BunkerWeb WAF, Nginx, Cloudflare CDN | `10.0.1.0/24` | Allow 80/443 from internet |
| **Application** | PHP-FPM, API endpoints, Redis sessions | `10.0.2.0/24` | Allow traffic only from DMZ |
| **Data** | MariaDB, OpenLDAP, NFS/EFS storage | `10.0.3.0/24` | No internet access (egress via NAT) |

---

## Infrastructure as Code

Terraform is used for AWS provisioning. The full configuration is in `infrastructure/terraform/`.

Key resources provisioned:
- **VPC** (`10.0.0.0/16`) with public/private subnets across 3 AZs
- **EC2 Auto Scaling Group** (min: 2, max: 10, desired: 3 instances)
- **Application Load Balancer** with HTTPS termination
- **RDS MariaDB 10.11** Multi-AZ for 99.95% availability
- **ElastiCache Redis** for session storage
- **S3 Bucket** for backups and logs

See [`infrastructure/terraform/main.tf`](./infrastructure/terraform/main.tf) for the full Terraform configuration.

---

## Core Services

### Web Server: Nginx 1.24
- Reverse proxy behind BunkerWeb WAF
- HTTP/2 support, Gzip compression (70% bandwidth reduction)
- Static file caching, connection pooling to PHP-FPM
- Security headers: `X-Frame-Options`, `X-Content-Type-Options`, `HSTS`

Configuration: [`config/nginx/default.conf`](./config/nginx/default.conf)

### Application Backend: PHP 8.2-FPM
- Custom lightweight MVC framework
- RESTful API with JWT authentication
- Rate limiting middleware, input validation, prepared statements
- Extensions: `mysqli`, `pdo_mysql`, `redis`, `ldap`, `openssl`, `opcache`

### Database: MariaDB 10.11
- AWS RDS Multi-AZ deployment
- Master-slave replication, 30-day automated backups
- Point-in-time recovery (PITR)
- AES-256 encryption at rest, TLS 1.2+ in transit

Key tables: `customers`, `security_events`, `vulnerability_scans`, `payment_transactions`, `subscriptions`

Schema: [`setup/database/schema.sql`](./setup/database/schema.sql)

### Authentication: OpenLDAP 2.6
- Centralized user authentication and authorization
- Directory structure:
  ```
  dc=cyberaudit,dc=local
  ├── ou=customers
  ├── ou=groups (basic_plan, professional_plan, business_plan, administrators)
  └── ou=services
  ```
- PHP LDAP extension for Single Sign-On (SSO)
- TLS encryption for all LDAP queries

### Session Management: Redis 7.2
- PHP session storage (persistent across servers)
- API rate limiting counters
- Real-time analytics cache
- OTP codes and password reset tokens (TTL-based expiry)

Configuration: [`config/redis.conf`](./config/redis.conf)

### Email Server: Postfix
- SMTP relay for transactional emails via SendGrid API
- Email types: security alerts, weekly summaries, monthly compliance reports, account notifications

### File Transfer: SFTP (OpenSSH)
- SSH key-based authentication (passwords disabled)
- Per-customer chroot jails (isolated directories)
- Automated ClamAV malware scanning on upload
- 500MB file size limit per upload

---

## Security Layer

### BunkerWeb WAF
- First line of defense against OWASP Top 10
- ModSecurity Core Rule Set (CRS)
- Anti-bot protection with CAPTCHA challenges
- Rate limiting: 20 req/s with burst of 40
- Bad behavior detection with 1-hour bans

### Snort IPS
- Inline mode (blocking malicious traffic)
- Emerging Threats Open ruleset (30,000+ rules) + custom rules
- Daily rule updates via cron
- All alerts forwarded to Wazuh SIEM

### Cloudflare
- DDoS mitigation Layer 3/4/7 (>100 Gbps capacity)
- 200+ global CDN edge locations
- Automatic SSL/TLS certificate provisioning
- Bot management, DNSSEC, HTTP/3 (QUIC), Brotli compression

### Backup Strategy
- **Daily** full database backup at 2 AM → AWS S3 (AES256 encrypted, STANDARD_IA)
- **Hourly** incremental binary log backups
- **Weekly** EC2 volume snapshots
- **Retention**: 30 days (database), 7 days (logs), 1 year (compliance reports)
- **RTO**: < 4 hours | **RPO**: < 1 hour

Backup script: [`scripts/backup_database.sh`](./scripts/backup_database.sh)

---

## Monitoring & Observability

### Wazuh SIEM
- Centralized security monitoring across all EC2 instances
- Monitored events: SSH auth failures, file integrity, log analysis, CVE scanning
- Compliance checks: PCI-DSS 3.2.1, GDPR, HIPAA
- AWS CloudTrail and GuardDuty integration

### Grafana Dashboards
Four main dashboards powered by Prometheus, MySQL, Elasticsearch, and AWS CloudWatch:
1. **System Health** - CPU, memory, disk I/O, network, container status
2. **Application Performance** - Request latency (p50/p95/p99), error rates, API response times
3. **Security Overview** - Attacks blocked by type, failed logins (geo-map), vulnerability results
4. **Business Metrics** - MRR, churn rate, customer acquisition, ARPU

### ELK Stack
- **Elasticsearch**: 3-node cluster for log storage
- **Logstash**: Log parsing with GeoIP enrichment, user-agent parsing, threat intel lookup
- **Kibana**: Visualization and querying
- **Filebeat**: Lightweight log shipper on all servers

Indexed logs: Nginx access/error, PHP errors, MariaDB slow queries, BunkerWeb audit, syslog, auth.log

### Alerting

| Level | Response | Examples | Channels |
|-------|----------|----------|---------|
| **P1 Critical** | < 5 min | Service outage, active breach | SMS + PagerDuty + Slack |
| **P2 High** | < 1 hour | DB high CPU, failed backups | Email + Slack |
| **P3 Medium** | < 4 hours | Disk >80%, high error rate | Email |
| **P4 Low** | < 24 hours | New signup, weekly summary | Email |
