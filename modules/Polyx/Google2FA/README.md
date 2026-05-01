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
