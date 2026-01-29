# 🚀 Gmail Integration Quickstart

Follow these 3 steps to get the email system running in 5 minutes.

## 1. Environment Configuration
Add your Google OAuth credentials to `.env`:

```env
GMAIL_CLIENT_ID=your_client_id_here
GMAIL_CLIENT_SECRET=your_client_secret_here
GMAIL_APP_NAME="Minimal Carbon CRM"
```

## 2. Initialize Module
Run the migrations and clear cache:

```bash
php artisan migrate
php artisan optimize:clear
```

## 3. Connect Your First Account
1. Open the CRM in your browser.
2. Navigate to **Email** in the sidebar.
3. Click **Connect Gmail Account**.
4. Select a Company and click **Continue with Google**.
5. After authorization, your inbox will automatically begin syncing.

---

**Next Step:** Read `GMAIL_SETUP_GUIDE.md` for detailed production configuration.
