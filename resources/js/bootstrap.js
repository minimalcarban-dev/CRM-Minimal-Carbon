import axios from 'axios';
import Pusher from 'pusher-js';
import Echo from 'laravel-echo';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;

const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    if (!import.meta.env.PROD) console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
    window.showAlert?.('CSRF token not found. Some requests may fail', 'warning');
}

// Initialize Laravel Echo
window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
    wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/admin/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': token ? token.content : '',
            Accept: 'application/json'
        }
    }
});

// --- Global 401/403 handling and graceful UX ---
const safeRedirect = (to = '/admin/dashboard') => {
    try { window.location.assign(to); } catch (_) { window.location.href = to; }
};

const ensurePermissionModal = (message, redirectTo = '/admin/dashboard', seconds = 5) => {
    // If already shown, ignore
    if (document.getElementById('perm-revoked-modal')) return;
    const overlay = document.createElement('div');
    overlay.id = 'perm-revoked-modal';
    overlay.style.cssText = `position:fixed;inset:0;background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;z-index:9999;`;
    const card = document.createElement('div');
    card.style.cssText = `background:#fff;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,.2);padding:20px;max-width:420px;width:92%;text-align:center;font-family:system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;`;
    card.innerHTML = `
      <div style="font-weight:700;font-size:18px;margin-bottom:8px">Access updated</div>
      <div style="color:#374151;margin-bottom:16px">${message || 'Your permissions have changed and you no longer have access to this page.'}</div>
      <div id="perm-revoked-timer" style="color:#6b7280;font-size:12px;margin-bottom:16px">You'll be redirected to the dashboard in ${seconds}s.</div>
      <button id="perm-revoked-btn" style="padding:8px 14px;border-radius:8px;border:1px solid #2563eb;background:#2563eb;color:#fff;cursor:pointer">Go to Dashboard</button>
    `;
    overlay.appendChild(card);
    document.body.appendChild(overlay);
    document.getElementById('perm-revoked-btn').addEventListener('click', () => safeRedirect());
    // Countdown + auto-redirect
    let remaining = seconds;
    const timerEl = document.getElementById('perm-revoked-timer');
    const iv = setInterval(() => {
        remaining -= 1;
        if (timerEl) {
            timerEl.textContent = `You'll be redirected to the dashboard in ${remaining}s.`;
        }
        if (remaining <= 0) {
            clearInterval(iv);
            safeRedirect(redirectTo);
        }
    }, 1000);
};

window.axios.interceptors.response.use(
    (response) => response,
    (error) => {
        const status = error?.response?.status;
        if (status === 401) {
            safeRedirect('/admin/login');
        } else if (status === 403) {
            const headers = error?.response?.headers || {};
            // Only show modal when server explicitly signals a global redirect
            const hinted = headers['x-redirect'];
            const hintedBody = error?.response?.data?.redirect;
            if (hinted || hintedBody) {
                const to = hinted || hintedBody;
                ensurePermissionModal(undefined, to, 5);
            }
        }
        // Always propagate the error so feature code can handle it locally
        return Promise.reject(error);
    }
);

// Pusher/Echo: catch subscription/auth errors and handle like 403
try {
    const connector = window.Echo && window.Echo.connector;
    if (connector && connector.pusher) {
        connector.pusher.connection.bind('error', (evt) => {
            const status = evt?.error?.data?.code || evt?.error?.status || evt?.status;
            if (status === 401 || status === 403) {
                ensurePermissionModal(undefined, '/admin/dashboard', 5);
            }
        });
    }
} catch (_) { /* noop */ }
