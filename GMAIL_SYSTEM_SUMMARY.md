# 🏗️ Gmail Integration System Architecture

Technical overview of the email module design and security features.

## Module Design
The system follows a **Modular Domain Driven** approach, encapsulated in `app/Modules/Email`.

### Key Components
1. **GmailAuthService**: Singleton handling OAuth flow and token encryption (AES-256).
2. **GmailSyncService**: Handles heavy lifting of fetching, parsing MIME, and transactional storage.
3. **EmailRepository**: Optimized queries with user-state eager loading.
4. **AuditLogger**: Captures every system change for compliance.

## Security Features
- **Zero-Password Storage**: Uses Google OAuth 2.0 exclusively.
- **Encrypted Tokens**: Access and Refresh tokens are encrypted at rest using Laravel's `Crypt` facade.
- **Transactional Sync**: DB Transactions ensure that an email is either fully stored (including attachments) or not at all.
- **Gate-Protected Routing**: All routes are protected by `EmailPolicy` and `admin.auth` middleware.

## Database Schema
- `email_accounts`: Central authority for tokens and sync status.
- `emails`: Denormalized message content for fast searching.
- `email_attachments`: Metadata linking to Gmail binary blobs or local storage.
- `email_user_states`: Decouples "Read/Starred" status from the email itself, allowing multiple users to have separate read counts.
- `email_audit_logs`: Immutable trail of user and system actions.

## Data Flow
```text
[Gmail API] -> [SyncService] -> [Database] -> [Repository] -> [UI]
     ^              |              |
  [AuthService] <---+ [AuditLogger] +--> [Policy Checks]
```
