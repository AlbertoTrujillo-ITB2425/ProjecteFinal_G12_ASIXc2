# 🛡️ CyberAudit SaaS - Small Business Security Platform

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?logo=docker)](https://www.docker.com/)
[![Solana](https://img.shields.io/badge/Payments-Solana-14F195?logo=solana)](https://solana.com/)
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
- **Web3-native businesses**: Crypto-friendly merchants

### 📈 Market Size
- **Spain**: 2.9 million small businesses (95% of all companies)
- **Europe**: 23 million SMEs
- **Global SMB cybersecurity market**: $75.5 billion (2024), growing at 15.2% CAGR
- **Crypto adoption in SMBs**: Growing 45% YoY in Europe

### 💰 Pricing Strategy

| Plan | EURC/Month | USDC/Month | Features | Target Customer |
|------|------------|------------|----------|-----------------|
| **Basic** | 29.99 EURC | $32.99 USDC | 1 website, daily scans, email alerts | Single-location shops |
| **Professional** | 59.99 EURC | $65.99 USDC | 3 websites, real-time monitoring, SMS alerts | Small chains (2-3 locations) |
| **Business** | 99.99 EURC | $109.99 USDC | 10 websites, API access, compliance reports | Growing businesses |

**Annual Plans**: Save 20% when paying yearly in stablecoins

## 💳 Payment Infrastructure - Solana Blockchain

### 🌐 Why Solana?
We chose Solana for subscription payments to provide:

| Feature | Benefit | Traditional Payments |
|---------|---------|---------------------|
| **Transaction Speed** | 400ms finality | 3-5 business days |
| **Transaction Cost** | $0.00025 per transaction | 2.9% + €0.30 (Stripe) |
| **Global Accessibility** | Anyone with crypto wallet | Requires bank account |
| **No Chargebacks** | Eliminates fraud risk | 0.5-1% chargeback rate |
| **Instant Settlement** | Immediate cash flow | T+2 settlement |
| **24/7 Operations** | Always available | Banking hours limitations |

### 💎 Accepted Stablecoins

#### EURC (Euro Coin)
- **Issuer**: Circle
- **1 EURC = 1 EUR** (1:1 backed)
- **Perfect for European customers**
- **MICA-compliant** (EU regulation)

#### USDC (USD Coin)
- **Issuer**: Circle
- **1 USDC = 1 USD** (1:1 backed)
- **Global standard stablecoin**
- **Monthly reserves attestation**

### 🔐 Payment Security & Transparency

```
Customer Wallet → Solana Network → CyberAudit Treasury Wallet
     ↓                  ↓                      ↓
[Signs transaction] [Validates] [Auto-activates subscription]
```

**Key Features**:
- ✅ **Non-custodial**: We never hold your private keys
- ✅ **On-chain verification**: All payments publicly verifiable
- ✅ **Smart contract automation**: Subscription management via Solana programs
- ✅ **Instant activation**: Service starts immediately after payment confirmation
- ✅ **Crypto-to-fiat offramp**: We convert to EUR/USD as needed for operations

### 📱 How Payment Works

1. **Select Plan**: Choose Basic/Professional/Business
2. **Connect Wallet**: Phantom, Solflare, or any Solana wallet
3. **Approve Transaction**: Sign payment of EURC/USDC
4. **Instant Activation**: Account activated within 1 minute
5. **Monthly Auto-renewal**: (Optional) Automated recurring payments

### 🌟 Payment Benefits for Customers

#### For Crypto-Native Businesses
- **No conversion needed**: Already operate in crypto
- **Tax simplification**: Crypto-to-crypto transactions
- **Privacy**: No bank account exposure

#### For Traditional Businesses
- **Lower costs**: Save ~3% on payment processing
- **Fast settlement**: Use funds immediately
- **Innovation**: Early adopter advantage
- **Optional fiat**: We provide EUR/USD invoices for accounting

## 🏆 Competitive Analysis

### 🥇 Our Advantages

| Feature | CyberAudit | Competitor A | Competitor B |
|---------|------------|--------------|--------------|
| **Price** | 29.99 EURC/month | €99/month | €49/month |
| **Payment Fees** | $0.00025 (Solana) | 2.9% + fee | 2.9% + fee |
| **Setup Time** | 5 minutes | 2-3 days | 1 hour |
| **No IT Required** | ✅ | ❌ | ⚠️ |
| **Spanish Support** | ✅ | ❌ | ⚠️ |
| **GDPR Compliance** | ✅ | Extra €50/month | ❌ |
| **Crypto Payments** | ✅ Solana | ❌ | ❌ |
| **International** | ✅ No borders | Bank restrictions | Bank restrictions |

### 🚀 Unique Selling Propositions

1. **First crypto-native SMB security platform** - Web3 meets traditional business
2. **Designed for non-technical users** - Simple interface, no configuration needed
3. **Borderless payments** - Accept customers worldwide instantly
4. **Zero payment fraud** - Blockchain eliminates chargebacks
5. **Transparent pricing** - No hidden fees, all on-chain
6. **Local market understanding** - Built for Spanish/European business regulations
7. **Quick deployment** - Active protection in under 10 minutes

## 🏗️ Technical Overview

### Architecture
```
Small Business Website → CyberAudit Monitoring → Security Dashboard
        ↑                       ↑                       ↑
    [Threats Blocked]   [Vulnerabilities Found]   [Real-time Alerts]
                                ↓
                    [Solana Payment Gateway]
                                ↓
                    [EURC/USDC Subscription]
```

### Key Technologies

**Security Stack**:
- **Monitoring Engine**: Custom PHP application with real-time scanning
- **Security Layer**: BunkerWeb WAF (Web Application Firewall)
- **Data Storage**: MariaDB for logs and reports
- **User Management**: OpenLDAP for secure authentication
- **Session Handling**: Redis for performance
- **Deployment**: Docker containers for easy setup

**Payment Stack**:
- **Blockchain**: Solana (high-speed, low-cost)
- **Stablecoins**: EURC & USDC (Circle-issued)
- **Wallet Integration**: Solana Wallet Adapter
- **Smart Contracts**: Rust-based Solana programs
- **Payment Monitoring**: Real-time transaction tracking
- **Offramp**: Circle APIs for fiat conversion

## 📅 Development Roadmap

### Phase 1: MVP (Current) ✅
- Basic website monitoring
- Vulnerability scanning
- Email alerts
- Simple dashboard
- **Solana payment integration** ✅
- **EURC/USDC acceptance** ✅

### Phase 2: Growth (Q3 2026) 🔄
- Mobile app for alerts
- SMS notifications
- Advanced reporting
- API for developers
- **Automated subscription renewals** (Solana programs)
- **Multi-signature treasury** for security
- **DAO governance** for platform decisions

### Phase 3: Expansion (Q4 2026) 📅
- Multi-language support
- Advanced compliance features
- **SPL token loyalty program**
- **Partner NFT access passes**
- **Crypto cashback rewards**

## 👥 Team

### 🎓 ITB Final Project - Group 7
**Supervised by**: Institut Tecnològic de Barcelona

| Role | Name | Responsibilities |
|------|------|------------------|
| **Project Lead** | Alberto Trujillo | Architecture, Business Strategy, Blockchain Integration |
| **Backend Developer** | Joel Muñoz | Core Platform Development, Smart Contracts |
| **Security Specialist** | Luka Ukleba | Security Features, Compliance, Crypto Security |

### 🎯 Project Goals
1. **Academic**: Demonstrate comprehensive IT systems administration skills
2. **Practical**: Create a market-ready SaaS product with Web3 integration
3. **Business**: Validate the small business cybersecurity market with crypto payments
4. **Technical**: Implement enterprise-grade security in an accessible package
5. **Innovation**: Bridge traditional SMBs with blockchain technology

## 📈 Business Model

### Revenue Streams
1. **Subscription Fees** (Primary): Monthly/Annual plans in EURC/USDC
2. **Setup Services**: One-time setup fee for complex deployments
3. **Compliance Certification**: GDPR/PCI-DSS compliance packages
4. **Partner Program**: Commission for referrals (paid in crypto)
5. **Token Staking** (Future): Passive income for long-term subscribers

### Cost Structure
- **Development**: €0 (academic project)
- **Hosting**: €50/month (scalable cloud infrastructure)
- **Solana RPC**: €15/month (transaction monitoring)
- **Support**: €500/month (part-time customer service)
- **Marketing**: €300/month (digital campaigns)
- **Crypto Offramp Fees**: 0.1% (significantly lower than traditional 2.9%)

### Financial Projections (Year 1)

**Assumptions**:
- 100 customers by end of Year 1
- Average plan: 49.99 EURC/month
- 60% monthly retention, 40% annual prepay

**Revenue**:
- **Monthly**: €5,000 (100 customers × €50 avg)
- **Annual**: €60,000
- **Savings vs Traditional Payments**: €1,740/year (no 2.9% fees)

**Break-even**: 3-4 months (faster due to instant settlement)

**Year 1 Net Profit**: €25,000-€35,000

## 🚀 Getting Started

### For Businesses (Traditional Payment Users)

#### Option 1: Get a Solana Wallet (Recommended)
```
1. Download Phantom Wallet (phantom.app)
2. Create new wallet & backup seed phrase
3. Buy EURC/USDC on Coinbase or Kraken
4. Send to your Phantom wallet
5. Subscribe to CyberAudit
```

#### Option 2: We Help You Get Started
- **Free consultation**: We guide you through wallet setup
- **Onboarding bonus**: First month 50% off for new crypto users
- **Educational resources**: Video tutorials in Spanish/English

### For Crypto-Native Businesses
```bash
# You already know the drill 😎
1. Connect wallet
2. Approve EURC/USDC transaction
3. Get instant access
```

### For Developers
```bash
# Quick demo deployment
git clone https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7
cd ProjecteFinal_G7
docker-compose up -d
# Access at: http://localhost:8080

# Test Solana payment integration (devnet)
npm run test:payment
```

## 📚 Documentation

- **[Admin Manual](./docs/admin_manual.md)** - Installation, configuration, troubleshooting
- **[Client Manual](./docs/client_manual.md)** - User guide, FAQs, common issues
- **[API Documentation](./docs/api.md)** - Developer integration guide
- **[Business Plan](./docs/business_plan.md)** - Market analysis, financial projections
- **[Crypto Payment Guide](./docs/crypto_payments.md)** - Wallet setup, payment instructions
- **[Smart Contract Docs](./docs/solana_contracts.md)** - Technical blockchain implementation

## 🔒 Security & Compliance

### Certifications & Standards
- **GDPR Compliant**: Data protection for European customers
- **PCI-DSS Not Required**: No credit card data stored
- **ISO 27001 Alignment**: Information security management
- **MICA Compliant**: EU Markets in Crypto-Assets regulation
- **SOC 2 Type II** (Planned): Audited security controls

### Data Protection
- **Encryption**: All data encrypted at rest and in transit (AES-256)
- **Privacy**: No customer data sold or shared
- **Transparency**: Clear data usage policies
- **Blockchain Privacy**: Wallet addresses anonymized in reports
- **Right to be Forgotten**: Full GDPR erasure capability

### Crypto Security
- **Multi-signature treasury**: 2-of-3 signatures required for fund movements
- **Cold storage**: 90% of funds in offline wallets
- **Real-time monitoring**: Automated fraud detection on-chain
- **Insurance**: Crypto holdings insured through Coincover
- **Regular audits**: Smart contract audits by Trail of Bits

## 🤝 Partnerships & Integration

### Current Integrations
- **Blockchain**: Solana mainnet, Circle APIs
- **Wallets**: Phantom, Solflare, Backpack, Ledger
- **Communication**: Twilio (SMS), SendGrid (Email)
- **Hosting**: AWS, DigitalOcean, Local providers
- **Crypto Infrastructure**: Helius RPC, Circle EURC/USDC

### Seeking Partnerships With:
- **Web hosting companies** (reseller program with crypto payouts)
- **Local business associations** (group discounts, crypto education)
- **IT service providers** (referral program with USDC rewards)
- **Crypto exchanges** (direct onboarding for new users)
- **Web3 DAOs** (community-driven security governance)

## 💡 Why Blockchain for Traditional Businesses?

### Common Objections Answered

**"Crypto is too complicated"**
- ✅ We provide full onboarding support
- ✅ Works like any payment app (scan QR, confirm)
- ✅ Optional traditional invoicing for accounting

**"Crypto is volatile"**
- ✅ EURC/USDC are stable (1:1 to EUR/USD)
- ✅ No price fluctuation risk
- ✅ Can instantly convert to fiat if needed

**"I don't trust blockchain"**
- ✅ Solana processes 400M+ transactions safely
- ✅ Circle stablecoins are audited monthly
- ✅ More transparent than traditional banking

**"What about taxes?"**
- ✅ We provide EUR-denominated invoices
- ✅ Stablecoin = same as fiat for EU tax purposes
- ✅ Accounting software integration available

## 📞 Contact & Support

### Customer Support
- **Email**: support@cyberaudit.local
- **Discord**: [discord.gg/cyberaudit](https://discord.gg/cyberaudit)
- **Telegram**: @CyberAuditSupport
- **Phone**: +34 93 XXX XX XX (Business hours)

### Crypto Payment Support
- **Wallet Help**: crypto-help@cyberaudit.local
- **Video Tutorials**: [youtube.com/@cyberaudit](https://youtube.com/@cyberaudit)
- **Live Chat**: Available on website (Spanish/English)

### Team Contact
- **Alberto Trujillo**: alberto.trujillo.7e6@itb.cat
- **Joel Muñoz**: joel.munoz.7e8@itb.cat
- **Luka Ukleba**: luka.ukleba.7e8@itb.cat

### Treasury Addresses (Solana Mainnet)
- **EURC Payments**: `CyberXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX`
- **USDC Payments**: `CyberYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY`

*All payments are publicly verifiable on Solana Explorer*

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **Institut Tecnològic de Barcelona** for academic guidance
- **Solana Foundation** for blockchain infrastructure
- **Circle** for EURC/USDC stablecoin technology
- **Open source community** for invaluable tools and libraries
- **Small business owners** who provided feedback and testing
- **Web3 community** for pushing innovation boundaries

---

<div align="center">
  
**Protecting Small Businesses in the Digital Age**  
*Enterprise-grade security meets Web3 innovation*

🌐 [Website](http://cyberaudit.local) | 🎮 [Demo](http://demo.cyberaudit.local) | 📚 [Docs](http://docs.cyberaudit.local) | 💬 [Discord](https://discord.gg/cyberaudit)

**Accepted Payments**: EURC • USDC • Solana Network  
*Fast • Secure • Borderless*

</div>
