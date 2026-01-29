# 📖 Gmail Setup & Configuration Guide

Detailed instructions for configuring the Google Cloud Console and system settings.

## 1. Google Cloud Setup
1. Go to [Google Cloud Console](https://console.cloud.google.com/).
2. Create a new project named **Minimal-Carbon-CRM**.
3. Enable **Gmail API** and **Google People API**.
4. Configure **OAuth Consent Screen**:
   - User Type: Internal (or External for testing).
   - Scopes: Add `gmail.readonly`, `gmail.modify`, `gmail.compose`, `gmail.send`.
5. Create **OAuth 2.0 Client ID**:
   - Application Type: Web Application.
   - Authorized Redirect URI: `https://your-domain.com/admin/email/oauth/callback`.

## 2. Permissions & Roles
The system supports 4 access levels:
- **Owner**: Full account management, user assignment, and revoking.
- **Manager**: Inbox usage + settings management.
- **Agent**: Inbox usage (Read/Reply/Send).
- **Auditor**: View only + Audit logs access.

## 3. Automation (Cron Job)
To keep the inbox in sync automatically, add this to your server's crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Or run the specific sync command:
```bash
php artisan email:sync --limit=100
```

## 4. Troubleshooting
- **Token Expired**: The system automatically refreshes tokens. If it fails, check if the `refresh_token` is present in the `email_accounts` table.
- **OAuth Error 401**: Check your `GMAIL_CLIENT_SECRET`.
- **Redirect Mismatch**: Ensure your `APP_URL` in `.env` matches the redirect URI in Google Console.
