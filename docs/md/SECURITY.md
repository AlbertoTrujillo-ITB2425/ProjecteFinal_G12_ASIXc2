# 🔒 Security - CyberAudit SaaS

Security specifications, penetration testing reports, and compliance documentation.

---

## Table of Contents

- [Security Architecture](#security-architecture)
- [Penetration Testing Report — Phase 1 (IsardVDI Lab)](#penetration-testing-report--phase-1-isardvdi-lab)
- [AWS Cloud Audit — Phase 2 (Production)](#aws-cloud-audit--phase-2-production)
- [Compliance](#compliance)

---

## Security Architecture

### Defense in Depth

CyberAudit uses a layered security approach with multiple independent controls:

```
Internet → Cloudflare (DDoS/CDN) → AWS ALB → BunkerWeb WAF → Nginx → PHP-FPM
                                            ↘ Snort IPS
                                            ↘ Wazuh SIEM ← All layers
```

### Web Application Firewall: BunkerWeb
- ModSecurity Core Rule Set (CRS) protecting against OWASP Top 10
- Anti-bot CAPTCHA challenges
- Rate limiting: 20 req/s, burst 40, bad behavior ban 1 hour
- Protects against: SQLi, XSS, CSRF, RFI, LFI, directory traversal, HTTP flood, brute force

### Intrusion Detection/Prevention: Snort
- Inline IPS mode (blocking malicious traffic)
- Emerging Threats Open ruleset (30,000+ rules), updated daily
- Custom rules for application stack (SQLi patterns, malicious user agents)
- All alerts forwarded to Wazuh SIEM

### DDoS Protection: Cloudflare
- Layer 3/4/7 attack mitigation (>100 Gbps capacity)
- Always HTTPS, minimum TLS 1.2, DNSSEC enabled
- HTTP/3 (QUIC), Brotli compression, Rocket Loader
- Bot management: challenge bad bots, allow verified crawlers

### SIEM: Wazuh
- Centralized monitoring across all EC2 instances
- File integrity monitoring (FIM) on `/etc`, `/var/www/html`, `/root`
- Compliance checks: PCI-DSS 3.2.1, GDPR, HIPAA
- AWS CloudTrail and GuardDuty integration
- Custom alerts: brute force SSH, web directory changes, cryptomining indicators

### Vulnerability Management: Shodan API
- Weekly automated scans of all customer domains/IPs
- Alerts for: exposed dangerous services (FTP, Telnet, RDP, SMB), outdated software, SSL misconfigurations, open databases, known CVEs

---

## Penetration Testing Report — Phase 1 (IsardVDI Lab)

> **Phase**: Pre-Migration Security Validation (IsardVDI Lab)  
> **Lead Auditor**: Joel Muñoz (@joel㉿kali2025)  
> **Environment**: IsardVDI (Kali Linux 2025)

### Methodology
Project follows a modular framework (PTES-aligned) for evidence traceability:

```
01_recon/       # Host discovery & port scanning
02_scan/        # Vulnerability analysis (Trivy, DNS, SSH, SMB)
03_exploits/    # PoC & exploitation logs
04_web_tests/   # Nginx, PHP API, SQLi & XSS auditing
05_wazuh_tests/ # SIEM alert validation & FIM telemetry
06_reports/     # Final documentation
scripts/        # Custom Bash automation suite
```

### Reconnaissance — Network Discovery
Segment: `192.168.120.0/22`
- **Gateway**: `192.168.120.1` (pfSense/QEMU)
- **Audit Machine**: `192.168.123.167` (Kali Linux)

### Deep Service Enumeration — pfSense (`192.168.120.1`)
- **DNS (53/TCP)**: `dnsmasq 2.91` — version fingerprinting exposed (`bind.version`)
- **SSH (2022/TCP)**: `OpenSSH 10.0` — non-standard port, updated version
- **VNC (5700–5719/TCP)**: Massive exposure of WebSocket (QEMU VNC) management ports

### Automation Suite

**Web Auditing** (`scripts/audit_web.sh`): Nmap vuln scanning, Gobuster directory brute-force, SQLi prep
```bash
nmap -sV --script http-enum,http-vuln*,http-security-headers -p 80,443 $TARGET
gobuster dir -u http://$TARGET -w /usr/share/wordlists/dirb/common.txt
```

**SIEM Validator** (`scripts/test_wazuh.sh`): Generates real attack telemetry to verify Wazuh
```bash
# SSH brute-force simulation (triggers rules 5710/5720)
for i in {1..5}; do ssh -o ConnectTimeout=2 invalid_user@$TARGET "exit"; done
# FIM trigger
echo "test_audit_$(date)" >> /tmp/wazuh_fim_test
```

**Cloud & Windows** (`aws_audit.sh`, `audit_windows.sh`): Prowler CIS compliance, SMB enumeration

### Findings

| ID | Finding | Severity | Evidence | Mitigation |
|----|---------|----------|---------|-----------|
| **V-01** | VNC Exposure | 🔴 **CRITICAL** | `pfsense_completo.txt` | Close 5700–5800; use SSH tunneling |
| **V-02** | Docker Vulns | 🟠 **HIGH** | `02_scan/docker/` | Migrate `nginx:latest` → `nginx:alpine` |
| **V-03** | DNS Leak | 🔵 **LOW** | `pfsense_completo.txt` | Set `no-version` in `dnsmasq.conf` |
| **V-04** | MariaDB TLS | 🟡 **MEDIUM** | Local audit | Enforce `require_secure_transport=ON` |

### Roadmap to AWS (Golden Rules)
1. **Network Hardening**: Replicate strict firewall rules via AWS Security Groups (eliminate VNC exposure)
2. **Container Security**: Implement Trivy scanning in CI/CD before ECR pushes
3. **Cloud Auditing**: Run Prowler immediately after infrastructure deployment

---

## AWS Cloud Audit — Phase 2 (Production)

> **Status**: ✅ Infrastructure Audited (Live Environment)  
> **Target IP**: `32.194.186.97` (`ec2-32-194-186-97.compute-1.amazonaws.com`)

### External Reconnaissance

```
PORT    STATE SERVICE  VERSION
80/tcp  open  http     nginx 1.24.0
443/tcp open  ssl/http nginx 1.24.0
```

**Security Improvements vs. Lab**:
- ✅ **VNC Cluster Neutralized**: All 20 WebSocket ports (5700–5719) now **Closed**
- ✅ **SSH Hardened**: Port 2022 no longer exposed to public internet

### Web Application Hardening
- ✅ `X-XSS-Protection: 1; mode=block`
- ✅ `X-Content-Type-Options: nosniff`
- ✅ `Content-Security-Policy` basic implementation
- ⚠️ **Pending (Low)**: `Strict-Transport-Security` (HSTS) not yet configured

### Advanced Directory Enumeration
- Global HTTP 403 for `.bash_history`, `.ssh`, `.git` — hardening is consistent
- Server implements Wildcard/Honeytoken strategy (constant 8849 bytes for WordPress paths)

### WAF & SQL Injection Validation
Test: `curl -i -k "https://32.194.186?id=1'%20OR%20'1'='1"`  
**Result**: ✅ WAF detected and blocked SQLi pattern — BunkerWeb verified

### Docker Container Audit

| Container | Service | External Port | Status |
|-----------|---------|--------------|--------|
| `s1_nginx` | Web Server | 80, 443 | ✅ Hardened |
| `s12_ollama` | AI Service | 11434 | 🔴 EXPOSED (resource hijacking risk) |
| `s10_postfix` | Mail Server | 25, 587 | 🔴 EXPOSED (open relay risk) |
| `s8_grafana` | Monitoring | 3000 | 🟡 EXPOSED (info leak risk) |
| `s4_mariadb` | Database | Internal only | ✅ Secure |
| `s6_openldap` | Directory | Internal only | ✅ Secure |

**Note**: Although Ollama and Postfix are mapped to `0.0.0.0` internally, AWS Security Groups drop all external traffic — confirmed by connection timeout from audit host.

### Final Security Assessment

| Metric | Lab (IsardVDI) | Production (AWS) | Status |
|--------|--------------|----------------|--------|
| **Attack Surface** | 🟠 High (20+ ports) | 🟢 Low (2 ports) | **Improved** |
| **Vuln Management** | 🔴 Critical (Outdated) | 🟡 Medium (Hardened) | **Improved** |
| **Web Security** | 🔴 Poor (No headers) | 🟢 Strong (WAF Active) | **Improved** |
| **SIEM Integration** | ✅ Active | ✅ Active (Agent) | **Verified** |

### Full-Range Port Scan Verification
- **65,532** ports: `filtered` (no-response)
- **Open**: 22 (SSH), 80 (HTTP), 443 (HTTPS)

**Auditor's Verdict**: 99.99% of attack surface is invisible to external threats. AWS Security Groups successfully implement "Default Deny" policy.

**Final Status**: ✅ **PASSED** — System meets industry security standards for SME cloud deployments.

---

## Compliance

### Supported Standards
| Standard | Coverage | Report Type |
|----------|---------|------------|
| **GDPR** | Data protection, breach notification, access logging | Monthly automated report |
| **PCI-DSS 3.2.1** | Cardholder data environment checks | Quarterly report |
| **ISO 27001** | Information security management | Annual audit support |
| **NIS2** | EU network/information security directive | Continuous monitoring |

### Data Protection Measures
- All data encrypted at rest (AES-256) and in transit (TLS 1.2+)
- AWS region: `eu-west-1` (Ireland) — GDPR compliant jurisdiction
- 30-day backup retention with secure deletion
- Access logging for all sensitive operations
- Automated breach detection and notification workflow
