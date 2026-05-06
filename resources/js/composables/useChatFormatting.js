import DOMPurify from "dompurify";

export const avatarInitials = (name) => {
    if (!name) return "?";
    const parts = name.trim().split(/\s+/);
    return ((parts[0]?.[0] || "") + (parts[1]?.[0] || "")).toUpperCase();
};

export const formatDate = (date) => {
    if (!date) return "";
    try {
        const options = {
            hour: "2-digit",
            minute: "2-digit",
            timeZone: "Asia/Kolkata",
            hour12: false,
        };
        return new Date(date).toLocaleString("en-IN", options);
    } catch {
        return "";
    }
};

export const formatMessageBody = (
    message,
    { getMembers = () => [], getOrderReferences = () => [] } = {},
) => {
    if (!message?.body) return "";

    try {
        let content = message.body
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;");

        const emailRegex =
            /[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/g;
        const emails = [];
        content = content.replace(emailRegex, (match) => {
            emails.push(match);
            return `__EMAIL_PLACEHOLDER_${emails.length - 1}__`;
        });

        const urlRegex =
            /\b(?:https?:\/\/|www\.)[^\s<>()"']+|\b[a-z0-9][-a-z0-9]*(?:\.[a-z0-9][-a-z0-9]*)*\.[a-z]{2,}\b(?:\/[^\s<>()"']*)?/gi;
        content = content.replace(urlRegex, (url) => {
            const href = /^https?:\/\//i.test(url) ? url : `https://${url}`;
            return `<a href="${href}" target="_blank" rel="noopener noreferrer" class="message-link">${url}</a>`;
        });

        emails.forEach((email, index) => {
            content = content.replace(`__EMAIL_PLACEHOLDER_${index}__`, email);
        });

        try {
            const members = getMembers();
            if (members.length > 0) {
                const memberNames = members
                    .map((m) => m.name)
                    .filter(Boolean)
                    .sort((a, b) => b.length - a.length);

                if (memberNames.length) {
                    const escapeRegExp = (string) =>
                        string.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
                    const pattern = new RegExp(
                        `(@)(${memberNames.map(escapeRegExp).join("|")})\\b`,
                        "g",
                    );

                    content = content.replace(
                        pattern,
                        (match, prefix, name) =>
                            `<span class="mention-token">${prefix}${name}</span>`,
                    );
                }
            }
        } catch (_) {
            // Mention formatting is best-effort.
        }

        try {
            const orderRefs = getOrderReferences(message);
            if (orderRefs.length > 0) {
                const orderRefMap = new Map(
                    orderRefs.map((ref) => [
                        String(ref.display_number ?? ref.id),
                        ref,
                    ]),
                );
                const orderPattern = /(^|[^\w])#(\d+)\b/g;
                content = content.replace(
                    orderPattern,
                    (match, prefix, orderNumber) => {
                        const ref = orderRefMap.get(String(orderNumber));
                        if (!ref) return match;

                        if (!ref.order_url) {
                            return `${prefix}<span class="order-reference-token order-reference-token--missing">#${orderNumber}</span>`;
                        }

                        return `${prefix}<a href="${ref.order_url}" class="order-reference-token" target="_self" rel="noopener noreferrer">#${orderNumber}</a>`;
                    },
                );
            }
        } catch (_) {
            // Order reference formatting is best-effort.
        }

        return DOMPurify.sanitize(content, {
            ALLOWED_TAGS: ["span", "a"],
            ALLOWED_ATTR: ["class", "href", "target", "rel"],
        });
    } catch (error) {
        console.error("Message formatting failed", error);
        return message.body || "";
    }
};
