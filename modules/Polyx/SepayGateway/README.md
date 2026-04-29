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
