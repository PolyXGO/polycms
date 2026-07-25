# PayPal Gateway

PayPal payment gateway integration for PolyCMS e-commerce.

## Features

- PayPal Checkout integration
- Support for PayPal sandbox and live modes
- Order payment processing via PayPal API
- Automatic order status update on payment confirmation
- Refund support through PayPal
- Configurable client ID and secret

## Configuration

Configure via **Settings > Payment Gateways > PayPal** in the admin panel:

| Setting | Description |
|---------|-------------|
| Enable PayPal | Toggle gateway on/off |
| Mode | Sandbox or Live |
| Client ID | PayPal API client ID |
| Client Secret | PayPal API secret key |
| Currency | Payment currency code (USD, EUR, etc.) |

## Checkout Flow

1. Customer selects PayPal at checkout
2. Redirected to PayPal for authentication
3. Customer approves payment on PayPal
4. Redirected back to PolyCMS
5. Order status updated to "Paid"

## Requirements

- PayPal Business account
- API credentials from PayPal Developer Dashboard
- HTTPS enabled on your site (required for live mode)

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
