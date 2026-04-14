# 🛡️ CyberAudit SaaS - Small Business Security Platform

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?logo=docker)](https://www.docker.com/)
[![Status](https://img.shields.io/badge/status-active-success.svg)]()

## 📋 Executive Summary

**CyberAudit SaaS** is an affordable cybersecurity monitoring platform designed specifically for **small businesses** (bakeries, pharmacies, local shops, restaurants) that lack dedicated IT departments but need professional-grade security protection.

### 🎯 The Problem We Solve

Small businesses are increasingly targeted by cybercriminals (43% of attacks target SMBs) but often can't afford enterprise security solutions. They face:
- **Data breaches** (customer information, payment details)
- **Ransomware attacks** (average cost: $133,000 for SMBs)
- **Compliance requirements** (GDPR, PCI-DSS)
- **Limited IT knowledge** and resources

### 💡 Our Solution

A simple, affordable SaaS platform that provides:
- **24/7 threat monitoring** for websites and online services
- **Automated vulnerability scanning**
- **Real-time alerts** via email/SMS
- **Monthly security reports**
- **GDPR compliance assistance**
- **No IT expertise required**

## 📊 Market Analysis

### 🎯 Target Market
- **Small Retailers**: Bakeries, pharmacies, butcher shops, local stores
- **Service Businesses**: Restaurants, cafes, hair salons, repair shops
- **Professional Services**: Accountants, lawyers, consultants (1-10 employees)
- **E-commerce Startups**: Small online stores

### 📈 Market Size
- **Spain**: 2.9 million small businesses (95% of all companies)
- **Europe**: 23 million SMEs
- **Global SMB cybersecurity market**: $75.5 billion (2024), growing at 15.2% CAGR

### 💰 Pricing Strategy
| Plan | Price/Month | Features | Target Customer |
|------|-------------|----------|-----------------|
| **Basic** | €29.99 | 1 website, daily scans, email alerts | Single-location shops |
| **Professional** | €59.99 | 3 websites, real-time monitoring, SMS alerts | Small chains (2-3 locations) |
| **Business** | €99.99 | 10 websites, API access, compliance reports | Growing businesses |

## 🏆 Competitive Analysis

### 🥇 Our Advantages
| Feature | CyberAudit | Competitor A | Competitor B |
|---------|------------|--------------|--------------|
| **Price** | €29.99/month | €99/month | €49/month |
| **Setup Time** | 5 minutes | 2-3 days | 1 hour |
| **No IT Required** | ✅ | ❌ | ⚠️ |
| **Spanish Support** | ✅ | ❌ | ⚠️ |
| **GDPR Compliance** | ✅ | Extra €50/month | ❌ |
| **Local Business Focus** | ✅ | ❌ | ❌ |

### 🚀 Unique Selling Propositions
1. **Designed for non-technical users** - Simple interface, no configuration needed
2. **Local market understanding** - Built for Spanish/European business regulations
3. **Affordable entry point** - 70% cheaper than enterprise solutions
4. **Quick deployment** - Active protection in under 10 minutes
5. **Bilingual support** - Spanish and English customer service

## 🏗️ Technical Overview

### Architecture
```
Small Business Website → CyberAudit Monitoring → Security Dashboard
        ↑                       ↑                       ↑
    [Threats Blocked]   [Vulnerabilities Found]   [Real-time Alerts]
```

### Key Technologies
- **Monitoring Engine**: Custom PHP application with real-time scanning
- **Security Layer**: BunkerWeb WAF (Web Application Firewall)
- **Data Storage**: MariaDB for logs and reports
- **User Management**: OpenLDAP for secure authentication
- **Session Handling**: Redis for performance
- **Deployment**: Docker containers for easy setup

## 📅 Development Roadmap

### Phase 1: MVP (Current) ✅
- Basic website monitoring
- Vulnerability scanning
- Email alerts
- Simple dashboard

### Phase 2: Growth (Q3 2026) 🔄
- Mobile app for alerts
- SMS notifications
- Advanced reporting
- API for developers

### Phase 3: Expansion (Q4 2026) 📅
- Multi-language support
- Payment integration
- Partner program
- Advanced compliance features

## 👥 Team

### 🎓 ITB Final Project - Group 7
**Supervised by**: Institut Tecnològic de Barcelona

| Role | Name | Responsibilities |
|------|------|------------------|
| **Project Lead** | Alberto Trujillo | Architecture, Business Strategy |
| **Backend Developer** | Joel Muñoz | Core Platform Development |
| **Security Specialist** | Luka Ukleba | Security Features, Compliance |

### 🎯 Project Goals
1. **Academic**: Demonstrate comprehensive IT systems administration skills
2. **Practical**: Create a market-ready SaaS product
3. **Business**: Validate the small business cybersecurity market
4. **Technical**: Implement enterprise-grade security in an accessible package

## 📈 Business Model

### Revenue Streams
1. **Subscription Fees** (Primary): Monthly/Annual plans
2. **Setup Services**: One-time setup fee for complex deployments
3. **Compliance Certification**: GDPR/PCI-DSS compliance packages
4. **Partner Program**: Commission for referrals

### Cost Structure
- **Development**: €0 (academic project)
- **Hosting**: €50/month (scalable cloud infrastructure)
- **Support**: €500/month (part-time customer service)
- **Marketing**: €300/month (digital campaigns)

### Financial Projections (Year 1)
- **Target Customers**: 100 small businesses
- **Monthly Revenue**: €3,000-€6,000
- **Break-even**: 4-6 months
- **Year 1 Profit**: €15,000-€30,000

## 🚀 Getting Started

### For Businesses
1. **Visit**: cyberaudit.local (demo)
2. **Sign Up**: Choose your plan
3. **Add Website**: Enter your website URL
4. **Activate**: Protection starts immediately

### For Developers
```bash
# Quick demo deployment
git clone https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7
cd ProjecteFinal_G7
docker-compose up -d
# Access at: http://localhost:8080
```

## 📚 Documentation

- **[Admin Manual](./docs/admin_manual.md)** - Installation, configuration, troubleshooting
- **[Client Manual](./docs/client_manual.md)** - User guide, FAQs, common issues
- **[API Documentation](./docs/api.md)** - Developer integration guide
- **[Business Plan](./docs/business_plan.md)** - Market analysis, financial projections

## 🔒 Security & Compliance

### Certifications & Standards
- **GDPR Compliant**: Data protection for European customers
- **PCI-DSS Ready**: Payment card industry standards
- **ISO 27001 Alignment**: Information security management

### Data Protection
- **Encryption**: All data encrypted at rest and in transit
- **Privacy**: No customer data sold or shared
- **Transparency**: Clear data usage policies

## 🤝 Partnerships & Integration

### Current Integrations
- **Payment**: Stripe, PayPal
- **Communication**: Twilio (SMS), SendGrid (Email)
- **Hosting**: AWS, DigitalOcean, Local providers

### Seeking Partnerships With:
- **Web hosting companies** (reseller program)
- **Local business associations** (group discounts)
- **IT service providers** (referral program)
- **Business software platforms** (API integration)

## 📞 Contact & Support

### Customer Support
- **Email**: support@cyberaudit.local
- **Phone**: +34 93 XXX XX XX (Business hours)
- **Live Chat**: Available on website

### Technical Support
- **Documentation**: [docs.cyberaudit.local](http://docs.cyberaudit.local)
- **Community**: [forum.cyberaudit.local](http://forum.cyberaudit.local)
- **Emergency**: 24/7 critical issue response

### Team Contact
- **Alberto Trujillo**: alberto.trujillo.7e6@itb.cat
- **Joel Muñoz**: joel.munoz.7e8@itb.cat
- **Luka Ukleba**: luka.ukleba.7e8@itb.cat

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **Institut Tecnològic de Barcelona** for academic guidance
- **Open source community** for invaluable tools and libraries
- **Small business owners** who provided feedback and testing
- **Cybersecurity experts** who contributed insights

---

<div align="center">
  
**Protecting Small Businesses in the Digital Age**  
*Because every business deserves enterprise-grade security*

[Website](http://cyberaudit.local) | [Demo](http://demo.cyberaudit.local) | [Documentation](http://docs.cyberaudit.local)

</div>
