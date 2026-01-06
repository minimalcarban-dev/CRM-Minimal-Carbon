# 🚀 Quick Command Reference - Real-Time Chat

## ✅ Current Status

-   **Broadcasting Driver:** Pusher ✅
-   **Queue Worker:** Running via PM2 ✅
-   **Frontend Assets:** Built with Vite ✅
-   **Queue Jobs:** All processed successfully ✅

---

## 📊 Daily Management Commands

### Check Queue Worker Status

```powershell
pm2 status
pm2 logs laravel-queue --lines 20
```

### Check Failed Jobs (should be empty)

```bash
php artisan queue:failed
```

### Restart Queue Worker (if needed)

```powershell
pm2 restart laravel-queue
pm2 logs laravel-queue
```

### Stop/Start Queue Worker

```powershell
# Stop worker (stops real-time chat)
pm2 stop laravel-queue

# Start worker again
pm2 start laravel-queue

# Delete from PM2 (completely remove)
pm2 delete laravel-queue
```

### ⚠️ IMPORTANT: PowerShell Close करने से Queue नहीं Rukेगा!
**PM2 background daemon है** - आप PowerShell close कर सकते हो, queue worker चलता रहेगा! ✅

```powershell
# PowerShell close karo → Queue चलेगा ✅
# Computer restart → Queue dubara start hoga (if pm2 startup configured) ✅
# Ctrl+C karo → Queue पर कोई असर नहीं ✅
```

### View Real-Time Queue Processing

```powershell
php artisan queue:work --queue=default --tries=3 --sleep=3
```

---

## 🔧 Common Troubleshooting

### If Messages Not Appearing in Real-Time

1. **Check Queue Worker:**
    ```powershell
    pm2 status  # Should show "online"
    pm2 logs laravel-queue
    ```
2. **Check Failed Jobs:**
    ```bash
    php artisan queue:failed
    ```
3. **Restart Worker:**
    ```powershell
    pm2 restart laravel-queue
    ```

### If WebSocket Connection Fails

1. Browser DevTools → Console → Look for Echo errors
2. Check broadcasting auth: `/admin/broadcasting/auth` should return 200
3. Verify `.env` has correct Pusher credentials:
    ```
    BROADCAST_DRIVER=pusher
    PUSHER_APP_KEY=d7dc0aff78f8a09bee0b
    PUSHER_APP_CLUSTER=ap2
    ```

### If Queue Worker Won't Start

1. Check Node.js installed: `node --version`
2. Check queue-worker.cjs exists
3. Try manual run: `php artisan queue:work --queue=default`
4. Check database connection and queue_jobs table exists

---

## 📝 Configuration Quick Check

### Backend Configuration (.env)

```
BROADCAST_DRIVER=pusher
QUEUE_CONNECTION=database
PUSHER_APP_ID=2072446
PUSHER_APP_KEY=d7dc0aff78f8a09bee0b
PUSHER_APP_SECRET=1787c5a6f7d919d0e4fd
PUSHER_APP_CLUSTER=ap2
```

### Frontend Configuration (.env → Vite)

```
VITE_PUSHER_APP_KEY=d7dc0aff78f8a09bee0b
VITE_PUSHER_APP_CLUSTER=ap2
```

---

## 🎯 Test Real-Time Chat (Quick)

1. **Open two browser windows** (or incognito)
2. **Admin 1 window:** Login and open chat
3. **Admin 2 window:** Login and open same chat
4. **Admin 1:** Send message
5. **Admin 2:** Message appears INSTANTLY ✅

If not working → Check commands above

---

## 🔐 Production Checklist Before Deploy

-   [ ] PM2 configured: `pm2 status` shows `online`
-   [ ] Queue tables exist: Database has `jobs` table
-   [ ] No failed jobs: `php artisan queue:failed` is empty
-   [ ] Frontend rebuilt: `npm run build` completed
-   [ ] .env matches production Pusher cluster
-   [ ] PM2 will auto-start: `pm2 startup` && `pm2 save`
-   [ ] Monitor logs: Set up monitoring for `pm2 logs`

---

## 🛠️ Environment Setup Files

-   **Queue Worker:** `queue-worker.cjs` (Node.js wrapper)
-   **Fallback Scripts:** `queue-worker.bat` / `queue-worker.ps1`
-   **Full Guide:** `CHAT_REALTIME_FIX_SUMMARY.md`
-   **Verification:** `CHAT_REALTIME_VERIFICATION.md`

---

## 📞 Emergency Revert (If Needed)

If everything breaks, change back to test mode:

```
BROADCAST_DRIVER=log
pm2 stop laravel-queue
```

(Messages will still save but won't appear in real-time)

---

**Last Updated:** 2025-12-10  
**Status:** ✅ Production Ready  
**Tested:** Yes - Queue worker running, jobs processing
