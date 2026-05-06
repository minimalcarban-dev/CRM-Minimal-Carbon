export const useChatWebsockets = () => {
    const globalListenerChannelIds = new Set();
    const activeListenerChannelIds = new Set();
    const typingTimeouts = new Map();
    const sidebarTypingTimeouts = new Map();

    const clearTypingTimeout = (key) => {
        const timeout = typingTimeouts.get(key);
        if (timeout) {
            clearTimeout(timeout);
            typingTimeouts.delete(key);
        }
    };

    const clearSidebarTypingTimeout = (key) => {
        const timeout = sidebarTypingTimeouts.get(key);
        if (timeout) {
            clearTimeout(timeout);
            sidebarTypingTimeouts.delete(key);
        }
    };

    const clearAllTypingTimeouts = () => {
        typingTimeouts.forEach((timeout) => clearTimeout(timeout));
        sidebarTypingTimeouts.forEach((timeout) => clearTimeout(timeout));
        typingTimeouts.clear();
        sidebarTypingTimeouts.clear();
    };

    return {
        globalListenerChannelIds,
        activeListenerChannelIds,
        typingTimeouts,
        sidebarTypingTimeouts,
        clearTypingTimeout,
        clearSidebarTypingTimeout,
        clearAllTypingTimeouts,
    };
};
