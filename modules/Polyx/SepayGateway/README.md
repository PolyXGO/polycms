# SePay Gateway

SePay QR Code payment gateway for Vietnamese banks. Enables instant bank transfer payments via QR code at checkout.

## Features

- QR code generation for bank transfers
- Support for major Vietnamese banks
- Automatic payment verification via SePay webhook
- Order status auto-update on payment confirmation
- Configurable bank account details
- Transaction reference matching

## Configuration

Configure via **Settings > Payment Gateways > SePay** in the admin panel:

| Setting | Description |
|---------|-------------|
| Enable SePay | Toggle gateway on/off |
| API Key | SePay API key |
| Bank Account | Receiving bank account number |
| Bank Name | Bank name for display |
| Account Holder | Account holder name |

## Checkout Flow

1. Customer selects bank transfer at checkout
2. QR code is generated with order reference
3. Customer scans QR code with banking app
4. Customer completes transfer
5. SePay webhook confirms payment
6. Order status updated automatically

## Requirements

- SePay account (sepay.vn)
- Vietnamese bank account
- Webhook URL must be publicly accessible

## Version

1.0.0


---

## Live Demo

Explore PolyCMS with a ready-to-use live demo and walk through the admin dashboard, customer portal, content tools, e-commerce flows, theme system, and module features.

- **Admin demo**: [https://polycms.org/admin/login](https://polycms.org/admin/login)
- **Customer demo**: [https://polycms.org/account/login](https://polycms.org/account/login)

Try the demo, review the workflows, and see how PolyCMS can accelerate your next content, commerce, or SaaS project.

## 🌐 Resources & Links

- 🌐 **Official Website**: [https://polycms.org](https://polycms.org)
- 📚 **Documentation**: [https://headrandom.com/_EpBecN8](https://headrandom.com/_EpBecN8)
- 🐙 **GitHub Repository**: [https://github.com/PolyXGO/polycms](https://github.com/PolyXGO/polycms)
- 🐛 **Issue Tracker**: [https://github.com/PolyXGO/polycms/issues](https://github.com/PolyXGO/polycms/issues)
