<template>
    <!-- ChatInput — Single source of truth for all chat input areas -->
    <div class="chat-input-root" :class="containerClass">
        <!-- Attachment Preview Strip -->
        <div v-if="files.length" class="attachments-preview">
            <div
                v-for="(file, index) in files"
                :key="index"
                class="attachment-preview-item"
            >
                <i class="bi bi-paperclip"></i>
                <span>{{ file.name }}</span>
                <button @click="$emit('remove-file', index)" class="remove-attachment">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>

        <!-- Unified Input Box — reply-bar lives INSIDE so both share the rounded border -->
        <div class="input-box-wrapper" :class="{ 'has-reply': !!replyTo }">
            <!-- Reply Preview (top portion of the box, only when replying) -->
            <div v-if="replyTo" class="reply-bar">
                <div class="reply-bar-icon">
                    <i class="bi bi-arrow-return-right"></i>
                </div>
                <div class="reply-bar-content">
                    <div class="reply-bar-title">
                        Replying to {{ replyTo.sender?.name || 'message' }}
                    </div>
                    <div class="reply-bar-preview">
                        "{{ replyTo.body?.slice(0, 80) || 'Attachment' }}"
                    </div>
                </div>
                <button class="reply-bar-cancel" @click="$emit('cancel-reply')" title="Cancel reply">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <div class="input-row">
                <!-- Hidden file input — self-contained ref -->
                <input
                    ref="fileInputEl"
                    type="file"
                    @change="$emit('attach-files', $event)"
                    multiple
                    style="display: none"
                />

                <!-- Attach Button -->
                <button @click="fileInputEl.click()" class="btn-attach" title="Attach files">
                    <i class="bi bi-paperclip"></i>
                </button>

                <!-- Emoji Button -->
                <button @click.stop="$emit('toggle-emoji')" class="btn-attach" title="Emoji">
                    <i class="bi bi-emoji-smile"></i>
                </button>

                <!-- Textarea + Popovers Wrapper -->
                <div class="input-with-suggestions" @click.stop>
                    <!-- Emoji Picker slot -->
                    <div v-if="emojiPickerOpen" class="emoji-mart-wrapper" @click.stop>
                        <slot name="emoji-picker" />
                    </div>

                    <!-- Textarea -->
                    <textarea
                        ref="textareaEl"
                        :value="modelValue"
                        class="message-textarea"
                        :placeholder="placeholder"
                        style="width: 100%"
                        rows="1"
                        @input="$emit('update:modelValue', $event.target.value); $emit('editor-input', $event)"
                        @keydown="$emit('editor-keydown', $event)"
                        @paste="$emit('editor-paste', $event)"
                    ></textarea>

                    <!-- Mention Popover -->
                    <div v-if="mentionOpen && mentionItems.length" class="mention-popover">
                        <div
                            v-for="(m, i) in mentionItems"
                            :key="m.id"
                            :class="['mention-item', { active: i === mentionIndex }]"
                            @mousedown.prevent="$emit('pick-mention', m)"
                        >
                            <span class="mention-avatar">{{ avatarInitials(m.name) }}</span>
                            <div class="mention-info">
                                <div class="mention-name">{{ m.name }}</div>
                                <div class="mention-email">{{ m.email }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Suggestion Popover -->
                    <div v-if="orderSuggestOpen && orderSuggestItems.length" class="mention-popover order-suggest-popover">
                        <div
                            v-for="(order, i) in orderSuggestItems"
                            :key="`order-${order.id}`"
                            :class="['mention-item', { active: i === orderSuggestIndex }]"
                            @mousedown.prevent="$emit('pick-order', order)"
                        >
                            <span class="mention-avatar">#</span>
                            <div class="mention-info">
                                <div class="mention-name">#{{ order.id }}</div>
                                <div class="mention-email">
                                    {{ order.client_name || 'Unknown client' }}
                                    · {{ order.status_label || 'Unknown' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Send Button -->
                <button
                    @click="$emit('send')"
                    class="btn-send"
                    :disabled="!canSend || sending"
                    title="Send"
                >
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { ref } from 'vue';

export default {
    name: 'ChatInput',

    props: {
        modelValue: { type: String, default: '' },
        placeholder: { type: String, default: 'Type a message... Use @ to mention' },
        files: { type: Array, default: () => [] },
        replyTo: { type: Object, default: null },
        emojiPickerOpen: { type: Boolean, default: false },
        sending: { type: Boolean, default: false },
        canSend: { type: Boolean, default: false },
        mentionOpen: { type: Boolean, default: false },
        mentionItems: { type: Array, default: () => [] },
        mentionIndex: { type: Number, default: 0 },
        orderSuggestOpen: { type: Boolean, default: false },
        orderSuggestItems: { type: Array, default: () => [] },
        orderSuggestIndex: { type: Number, default: 0 },
        containerClass: { type: String, default: '' },
    },

    emits: [
        'update:modelValue',
        'send',
        'attach-files',
        'remove-file',
        'toggle-emoji',
        'editor-input',
        'editor-keydown',
        'editor-paste',
        'pick-mention',
        'pick-order',
        'cancel-reply',
    ],

    setup() {
        const fileInputEl = ref(null);
        const textareaEl = ref(null);

        const avatarInitials = (name) => {
            if (!name) return '?';
            const parts = name.trim().split(/\s+/);
            return ((parts[0]?.[0] || '') + (parts[1]?.[0] || '')).toUpperCase();
        };

        return { fileInputEl, textareaEl, avatarInitials };
    },
};
</script>

<style scoped>
/*
 * ChatInput — Pixel-perfect match to Chat.vue main chat input styles.
 * Values are kept in sync: border-radius, shadow, padding, btn sizes.
 * Parent containers (.message-input-container / .thread-input-area)
 * only control outer padding & border-top — everything inside is identical.
 */

.chat-input-root {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 0;
}

/* ── Attachment Preview ────────────────────────────────────── */
.attachments-preview {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.attachment-preview-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: #f3f4f6;
    border-radius: 8px;
    font-size: 0.875rem;
    color: #374151;
}

.remove-attachment {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #d1d5db;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    color: white;
    font-size: 1rem;
    line-height: 1;
}

.remove-attachment:hover { background: #ef4444; }

/* ── Reply Bar — top section inside input-box-wrapper ── */
.reply-bar {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.55rem 1rem 0.55rem 0.75rem;
    background: #f0f4ff;
    border-bottom: 1px solid #e0e7ff;
    /* Match the wrapper's top border-radius so it looks clipped */
    border-radius: 18.5px 18.5px 0 0;
}

.reply-bar-icon { color: #6366f1; font-size: 1rem; flex-shrink: 0; }
.reply-bar-content { flex: 1; min-width: 0; }
.reply-bar-title { font-size: 0.78rem; font-weight: 600; color: #6366f1; }
.reply-bar-preview {
    font-size: 0.8rem;
    color: #6b7280;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.reply-bar-cancel {
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    font-size: 1.1rem;
    padding: 0 2px;
    line-height: 1;
    transition: color 0.2s;
    flex-shrink: 0;
}
.reply-bar-cancel:hover { color: #374151; }

/* ── Unified Input Box ───────────────────────────────────── */
.input-box-wrapper {
    display: flex;
    flex-direction: column;
    background: var(--bs-body-bg, #ffffff);
    border: 1.5px solid var(--bs-border-color, #d9deea);
    border-radius: 20px;
    box-shadow:
        0 8px 22px rgba(15, 23, 42, 0.08),
        0 1px 2px rgba(15, 23, 42, 0.08);
    overflow: visible; /* Must stay visible — popovers (emoji/mentions) escape upward */
    transition: border-color 0.2s, box-shadow 0.2s, background-color 0.3s;
}

[data-theme="dark"] .input-box-wrapper {
    background: rgba(0, 0, 0, 0.1);
    border-color: #374151;
    box-shadow: none;
}

.input-box-wrapper:focus-within {
    border-color: #a5b4fc;
    box-shadow:
        0 0 0 4px rgba(99, 102, 241, 0.13),
        0 10px 24px rgba(15, 23, 42, 0.12);
}

[data-theme="dark"] .input-box-wrapper:focus-within {
    border-color: #6366f1;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
}

/* ── Input Row ───────────────────────────────────────────── */
.input-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-height: 50px;
    padding: 0.5rem 0.58rem 0.5rem 0.62rem;
    background: transparent;
    border: none;
    box-shadow: none;
}

/* ── Buttons ─────────────────────────────────────────────── */
.btn-attach {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    background: transparent;
    border: none;
    color: #4b5563;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 1.15rem;
    flex-shrink: 0;
}
.btn-attach:hover { background: #f1f5ff; }

.btn-send {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    border: none;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 1.12rem;
    flex-shrink: 0;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
.btn-send:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}
.btn-send:disabled { opacity: 0.5; cursor: not-allowed; }

/* ── Textarea ────────────────────────────────────────────── */
.input-with-suggestions {
    position: relative;
    flex: 1;
    min-width: 0;
}

.message-textarea {
    width: 100%;
    padding: 0.68rem 0.52rem;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-family: inherit;
    resize: none;
    min-height: 26px;
    max-height: 176px;
    overflow-y: auto;
    line-height: 1.4;
    background: transparent;
    color: #1f2937;
    display: block;
    box-sizing: border-box;
}
.message-textarea:focus { outline: none; }

/* ── Emoji Picker ────────────────────────────────────────── */
.emoji-mart-wrapper {
    position: absolute;
    bottom: calc(100% + 12px);
    left: 0;
    z-index: 1000;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

/* ── Mention / Order Popover ─────────────────────────────── */
.mention-popover {
    position: absolute;
    left: 0;
    bottom: calc(100% + 10px);
    width: 100%;
    max-width: min(420px, calc(100vw - 24px));
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.15);
    max-height: 220px;
    overflow-y: auto;
    z-index: 999;
}

.mention-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.6rem 1rem;
    cursor: pointer;
    transition: background 0.15s;
}
.mention-item:hover,
.mention-item.active { background: #eef2ff; }

.mention-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
    flex-shrink: 0;
}

.mention-info { flex: 1; min-width: 0; }
.mention-name {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1f2937;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.mention-email {
    font-size: 0.75rem;
    color: #6b7280;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
