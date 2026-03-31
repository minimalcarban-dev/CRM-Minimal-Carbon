/**
 * useChatEditor — Reusable Chat Input Composable
 *
 * Architecture:
 *  - Each call creates a fully isolated reactive state
 *  - Prevents any state sharing between main chat and thread
 *  - Implements Algolia-style autocomplete pattern for mentions & order suggestions
 *  - Debounce-pattern for API-driven order suggestions
 *  - Keyboard navigation: ArrowUp, ArrowDown, Enter, Tab, Escape
 *
 * Usage:
 *   const mainEditor = useChatEditor({ type: 'main', getMembers, onSend, textareaRef });
 *   const threadEditor = useChatEditor({ type: 'thread', getMembers, onSend, textareaRef });
 *
 * @param {Object}   options
 * @param {'main'|'thread'} options.type         - Identifies editor context (for future extensibility)
 * @param {Function} options.getMembers          - () => Member[]  — returns current channel members
 * @param {Function} options.onSend              - Callback to trigger send action on plain Enter
 * @param {Function} [options.onTyping]          - Optional typing indicator callback
 * @param {Ref}      options.textareaRef         - Ref to the <textarea> element
 * @param {Function} [options.fetchOrderSuggestions] - async (q) => Order[] — fetches order suggestions
 */

import { ref, nextTick } from 'vue';

export function useChatEditor({
    type = 'main',
    getMembers = () => [],
    onSend = () => {},
    onTyping = null,
    textareaRef,
    fetchOrderSuggestions = null,
}) {
    // ─────────────────────────────────────────────
    // Core Input State
    // ─────────────────────────────────────────────
    const input = ref('');

    // ─────────────────────────────────────────────
    // Mention State (Algolia-style autocomplete)
    // ─────────────────────────────────────────────
    const mentionOpen = ref(false);
    const mentionQuery = ref('');
    const mentionItems = ref([]);
    const mentionIndex = ref(0);
    const pendingMentionIds = ref(new Set());

    // ─────────────────────────────────────────────
    // Emoji Picker State
    // ─────────────────────────────────────────────
    const emojiPickerOpen = ref(false);

    // ─────────────────────────────────────────────
    // Order Suggestion State (# trigger)
    // ─────────────────────────────────────────────
    const orderSuggestOpen = ref(false);
    const orderSuggestQuery = ref('');
    const orderSuggestItems = ref([]);
    const orderSuggestIndex = ref(0);

    // ─────────────────────────────────────────────
    // Internal helpers
    // ─────────────────────────────────────────────
    const _avatarInitials = (name) => {
        if (!name) return '?';
        const parts = name.trim().split(/\s+/);
        return ((parts[0]?.[0] || '') + (parts[1]?.[0] || '')).toUpperCase();
    };

    /**
     * Auto-resize textarea height to fit content (WhatsApp style).
     * Max 8 lines.
     */
    const autoResize = () => {
        const el = textareaRef?.value;
        if (!el) return;
        el.style.height = 'auto';
        const lineHeight = 22;
        const maxHeight = lineHeight * 8;
        el.style.height = Math.min(el.scrollHeight, maxHeight) + 'px';
    };

    /**
     * Update mention suggestion list by filtering channel members.
     * Implements Algolia-style: filter + limit + debounce-free (synchronous).
     */
    const updateMentionList = () => {
        const q = mentionQuery.value.trim().toLowerCase();
        const members = getMembers();
        const items = members
            .filter(
                (m) =>
                    m &&
                    m.id &&
                    (m.name + ' ' + (m.email || '')).toLowerCase().includes(q),
            )
            .slice(0, 8); // Limit - performance optimization

        mentionItems.value = items;
        mentionIndex.value = 0;
        mentionOpen.value = items.length > 0;
    };

    /**
     * Fetch order suggestions from backend.
     * Gracefully degrades if fetchOrderSuggestions is not provided.
     */
    const updateOrderSuggest = async () => {
        if (!fetchOrderSuggestions) {
            orderSuggestOpen.value = false;
            return;
        }
        try {
            const orders = await fetchOrderSuggestions(orderSuggestQuery.value.trim());
            orderSuggestItems.value = orders || [];
            orderSuggestIndex.value = 0;
            orderSuggestOpen.value = (orders || []).length > 0;
        } catch (_) {
            orderSuggestItems.value = [];
            orderSuggestOpen.value = false;
        }
    };

    // ─────────────────────────────────────────────
    // Event Handler: Input
    // ─────────────────────────────────────────────
    const onInput = (e) => {
        if (onTyping) onTyping();
        autoResize();

        const val = input.value;
        const caret = e.target.selectionStart;
        const before = val.slice(0, caret);

        // Detect @mention trigger
        const mentionMatch = before.match(/(^|\s)@([\w.\-]*)$/);
        // Detect #order trigger
        const orderMatch = before.match(/(^|\s)#(\w*)$/);

        if (mentionMatch) {
            mentionQuery.value = mentionMatch[2] || '';
            updateMentionList();
            orderSuggestOpen.value = false;
        } else {
            mentionOpen.value = false;
        }

        if (orderMatch) {
            orderSuggestQuery.value = orderMatch[2] || '';
            updateOrderSuggest();
            // Don't close mention here — mutually exclusive
        } else {
            orderSuggestOpen.value = false;
        }
    };

    // ─────────────────────────────────────────────
    // Event Handler: KeyDown (keyboard navigation)
    // ─────────────────────────────────────────────
    const onKeyDown = (e) => {
        const NAV_KEYS = ['ArrowDown', 'ArrowUp', 'Enter', 'Tab', 'Escape'];

        // ── Mention popover navigation ──
        if (mentionOpen.value) {
            if (NAV_KEYS.includes(e.key)) {
                if (mentionItems.value.length === 0) {
                    mentionOpen.value = false;
                    return;
                }
                e.preventDefault();
            }
            if (e.key === 'ArrowDown') {
                mentionIndex.value = (mentionIndex.value + 1) % mentionItems.value.length;
            } else if (e.key === 'ArrowUp') {
                mentionIndex.value =
                    (mentionIndex.value - 1 + mentionItems.value.length) %
                    mentionItems.value.length;
            } else if (e.key === 'Enter' || e.key === 'Tab') {
                pickMention(mentionItems.value[mentionIndex.value]);
            } else if (e.key === 'Escape') {
                mentionOpen.value = false;
            }
            return;
        }

        // ── Order suggest popover navigation ──
        if (orderSuggestOpen.value) {
            if (NAV_KEYS.includes(e.key)) e.preventDefault();
            if (orderSuggestItems.value.length === 0) {
                orderSuggestOpen.value = false;
                return;
            }
            if (e.key === 'ArrowDown') {
                orderSuggestIndex.value =
                    (orderSuggestIndex.value + 1) % orderSuggestItems.value.length;
                return;
            }
            if (e.key === 'ArrowUp') {
                orderSuggestIndex.value =
                    (orderSuggestIndex.value - 1 + orderSuggestItems.value.length) %
                    orderSuggestItems.value.length;
                return;
            }
            if (e.key === 'Enter' || e.key === 'Tab') {
                pickOrderSuggest(orderSuggestItems.value[orderSuggestIndex.value]);
                return;
            }
            if (e.key === 'Escape') {
                orderSuggestOpen.value = false;
                return;
            }
        }

        // ── Plain Enter → send; Shift+Enter → newline ──
        if (!mentionOpen.value && !orderSuggestOpen.value && e.key === 'Enter') {
            if (e.shiftKey) return; // allow newline
            e.preventDefault();
            onSend();
        }
    };

    // ─────────────────────────────────────────────
    // Mention: Insert selected user
    // ─────────────────────────────────────────────
    const pickMention = (m) => {
        if (!m) return;
        const el = textareaRef?.value;
        const val = input.value;
        const caret = el?.selectionStart ?? val.length;
        const before = val.slice(0, caret);
        const after = val.slice(caret);

        const startMatch = before.match(/(^|\s)@([\w.\-]*)$/);
        if (!startMatch) return;

        const prefix = startMatch[1] || '';
        const insert = `${prefix}@${m.name} `;
        input.value = before.replace(/(^|\s)@([\w.\- ]*)$/, insert) + after;

        // Restore cursor after inserted mention — preserves position precision
        nextTick(() => {
            try {
                const pos = (before.replace(/(^|\s)@([\w.\- ]*)$/, '') + insert).length;
                el?.focus();
                el?.setSelectionRange(pos, pos);
            } catch (_) {}
        });

        pendingMentionIds.value.add(m.id);
        mentionOpen.value = false;
    };

    // ─────────────────────────────────────────────
    // Order Suggest: Insert selected order ref
    // ─────────────────────────────────────────────
    const pickOrderSuggest = (order) => {
        if (!order?.id) return;
        const el = textareaRef?.value;
        const val = input.value;
        const caret = el?.selectionStart ?? val.length;
        const before = val.slice(0, caret);
        const after = val.slice(caret);

        const orderMatch = before.match(/(^|\s)#(\w*)$/);
        if (!orderMatch) return;

        const prefix = orderMatch[1] || '';
        const insert = `${prefix}#${order.id} `;
        input.value = before.replace(/(^|\s)#(\w*)$/, insert) + after;

        orderSuggestOpen.value = false;
        orderSuggestItems.value = [];
        orderSuggestQuery.value = '';

        nextTick(() => {
            try {
                const pos = (before.replace(/(^|\s)#(\w*)$/, '') + insert).length;
                el?.focus();
                el?.setSelectionRange(pos, pos);
            } catch (_) {}
        });
    };

    // ─────────────────────────────────────────────
    // Emoji: Append emoji at cursor end
    // ─────────────────────────────────────────────
    const appendEmoji = (emoji) => {
        const selectedEmoji = typeof emoji === 'string' ? emoji : emoji?.native;
        if (!selectedEmoji) return;
        input.value += selectedEmoji;
        emojiPickerOpen.value = false;
        nextTick(() => textareaRef?.value?.focus());
    };

    // ─────────────────────────────────────────────
    // Reset: Clear editor state after send
    // ─────────────────────────────────────────────
    const reset = () => {
        input.value = '';
        mentionOpen.value = false;
        mentionQuery.value = '';
        mentionItems.value = [];
        mentionIndex.value = 0;
        pendingMentionIds.value = new Set();
        emojiPickerOpen.value = false;
        orderSuggestOpen.value = false;
        orderSuggestQuery.value = '';
        orderSuggestItems.value = [];
        orderSuggestIndex.value = 0;
        // Reset textarea height
        nextTick(() => {
            const el = textareaRef?.value;
            if (el) el.style.height = 'auto';
        });
    };

    // ─────────────────────────────────────────────
    // Close all dropdowns (click-outside handler)
    // ─────────────────────────────────────────────
    const closeDropdowns = () => {
        mentionOpen.value = false;
        orderSuggestOpen.value = false;
        emojiPickerOpen.value = false;
    };

    return {
        // State
        input,
        mentionOpen,
        mentionItems,
        mentionIndex,
        pendingMentionIds,
        emojiPickerOpen,
        orderSuggestOpen,
        orderSuggestItems,
        orderSuggestIndex,
        // Handlers
        onInput,
        onKeyDown,
        pickMention,
        pickOrderSuggest,
        appendEmoji,
        reset,
        closeDropdowns,
        autoResize,
        // Utilities (exposed for avatar rendering in popover)
        avatarInitials: _avatarInitials,
    };
}
