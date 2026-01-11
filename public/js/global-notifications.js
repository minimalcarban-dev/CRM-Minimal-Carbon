// Global Notification System - Works on all pages
(function () {
    console.log("Global notifications script loaded");
    
    // Request notification permission on page load
    if ("Notification" in window && Notification.permission === "default") {
        Notification.requestPermission();
        console.log("Requesting notification permission");
    }

    // Play notification sound
    window.playNotificationSound = function () {
        console.log("Playing notification sound");
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gain = audioContext.createGain();

            oscillator.connect(gain);
            gain.connect(audioContext.destination);

            oscillator.frequency.value = 800; // 800 Hz tone
            oscillator.type = "sine";

            gain.gain.setValueAtTime(0.3, audioContext.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.3);
        } catch (e) {
            console.error("Error playing sound:", e);
        }
    };

    // Show desktop notification
    window.showDesktopNotification = function (title, options = {}) {
        console.log("Showing desktop notification:", title, options);
        try {
            if ("Notification" in window) {
                if (Notification.permission === "granted") {
                    new Notification(title, {
                        icon: "/images/app-logo.png",
                        tag: options.tag || "notification",
                        ...options,
                    });
                    window.playNotificationSound();
                } else if (Notification.permission === "default") {
                    Notification.requestPermission().then((perm) => {
                        if (perm === "granted") {
                            new Notification(title, {
                                icon: "/images/app-logo.png",
                                tag: options.tag || "notification",
                                ...options,
                            });
                            window.playNotificationSound();
                        }
                    });
                } else {
                    console.warn("Notifications not permitted");
                }
            } else {
                console.warn("Notifications not supported");
            }
        } catch (e) {
            console.error("Error showing notification:", e);
        }
    };

    // Test notification function (for debugging)
    window.testNotification = function (title = "Test Notification", body = "This is a test") {
        console.log("Testing notification");
        if (typeof showToast === "function") {
            showToast(`${title}: ${body}`);
        }
        window.showDesktopNotification(title, { body });
    };

    // Wait for Echo to be ready
    const setupEchoListeners = () => {
        console.log("Setting up Echo listeners");
        console.log("Echo available:", typeof Echo !== "undefined");
        console.log("Auth admin ID:", window.authAdminId);
        
        if (typeof Echo === "undefined") {
            console.warn("Echo not available yet, retrying...");
            setTimeout(setupEchoListeners, 2000);
            return;
        }

        if (!window.authAdminId) {
            console.warn("authAdminId not set");
            return;
        }

        try {
            const userId = window.authAdminId;
            const channelName = `admin.notifications.${userId}`;
            console.log("Subscribing to channel:", channelName);
            
            const notificationChannel = Echo.private(channelName);

            // Listen for mentions on ALL pages
            notificationChannel.listen("UserMentioned", (e) => {
                console.log("UserMentioned event received:", e);
                try {
                    const sender = e?.message?.sender?.name || "Someone";
                    const channelName = e?.message?.channel?.name || "";
                    const msg = channelName
                        ? `${sender} mentioned you in ${channelName}`
                        : `${sender} mentioned you`;

                    console.log("Showing mention notification:", msg);

                    // Show toast on current page (primary notification)
                    if (typeof showToast === "function") {
                        showToast(msg);
                        window.playNotificationSound();
                    } else {
                        // Fallback: show desktop notification if toast not available
                        window.showDesktopNotification("You were mentioned", {
                            body: msg,
                            tag: `mention-${e?.message?.id || 'notification'}`,
                        });
                    }
                } catch (err) {
                    console.error("Error handling UserMentioned:", err);
                }
            });

            // Listen for direct message notifications
            notificationChannel.listen("DirectMessageReceived", (e) => {
                console.log("DirectMessageReceived event received:", e);
                try {
                    const sender = e?.message?.sender?.name || "Someone";
                    const msg = `New message from ${sender}`;

                    console.log("Showing message notification:", msg);

                    // Show toast
                    if (typeof showToast === "function") {
                        showToast(msg);
                    }

                    // Show desktop notification
                    window.showDesktopNotification("New Message", {
                        body: msg,
                        tag: "message-notification",
                    });
                } catch (err) {
                    console.error("Error handling DirectMessageReceived:", err);
                }
            });

            // Listen for channel membership changes
            notificationChannel.listen("ChannelMembershipChanged", (e) => {
                console.log("ChannelMembershipChanged event received:", e);
                if (!e || !e.channelId || !e.action) return;

                if (e.action === "removed") {
                    const msg = "You were removed from a channel";
                    if (typeof showToast === "function") {
                        showToast(msg);
                    }
                } else if (e.action === "added") {
                    const msg = "You were added to a new channel";
                    if (typeof showToast === "function") {
                        showToast(msg);
                    }
                }
            });

            console.log("Echo listeners setup complete");
        } catch (e) {
            console.error("Error setting up Echo listeners:", e);
        }
    };

    // Setup listeners when document is ready
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", setupEchoListeners);
    } else {
        setupEchoListeners();
    }
})();
