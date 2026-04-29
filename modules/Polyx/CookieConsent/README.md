# CookieConsent

GDPR and CCPA compliant cookie consent banner for user tracking and data collection acknowledgment.

## Features

- Configurable cookie consent banner on frontend
- Accept / Reject / Customize buttons
- Privacy policy URL link
- Customizable consent message text
- Stores user consent preference
- Non-intrusive banner placement

## Configuration

All settings are managed via **Settings > General** in the admin panel:

| Setting | Description |
|---------|-------------|
| Enable Cookie Consent | Toggle banner on/off |
| Consent Message | Text displayed in the banner |
| Privacy Policy URL | Link to your privacy policy page |
| Accept Button Text | Label for the accept button |
| Reject Button Text | Label for the reject button |
| Customize/Policy Button Text | Label for the policy link button |

## How It Works

1. When enabled, a banner appears at the bottom of every frontend page for new visitors
2. User clicks Accept or Reject
3. Preference is stored in a cookie
4. Banner does not appear again for users who have responded

## Version

1.0.0
