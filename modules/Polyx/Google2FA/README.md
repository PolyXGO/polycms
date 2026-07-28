# Google2FA

Google Authenticator (TOTP) 2FA support for PolyCMS.

## Features

- Per-user 2FA enablement.
- QR code generation for easy setup.
- Interception of login flow for 2FA verification.
- Recovery codes support.

## Module Structure

```
modules/Polyx/Google2FA/
├── module.json                    # Module manifest
├── src/
│   ├── Google2FAServiceProvider.php  # Main service provider
│   ├── Controllers/               # Controllers
│   ├── Models/                    # Models
│   └── database/
│       └── migrations/            # Migrations
└── README.md                      # This file
```

## How It Works

### 1. Login Interception

The module filters the `auth.login.pre_token` hook. If a user has 2FA enabled, the standard login response is replaced with a 403 response requiring 2FA verification.

### 2. Verification

Users must then call the 2FA verification endpoint with their 6-digit code to receive a final Sanctum token.

## Hooks Used

- `auth.login.pre_token` - Filter hook for intercepting the login flow.


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
