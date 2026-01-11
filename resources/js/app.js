import "./bootstrap";
import { createApp } from "vue";
import Chat from "./components/Chat.vue";
import axios from "axios";

console.log("Vue app initializing...");

// Make axios available in components
const app = createApp({});
app.config.globalProperties.$axios = axios;

console.log("Chat component:", Chat); // Debug log

// Register components
app.component("chat", Chat);

// Mount the app when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById("app")) {
        app.mount("#app");
    }

    // Make showToast available globally (defined in admin.blade.php but ensure it's accessible)
    if (typeof showToast !== "undefined") {
        window.showToast = showToast;
    }

    // --- NEW GLOBAL NOTIFICATION LOGIC ---
    // Ensure Echo is available
    if (typeof window.Echo === "undefined") {
        console.warn(
            "Echo is not defined. Real-time notifications will not work outside of dedicated components."
        );
        return;
    }

    const userId = window.authAdminId;
    if (!userId) {
        console.log(
            "User is not authenticated. Skipping global notification setup."
        );
        return;
    }

    // A helper function to show a desktop notification
    const showDesktopNotification = (title, options = {}) => {
        if (!("Notification" in window)) {
            console.warn(
                "This browser does not support desktop notifications."
            );
            return;
        }

        const show = () => {
            const notification = new Notification(title, {
                icon: "/images/Luxurious-Logo.png", // Ensure this path is correct
                ...options,
            });
            // Optional: navigate to a URL on click
            notification.onclick = () => {
                if (options.url) {
                    window.location.href = options.url;
                }
            };
        };

        if (Notification.permission === "granted") {
            show();
        } else if (Notification.permission !== "denied") {
            Notification.requestPermission().then((permission) => {
                if (permission === "granted") {
                    show();
                }
            });
        }
    };

    // --- NOTIFICATION CHANNEL LISTENER (Updates Dropdown & Badge) ---
    try {
        const notificationChannel = window.Echo.private(
            `admin.notifications.${userId}`
        );
        console.log(
            "[DEBUG] Subscribing to notification channel:",
            `admin.notifications.${userId}`
        );

        notificationChannel.listen(
            ".Illuminate\\Notifications\\Events\\BroadcastNotificationCreated",
            (e) => {
                console.log("[DEBUG] Notification received:", e);
                console.log("[DEBUG] Notification type:", e.type);
                console.log("[DEBUG] Notification message:", e.message);

                // 1. Update Badge
                const badge = document.querySelector(".notification-badge");
                if (badge) {
                    let count = parseInt(badge.innerText) || 0;
                    badge.innerText = count + 1;
                    console.log("[DEBUG] Badge updated to:", count + 1);
                } else {
                    const btn = document.getElementById("notificationBtn");
                    if (btn) {
                        const newBadge = document.createElement("span");
                        newBadge.className = "notification-badge";
                        newBadge.innerText = "1";
                        btn.appendChild(newBadge);
                        console.log("[DEBUG] Badge created with count: 1");
                    }
                }

                // 2. Add to Dropdown List
                const list = document.querySelector(".notification-list");
                const emptyMsg = document.querySelector(".notification-empty");

                if (list) {
                    if (emptyMsg) emptyMsg.remove();

                    const item = document.createElement("div");
                    item.className = "notification-item unread";
                    item.setAttribute("data-notification-id", e.id);
                    item.setAttribute("data-url", e.url || "#");
                    item.style.cursor = "pointer"; // Ensure it looks clickable

                    // Determine Icon
                    let iconClass = "bi-info-circle";
                    if (
                        e.type === "App\\Notifications\\ChatMentionNotification"
                    )
                        iconClass = "bi-at";
                    else if (
                        e.type ===
                        "App\\Notifications\\ChannelAddedNotification"
                    )
                        iconClass = "bi-people-fill";
                    else if (
                        e.type ===
                        "App\\Notifications\\DiamondAssignedNotification"
                    )
                        iconClass = "bi-gem";
                    else if (e.type === "App\\Notifications\\ImportCompleted")
                        iconClass = "bi-cloud-upload";
                    else if (e.type === "App\\Notifications\\ExportCompleted")
                        iconClass = "bi-cloud-download";
                    else if (
                        e.type === "App\\Notifications\\DiamondSoldNotification"
                    )
                        iconClass = "bi-gem";

                    item.innerHTML = `
                    <div class="notification-icon">
                        <i class="bi ${iconClass}"></i>
                    </div>
                    <div class="notification-content">
                        <p class="notification-message">
                            ${e.title ? `<strong>${e.title}</strong><br>` : ""}
                            ${e.message || "New notification"}
                        </p>
                        ${
                            e.message_preview
                                ? `<p class="notification-preview" style="font-size: 0.85rem; color: #64748b; margin-top: 0.25rem; font-style: italic;">${e.message_preview}</p>`
                                : ""
                        }
                        <span class="notification-time">Just now</span>
                    </div>
                `;

                    // Add Click Handler (Reuse logic from admin.blade.php)
                    item.addEventListener("click", function (evt) {
                        const url = this.getAttribute("data-url");
                        const notificationId = this.getAttribute(
                            "data-notification-id"
                        );
                        if (url && url !== "#") {
                            // Optimistic UI: mark read
                            fetch(
                                `/admin/notifications/${notificationId}/read`,
                                {
                                    method: "POST",
                                    headers: {
                                        "X-CSRF-TOKEN": document.querySelector(
                                            'meta[name="csrf-token"]'
                                        )?.content,
                                        "Content-Type": "application/json",
                                    },
                                }
                            ).catch(console.error);
                            window.location.href = url;
                        }
                    });

                    // Insert at top
                    list.insertBefore(item, list.firstChild);
                    console.log("[DEBUG] Notification added to dropdown list");
                }

                // 3. Show Toast/Notification for ALL notifications (including mentions)
                // We prioritize this specific listener for Mentions because it has better text ("You were mentioned...")
                console.log("[DEBUG] Showing toast and desktop notification");
                if (typeof window.showToast === "function") {
                    window.showToast(e.message || "New Notification");
                } else {
                    console.warn("[DEBUG] window.showToast is not available");
                }

                showDesktopNotification(e.title || "New Notification", {
                    body: e.message,
                });

                if (typeof window.playNotificationSound === "function") {
                    window.playNotificationSound();
                    console.log("[DEBUG] Notification sound played");
                } else {
                    console.warn(
                        "[DEBUG] window.playNotificationSound is not available"
                    );
                }
            }
        );
        console.log(
            `[OK] Subscribed to notification channel: admin.notifications.${userId}`
        );
    } catch (err) {
        console.error("Error subscribing to notification channel:", err);
    }

    // --- GLOBAL CHAT CHANNEL LISTENER (For general messages when away from chat) ---
    const subscribeToChatChannels = async () => {
        // If on chat page, let Chat.vue handle notifications to avoid conflict/duplication
        // Check both URL and if the Chat component is mounted
        if (
            window.location.pathname.includes("/admin/chat") ||
            document.querySelector(".chat-container")
        ) {
            console.log(
                "[DEBUG] On chat page, skipping global chat listeners (Chat.vue will handle)"
            );
            return;
        }

        try {
            const response = await window.axios.get("/admin/chat/channels");
            const channels = response.data || [];

            console.log(
                "[DEBUG] Setting up global chat listeners for",
                channels.length,
                "channels"
            );

            channels.forEach((channel) => {
                window.Echo.private(`chat.channel.${channel.id}`).listen(
                    "MessageSent",
                    (e) => {
                        if (!e || !e.message) return;
                        if (e.message.sender_id === userId) return;

                        // PREVENT DUPLICATES:
                        // If I am mentioned, the `admin.notifications.{id}` listener (above) will handle it
                        // with a better message ("You were mentioned..."). So we SKIP here.
                        if (
                            window.authAdminName &&
                            e.message.body &&
                            e.message.body.includes("@" + window.authAdminName)
                        ) {
                            return; // Skip generic notification
                        }

                        // Update Chat Unread Badge
                        const chatBadge =
                            document.getElementById("chatUnreadBadge");
                        if (chatBadge) {
                            let currentCount =
                                parseInt(chatBadge.innerText) || 0;
                            currentCount++;
                            chatBadge.innerText = currentCount;
                            chatBadge.classList.remove("hidden");
                            console.log(
                                "[DEBUG] Chat badge updated to:",
                                currentCount
                            );
                        }

                        // Show in-app toast
                        if (typeof window.showToast === "function") {
                            window.showToast(
                                `New message from ${
                                    e.message.sender?.name || "someone"
                                }`
                            );
                        }

                        // Show desktop notification
                        showDesktopNotification(
                            e.message.sender?.name || "New Message",
                            {
                                body: e.message.body,
                                tag: `chat-${channel.id}-${e.message.id}`,
                                url: `/admin/chat?channel=${channel.id}`, // Link to specific channel
                            }
                        );

                        // Play sound
                        if (
                            typeof window.playNotificationSound === "function"
                        ) {
                            window.playNotificationSound();
                        }
                    }
                );
            });
            console.log(
                `[OK] Subscribed to ${channels.length} chat channels for background notifications.`
            );

            // Fetch and display initial unread count
            fetchChatUnreadCount();
        } catch (error) {
            console.error("Failed to subscribe to chat channels:", error);
        }
    };

    // Function to fetch chat unread count
    const fetchChatUnreadCount = async () => {
        try {
            const response = await window.axios.get("/admin/chat/unread-count");
            const count = response.data.unread_count || 0;

            const chatBadge = document.getElementById("chatUnreadBadge");
            if (chatBadge && count > 0) {
                chatBadge.innerText = count;
                chatBadge.classList.remove("hidden");
                console.log("[DEBUG] Initial chat unread count:", count);
            }
        } catch (error) {
            console.error("Failed to fetch chat unread count:", error);
        }
    };

    subscribeToChatChannels();
});

// Helper for sound
window.playNotificationSound = () => {
    try {
        const audioContext = new (window.AudioContext ||
            window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gain = audioContext.createGain();

        oscillator.connect(gain);
        gain.connect(audioContext.destination);

        oscillator.frequency.value = 800;
        oscillator.type = "sine";

        gain.gain.setValueAtTime(0.3, audioContext.currentTime);
        gain.gain.exponentialRampToValueAtTime(
            0.01,
            audioContext.currentTime + 0.3
        );

        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.3);
    } catch (_) {
        // Fallback
        try {
            const beep = new Audio(
                "data:audio/wav;base64,UklGRiYAAABXQVZFZm10IBAAAAABAAEAQB8AAAA="
            );
            beep.play().catch(() => {});
        } catch (_) {}
    }
};
