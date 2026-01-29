# Email Module - Implementation Guide

## Overview

This document provides a comprehensive guide to the newly implemented Gmail Email Management System for the CRM. The module includes a production-ready backend with full OAuth integration, email synchronization, and a complete Blade-based frontend UI.

## What Has Been Created

### Backend Components (Previously Created)

#### Database
- **6 Migrations** for email-related tables:
  - `email_accounts` - Gmail account credentials with OAuth tokens
  - `emails` - Email message storage with soft deletes
  - `email_attachments` - Attachment tracking with checksums
  - `email_user_states` - Per-user read/unread states
  - `email_audit_logs` - Immutable compliance logging

#### Models
- **EmailAccount**: OAuth account management with encrypted token storage
- **Email**: Core email entity with relationships to attachments, user states, and audit logs
- **EmailAttachment**: File tracking with checksum verification
- **EmailUserState**: Isolated per-user read state tracking
- **EmailAuditLog**: Immutable audit trail for compliance

#### Services
- **GmailAuthService**: OAuth 2.0 implementation with automatic token refresh
- **GmailSyncService**: Full sync + incremental sync via Gmail History API
- **EmailParserService**: MIME parsing and email structure extraction
- **AttachmentService**: Streaming download with integrity verification
- **AuditLogger**: Centralized event logging

#### Controllers (Updated in This Session)
- **AuthController**: OAuth redirect, callback, revoke (supports both JSON and redirects)
- **InboxController**: Email listing, viewing, searching, starring, marking as read (full Blade support)
- **AttachmentController**: Download, preview, temporary URLs
- **AuditController**: Audit logs, summary, export (now with Blade views)

### Frontend Components (Created in This Session)

#### View Files (11 Blade Templates)

**Main Views:**
- `resources/views/email/index.blade.php` - Inbox with email list and filters
- `resources/views/email/show.blade.php` - Single email detail view with attachments
- `resources/views/email/thread.blade.php` - Email thread/conversation view
- `resources/views/email/search.blade.php` - Search results with filter sidebar
- `resources/views/email/incoming.blade.php` - Incoming emails only
- `resources/views/email/outgoing.blade.php` - Sent emails only
- `resources/views/email/unread.blade.php` - Unread emails only
- `resources/views/email/accounts.blade.php` - Email account management

**Audit Views:**
- `resources/views/email/audit/logs.blade.php` - Detailed audit logs
- `resources/views/email/audit/summary.blade.php` - Audit statistics and summary

**Partials:**
- `resources/views/email/_sidebar.blade.php` - Navigation and account selector
- `resources/views/email/_email-row.blade.php` - Email list item
- `resources/views/email/_attachment-list.blade.php` - Attachments display
- `resources/views/email/_thread-message.blade.php` - Single message in thread

### Routes Integration

All email routes have been added to `routes/web.php` within the admin authentication middleware:

```php
Route::middleware(['admin.auth'])->prefix('admin')->group(function () {
    Route::prefix('email')->name('email.')->group(function () {
        // 40+ routes for email management
    });
});
```

## Key Features

### Security & Authorization
- ✅ Custom admin guard integration (not default Laravel Auth)
- ✅ Role-based access control via policies
- ✅ OAuth token encryption in database
- ✅ CSRF protection on all state-changing operations
- ✅ Immutable audit logging for compliance

### Email Functionality
- ✅ Gmail OAuth 2.0 integration with automatic token refresh
- ✅ Full + incremental sync via Gmail History API
- ✅ Thread-based email grouping
- ✅ Search by subject, from, or content
- ✅ Star/unstar emails
- ✅ Mark as read/unread with per-user state isolation
- ✅ Soft delete with restore capability
- ✅ Attachment handling with checksum verification
- ✅ Email preview in list with subject truncation

### User Experience
- ✅ Bootstrap 5 responsive design
- ✅ Sidebar navigation with account selector
- ✅ Pagination support for large email lists
- ✅ Real-time status badges (Active, Error, Paused)
- ✅ Last sync timestamp tracking
- ✅ Inline action buttons (star, delete, read)
- ✅ Search functionality with type filters
- ✅ Breadcrumb navigation

## Installation & Setup

### 1. Run Migrations

```bash
php artisan migrate
```

This will create all 6 email-related tables.

### 2. Configure Gmail OAuth

Add to your `.env`:

```env
GMAIL_CLIENT_ID=your_client_id
GMAIL_CLIENT_SECRET=your_client_secret
GMAIL_REDIRECT_URL=http://localhost:8000/admin/email/auth/callback
```

### 3. Add Service Provider

The EmailServiceProvider is auto-registered. If not, add to `config/app.php`:

```php
'providers' => [
    // ...
    \App\Modules\Email\Providers\EmailServiceProvider::class,
],
```

### 4. Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=email-config
```

This publishes `config/gmail.php` for customization.

### 5. Access Email Module

Navigate to:
```
/admin/email
```

The system will guide you to connect a Gmail account if none exists.

## Usage

### Connecting a Gmail Account

1. Go to `/admin/email` → "Connect Account" button
2. Click "Connect Gmail" link
3. Authenticate with your Gmail account
4. Grant requested permissions
5. Account appears in sidebar

### Syncing Emails

#### Option 1: Manual Sync (UI)
Each account shows "Last Sync" timestamp. Sync happens automatically on:
- Account creation (initial full sync)
- Periodic cron job (if configured)

#### Option 2: Command Line
```bash
php artisan email:sync --account-id=1
```

#### Option 3: Scheduled Job
Add to `app/Console/Kernel.php`:

```php
$schedule->command('email:sync')->everyFiveMinutes();
```

### Viewing Emails

1. **Inbox**: `/admin/email/?account=1`
2. **Incoming**: `/admin/email/incoming/1`
3. **Sent**: `/admin/email/outgoing/1`
4. **Unread**: `/admin/email/unread/1`
5. **Single Email**: `/admin/email/{emailId}`
6. **Search**: `/admin/email/search/1?q=keyword`

### Attachments

Attachments are:
- Automatically downloaded and stored
- Verified with checksums
- Available for download/preview
- Logged in audit trail when accessed

### Audit & Compliance

All actions are logged:
- OAuth connections/revocations
- Token refreshes
- Email reads
- Attachment downloads
- Sync operations

View audit logs:
1. Go to account's "Audit Log" tab
2. Filter by action, user, or date range
3. Export as CSV or JSON

## Database Schema

### emails table
```
id, email_account_id, gmail_message_id, thread_id, direction
from_email, to_emails (JSON), cc_emails (JSON), bcc_emails (JSON)
subject, body_text, body_html, received_at, sent_at
is_starred, checksum, created_at, updated_at, deleted_at
```

### email_accounts table
```
id, admin_id, company_id, email_address, refresh_token (encrypted)
access_token (encrypted), token_expires_at, scopes, sync_status
last_sync_at, last_history_id, sync_error, created_at, updated_at
```

### email_attachments table
```
id, email_id, filename, mime_type, size, checksum
storage_path, downloaded_at, created_at, updated_at
```

### email_user_states table
```
id, email_id, admin_id, is_read, created_at, updated_at
```

### email_audit_logs table (Immutable)
```
id, email_account_id, email_id, admin_id, action
ip_address, description, created_at (no updated_at)
```

## API Response Examples

### Get Inbox (JSON)
```
GET /admin/email?account=1
Accept: application/json

{
  "data": [
    {
      "id": 1,
      "subject": "Meeting tomorrow",
      "from_email": "john@example.com",
      "received_at": "2024-01-27T10:30:00Z",
      "is_starred": false,
      "has_attachments": true
    }
  ],
  "pagination": {
    "total": 42,
    "per_page": 25,
    "current_page": 1,
    "last_page": 2
  }
}
```

### Mark as Read (JSON)
```
POST /admin/email/{emailId}/read
Accept: application/json

{ "message": "Marked as read" }
```

## Troubleshooting

### Issue: "Gmail API not configured"
**Solution**: Check `config/gmail.php` and ensure OAuth credentials are in `.env`

### Issue: "Sync error: Invalid refresh token"
**Solution**: Revoke account and reconnect. Old tokens may have expired.

### Issue: "No email accounts connected"
**Solution**: Click "Connect Account" and authorize a Gmail account

### Issue: Emails not syncing
**Solution**: 
1. Check cron job is running: `php artisan schedule:run`
2. View sync status in Audit Log
3. Check app logs: `storage/logs/laravel.log`

## Performance Considerations

### Caching
- Email list paginated (25 per page)
- Database indexes on: `email_account_id`, `direction`, `created_at`

### Large Attachments
- Streamed in 256KB chunks
- Temporary URLs expire after 60 minutes
- Preview limited to images and PDF

### Sync Optimization
- Incremental sync via Gmail History API (not full re-sync)
- Duplicate prevention via `gmail_message_id`
- Transaction-safe database writes

## Security Notes

1. **Token Storage**: Refresh tokens encrypted in database using Laravel's encryption
2. **CSRF Protection**: All forms protected with CSRF tokens
3. **Authorization**: All actions checked against EmailPolicy
4. **Audit Trail**: Immutable logging for compliance
5. **Soft Deletes**: Emails never truly deleted (recoverable)
6. **Checksum Verification**: Attachments verified against checksums

## Extending the Module

### Adding New Email Actions

1. Add route in `routes/web.php`
2. Create controller method
3. Add authorization check in EmailPolicy
4. Create Blade template (if UI)
5. Log action in AuditLogger

### Custom Email Filtering

Extend `EmailRepository::getByAccount()`:

```php
public function getByStarred(EmailAccount $account, $paginate = 25)
{
    return $this->query()
        ->where('email_account_id', $account->id)
        ->where('is_starred', true)
        ->paginate($paginate);
}
```

### Webhook Integration

Emails can trigger webhooks:

```php
AuditLogger::logEmailReceived($email, 'webhook_triggered');
// Send to external system
```

## File Structure

```
app/Modules/Email/
├── Controllers/
│   ├── AuthController.php
│   ├── InboxController.php
│   ├── AttachmentController.php
│   └── AuditController.php
├── Models/
│   ├── EmailAccount.php
│   ├── Email.php
│   ├── EmailAttachment.php
│   ├── EmailUserState.php
│   └── EmailAuditLog.php
├── Services/
│   ├── GmailAuthService.php
│   ├── GmailSyncService.php
│   ├── EmailParserService.php
│   ├── AttachmentService.php
│   ├── AuditLogger.php
│   └── TokenRefreshService.php
├── Repositories/
│   └── EmailRepository.php
├── Policies/
│   └── EmailPolicy.php
├── Traits/
│   └── HasEmailAccounts.php
├── Resources/
│   ├── EmailResource.php
│   └── AttachmentResource.php
├── Commands/
│   └── SyncGmailCommand.php
├── Jobs/
│   └── VerifyEmailIntegrity.php
├── Providers/
│   └── EmailServiceProvider.php
└── DTOs/
    └── ParsedEmailDTO.php

resources/views/email/
├── index.blade.php
├── show.blade.php
├── thread.blade.php
├── search.blade.php
├── incoming.blade.php
├── outgoing.blade.php
├── unread.blade.php
├── accounts.blade.php
├── audit/
│   ├── logs.blade.php
│   └── summary.blade.php
├── _sidebar.blade.php
├── _email-row.blade.php
├── _attachment-list.blade.php
└── _thread-message.blade.php

config/
└── gmail.php

routes/
└── web.php (contains email routes)
```

## Production Checklist

- [ ] Gmail OAuth credentials configured in `.env`
- [ ] Database migrations run
- [ ] Cron job scheduled for sync (`php artisan schedule:run`)
- [ ] Email backups configured
- [ ] Audit logs regularly exported
- [ ] Rate limiting configured for OAuth endpoints
- [ ] Admin users trained on email UI
- [ ] Error monitoring configured (Sentry, etc.)

## Support & Updates

For issues or updates:
1. Check Audit Log for detailed error messages
2. Review application logs: `storage/logs/laravel.log`
3. Contact CRM support with account ID and error details

---

**Version**: 1.0  
**Last Updated**: January 27, 2024  
**Status**: Production Ready
