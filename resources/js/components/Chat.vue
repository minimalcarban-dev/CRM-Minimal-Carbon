<template>
    <div class="chat-container">
        <!-- Channel List Sidebar -->
        <div class="channels-sidebar">
            <!-- Search Header -->
            <div class="sidebar-header">
                <div class="search-wrapper">
                    <i class="bi bi-search search-icon"></i>
                    <input
                        type="text"
                        v-model="searchQuery"
                        @keyup="debounceSearch"
                        placeholder="Search conversations..."
                        class="search-input"
                    />
                </div>
            </div>

            <!-- Channel Sections -->
            <div class="channels-scroll">
                <!-- Group Chats -->
                <div class="channel-section" v-if="groupChannels.length">
                    <div class="section-header">
                        <i class="bi bi-people-fill"></i>
                        <span>Group Chats</span>
                        <span class="count-badge">{{
                            groupChannels.length
                        }}</span>
                    </div>
                    <div
                        v-for="channel in groupChannels"
                        :key="channel.id"
                        @click="selectChannel(channel)"
                        :class="[
                            'channel-item',
                            { active: currentChannel?.id === channel.id },
                        ]"
                    >
                        <div class="channel-avatar group">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="channel-info">
                            <div class="channel-header-row">
                                <h5 class="channel-title">
                                    {{ channel.name }}
                                </h5>
                                <span
                                    class="channel-time"
                                    v-if="lastMessagePreview[channel.id]?.time"
                                >
                                    {{ lastMessagePreview[channel.id].time }}
                                </span>
                            </div>
                            <div class="channel-preview-row">
                                <p class="channel-preview">
                                    {{
                                        lastMessagePreview[channel.id]?.text ||
                                        "No messages yet"
                                    }}
                                </p>
                                <span
                                    v-if="channel.unread_messages_count"
                                    class="unread-badge"
                                >
                                    {{ channel.unread_messages_count }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personal Chats -->
                <div class="channel-section" v-if="personalChannels.length">
                    <div class="section-header">
                        <i class="bi bi-chat-dots-fill"></i>
                        <span>Direct Messages</span>
                        <span class="count-badge">{{
                            personalChannels.length
                        }}</span>
                    </div>
                    <div
                        v-for="channel in personalChannels"
                        :key="channel.id"
                        @click="selectChannel(channel)"
                        :class="[
                            'channel-item',
                            { active: currentChannel?.id === channel.id },
                        ]"
                    >
                        <div class="channel-avatar personal">
                            {{ avatarInitials(channel.name) }}
                        </div>
                        <div class="channel-info">
                            <div class="channel-header-row">
                                <h5 class="channel-title">
                                    {{ channel.name }}
                                </h5>
                                <span
                                    class="channel-time"
                                    v-if="lastMessagePreview[channel.id]?.time"
                                >
                                    {{ lastMessagePreview[channel.id].time }}
                                </span>
                            </div>
                            <div class="channel-preview-row">
                                <p class="channel-preview">
                                    {{
                                        lastMessagePreview[channel.id]?.text ||
                                        "No messages yet"
                                    }}
                                </p>
                                <span
                                    v-if="channel.unread_messages_count"
                                    class="unread-badge"
                                >
                                    {{ channel.unread_messages_count }}
                                </span>
                            </div>
                        </div>
                        <button
                            v-if="isSuperAdmin"
                            class="channel-delete-btn"
                            @click.stop="deleteChannel(channel.id)"
                            title="Delete Conversation"
                        >
                            <i class="bi bi-trash3"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="chat-main" v-if="currentChannel" :style="chatMainStyle">
            <!-- Chat Header -->
            <div class="chat-header">
                <div class="header-left">
                    <div class="header-avatar">
                        <div
                            v-if="isGroupChannel(currentChannel)"
                            class="avatar-icon group"
                        >
                            <i class="bi bi-people"></i>
                        </div>
                        <div v-else class="avatar-icon personal">
                            {{ avatarInitials(currentChannel.name) }}
                        </div>
                        <span class="status-dot online"></span>
                    </div>
                    <div class="header-info">
                        <h4 class="header-title">{{ currentChannel.name }}</h4>
                        <p class="header-subtitle" v-if="typingLabel">
                            {{ typingLabel }}
                        </p>
                        <p
                            class="header-subtitle"
                            v-else-if="!isPersonalChannel && isSuperAdmin"
                        >
                            {{ channelInfo.members?.length || 0 }} members
                        </p>
                    </div>
                </div>
                <div class="header-actions">
                    <button
                        v-if="isSuperAdmin"
                        class="btn-icon-primary"
                        @click="openCreateChannel"
                        title="New Channel"
                    >
                        <i class="bi bi-plus-lg"></i>
                    </button>
                    <button
                        v-if="isSuperAdmin"
                        class="btn-icon-secondary"
                        @click="openManageMembers"
                        title="Manage Members"
                    >
                        <i class="bi bi-people"></i>
                    </button>
                    <!-- Toggle info sidebar -->
                    <button
                        class="btn-icon-secondary"
                        :class="{ active: userInfoOpen }"
                        @click="userInfoOpen = !userInfoOpen"
                        title="Toggle Info"
                    >
                        <i class="bi bi-info-circle"></i>
                    </button>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="messages-container" ref="messageContainer">
                <!-- Search Results -->
                <div v-if="searchResults.length" class="search-results-area">
                    <div class="search-results-header">
                        <div class="results-info">
                            <i class="bi bi-search"></i>
                            <span
                                >{{ searchResults.length }} results found</span
                            >
                        </div>
                        <button @click="clearSearch" class="btn-clear">
                            <i class="bi bi-x-lg"></i>
                            Clear
                        </button>
                    </div>
                    <div class="search-results-list">
                        <div
                            v-for="message in searchResults"
                            :key="message.id"
                            @click="scrollToMessage(message)"
                            class="search-result-item"
                        >
                            <div class="result-channel">
                                {{ message.channel.name }}
                            </div>
                            <div class="result-content">
                                <strong>{{ message.sender?.name }}</strong>
                                <span class="result-text">{{
                                    message.body
                                }}</span>
                            </div>
                            <div class="result-time">
                                {{ formatDate(message.created_at) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Regular Messages -->
                <template v-else>
                    <!-- Empty State -->
                    <div v-if="!messages.length" class="empty-state">
                        <div class="empty-icon">
                            <i class="bi bi-chat-dots"></i>
                        </div>
                        <h3 class="empty-title">Start the conversation</h3>
                        <p class="empty-subtitle">
                            Send a message or attach files to kick things off.
                        </p>
                    </div>

                    <!-- Messages List -->
                    <div v-else class="messages-list">
                        <div
                            v-for="(message, idx) in messages"
                            :key="message.id"
                            :data-message-id="message.id"
                            class="message-wrapper"
                        >
                            <!-- Date Separator -->
                            <div
                                v-if="shouldShowDateSeparator(idx)"
                                class="date-separator"
                            >
                                <span class="date-label">{{
                                    dayLabel(message.created_at)
                                }}</span>
                            </div>

                            <!-- Message -->
                            <div
                                :class="[
                                    'message-group',
                                    {
                                        'own-message':
                                            message.sender_id === userId,
                                    },
                                ]"
                            >
                                <!-- Avatar (for received messages) -->
                                <div
                                    v-if="message.sender_id !== userId"
                                    class="message-avatar"
                                >
                                    {{ avatarInitials(message.sender?.name) }}
                                </div>

                                <div class="message-content-wrapper">
                                    <!-- Sender Name -->
                                    <div
                                        v-if="
                                            message.sender &&
                                            message.sender_id !== userId
                                        "
                                        class="message-sender"
                                    >
                                        {{ message.sender.name }}
                                    </div>

                                    <!-- Message Bubble -->
                                    <div class="message-bubble">
                                        <div
                                            v-if="message.metadata?.reply_to_id"
                                            class="reply-inline rounded-2"
                                            style="
                                                background: #dde7ff;
                                                cursor: pointer;
                                            "
                                            @click="
                                                scrollToMessageById(
                                                    message.metadata.reply_to_id
                                                )
                                            "
                                            title="Jump to original message"
                                        >
                                            <div class="reply-inline-bar"></div>
                                            <div class="reply-inline-content">
                                                <div class="reply-inline-title">
                                                    Replying to
                                                    {{
                                                        message.metadata
                                                            ?.reply_sender ||
                                                        resolveReply(message)
                                                            ?.sender?.name ||
                                                        "message"
                                                    }}
                                                </div>
                                                <div class="reply-inline-text">
                                                    {{
                                                        message.metadata
                                                            ?.reply_preview ||
                                                        resolveReply(
                                                            message
                                                        )?.body?.slice(
                                                            0,
                                                            100
                                                        ) ||
                                                        "Attachment"
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Text Content -->
                                        <div
                                            v-if="message.body"
                                            class="message-text"
                                            v-html="
                                                formatMessageWithMentions(
                                                    message
                                                )
                                            "
                                        ></div>

                                        <!-- Attachments -->
                                        <!-- <div v-if="message.attachments && message.attachments.length"
                                            class="message-attachments">
                                            <div
                                                v-for="attachment in message.attachments"
                                                :key="attachment.id"
                                                class="attachment-item">
                                                <img
                                                    v-if="isImage(attachment)"
                                                    :src="getAttachmentUrl(attachment)"
                                                    @click="openAttachment(attachment)"
                                                    class="attachment-image"/>
                                                <div
                                                    v-else
                                                    class="attachment-file"
                                                    @click="downloadAttachment(attachment)">
                                                    <i class="bi bi-file-earmark"></i>
                                                    <span>{{attachment.filename}}</span>
                                                </div>
                                            </div>
                                        </div> -->

                                        <div
                                            v-if="
                                                message.attachments &&
                                                message.attachments.length
                                            "
                                            class="message-attachments"
                                        >
                                            <div
                                                v-for="attachment in message.attachments"
                                                :key="attachment.id"
                                                class="attachment-item"
                                            >
                                                <!-- IMAGE -->
                                                <img
                                                    v-if="isImage(attachment)"
                                                    :src="attachment.url"
                                                    class="attachment-image"
                                                    @click="
                                                        openImageLightbox(
                                                            attachment
                                                        )
                                                    "
                                                />

                                                <!-- PDF / FILE -->
                                                <div
                                                    v-else-if="
                                                        isPdf(attachment)
                                                    "
                                                    class="attachment-file attachment-pdf"
                                                    @click="
                                                        openPdfModal(attachment)
                                                    "
                                                >
                                                    <i
                                                        class="bi bi-file-pdf"
                                                    ></i>
                                                    <span>{{
                                                        attachment.filename
                                                    }}</span>
                                                </div>
                                                <a
                                                    v-else
                                                    :href="
                                                        attachment.download_url
                                                    "
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="attachment-file"
                                                >
                                                    <i
                                                        class="bi bi-file-earmark"
                                                    ></i>
                                                    <span>{{
                                                        attachment.filename
                                                    }}</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Thread Indicator -->
                                    <div
                                        v-if="message.thread_count > 0"
                                        class="thread-indicator"
                                        @click.stop="openThread(message)"
                                        style="
                                            display: flex;
                                            align-items: center;
                                            gap: 0.5rem;
                                            margin-top: 0.25rem;
                                            cursor: pointer;
                                            background: var(--primary-light);
                                            padding: 0.25rem 0.5rem;
                                            border-radius: 8px;
                                            font-size: 0.75rem;
                                            color: var(--primary);
                                            width: fit-content;
                                        "
                                    >
                                        <i class="bi bi-person-lines-fill"></i>
                                        <span
                                            >{{ message.thread_count }}
                                            {{
                                                message.thread_count === 1
                                                    ? "reply"
                                                    : "replies"
                                            }}</span
                                        >
                                        <i class="bi bi-chevron-right"></i>
                                    </div>

                                    <!-- Time Outside Bubble -->
                                    <div class="message-time-outside">
                                        {{ formatDate(message.created_at) }}
                                        <button
                                            class="meta-action"
                                            title="Reply in Thread"
                                            @click.stop="openThread(message)"
                                        >
                                            <i class="bi bi-chat-text"></i>
                                        </button>
                                        <button
                                            class="meta-action"
                                            title="Reply Quote"
                                            @click="replyToMessage(message)"
                                        >
                                            <i class="bi bi-reply"></i>
                                        </button>
                                        <span
                                            v-if="message.sender_id === userId"
                                            class="message-status"
                                            :title="
                                                readByOthers(message)
                                                    ? 'Read'
                                                    : 'Sent'
                                            "
                                        >
                                            <i
                                                v-if="readByOthers(message)"
                                                class="bi bi-check-all read"
                                            ></i>
                                            <i
                                                v-else
                                                class="bi bi-check-all"
                                            ></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Scroll to Bottom Button -->
            <button
                v-if="showScrollDown"
                @click="scrollToBottom"
                class="scroll-to-bottom"
                title="Scroll to latest"
            >
                <i class="bi bi-arrow-down"></i>
            </button>

            <!-- Message Input -->
            <div class="message-input-container" ref="inputContainer">
                <!-- Attachment Preview -->
                <div v-if="attachmentFiles.length" class="attachments-preview">
                    <div
                        v-for="(file, index) in attachmentFiles"
                        :key="index"
                        class="attachment-preview-item"
                    >
                        <i class="bi bi-paperclip"></i>
                        <span>{{ file.name }}</span>
                        <button
                            @click="removeAttachment(index)"
                            class="remove-attachment"
                        >
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>

                <!-- Input Row -->
                <div class="input-row">
                    <input
                        type="file"
                        ref="fileInput"
                        @change="handleFiles"
                        multiple
                        style="display: none"
                    />
                    <button
                        @click="$refs.fileInput.click()"
                        class="btn-attach"
                        title="Attach files"
                    >
                        <i class="bi bi-paperclip"></i>
                    </button>
                    <div
                        :class="[
                            'input-with-suggestions',
                            { 'has-reply': !!replyTo },
                        ]"
                    >
                        <div v-if="replyTo" class="reply-chip">
                            <div class="reply-title">
                                Replying to
                                {{ replyTo.sender?.name || "message" }}
                            </div>
                            <div class="reply-preview">
                                {{ replyTo.body?.slice(0, 80) || "Attachment" }}
                            </div>
                            <button
                                class="reply-cancel"
                                @click="replyTo = null"
                                title="Cancel reply"
                            >
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                        <textarea
                            v-model="newMessage"
                            @keydown="onKeyDownInEditor"
                            @input="onEditorInput"
                            placeholder="Type a message... Use @ to mention"
                            ref="messageInput"
                            class="message-textarea"
                            style="width: 100%"
                            rows="1"
                        ></textarea>

                        <div
                            v-if="mentionOpen && mentionItems.length"
                            class="mention-popover"
                        >
                            <div
                                v-for="(m, i) in mentionItems"
                                :key="m.id"
                                :class="[
                                    'mention-item',
                                    { active: i === mentionIndex },
                                ]"
                                @mousedown.prevent="pickMention(m)"
                            >
                                <span class="mention-avatar">{{
                                    avatarInitials(m.name)
                                }}</span>
                                <div class="mention-info">
                                    <div class="mention-name">{{ m.name }}</div>
                                    <div class="mention-email">
                                        {{ m.email }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button
                        @click="sendMessage"
                        :disabled="!canSendMessage"
                        class="btn-send"
                        title="Send message"
                    >
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Right Sidebar (Channel Info) -->
        <!-- Right Sidebar (Channel Info or Thread) -->
        <div
            class="info-sidebar"
            v-if="currentChannel && (showSidebar || threadPanelOpen)"
            :style="infoSidebarStyle"
        >
            <!-- THREAD PANEL -->
            <div
                v-if="threadPanelOpen"
                class="thread-panel"
                style="width: 100%"
            >
                <!-- Resize Handle -->
                <div
                    class="thread-resize-handle"
                    @mousedown="startResizeThread"
                ></div>

                <div class="thread-header">
                    <div class="thread-header-left">
                        <h3 class="thread-title">Thread</h3>
                        <span class="thread-subtitle">
                            {{
                                activeThreadMessage?.sender?.name
                                    ? "with " + activeThreadMessage.sender.name
                                    : ""
                            }}
                        </span>
                    </div>
                    <button @click="closeThread" class="btn-icon-secondary">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="thread-content" ref="threadContent">
                    <!-- Parent Message -->
                    <div
                        class="thread-parent-message"
                        v-if="activeThreadMessage"
                    >
                        <div class="message-avatar">
                            {{
                                avatarInitials(activeThreadMessage.sender?.name)
                            }}
                        </div>
                        <div class="message-content-wrapper">
                            <div class="message-sender">
                                {{ activeThreadMessage.sender?.name }}
                                <span class="message-time">{{
                                    formatDate(activeThreadMessage.created_at)
                                }}</span>
                            </div>
                            <div class="message-bubble parent-bubble">
                                <div
                                    v-html="
                                        formatMessageWithMentions(
                                            activeThreadMessage
                                        )
                                    "
                                    class="message-text"
                                ></div>
                                <!-- <div
                                    v-if="
                                        activeThreadMessage.attachments &&
                                        activeThreadMessage.attachments.length
                                    "
                                    class="message-attachments">
                                    <div
                                        v-for="attachment in activeThreadMessage.attachments"
                                        :key="attachment.id"
                                        class="attachment-item"
                                    >
                                        <img
                                            v-if="isImage(attachment)"
                                            :src="getAttachmentUrl(attachment)"
                                            class="attachment-image"
                                        />
                                        <div v-else class="attachment-file">
                                            <i class="bi bi-file-earmark"></i>
                                            <span>{{
                                                attachment.filename
                                            }}</span>
                                        </div>
                                    </div>
                                </div> -->

                                <div
                                    v-if="
                                        activeThreadMessage.attachments &&
                                        activeThreadMessage.attachments.length
                                    "
                                    class="message-attachments"
                                >
                                    <div
                                        v-for="attachment in activeThreadMessage.attachments"
                                        :key="attachment.id"
                                        class="attachment-item"
                                    >
                                        <img
                                            v-if="attachment.is_image"
                                            :src="attachment.url"
                                            class="attachment-image"
                                            @click="
                                                window.open(
                                                    attachment.url,
                                                    '_blank'
                                                )
                                            "
                                        />

                                        <div
                                            v-else-if="isPdf(attachment)"
                                            class="attachment-file attachment-pdf"
                                            @click="openPdfModal(attachment)"
                                        >
                                            <i class="bi bi-file-pdf"></i>
                                            <span>{{
                                                attachment.filename
                                            }}</span>
                                        </div>
                                        <a
                                            v-else
                                            :href="attachment.download_url"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="attachment-file"
                                        >
                                            <i class="bi bi-file-earmark"></i>
                                            <span>{{
                                                attachment.filename
                                            }}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="thread-divider">
                        <span>{{ threadReplies.length }} replies</span>
                    </div>

                    <!-- Replies -->
                    <div class="thread-replies">
                        <div
                            v-for="reply in threadReplies"
                            :key="reply.id"
                            class="message-group"
                        >
                            <div class="message-avatar">
                                {{ avatarInitials(reply.sender?.name) }}
                            </div>
                            <div class="message-content-wrapper">
                                <div class="message-sender">
                                    {{ reply.sender?.name }}
                                    <span class="message-time">{{
                                        formatDate(reply.created_at)
                                    }}</span>
                                </div>
                                <div class="message-bubble">
                                    <div
                                        v-html="
                                            formatMessageWithMentions(reply)
                                        "
                                        class="message-text"
                                    ></div>

                                    <!-- <div
                                        v-if="reply.attachments && reply.attachments.length"
                                        class="message-attachments">
                                        <div
                                            v-for="attachment in reply.attachments"
                                            :key="attachment.id"
                                            class="attachment-item">
                                            <img
                                                v-if="isImage(attachment)"
                                                :src="
                                                    getAttachmentUrl(attachment)
                                                "
                                                class="attachment-image"
                                                />
                                            <div v-else class="attachment-file">
                                                <i
                                                    class="bi bi-file-earmark"
                                                ></i>
                                                <span>{{
                                                    attachment.filename
                                                }}</span>
                                            </div>  
                                        </div>
                                    </div> -->

                                    <div
                                        v-if="
                                            reply.attachments &&
                                            reply.attachments.length
                                        "
                                        class="message-attachments"
                                    >
                                        <div
                                            v-for="attachment in reply.attachments"
                                            :key="attachment.id"
                                            class="attachment-item"
                                        >
                                            <img
                                                v-if="attachment.is_image"
                                                :src="attachment.url"
                                                class="attachment-image"
                                                @click="
                                                    window.open(
                                                        attachment.url,
                                                        '_blank'
                                                    )
                                                "
                                            />

                                            <div
                                                v-else-if="isPdf(attachment)"
                                                class="attachment-file attachment-pdf"
                                                @click="
                                                    openPdfModal(attachment)
                                                "
                                            >
                                                <i class="bi bi-file-pdf"></i>
                                                <span>{{
                                                    attachment.filename
                                                }}</span>
                                            </div>
                                            <a
                                                v-else
                                                :href="attachment.download_url"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="attachment-file"
                                            >
                                                <i
                                                    class="bi bi-file-earmark"
                                                ></i>
                                                <span>{{
                                                    attachment.filename
                                                }}</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thread Input -->
                <div class="thread-input-area">
                    <div
                        v-if="threadReplyFiles.length"
                        class="attachments-preview"
                    >
                        <div
                            v-for="(file, index) in threadReplyFiles"
                            :key="index"
                            class="attachment-preview-item"
                        >
                            <i class="bi bi-paperclip"></i>
                            <span>{{ file.name }}</span>
                            <button
                                @click="removeThreadAttachment(index)"
                                class="remove-attachment"
                            >
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                    <div class="input-row">
                        <input
                            type="file"
                            ref="threadFileInput"
                            @change="handleThreadFiles"
                            multiple
                            style="display: none"
                        />
                        <button
                            @click="$refs.threadFileInput.click()"
                            class="btn-attach-thread"
                            title="Attach files"
                        >
                            <i class="bi bi-paperclip"></i>
                        </button>
                        <textarea
                            v-model="threadReplyInput"
                            class="thread-textarea"
                            placeholder="Reply to thread..."
                            rows="1"
                            @keydown.enter.prevent="sendThreadReply"
                        ></textarea>
                        <button
                            @click="sendThreadReply"
                            class="btn-send-thread"
                            :disabled="
                                !threadReplyInput.trim() &&
                                !threadReplyFiles.length
                            "
                            title="Send reply"
                        >
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- EXISTING INFO PANEL -->
            <div
                v-else
                class="channel-info-panel"
                style="height: 100%; display: flex; flex-direction: column"
            >
                <!-- Resize handle for info panel -->
                <div
                    class="info-resize-handle"
                    @mousedown="startResizeInfo"
                ></div>
                <!-- Profile Card -->
                <div class="info-profile">
                    <div class="profile-avatar-large">
                        <div
                            v-if="isGroupChannel(currentChannel)"
                            class="avatar-large group"
                        >
                            <i class="bi bi-people"></i>
                        </div>
                        <div v-else class="avatar-large personal">
                            {{ avatarInitials(currentChannel.name) }}
                        </div>
                    </div>
                    <button
                        class="btn-icon-secondary info-close-btn"
                        @click="userInfoOpen = false"
                        title="Close Info"
                    >
                        <i class="bi bi-x-lg"></i>
                    </button>
                    <h4 class="profile-name">{{ currentChannel.name }}</h4>
                    <p class="profile-type">
                        {{
                            isGroupChannel(currentChannel)
                                ? "Group Chat"
                                : "Direct Message"
                        }}
                    </p>
                </div>

                <!-- Info Sections -->
                <div class="info-sections">
                    <!-- About Section (hidden in personal DMs for normal admins) -->
                    <div class="info-section" v-if="showAboutSection">
                        <button
                            @click="togglePanel('info')"
                            class="section-toggle"
                        >
                            <div class="section-header-content">
                                <i class="bi bi-info-circle"></i>
                                <span>About</span>
                            </div>
                            <i
                                :class="[
                                    'bi',
                                    openPanel === 'info'
                                        ? 'bi-chevron-up'
                                        : 'bi-chevron-down',
                                ]"
                            ></i>
                        </button>
                        <div
                            v-show="openPanel === 'info'"
                            class="section-content"
                        >
                            <div
                                v-if="channelInfo.description"
                                class="info-item"
                            >
                                <p>{{ channelInfo.description }}</p>
                            </div>
                            <div class="info-item">
                                <label>Created by</label>
                                <strong>
                                    <p>
                                        {{ channelInfo.creator?.name || "—" }}
                                    </p>
                                </strong>
                            </div>
                            <div class="info-item">
                                <label>Created on</label>
                                <strong>
                                    <p>
                                        {{
                                            formatFullDate(
                                                channelInfo.created_at
                                            )
                                        }}
                                    </p>
                                </strong>
                            </div>
                        </div>
                    </div>

                    <!-- Members Section (hidden in personal DMs for normal admins) -->
                    <div class="info-section" v-if="showMembersSection">
                        <button
                            @click="togglePanel('members')"
                            class="section-toggle"
                        >
                            <div class="section-header-content">
                                <i class="bi bi-people"></i>
                                <span
                                    >Members ({{
                                        channelInfo.members?.length || 0
                                    }})</span
                                >
                            </div>
                            <i
                                :class="[
                                    'bi',
                                    openPanel === 'members'
                                        ? 'bi-chevron-up'
                                        : 'bi-chevron-down',
                                ]"
                            ></i>
                        </button>
                        <div
                            v-show="openPanel === 'members'"
                            class="section-content"
                        >
                            <div
                                v-for="member in channelInfo.members"
                                :key="member.id"
                                class="member-item"
                            >
                                <div class="member-avatar">
                                    {{ avatarInitials(member.name) }}
                                </div>
                                <div class="member-info">
                                    <p class="member-name">
                                        {{ member.name }}
                                    </p>
                                    <p class="member-email">
                                        {{ member.email }}
                                    </p>
                                </div>
                                <button
                                    class="btn-icon-secondary"
                                    @click="startDirect(member.id)"
                                    title="Message"
                                >
                                    <i class="bi bi-chat-dots"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Media Section -->
                    <div class="info-section">
                        <button
                            @click="togglePanel('media')"
                            class="section-toggle"
                        >
                            <div class="section-header-content">
                                <i class="bi bi-image"></i>
                                <span>Media ({{ sidebarImages.length }})</span>
                            </div>
                            <i
                                :class="[
                                    'bi',
                                    openPanel === 'media'
                                        ? 'bi-chevron-up'
                                        : 'bi-chevron-down',
                                ]"
                            ></i>
                        </button>
                        <div
                            v-show="openPanel === 'media'"
                            class="section-content"
                        >
                            <div
                                v-if="!sidebarImages.length"
                                class="empty-section"
                            >
                                <i class="bi bi-image"></i>
                                <p>No media yet</p>
                            </div>
                            <div v-else>
                                <MediaGallery :images="sidebarImages" />
                            </div>
                        </div>
                    </div>

                    <!-- Files Section -->
                    <div class="info-section">
                        <button
                            @click="togglePanel('files')"
                            class="section-toggle"
                        >
                            <div class="section-header-content">
                                <i class="bi bi-file-earmark"></i>
                                <span>Files ({{ sidebarFiles.length }})</span>
                            </div>
                            <i
                                :class="[
                                    'bi',
                                    openPanel === 'files'
                                        ? 'bi-chevron-up'
                                        : 'bi-chevron-down',
                                ]"
                            ></i>
                        </button>
                        <div
                            v-show="openPanel === 'files'"
                            class="section-content"
                        >
                            <div
                                v-if="!sidebarFiles.length"
                                class="empty-section"
                            >
                                <i class="bi bi-file-earmark"></i>
                                <p>No files yet</p>
                            </div>
                            <div
                                v-else
                                v-for="file in sidebarFiles"
                                :key="file.id"
                                class="file-link"
                                @click="
                                    isPdf(file)
                                        ? openPdfModal(file)
                                        : openAttachment(file)
                                "
                                style="cursor: pointer"
                            >
                                <i
                                    :class="[
                                        'bi',
                                        isPdf(file)
                                            ? 'bi-file-pdf'
                                            : 'bi-file-earmark',
                                    ]"
                                ></i>
                                <span>{{ file.filename }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Links Section -->
                    <div class="info-section">
                        <button
                            @click="togglePanel('links')"
                            class="section-toggle"
                        >
                            <div class="section-header-content">
                                <i class="bi bi-link-45deg"></i>
                                <span>Links ({{ sidebarLinks.length }})</span>
                            </div>
                            <i
                                :class="[
                                    'bi',
                                    openPanel === 'links'
                                        ? 'bi-chevron-up'
                                        : 'bi-chevron-down',
                                ]"
                            ></i>
                        </button>
                        <div
                            v-show="openPanel === 'links'"
                            class="section-content"
                        >
                            <div
                                v-if="!sidebarLinks.length"
                                class="empty-section"
                            >
                                <i class="bi bi-link-45deg"></i>
                                <p>No links yet</p>
                            </div>
                            <a
                                v-else
                                v-for="link in sidebarLinks"
                                :key="link.message_id + '-' + link.url"
                                :href="link.url"
                                target="_blank"
                                class="file-link"
                            >
                                <i class="bi bi-link-45deg"></i>
                                <span>{{ link.url }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manage Members Modal -->
        <div
            v-if="manageOpen"
            class="modal-overlay"
            @click.self="manageOpen = false"
        >
            <div class="modal-container">
                <div class="modal-header">
                    <h3 class="modal-title">
                        <i class="bi bi-people"></i>
                        Manage Members
                    </h3>
                    <button @click="manageOpen = false" class="modal-close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="search-wrapper">
                        <i class="bi bi-search search-icon"></i>
                        <input
                            v-model="memberSearch"
                            class="search-input"
                            placeholder="Search members..."
                        />
                    </div>
                    <div v-if="membersLoading" class="loading-state">
                        <div class="spinner"></div>
                        <p>Loading members...</p>
                    </div>
                    <div v-else class="members-list">
                        <label
                            v-for="admin in members.filter((a) =>
                                (a.name + ' ' + a.email)
                                    .toLowerCase()
                                    .includes(memberSearch.toLowerCase())
                            )"
                            :key="admin.id"
                            class="member-checkbox-item"
                        >
                            <input
                                type="checkbox"
                                :checked="memberIds.includes(admin.id)"
                                :disabled="admin.id === userId"
                                @change="toggleMember(admin.id)"
                                class="member-checkbox"
                            />
                            <div class="member-avatar small">
                                {{ avatarInitials(admin.name) }}
                            </div>
                            <div class="member-details">
                                <p class="member-name">
                                    {{ admin.name }}
                                    <span
                                        v-if="admin.id === userId"
                                        class="you-badge"
                                        >You</span
                                    >
                                </p>
                                <p class="member-email">{{ admin.email }}</p>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button @click="manageOpen = false" class="btn-secondary">
                        Cancel
                    </button>
                    <button @click="saveMembers" class="btn-primary">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>

        <!-- Create Channel Modal -->
        <div
            v-if="createOpen"
            class="modal-overlay"
            @click.self="createOpen = false"
        >
            <div class="modal-container">
                <div class="modal-header">
                    <h3 class="modal-title">
                        <i class="bi bi-plus-circle"></i>
                        Create New Channel
                    </h3>
                    <button @click="createOpen = false" class="modal-close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Channel Name</label>
                        <input
                            v-model="createName"
                            class="form-input"
                            placeholder="Enter channel name"
                        />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description (Optional)</label>
                        <textarea
                            v-model="createDescription"
                            class="form-textarea"
                            placeholder="Enter channel description"
                            rows="3"
                        ></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Add Members</label>
                        <div class="search-wrapper">
                            <i class="bi bi-search search-icon"></i>
                            <input
                                v-model="createSearch"
                                class="search-input"
                                placeholder="Search members..."
                            />
                        </div>
                    </div>
                    <div class="members-list">
                        <label
                            v-for="admin in members.filter((a) =>
                                (a.name + ' ' + a.email)
                                    .toLowerCase()
                                    .includes(createSearch.toLowerCase())
                            )"
                            :key="admin.id"
                            class="member-checkbox-item"
                        >
                            <input
                                type="checkbox"
                                :checked="createMemberIds.includes(admin.id)"
                                :disabled="admin.id === userId"
                                @change="toggleCreateMember(admin.id)"
                                class="member-checkbox"
                            />
                            <div class="member-avatar small">
                                {{ avatarInitials(admin.name) }}
                            </div>
                            <div class="member-details">
                                <p class="member-name">
                                    {{ admin.name }}
                                    <span
                                        v-if="admin.id === userId"
                                        class="you-badge"
                                        >You</span
                                    >
                                </p>
                                <p class="member-email">{{ admin.email }}</p>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button @click="createOpen = false" class="btn-secondary">
                        Cancel
                    </button>
                    <button @click="saveCreateChannel" class="btn-primary">
                        Create Channel
                    </button>
                </div>
            </div>
        </div>

        <!-- PDF Viewer Modal -->
        <div
            v-if="pdfModalOpen"
            class="pdf-modal-overlay"
            @click.self="closePdfModal"
        >
            <div class="pdf-modal-container">
                <div class="pdf-modal-header">
                    <div class="pdf-modal-title">
                        <i class="bi bi-file-pdf"></i>
                        <span>{{ currentPdfFilename }}</span>
                    </div>
                    <div class="pdf-modal-actions">
                        <button
                            @click="downloadCurrentPdf"
                            class="pdf-action-btn"
                            title="Download"
                        >
                            <i class="bi bi-download"></i>
                        </button>
                        <button
                            @click="closePdfModal"
                            class="pdf-action-btn close-btn"
                            title="Close"
                        >
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
                <div class="pdf-modal-body">
                    <div v-if="pdfLoading" class="pdf-loading">
                        <div class="spinner"></div>
                        <p>Loading PDF...</p>
                    </div>
                    <iframe
                        v-show="!pdfLoading"
                        ref="pdfIframe"
                        :src="pdfViewerUrl"
                        class="pdf-iframe"
                        @load="onPdfIframeLoad"
                        frameborder="0"
                    ></iframe>
                </div>
            </div>
        </div>

        <!-- Image Lightbox Modal -->
        <div
            v-if="imageLightboxOpen"
            class="image-lightbox-overlay"
            @click.self="closeImageLightbox"
            @keydown.esc="closeImageLightbox"
            tabindex="-1"
        >
            <button
                class="lightbox-close"
                @click="closeImageLightbox"
                title="Close"
            >
                <i class="bi bi-x-lg"></i>
            </button>
            <button
                class="lightbox-download"
                @click="downloadLightboxImage"
                title="Download"
            >
                <i class="bi bi-download"></i>
            </button>
            <img
                v-if="currentLightboxImage"
                :src="currentLightboxImage.url || currentLightboxImage.path"
                :alt="currentLightboxImage.filename || 'Image'"
                class="lightbox-image"
            />
        </div>
    </div>
</template>

<script>
import {
    ref,
    onMounted,
    onBeforeUnmount,
    watch,
    computed,
    nextTick,
} from "vue";
import axios from "axios";
import { format } from "date-fns";
import debounce from "lodash/debounce";
import DOMPurify from "dompurify";
import MediaGallery from "./MediaGallery.vue";
import * as pdfjsLib from "pdfjs-dist";

// Set PDF.js worker
pdfjsLib.GlobalWorkerOptions.workerSrc = new URL(
    "pdfjs-dist/build/pdf.worker.mjs",
    import.meta.url
).toString();

export default {
    components: { MediaGallery },
    props: {
        userId: { type: Number, required: true },
        pusherKey: { type: String, required: true },
        pusherCluster: { type: String, required: true },
    },
    setup(props) {
        // State declarations
        const channels = ref([]);
        const currentChannel = ref(null);
        const messages = ref([]);
        const lastMessagePreview = ref({});
        const searchResults = ref([]);
        const searchQuery = ref("");
        const isSearching = ref(false);
        const newMessage = ref("");
        const attachmentFiles = ref([]);
        const replyTo = ref(null);
        const isSending = ref(false);
        // Mentions state
        const mentionOpen = ref(false);
        const mentionQuery = ref("");
        const mentionItems = ref([]);
        const mentionIndex = ref(0);
        const pendingMentionIds = ref(new Set());
        const messageContainer = ref(null);
        const messageInput = ref(null);
        const inputContainer = ref(null);
        const page = ref(1);
        const hasMoreMessages = ref(true);
        const loadingMessages = ref(false);
        const showScrollDown = ref(false);
        const channelInfo = ref({
            creator: null,
            members: [],
            created_at: null,
        });
        const sidebarImages = ref([]);
        const sidebarFiles = ref([]);
        const sidebarLinks = ref([]);
        const typingUsers = ref({});
        const lastTypingSentAt = ref(0);
        const manageOpen = ref(false);
        const membersLoading = ref(false);
        const members = ref([]);
        const memberIds = ref([]);
        const memberSearch = ref("");
        const canCreateChannel = ref(false);
        const createOpen = ref(false);
        const createName = ref("");
        const createDescription = ref("");
        const createSearch = ref("");
        const createMemberIds = ref([]);
        const openPanel = ref("info");

        // PDF Viewer Modal State
        const pdfModalOpen = ref(false);
        const currentPdfUrl = ref("");
        const currentPdfFilename = ref("");
        const pdfLoading = ref(false);
        const pdfError = ref("");
        const pdfCurrentPage = ref(1);
        const pdfTotalPages = ref(0);
        const pdfZoom = ref(1.0);
        const pdfCanvas = ref(null);
        const pdfCanvasWrapper = ref(null);
        let pdfDocInstance = null;

        // Image Lightbox State
        const imageLightboxOpen = ref(false);
        const currentLightboxImage = ref(null);

        // Utility Functions
        const avatarInitials = (name) => {
            if (!name) return "?";
            const parts = name.trim().split(/\s+/);
            return (
                (parts[0]?.[0] || "") + (parts[1]?.[0] || "")
            ).toUpperCase();
        };

        const isGroupChannel = (c) => {
            const t = (c?.type || "").toLowerCase();
            return t === "group" || t === "public";
        };

        const formatDate = (date) => {
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

        const formatFullDate = (date) => {
            if (!date) return "";
            try {
                return format(new Date(date), "dd MMM yyyy HH:mm");
            } catch {
                return "";
            }
        };

        // Return a publicly accessible URL for an attachment (handles Cloudinary full URLs)
        const getAttachmentUrl = (attachment) => {
            if (!attachment) return "";
            if (attachment.url) return attachment.url;
            if (attachment.thumbnail_url) return attachment.thumbnail_url;
            if (attachment.path && attachment.path.startsWith("http"))
                return attachment.path;
            if (
                attachment.thumbnail_path &&
                attachment.thumbnail_path.startsWith("http")
            )
                return attachment.thumbnail_path;
            if (attachment.path) return `/storage/${attachment.path}`;
            if (attachment.thumbnail_path)
                return `/storage/${attachment.thumbnail_path}`;
            return "";
        };

        const storageUrl = (item) => {
            if (!item) return "#";
            if (item.download_url) return item.download_url;
            if (item.url) return item.url;
            if (item.path && item.path.startsWith("http")) return item.path;
            return item.path ? `/storage/${item.path}` : "#";
        };

        const storageThumbUrl = (item) => {
            if (!item) return "";
            if (item.thumbnail_url) return item.thumbnail_url;
            if (item.thumbnail_path && item.thumbnail_path.startsWith("http"))
                return item.thumbnail_path;
            if (item.thumbnail_path) return `/storage/${item.thumbnail_path}`;
            if (item.url) return item.url;
            if (item.path && item.path.startsWith("http")) return item.path;
            if (item.path) return `/storage/${item.path}`;
            return "";
        };

        const downloadAttachment = (attachment) => {
            const url = storageUrl(attachment);
            const filename = attachment.filename || "download";

            let downloadUrl = url;

            // Check if it's a Cloudinary URL and insert fl_attachment
            if (url.includes("res.cloudinary.com")) {
                const parts = url.split("/upload/");
                if (parts.length === 2) {
                    // Avoid adding fl_attachment if it's already there
                    if (!parts[1].startsWith("fl_attachment")) {
                        downloadUrl =
                            parts[0] + "/upload/fl_attachment/" + parts[1];
                    }
                }
            }

            const link = document.createElement("a");
            link.href = downloadUrl;

            // The 'download' attribute suggests a filename to the browser.
            // For cross-origin URLs, this only works if the server sends
            // the Content-Disposition: attachment header. `fl_attachment` does this.
            link.setAttribute("download", filename);

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        };
        const openAttachment = (attachment) => {
            window.open(storageUrl(attachment), "_blank");
        };

        // PDF Viewer Modal Functions
        const isPdf = (attachment) => {
            if (!attachment) return false;
            const mimeType = attachment.mime_type || "";
            const filename = attachment.filename || "";
            return (
                mimeType === "application/pdf" ||
                filename.toLowerCase().endsWith(".pdf")
            );
        };

        // Image check function
        const isImage = (attachment) => {
            if (!attachment) return false;
            const mimeType = attachment.mime_type || "";
            const filename = (attachment.filename || "").toLowerCase();
            return (
                mimeType.startsWith("image/") ||
                /\.(jpg|jpeg|png|gif|webp|svg|bmp)$/.test(filename)
            );
        };

        const openPdfModal = (attachment) => {
            if (!attachment?.id) {
                window.showToast?.("PDF not available");
                return;
            }

            // Use proxy URL to serve PDF from local storage via Laravel
            currentPdfUrl.value = `/admin/chat/attachments/${attachment.id}/proxy`;
            currentPdfFilename.value = attachment.filename || "Document.pdf";
            pdfModalOpen.value = true;
            pdfLoading.value = true;
        };

        // Computed property for PDF viewer URL (using proxy endpoint with fit-to-width)
        const pdfViewerUrl = computed(() => {
            if (!currentPdfUrl.value) return "";
            // Add #view=FitH to make PDF fit horizontally in the viewer
            return `${currentPdfUrl.value}#view=FitH`;
        });

        // Handle iframe load event
        const onPdfIframeLoad = () => {
            pdfLoading.value = false;
        };

        // Image Lightbox Functions
        const openImageLightbox = (attachment) => {
            if (!attachment) return;
            currentLightboxImage.value = attachment;
            imageLightboxOpen.value = true;
        };

        const closeImageLightbox = () => {
            imageLightboxOpen.value = false;
            currentLightboxImage.value = null;
        };

        const downloadLightboxImage = () => {
            const img = currentLightboxImage.value;
            if (!img) return;
            const url = img.url || img.path;
            if (url) {
                const a = document.createElement("a");
                a.href = url;
                a.download = img.filename || "image";
                a.target = "_blank";
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            }
        };

        const renderPdfPage = async (pageNum) => {
            if (!pdfDocInstance || !pdfCanvas.value) return;

            try {
                const page = await pdfDocInstance.getPage(pageNum);
                const canvas = pdfCanvas.value;
                const context = canvas.getContext("2d");

                // Calculate scale based on container width and zoom
                const wrapper = pdfCanvasWrapper.value;
                const containerWidth = wrapper ? wrapper.clientWidth - 40 : 800;
                const viewport = page.getViewport({ scale: 1 });
                const baseScale = containerWidth / viewport.width;
                const scale = baseScale * pdfZoom.value;

                const scaledViewport = page.getViewport({ scale });

                canvas.height = scaledViewport.height;
                canvas.width = scaledViewport.width;

                await page.render({
                    canvasContext: context,
                    viewport: scaledViewport,
                }).promise;

                pdfCurrentPage.value = pageNum;
            } catch (err) {
                console.error("PDF render error:", err);
            }
        };

        const nextPdfPage = () => {
            if (pdfCurrentPage.value < pdfTotalPages.value) {
                renderPdfPage(pdfCurrentPage.value + 1);
            }
        };

        const prevPdfPage = () => {
            if (pdfCurrentPage.value > 1) {
                renderPdfPage(pdfCurrentPage.value - 1);
            }
        };

        const zoomIn = () => {
            if (pdfZoom.value < 3) {
                pdfZoom.value = Math.min(pdfZoom.value + 0.25, 3);
                renderPdfPage(pdfCurrentPage.value);
            }
        };

        const zoomOut = () => {
            if (pdfZoom.value > 0.5) {
                pdfZoom.value = Math.max(pdfZoom.value - 0.25, 0.5);
                renderPdfPage(pdfCurrentPage.value);
            }
        };

        const closePdfModal = () => {
            pdfModalOpen.value = false;
            pdfDocInstance = null;
            currentPdfUrl.value = "";
            currentPdfFilename.value = "";
            pdfError.value = "";
        };

        const downloadCurrentPdf = () => {
            if (!currentPdfUrl.value) return;
            downloadAttachment({
                download_url: currentPdfUrl.value,
                filename: currentPdfFilename.value,
            });
        };

        // Computed Properties
        const SIDEBAR_WIDTH = 320;

        const groupChannels = computed(() =>
            channels.value.filter(isGroupChannel)
        );
        const personalChannels = computed(() =>
            channels.value.filter((c) => !isGroupChannel(c))
        );
        const showSidebar = computed(() => {
            const hint = channelInfo.value?.show_sidebar;
            if (typeof hint === "boolean") return hint;
            return (
                (currentChannel.value?.type || "").toLowerCase() !== "public"
            );
        });
        const canSendMessage = computed(
            () => newMessage.value.trim() || attachmentFiles.value.length > 0
        );
        const isSuperAdmin = computed(() => !!window.authAdminIsSuper);
        const isPersonalChannel = computed(
            () =>
                (currentChannel.value?.type || "").toLowerCase() === "personal"
        );
        // Hide the About section in the sidebar (always hidden)
        const showAboutSection = computed(() => false);
        const showMembersSection = computed(
            // Hide Members section for personal/direct chats (1-to-1 conversations)
            () => !isPersonalChannel.value
        );
        const typingLabel = computed(() => {
            const now = Date.now();
            Object.keys(typingUsers.value).forEach((uid) => {
                if (now - typingUsers.value[uid].at > 3500)
                    delete typingUsers.value[uid];
            });
            const names = Object.values(typingUsers.value)
                .map((x) => x.name)
                .filter(Boolean);
            if (!names.length) return "";
            return `${names.slice(0, 2).join(", ")}${
                names.length > 2 ? ` +${names.length - 2}` : ""
            } is typing...`;
        });

        // Core Functions
        const loadChannels = async () => {
            try {
                const response = await axios.get("/admin/chat/channels");
                channels.value = (response.data || []).map((c) => ({
                    can_manage_members: false,
                    ...c,
                    // Preserve unread count from backend, default to 0 if not provided
                    unread_messages_count: c.unread_messages_count || 0,
                }));

                // Load message previews for channels
                channels.value.forEach((channel) => {
                    if (channel.last_message) {
                        updatePreview(channel.id, channel.last_message);
                    }
                });

                // Setup listeners for all channels after they're loaded
                setupGlobalChannelListeners();
            } catch (error) {
                if (import.meta.env.DEV)
                    console.error("Error loading channels:", error);
            }
        };

        const updatePreview = (channelId, message) => {
            if (!message) return;
            const text = message.body?.trim()
                ? message.body.trim().slice(0, 40)
                : message.attachments?.length
                ? "📎 Attachment"
                : "";
            const time = message.created_at
                ? format(new Date(message.created_at), "HH:mm")
                : "";
            lastMessagePreview.value[channelId] = { text, time };
        };

        const loadMessages = async (channelId, reset = false) => {
            if (reset) {
                messages.value = [];
                page.value = 1;
                hasMoreMessages.value = true;
            }
            if (!hasMoreMessages.value) return;

            try {
                loadingMessages.value = true;
                const response = await axios.get(
                    `/admin/chat/channels/${channelId}/messages?page=${page.value}`
                );
                const newMessages = response.data.data;
                messages.value = [...messages.value, ...newMessages.reverse()];
                messages.value.sort(
                    (a, b) => new Date(a.created_at) - new Date(b.created_at)
                );

                if (messages.value.length) {
                    updatePreview(
                        channelId,
                        messages.value[messages.value.length - 1]
                    );
                }

                hasMoreMessages.value = response.data.next_page_url !== null;
                page.value++;

                await axios.post(`/admin/chat/channels/${channelId}/read`);
                channels.value = channels.value.map((c) =>
                    c.id === channelId ? { ...c, unread_messages_count: 0 } : c
                );
                return true;
            } catch (error) {
                if (error?.response?.status === 403) {
                    window.showToast?.("You don't have access to this channel");
                } else {
                    window.showToast?.(
                        error?.response?.data?.message ||
                            "Failed to load messages"
                    );
                    if (import.meta.env.DEV)
                        console.error("Error loading messages:", error);
                }
                return false;
            } finally {
                loadingMessages.value = false;
            }
        };

        const selectChannel = async (channel) => {
            currentChannel.value = channel;

            // Persist the selected channel ID to localStorage for page refresh persistence
            try {
                localStorage.setItem(
                    "chat_current_channel_id",
                    String(channel.id)
                );
            } catch (e) {
                // Ignore localStorage errors (e.g., private browsing mode)
            }

            const ok = await loadMessages(channel.id, true);
            if (!ok) {
                // Keep the header visible and surface the error so the UI is not blank
                window.showToast?.(
                    "Could not load messages for this conversation"
                );
            }
            await loadSidebar(channel.id);
            scrollToBottom();
        };

        const sendMessage = async () => {
            // Guard against duplicate sends (rapid Enter presses)
            if (
                !canSendMessage.value ||
                !currentChannel.value?.id ||
                isSending.value
            )
                return;

            isSending.value = true;

            const formData = new FormData();
            if (newMessage.value.trim()) {
                formData.append("body", newMessage.value);
            }
            attachmentFiles.value.forEach((file) => {
                formData.append("attachments[]", file);
            });
            // Append metadata as nested fields so Laravel treats it as array in multipart
            if (replyTo.value?.id) {
                formData.append(
                    "metadata[reply_to_id]",
                    String(replyTo.value.id)
                );
                if (replyTo.value?.body)
                    formData.append(
                        "metadata[reply_preview]",
                        replyTo.value.body.slice(0, 140)
                    );
                if (replyTo.value?.sender?.name)
                    formData.append(
                        "metadata[reply_sender]",
                        replyTo.value.sender.name
                    );
            }
            if (pendingMentionIds.value.size) {
                Array.from(pendingMentionIds.value).forEach((id) => {
                    formData.append("metadata[mentions][]", String(id));
                });
            }

            try {
                const { data } = await axios.post(
                    `/admin/chat/channels/${currentChannel.value.id}/messages`,
                    formData,
                    { headers: { "Content-Type": "multipart/form-data" } }
                );

                messages.value.push({
                    ...data,
                    attachments: Array.isArray(data.attachments)
                        ? data.attachments
                        : [],
                    reads: Array.isArray(data.reads) ? data.reads : [],
                });
                messages.value.sort(
                    (a, b) => new Date(a.created_at) - new Date(b.created_at)
                );
                updatePreview(currentChannel.value.id, data);

                newMessage.value = "";
                attachmentFiles.value = [];
                replyTo.value = null;
                pendingMentionIds.value = new Set();
                // Reset textarea height after sending
                nextTick(() => {
                    if (messageInput.value) {
                        messageInput.value.style.height = "auto";
                    }
                });
                scrollToBottom();
                // Load sidebar in background (non-blocking for faster UX)
                loadSidebar(currentChannel.value.id);
                // } catch (error) {
                //     if (import.meta.env.DEV)
                //         console.error("Error sending message:", error);
                // }
            } catch (error) {
                console.error("Error sending message:", error);
                window.showToast?.("Failed to send attachment");
            } finally {
                attachmentFiles.value = [];
                isSending.value = false;
            }
        };

        const handleFiles = (event) => {
            const files = Array.from(event.target.files);
            attachmentFiles.value = [...attachmentFiles.value, ...files];
        };

        const removeAttachment = (index) => {
            attachmentFiles.value.splice(index, 1);
        };

        // Reply helpers
        const resolveReply = (message) => {
            if (!message?.metadata?.reply_to_id) return null;
            return (
                messages.value.find(
                    (m) => m.id === message.metadata.reply_to_id
                ) || null
            );
        };

        const replyToMessage = (message) => {
            replyTo.value = message;
            nextTick(() => {
                try {
                    messageInput.value?.focus();
                } catch (_) {}
            });
        };

        // Mention helpers
        const currentMembers = () =>
            (channelInfo.value?.members || []).filter((m) => m && m.id);
        const updateMentionList = () => {
            const q = mentionQuery.value.trim().toLowerCase();
            const items = currentMembers()
                .filter((m) =>
                    (m.name + " " + (m.email || "")).toLowerCase().includes(q)
                )
                .slice(0, 8);
            mentionItems.value = items;
            mentionIndex.value = 0;
            mentionOpen.value = items.length > 0;
        };

        // Auto-resize textarea based on content (WhatsApp style)
        const autoResizeTextarea = () => {
            const textarea = messageInput.value;
            if (!textarea) return;

            // Reset height to auto to get correct scrollHeight
            textarea.style.height = "auto";

            // Calculate new height (max 8 lines = ~176px)
            const lineHeight = 22;
            const maxLines = 8;
            const maxHeight = lineHeight * maxLines;

            // Set new height, capped at maxHeight
            const newHeight = Math.min(textarea.scrollHeight, maxHeight);
            textarea.style.height = newHeight + "px";
        };

        const onEditorInput = (e) => {
            handleTyping();
            autoResizeTextarea(); // Auto-resize on input
            const val = newMessage.value;
            const caret = e.target.selectionStart;
            const before = val.slice(0, caret);
            // Mention trigger: @ followed by name characters (no spaces)
            const match = before.match(/(^|\s)@([\w.\-]*)$/);
            if (match) {
                mentionQuery.value = match[2] || "";
                updateMentionList();
            } else {
                mentionOpen.value = false;
            }
        };

        const onKeyDownInEditor = (e) => {
            // If mention popover is open, intercept navigation/selection keys
            if (mentionOpen.value) {
                if (
                    ["ArrowDown", "ArrowUp", "Enter", "Tab", "Escape"].includes(
                        e.key
                    )
                ) {
                    if (mentionItems.value.length === 0) {
                        mentionOpen.value = false;
                        return;
                    }
                    e.preventDefault();
                }
                if (e.key === "ArrowDown")
                    mentionIndex.value =
                        (mentionIndex.value + 1) % mentionItems.value.length;
                else if (e.key === "ArrowUp")
                    mentionIndex.value =
                        (mentionIndex.value - 1 + mentionItems.value.length) %
                        mentionItems.value.length;
                else if (e.key === "Enter" || e.key === "Tab") {
                    // Choose mention but do NOT trigger sendMessage here
                    pickMention(mentionItems.value[mentionIndex.value]);
                } else if (e.key === "Escape") mentionOpen.value = false;
                return;
            }

            // When mention popover is not open, handle Enter key
            if (!mentionOpen.value && e.key === "Enter") {
                // Shift+Enter = new line (allow default behavior)
                if (e.shiftKey) {
                    return;
                }
                // Plain Enter = send message
                e.preventDefault();
                sendMessage();
            }
        };

        const pickMention = (m) => {
            if (!m) return;
            const textarea = messageInput.value;
            const val = newMessage.value;
            const caret = textarea?.selectionStart ?? val.length;
            const before = val.slice(0, caret);
            const after = val.slice(caret);
            const startMatch = before.match(/(^|\s)@([\w.\-]*)$/);
            if (!startMatch) return;
            const prefix = startMatch[1] || "";
            // Insert the exact member name and add a trailing space so typing continues naturally
            const insert = `${prefix}@${m.name} `;
            newMessage.value =
                before.replace(/(^|\s)@([\w.\- ]*)$/, insert) + after;
            // Move caret to just after the inserted mention
            nextTick(() => {
                try {
                    const pos = (
                        before.replace(/(^|\s)@([\w.\- ]*)$/, "") + insert
                    ).length;
                    textarea.focus();
                    textarea.setSelectionRange(pos, pos);
                } catch (_) {}
            });
            pendingMentionIds.value.add(m.id);
            mentionOpen.value = false;
        };

        // const formatMessageWithMentions = (message) => {
        //   const text = message.body || "";
        //   const members = currentMembers();
        //   const byName = new Map(members.map((m) => [m.name, m]));
        //   const escaped = text
        //     .replace(/&/g, "&amp;")
        //     .replace(/</g, "&lt;")
        //     .replace(/>/g, "&gt;");

        //   // First, convert URLs to links
        //   const urlRegex = /(https?:\/\/[^\s<>()"']+)/gi;
        //   const withLinks = escaped.replace(urlRegex, (url) => {
        //     return `<a href="${url}" target="_blank" rel="noopener noreferrer" class="message-link">${url}</a>`;
        //   });

        //   // Then, handle mentions
        //   const withMentions = withLinks.replace(/(^|\s|>)@([\w.\- ]{1,50})/g, (all, prefix, name) => {
        //     const m = byName.get(name.trim());
        //     if (!m) return all;
        //     return `${prefix}<span class=\"mention-token\">@${name}</span>`;
        //   });

        //   // Sanitize the markup (allow span with class and anchor tags with specific attributes)
        //   return DOMPurify.sanitize(withMentions, {
        //     ALLOWED_TAGS: ["span", "a"],
        //     ALLOWED_ATTR: ["class", "href", "target", "rel"],
        //   });
        // };

        const formatMessageWithMentions = (message) => {
            if (!message?.body) return "";
            try {
                const text = message.body;

                // 1. Escape HTML characters first
                let content = text
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;");

                // 2. FIRST: Protect email addresses by replacing them with placeholders
                // This prevents emails like user@gmail.com from being split
                const emailRegex =
                    /[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/g;
                const emails = [];
                content = content.replace(emailRegex, (match) => {
                    emails.push(match);
                    return `__EMAIL_PLACEHOLDER_${emails.length - 1}__`;
                });

                // 3. THEN: Convert URLs to links (emails are now protected)
                const urlRegex =
                    /(?:https?:\/\/)?(?:www\.)?[a-z0-9][-a-z0-9]*(?:\.[a-z0-9][-a-z0-9]*)+(?:\/[^\s<>()"']*)?/gi;
                content = content.replace(urlRegex, (url) => {
                    let href = url;
                    if (!/^https?:\/\//i.test(url)) {
                        href = "https://" + url;
                    }
                    return `<a href="${href}" target="_blank" rel="noopener noreferrer" class="message-link">${url}</a>`;
                });

                // 4. FINALLY: Restore email addresses (as plain text)
                emails.forEach((email, index) => {
                    content = content.replace(
                        `__EMAIL_PLACEHOLDER_${index}__`,
                        email
                    );
                });

                // 5. Format Mentions
                try {
                    const members = currentMembers ? currentMembers() : [];
                    if (members.length > 0) {
                        const memberNames = members
                            .map((m) => m.name)
                            .sort((a, b) => b.length - a.length);

                        const escapeRegExp = (string) =>
                            string.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
                        const pattern = new RegExp(
                            `(@)(${memberNames
                                .map(escapeRegExp)
                                .join("|")})\\b`,
                            "g"
                        );

                        content = content.replace(
                            pattern,
                            (match, prefix, name) => {
                                return `<span class="mention-token">${prefix}${name}</span>`;
                            }
                        );
                    }
                } catch (e) {
                    // Ignore mention format errors
                }

                // 6. Sanitize final HTML
                if (DOMPurify && DOMPurify.sanitize) {
                    return DOMPurify.sanitize(content, {
                        ALLOWED_TAGS: ["span", "a"],
                        ALLOWED_ATTR: ["class", "href", "target", "rel"],
                    });
                }
                return content;
            } catch (e) {
                console.error("Message formatting failed", e);
                return message.body || "";
            }
        };

        const loadSidebar = async (channelId) => {
            if (!channelId) return;
            try {
                const { data } = await axios.get(
                    `/admin/chat/channels/${channelId}/sidebar`
                );
                channelInfo.value = data.channel || {
                    creator: null,
                    members: [],
                    created_at: null,
                };
                sidebarImages.value = Array.isArray(data.images)
                    ? data.images
                    : [];
                sidebarFiles.value = Array.isArray(data.files)
                    ? data.files
                    : [];
                sidebarLinks.value = Array.isArray(data.links)
                    ? data.links
                    : [];
            } catch (e) {
                if (import.meta.env.DEV)
                    console.error("Failed to load sidebar", e);
                channelInfo.value = {
                    creator: null,
                    members: [],
                    created_at: null,
                };
                sidebarImages.value = [];
                sidebarFiles.value = [];
                sidebarLinks.value = [];
            }
        };

        const debounceSearch = debounce(async () => {
            if (searchQuery.value.length < 2) {
                searchResults.value = [];
                isSearching.value = false;
                return;
            }

            isSearching.value = true;
            try {
                const params = {
                    query: searchQuery.value,
                    channel_id: currentChannel.value?.id,
                };
                const response = await axios.get(
                    "/admin/chat/messages/search",
                    { params }
                );
                searchResults.value = response.data;
            } catch (error) {
                if (import.meta.env.DEV)
                    console.error("Error searching messages:", error);
            } finally {
                isSearching.value = false;
            }
        }, 300);

        const clearSearch = () => {
            searchQuery.value = "";
            searchResults.value = [];
        };

        const scrollToMessage = async (message) => {
            if (!message) return;

            // If message belongs to a different channel, switch to it and wait for messages to load
            if (currentChannel.value?.id !== message.channel_id) {
                const channel = channels.value.find(
                    (c) => c.id === message.channel_id
                );
                if (channel) await selectChannel(channel);
                else return;
            }

            // close search UI
            clearSearch();

            await nextTick();

            // If this search result is a thread reply, target the parent in the main list
            const parentId =
                message?.metadata?.reply_to_id || message?.reply_to_id || null;
            const targetId = parentId || message.id;

            // Scroll to target message in main list and highlight
            scrollToMessageById(targetId);

            // If this message has a thread (or the search item refers to a reply), open the thread panel
            const shouldOpenThread =
                (message.thread_count && message.thread_count > 0) ||
                !!parentId ||
                !!message.is_thread;
            if (shouldOpenThread) {
                // Attempt to find the message object in the loaded messages; fall back to the search result
                const msgObj = messages.value.find(
                    (m) => m.id === targetId
                ) || { id: targetId };
                // Give DOM a moment to scroll and highlight
                setTimeout(() => openThread(msgObj), 400);
            }
        };

        const scrollToMessageById = (messageId) => {
            if (!messageId) return;

            // First check if message exists in current loaded messages
            const targetMessage = messages.value.find(
                (m) => m.id === messageId
            );

            // Use Vue's nextTick to ensure DOM is updated
            nextTick(() => {
                const messageElement = document.querySelector(
                    `[data-message-id="${messageId}"]`
                );
                if (messageElement && messageContainer.value) {
                    // Scroll to the message with smooth behavior
                    messageElement.scrollIntoView({
                        behavior: "smooth",
                        block: "center",
                    });

                    // Add highlight effect
                    messageElement.classList.add("message-highlight");
                    setTimeout(() => {
                        messageElement.classList.remove("message-highlight");
                    }, 2000);
                } else if (!targetMessage) {
                    // Message not in current view - might need to scroll up to load more
                    window.showToast?.("Scroll up to load older messages");
                } else {
                    // Message exists but DOM not ready, try again
                    setTimeout(() => scrollToMessageById(messageId), 300);
                }
            });
        };

        const scrollToBottom = () => {
            setTimeout(() => {
                if (messageContainer.value) {
                    messageContainer.value.scrollTop =
                        messageContainer.value.scrollHeight;
                }
            }, 100);
        };

        const onScrollMessages = () => {
            const el = messageContainer.value;
            if (!el) return;
            const nearBottom =
                el.scrollHeight - el.scrollTop - el.clientHeight < 120;
            showScrollDown.value = !nearBottom;
        };

        const sameDay = (a, b) => {
            const da = new Date(a),
                db = new Date(b);
            return (
                da.getFullYear() === db.getFullYear() &&
                da.getMonth() === db.getMonth() &&
                da.getDate() === db.getDate()
            );
        };

        const shouldShowDateSeparator = (idx) => {
            if (idx === 0) return true;
            const prev = messages.value[idx - 1];
            const curr = messages.value[idx];
            if (!prev || !curr) return false;
            return !sameDay(prev.created_at, curr.created_at);
        };

        const dayLabel = (date) => {
            const d = new Date(date);
            const today = new Date();
            const yesterday = new Date(Date.now() - 86400000);
            if (sameDay(d, today)) return "Today";
            if (sameDay(d, yesterday)) return "Yesterday";
            return format(d, "dd MMM yyyy");
        };

        const readByOthers = (msg) => {
            if (!Array.isArray(msg.reads)) return false;
            return msg.reads.some(
                (r) => r.user_id && r.user_id !== props.userId
            );
        };

        const handleTyping = () => {
            const now = Date.now();
            if (now - lastTypingSentAt.value < 1500) return;
            lastTypingSentAt.value = now;
            if (currentChannel.value?.id) {
                try {
                    Echo.private(
                        `chat.channel.${currentChannel.value.id}`
                    ).whisper("typing", {
                        userId: props.userId,
                        name: window?.authAdminName || "Someone",
                    });
                } catch (e) {}
            }
        };

        const togglePanel = (key) => {
            openPanel.value = openPanel.value === key ? "" : key;
        };

        // Member Management
        const checkCreateCapability = async () => {
            try {
                const res = await axios.get("/admin/chat/admins", {
                    validateStatus: () => true,
                });
                if (res.status === 200) {
                    members.value = res.data?.admins || [];
                    canCreateChannel.value = true;
                } else if (res.status === 403) {
                    canCreateChannel.value = false;
                }
            } catch (e) {
                canCreateChannel.value = false;
            }
        };

        const openManageMembers = async () => {
            if (!currentChannel.value?.id) return;
            manageOpen.value = true;
            membersLoading.value = true;
            try {
                const { data } = await axios.get(
                    `/admin/chat/channels/${currentChannel.value.id}/members`
                );
                members.value = data.admins || [];
                const initial = (data.member_ids || []).slice();
                if (!initial.includes(props.userId)) initial.push(props.userId);
                memberIds.value = initial;
            } catch (e) {
                if (import.meta.env.DEV)
                    console.error("Failed to load members", e);
                window.showToast?.("Failed to load members");
                manageOpen.value = false;
            } finally {
                membersLoading.value = false;
            }
        };

        const deleteChannel = async (channelId) => {
            const channel = channels.value.find((ch) => ch.id === channelId);
            if (!channel) return;

            const result = await Swal.fire({
                title: "Delete Conversation?",
                html: `All messages with <strong>${channel.name}</strong> will be permanently removed.`,
                icon: "warning",
                iconColor: "#f59e0b",
                showCancelButton: true,
                confirmButtonColor: "#6366f1",
                cancelButtonColor: "#f3f4f6",
                confirmButtonText: "Yes, delete it",
                cancelButtonText: "Cancel",
                background: "linear-gradient(135deg, #f9fafb 0%, #ffffff 100%)",
                backdrop: "rgba(17, 24, 39, 0.4)",
                customClass: {
                    popup: "swal-theme-popup",
                    title: "swal-theme-title",
                    htmlContainer: "swal-theme-text",
                    confirmButton: "swal-theme-confirm",
                    cancelButton: "swal-theme-cancel",
                    icon: "swal-theme-icon",
                },
            });

            if (!result.isConfirmed) return;

            try {
                await axios.delete(`/admin/chat/channels/${channelId}`);

                Swal.fire({
                    title: "Deleted!",
                    text: "Conversation deleted successfully",
                    icon: "success",
                    iconColor: "#10b981",
                    timer: 2000,
                    showConfirmButton: false,
                    background:
                        "linear-gradient(135deg, #f9fafb 0%, #ffffff 100%)",
                    customClass: {
                        popup: "swal-theme-popup",
                        title: "swal-theme-title",
                    },
                });

                if (currentChannel.value?.id === channelId) {
                    currentChannel.value = null;
                }
                await loadChannels();
            } catch (e) {
                if (import.meta.env.DEV)
                    console.error("Failed to delete channel", e);

                Swal.fire({
                    title: "Error!",
                    text: "Failed to delete conversation",
                    icon: "error",
                    iconColor: "#ef4444",
                    confirmButtonColor: "#6366f1",
                    background:
                        "linear-gradient(135deg, #f9fafb 0%, #ffffff 100%)",
                    customClass: {
                        popup: "swal-theme-popup",
                        title: "swal-theme-title",
                        confirmButton: "swal-theme-confirm",
                    },
                });
            }
        };

        const toggleMember = (id) => {
            if (id === props.userId) return;
            const idx = memberIds.value.indexOf(id);
            if (idx >= 0) memberIds.value.splice(idx, 1);
            else memberIds.value.push(id);
        };

        const saveMembers = async () => {
            if (!currentChannel.value?.id) return;
            try {
                const unique = Array.from(
                    new Set([...memberIds.value, props.userId])
                );
                await axios.put(
                    `/admin/chat/channels/${currentChannel.value.id}/members`,
                    {
                        member_ids: unique,
                    }
                );
                window.showToast?.("Members updated");
                manageOpen.value = false;
                await loadChannels();
            } catch (e) {
                if (import.meta.env.DEV)
                    console.error("Failed to update members", e);
                window.showToast?.("Failed to update members");
            }
        };

        const openCreateChannel = async () => {
            if (!canCreateChannel.value) {
                window.showToast?.("Only super admin can create channels");
                return;
            }
            try {
                await checkCreateCapability();
            } catch (e) {}
            createOpen.value = true;
            createName.value = "";
            createDescription.value = "";
            createSearch.value = "";
            createMemberIds.value = [props.userId];
        };

        const startDirect = async (adminId) => {
            if (!adminId) return;
            if (adminId === props.userId) {
                window.showToast?.("You cannot message yourself");
                return;
            }
            try {
                const { data } = await axios.post("/admin/chat/direct", {
                    target_admin_id: adminId,
                });
                // ensure it's in our channels list
                const exists = channels.value.find((c) => c.id === data.id);
                if (!exists)
                    channels.value.unshift({
                        unread_messages_count: 0,
                        can_manage_members: false,
                        ...data,
                    });
                const dm = channels.value.find((c) => c.id === data.id) || data;
                await selectChannel(dm);
            } catch (e) {
                if (import.meta.env.DEV)
                    console.error("Failed to start direct chat", e);
                const msg = e?.response?.data?.errors
                    ? JSON.stringify(e.response.data.errors)
                    : "Failed to start direct chat";
                window.showToast?.(msg);
            }
        };

        const toggleCreateMember = (id) => {
            if (id === props.userId) return;
            const idx = createMemberIds.value.indexOf(id);
            if (idx >= 0) createMemberIds.value.splice(idx, 1);
            else createMemberIds.value.push(id);
        };

        const saveCreateChannel = async () => {
            if (!createName.value.trim()) {
                window.showToast?.("Enter channel name");
                return;
            }
            try {
                const users = Array.from(
                    new Set([...createMemberIds.value, props.userId])
                );
                await axios.post("/admin/chat/channels", {
                    name: createName.value.trim(),
                    description: createDescription.value.trim() || null,
                    users,
                });
                window.showToast?.("Channel created");
                createOpen.value = false;
                await loadChannels();
            } catch (e) {
                if (import.meta.env.DEV)
                    console.error("Failed to create channel", e);
                window.showToast?.("Failed to create channel");
            }
        };

        // Thread Logic
        const threadPanelOpen = ref(false);
        const activeThreadMessage = ref(null);
        const threadReplies = ref([]);
        const threadReplyInput = ref("");
        const threadLoading = ref(false);
        const threadReplyFiles = ref([]);

        // Thread Panel Resize
        const threadPanelWidth = ref(420); // Default width
        const isResizingThread = ref(false);
        const resizeStartX = ref(0);
        const resizeStartWidth = ref(0);
        // Info Panel (channel info) Resize & Visibility
        const infoPanelWidth = ref(320); // Default info sidebar width
        const isResizingInfo = ref(false);
        const resizeInfoStartX = ref(0);
        const resizeInfoStartWidth = ref(0);
        const userInfoOpen = ref(true);
        const infoSidebarWidth = computed(() => {
            if (threadPanelOpen.value) return threadPanelWidth.value;
            return userInfoOpen.value ? Math.max(0, infoPanelWidth.value) : 0;
        });
        const chatMainStyle = computed(() => ({
            width: `calc(100% - ${SIDEBAR_WIDTH}px - ${Math.max(
                0,
                infoSidebarWidth.value
            )}px)`,
        }));
        const infoSidebarStyle = computed(() => ({
            width: `${Math.max(0, infoSidebarWidth.value)}px`,
        }));

        const startResizeThread = (e) => {
            isResizingThread.value = true;
            resizeStartX.value = e.clientX;
            resizeStartWidth.value = threadPanelWidth.value;

            document.addEventListener("mousemove", handleResizeThread);
            document.addEventListener("mouseup", stopResizeThread);
            document.body.style.cursor = "ew-resize";
            document.body.style.userSelect = "none";
        };

        // Info panel resizing (same UX as thread)
        const startResizeInfo = (e) => {
            isResizingInfo.value = true;
            resizeInfoStartX.value = e.clientX;
            resizeInfoStartWidth.value = infoPanelWidth.value;

            document.addEventListener("mousemove", handleResizeInfo);
            document.addEventListener("mouseup", stopResizeInfo);
            document.body.style.cursor = "ew-resize";
            document.body.style.userSelect = "none";
        };

        const handleResizeInfo = (e) => {
            if (!isResizingInfo.value) return;
            const delta = resizeInfoStartX.value - e.clientX;
            const newWidth = resizeInfoStartWidth.value + delta;
            infoPanelWidth.value = Math.max(240, Math.min(900, newWidth));
        };

        const stopResizeInfo = () => {
            isResizingInfo.value = false;
            document.removeEventListener("mousemove", handleResizeInfo);
            document.removeEventListener("mouseup", stopResizeInfo);
            document.body.style.cursor = "";
            document.body.style.userSelect = "";
        };

        const handleResizeThread = (e) => {
            if (!isResizingThread.value) return;

            const delta = resizeStartX.value - e.clientX;
            const newWidth = resizeStartWidth.value + delta;

            // Min width: 300px, Max width: 800px
            threadPanelWidth.value = Math.max(300, Math.min(800, newWidth));
        };

        const stopResizeThread = () => {
            isResizingThread.value = false;
            document.removeEventListener("mousemove", handleResizeThread);
            document.removeEventListener("mouseup", stopResizeThread);
            document.body.style.cursor = "";
            document.body.style.userSelect = "";
        };

        const openThread = async (message) => {
            activeThreadMessage.value = message;
            threadPanelOpen.value = true;
            threadLoading.value = true;
            threadReplies.value = [];

            try {
                const { data } = await axios.get(
                    `/admin/chat/messages/${message.id}/thread`
                );

                if (data.parent_message) {
                    const mainMsg = messages.value.find(
                        (m) => m.id === message.id
                    );
                    if (mainMsg) {
                        mainMsg.thread_count = data.parent_message.thread_count;
                    }
                    activeThreadMessage.value = data.parent_message;
                }
                threadReplies.value = data.replies || [];

                nextTick(() => {
                    const container = document.querySelector(".thread-replies");
                    if (container) container.scrollTop = container.scrollHeight;
                });
            } catch (e) {
                if (import.meta.env.DEV)
                    console.error("Failed to load thread", e);
            } finally {
                threadLoading.value = false;
            }
        };

        // Ensure initial visibility for info panel follows server-driven hint
        onMounted(() => {
            userInfoOpen.value = showSidebar.value;
        });

        const closeThread = () => {
            threadPanelOpen.value = false;
            activeThreadMessage.value = null;
            threadReplies.value = [];
        };

        const handleThreadFiles = (event) => {
            const files = Array.from(event.target.files);
            threadReplyFiles.value = [...threadReplyFiles.value, ...files];
        };

        const removeThreadAttachment = (index) => {
            threadReplyFiles.value.splice(index, 1);
        };

        const sendThreadReply = async () => {
            if (
                !threadReplyInput.value.trim() &&
                !threadReplyFiles.value.length
            )
                return;
            if (!activeThreadMessage.value) {
                console.warn("No active thread message");
                return;
            }

            const formData = new FormData();
            if (threadReplyInput.value.trim()) {
                formData.append("body", threadReplyInput.value);
            }
            threadReplyFiles.value.forEach((file) => {
                formData.append("attachments[]", file);
            });

            try {
                const { data } = await axios.post(
                    `/admin/chat/messages/${activeThreadMessage.value.id}/thread/replies`,
                    formData,
                    { headers: { "Content-Type": "multipart/form-data" } }
                );

                if (data.success) {
                    threadReplies.value.push(data.reply);

                    if (activeThreadMessage.value) {
                        activeThreadMessage.value.thread_count =
                            data.parent_thread_count;
                    }
                    const mainMsg = messages.value.find(
                        (m) => m.id === activeThreadMessage.value.id
                    );
                    if (mainMsg) {
                        mainMsg.thread_count = data.parent_thread_count;
                    }

                    threadReplyInput.value = "";
                    threadReplyFiles.value = [];

                    nextTick(() => {
                        const container =
                            document.querySelector(".thread-replies");
                        if (container)
                            container.scrollTop = container.scrollHeight;
                    });
                }
            } catch (e) {
                if (import.meta.env.DEV)
                    console.error("Failed to send thread reply", e);
                window.showToast?.("Failed to send reply");
            }
        };

        // Desktop notify when user is away from the chat or tab is unfocused
        const notifyIfBackground = (channelId, senderName, channelName) => {
            if (typeof window === "undefined") return;
            const isActiveChannel = currentChannel.value?.id === channelId;
            const isVisible = document.visibilityState === "visible";
            const hasFocus =
                typeof document.hasFocus === "function"
                    ? document.hasFocus()
                    : true;

            // Suppress if user is on this channel and looking at the tab
            if (isActiveChannel && isVisible && hasFocus) return;
            if (!("Notification" in window)) return;

            const title = senderName || "New message";
            const body = channelName
                ? `In ${channelName}`
                : "You have a new message";

            try {
                const show = () => {
                    new Notification(title, { body, tag: "chat-notification" });
                    playNotificationSound();
                };
                if (Notification.permission === "granted") {
                    show();
                } else if (Notification.permission === "default") {
                    Notification.requestPermission().then((perm) => {
                        if (perm === "granted") show();
                    });
                }
            } catch (_) {}
        };

        // Play notification sound
        const playNotificationSound = () => {
            try {
                const audioContext = new (window.AudioContext ||
                    window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gain = audioContext.createGain();

                oscillator.connect(gain);
                gain.connect(audioContext.destination);

                oscillator.frequency.value = 800; // 800 Hz tone
                oscillator.type = "sine";

                gain.gain.setValueAtTime(0.3, audioContext.currentTime);
                gain.gain.exponentialRampToValueAtTime(
                    0.01,
                    audioContext.currentTime + 0.3
                );

                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.3);
            } catch (_) {
                // Fallback: try audio element if Web Audio API fails
                try {
                    const beep = new Audio(
                        "data:audio/wav;base64,UklGRiYAAABXQVZFZm10IBAAAAABAAEAQB8AAAA="
                    );
                    beep.play().catch(() => {});
                } catch (_) {}
            }
        };

        // WebSocket Setup
        const setupChannelListeners = (channelId) => {
            if (!channelId) return;
            if (!window.Echo) {
                console.warn("Echo is not defined, skipping WebSocket setup");
                return;
            }

            try {
                return window.Echo.private(`chat.channel.${channelId}`)
                    .listen("MessageSent", (e) => {
                        if (import.meta.env.DEV)
                            console.log("Broadcasting Event Received:", e);
                        if (e?.message?.sender_id === props.userId) return;

                        // Handle Thread Replies
                        if (e.message.reply_to_id) {
                            if (import.meta.env.DEV)
                                console.log("Thread reply detected", {
                                    msgReplyId: e.message.reply_to_id,
                                    activeId: activeThreadMessage.value?.id,
                                });
                            // 1. If looking at this thread, add it
                            if (
                                activeThreadMessage.value?.id ==
                                e.message.reply_to_id
                            ) {
                                threadReplies.value.push({
                                    ...e.message,
                                    attachments: Array.isArray(
                                        e.message.attachments
                                    )
                                        ? e.message.attachments
                                        : [],
                                });
                                nextTick(() => {
                                    const container =
                                        document.querySelector(
                                            ".thread-replies"
                                        );
                                    if (container)
                                        container.scrollTop =
                                            container.scrollHeight;
                                });
                            }

                            // 2. Update parent count in main list
                            const parent = messages.value.find(
                                (m) => m.id === e.message.reply_to_id
                            );
                            if (parent) {
                                parent.thread_count =
                                    (parent.thread_count || 0) + 1;
                            }

                            // Do NOT add to main list
                            return;
                        }

                        messages.value.push({
                            ...e.message,
                            attachments: Array.isArray(e.message.attachments)
                                ? e.message.attachments
                                : [],
                            reads: Array.isArray(e.message.reads)
                                ? e.message.reads
                                : [],
                        });
                        messages.value.sort(
                            (a, b) =>
                                new Date(a.created_at) - new Date(b.created_at)
                        );
                        updatePreview(channelId, e.message);

                        const senderName =
                            e?.message?.sender?.name || "New message";
                        const channelName =
                            channels.value.find((c) => c.id === channelId)
                                ?.name ||
                            e?.message?.channel?.name ||
                            "";
                        notifyIfBackground(channelId, senderName, channelName);

                        const hasAttachments =
                            Array.isArray(e.message.attachments) &&
                            e.message.attachments.length > 0;
                        const hasLinks =
                            typeof e.message.body === "string" &&
                            /\bhttps?:\/\/[^\s<>()"']+/i.test(e.message.body);
                        if (hasAttachments || hasLinks) {
                            loadSidebar(channelId);
                        }
                        scrollToBottom();
                    })
                    .listenForWhisper("typing", (e) => {
                        if (!e || e.userId === props.userId) return;
                        typingUsers.value[e.userId] = {
                            name: e.name || "Someone",
                            at: Date.now(),
                        };
                    })
                    .listen("MessagesRead", (e) => {
                        messages.value = messages.value.map((message) => {
                            if (
                                !message.reads.some(
                                    (read) => read.user_id === e.userId
                                )
                            ) {
                                message.reads.push({
                                    user_id: e.userId,
                                    read_at: new Date(),
                                });
                            }
                            return message;
                        });
                    });
            } catch (e) {
                console.error("WebSocket setup failed", e);
            }
        };

        // Setup global listeners for ALL channels (for unread counts and notifications)
        const setupGlobalChannelListeners = () => {
            if (!window.Echo) {
                console.warn("[Chat] Echo not available for global listeners");
                return;
            }

            console.log(
                "[Chat] Setting up global listeners for",
                channels.value.length,
                "channels"
            );

            channels.value.forEach((channel) => {
                // Skip the current channel - it's already handled by setupChannelListeners
                if (
                    currentChannel.value &&
                    channel.id === currentChannel.value.id
                ) {
                    console.log(
                        "[Chat] Skipping current channel:",
                        channel.name
                    );
                    return;
                }

                try {
                    window.Echo.private(`chat.channel.${channel.id}`).listen(
                        "MessageSent",
                        (e) => {
                            if (!e || !e.message) return;
                            if (e.message.sender_id === props.userId) return;

                            // If this is NOT the current channel
                            if (currentChannel.value?.id !== channel.id) {
                                console.log(
                                    "[Chat] Message received in other channel:",
                                    channel.name
                                );

                                // Increment unread count
                                const ch = channels.value.find(
                                    (c) => c.id === channel.id
                                );
                                if (ch) {
                                    ch.unread_messages_count =
                                        (ch.unread_messages_count || 0) + 1;
                                    console.log(
                                        "[Chat] Unread count for",
                                        ch.name,
                                        ":",
                                        ch.unread_messages_count
                                    );
                                }

                                // Update preview
                                updatePreview(channel.id, e.message);

                                // Show in-app notification
                                const senderName =
                                    e.message.sender?.name || "Someone";
                                const channelName = channel.name;
                                const isPersonal = channel.type === "personal";

                                let notificationMessage;
                                if (isPersonal) {
                                    notificationMessage = `${senderName} messaged you`;
                                } else {
                                    notificationMessage = `New message in ${channelName}`;
                                }

                                console.log(
                                    "[Chat] Showing notification:",
                                    notificationMessage
                                );

                                if (typeof window.showToast === "function") {
                                    window.showToast(notificationMessage);
                                }

                                // Play sound
                                playNotificationSound();
                            }
                        }
                    );
                    console.log(
                        "[Chat] Listener setup for channel:",
                        channel.name
                    );
                } catch (err) {
                    console.error(
                        `Failed to setup listener for channel ${channel.id}`,
                        err
                    );
                }
            });
        };

        // Lifecycle Hooks
        onMounted(() => {
            loadChannels();
            checkCreateCapability();

            try {
                if (window.Echo) {
                    const notificationChannel = window.Echo.private(
                        `admin.notifications.${props.userId}`
                    );
                    notificationChannel.listen(
                        "ChannelMembershipChanged",
                        (e) => {
                            if (!e || !e.channelId || !e.action) return;
                            if (e.action === "removed") {
                                if (currentChannel.value?.id === e.channelId) {
                                    try {
                                        window.Echo.leave(
                                            `chat.channel.${e.channelId}`
                                        );
                                    } catch (_) {}
                                    currentChannel.value = null;
                                }
                                channels.value = channels.value.filter(
                                    (c) => c.id !== e.channelId
                                );
                                window.showToast?.(
                                    "You were removed from a channel"
                                );
                            } else if (e.action === "added") {
                                loadChannels();
                            }
                        }
                    );
                }
            } catch (_) {}

            if (currentChannel.value?.id) {
                setupChannelListeners(currentChannel.value.id);
                loadSidebar(currentChannel.value.id);
            }

            if (messageContainer.value) {
                messageContainer.value.addEventListener(
                    "scroll",
                    onScrollMessages
                );
            }
        });

        onBeforeUnmount(() => {
            if (messageContainer.value) {
                messageContainer.value.removeEventListener(
                    "scroll",
                    onScrollMessages
                );
            }
        });

        // Adjust messages container bottom padding dynamically based on input container height
        let _resizeObserver = null;
        const adjustMessagePadding = () => {
            const mc = messageContainer.value;
            const ic = inputContainer.value;
            if (!mc || !ic) return;
            // Use offsetHeight to include padding and borders
            const height = ic.offsetHeight || ic.getBoundingClientRect().height;
            // Add a small extra gap so messages don't touch the input
            const gap = 16;
            mc.style.paddingBottom = `${height + gap}px`;
        };

        onMounted(() => {
            // existing onMounted logic above will run; ensure resize observer and initial adjust
            try {
                if (window.ResizeObserver && inputContainer.value) {
                    _resizeObserver = new ResizeObserver(() => {
                        nextTick(adjustMessagePadding);
                    });
                    _resizeObserver.observe(inputContainer.value);
                }
            } catch (_) {}

            // adjust on window resize as well
            window.addEventListener("resize", adjustMessagePadding);
            // initial adjust after DOM settled
            nextTick(adjustMessagePadding);
        });

        onBeforeUnmount(() => {
            try {
                if (_resizeObserver && inputContainer.value) {
                    _resizeObserver.unobserve(inputContainer.value);
                    _resizeObserver.disconnect();
                }
            } catch (_) {}
            window.removeEventListener("resize", adjustMessagePadding);
        });

        // Watch attachment changes / reply changes to adjust layout
        watch(attachmentFiles, () => nextTick(adjustMessagePadding), {
            deep: true,
        });
        watch(replyTo, () => nextTick(adjustMessagePadding));

        watch(currentChannel, (newChannel, oldChannel) => {
            if (oldChannel?.id && window.Echo) {
                try {
                    window.Echo.leave(`chat.channel.${oldChannel.id}`);
                } catch (_) {}
            }
            if (newChannel?.id) {
                setupChannelListeners(newChannel.id);
            }
        });

        watch(
            channels,
            (list) => {
                if (
                    !currentChannel.value &&
                    Array.isArray(list) &&
                    list.length
                ) {
                    // Try to restore the last selected channel from localStorage
                    let channelToSelect = null;
                    try {
                        const savedId = localStorage.getItem(
                            "chat_current_channel_id"
                        );
                        if (savedId) {
                            channelToSelect = list.find(
                                (c) => String(c.id) === savedId
                            );
                        }
                    } catch (e) {
                        // Ignore localStorage errors
                    }

                    // If no saved channel found, or saved channel doesn't exist anymore, use first one
                    selectChannel(channelToSelect || list[0]);
                }
            },
            { deep: false }
        );

        return {
            channels,
            currentChannel,
            messages,
            lastMessagePreview,
            searchQuery,
            searchResults,
            newMessage,
            attachmentFiles,
            messageContainer,
            inputContainer,
            messageInput,
            replyTo,
            showScrollDown,
            groupChannels,
            personalChannels,
            showSidebar,
            chatMainStyle,
            infoSidebarStyle,
            channelInfo,
            sidebarImages,
            sidebarFiles,
            sidebarLinks,
            openPanel,
            manageOpen,
            membersLoading,
            members,
            memberIds,
            memberSearch,
            canCreateChannel,
            createOpen,
            createName,
            createDescription,
            createSearch,
            createMemberIds,
            typingLabel,
            canSendMessage,
            isSuperAdmin,
            isPersonalChannel,
            showAboutSection,
            showMembersSection,
            isGroupChannel,
            avatarInitials,
            formatDate,
            formatFullDate,
            isImage,
            getAttachmentUrl,
            storageUrl,
            storageThumbUrl,
            downloadAttachment,
            openAttachment,
            selectChannel,
            sendMessage,
            handleFiles,
            removeAttachment,
            handleTyping,
            // mentions
            mentionOpen,
            mentionItems,
            mentionIndex,
            onEditorInput,
            onKeyDownInEditor,
            pickMention,
            formatMessageWithMentions,
            // replies
            resolveReply,
            replyToMessage,
            debounceSearch,
            clearSearch,
            scrollToMessage,
            scrollToMessageById,
            scrollToBottom,
            shouldShowDateSeparator,
            dayLabel,
            readByOthers,
            togglePanel,
            openManageMembers,
            toggleMember,
            saveMembers,
            deleteChannel,
            openCreateChannel,
            toggleCreateMember,
            saveCreateChannel,
            startDirect,
            // Thread Logic
            threadPanelOpen,
            activeThreadMessage,
            threadReplies,
            threadReplyInput,
            threadLoading,
            threadReplyFiles,
            threadPanelWidth,
            startResizeThread,
            // Info panel controls
            userInfoOpen,
            infoPanelWidth,
            startResizeInfo,
            handleResizeInfo,
            stopResizeInfo,
            openThread,
            closeThread,
            sendThreadReply,
            handleThreadFiles,
            removeThreadAttachment,
            // PDF Viewer Modal (Google Docs Viewer)
            pdfModalOpen,
            currentPdfFilename,
            pdfLoading,
            pdfViewerUrl,
            isPdf,
            openPdfModal,
            closePdfModal,
            onPdfIframeLoad,
            downloadCurrentPdf,
            // Image Lightbox
            imageLightboxOpen,
            currentLightboxImage,
            openImageLightbox,
            closeImageLightbox,
            downloadLightboxImage,
        };
    },
};
</script>

<style scoped>
/* CSS Variables */
.chat-container {
    --primary: #6366f1;
    --primary-dark: #4f46e5;
    --primary-light: #eef2ff;
    --success: #10b981;
    --danger: #ef4444;
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-800: #1f2937;
    --gray-900: #111827;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);

    display: flex;
    height: 100%;
    background: white;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,
        sans-serif;
    overflow: hidden;
}

/* Channels Sidebar */
.channels-sidebar {
    width: 320px;
    background: white;
    border-right: 2px solid var(--gray-200);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.sidebar-header {
    padding: 1.25rem;
    border-bottom: 2px solid var(--gray-200);
    background: linear-gradient(180deg, #fff 0%, var(--gray-50) 100%);
}

.search-wrapper {
    position: relative;
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray-400);
    font-size: 0.875rem;
    pointer-events: none;
}

.search-input {
    width: 100%;
    padding: 0.625rem 1rem 0.625rem 2.5rem;
    border: 2px solid var(--gray-200);
    border-radius: 12px;
    font-size: 0.875rem;
    transition: all 0.2s;
    background: white;
}

.search-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 4px var(--primary-light);
}

.channels-scroll {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
}

.channel-section {
    padding: 0.75rem 0;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1.25rem;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--gray-500);
}

.section-header i {
    font-size: 0.875rem;
}

.count-badge {
    margin-left: auto;
    background: var(--gray-200);
    color: var(--gray-600);
    padding: 0.125rem 0.5rem;
    border-radius: 999px;
    font-size: 0.75rem;
}

.channel-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.25rem;
    cursor: pointer;
    transition: all 0.2s;
    border-left: 3px solid transparent;
    position: relative;
}

.channel-item:hover {
    background: var(--gray-50);
}

.channel-item:hover .channel-delete-btn {
    opacity: 1;
    visibility: visible;
}

.channel-item.active {
    background: var(--primary-light);
    border-left-color: var(--primary);
}

.channel-delete-btn {
    opacity: 0;
    visibility: hidden;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    border: none;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
    margin-left: auto;
    flex-shrink: 0;
}

.channel-delete-btn:hover {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.35);
}

.channel-delete-btn:active {
    transform: translateY(0);
}

.channel-delete-btn i {
    font-size: 15px;
}

.channel-avatar {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1rem;
    flex-shrink: 0;
    box-shadow: var(--shadow-sm);
}

.channel-avatar.group {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
}

.channel-avatar.personal {
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    color: #1e40af;
}

.channel-info {
    flex: 1;
    min-width: 0;
}

.channel-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.25rem;
}

.channel-title {
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--gray-900);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.channel-time {
    font-size: 0.75rem;
    color: var(--gray-400);
    flex-shrink: 0;
}

.channel-preview-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
}

.channel-preview {
    font-size: 0.8125rem;
    color: var(--gray-500);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.unread-badge {
    background: var(--primary);
    color: white;
    padding: 0.125rem 0.5rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    flex-shrink: 0;
    box-shadow: var(--shadow-sm);
}

/* Main Chat Area */
/* .chat-main {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: linear-gradient(180deg, #fafbfc 0%, white 100%);
    flex-shrink: 1;
    min-width: 0;
} */

.chat-main {
    display: flex;
    flex-direction: column;
    height: 100%;
    overflow: hidden;
    background: linear-gradient(180deg, #fafbfc 0%, white 100%);
    flex-shrink: 1;
    min-width: 0;
}

.chat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem;
    border-bottom: 2px solid var(--gray-200);
    background: white;
    box-shadow: var(--shadow-sm);
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.header-avatar {
    position: relative;
}

.avatar-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    box-shadow: var(--shadow);
}

.avatar-icon.group {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    font-size: 1.25rem;
}

.avatar-icon.personal {
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    color: #1e40af;
    font-size: 1.125rem;
}

.status-dot {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
}

.status-dot.online {
    background: var(--success);
}

.header-info {
    display: flex;
    flex-direction: column;
}

.header-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--gray-900);
    margin: 0;
}

.header-subtitle {
    font-size: 0.8125rem;
    color: var(--gray-500);
    margin: 0.125rem 0 0;
}

.header-actions {
    display: flex;
    gap: 0.5rem;
}

.btn-icon-primary,
.btn-icon-secondary {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 1.125rem;
}

.btn-icon-primary {
    background: var(--primary);
    color: white;
    box-shadow: var(--shadow);
}

.btn-icon-primary:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.btn-icon-secondary {
    background: var(--gray-100);
    color: var(--gray-700);
}

.btn-icon-secondary:hover {
    background: var(--gray-200);
}

/* Messages Container */
.messages-container {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 1.5rem;
    padding-bottom: calc(1.5rem + 140px);
    position: relative;
}

/* Search Results */
.search-results-area {
    background: var(--primary-light);
    border-radius: 16px;
    padding: 1rem;
    margin-bottom: 1rem;
    border: 2px solid var(--primary);
}

.search-results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.results-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: var(--primary-dark);
}

.btn-clear {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: white;
    border: 2px solid var(--primary);
    border-radius: 8px;
    color: var(--primary);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-clear:hover {
    background: var(--primary);
    color: white;
}

.search-results-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.search-result-item {
    background: white;
    border-radius: 12px;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.2s;
    border: 2px solid transparent;
}

.search-result-item:hover {
    border-color: var(--primary);
    box-shadow: var(--shadow);
}

.result-channel {
    font-size: 0.75rem;
    color: var(--gray-500);
    margin-bottom: 0.5rem;
}

.result-content {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.result-text {
    color: var(--gray-700);
}

.result-time {
    font-size: 0.75rem;
    color: var(--gray-400);
    margin-top: 0.25rem;
}

/* Empty State */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    text-align: center;
    color: var(--gray-500);
}

.empty-icon {
    font-size: 4rem;
    color: var(--gray-300);
    margin-bottom: 1rem;
}

.empty-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--gray-700);
    margin: 0 0 0.5rem;
}

.empty-subtitle {
    font-size: 0.9375rem;
    color: var(--gray-500);
    margin: 0;
}

/* Messages List */
.messages-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.message-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.date-separator {
    display: flex;
    justify-content: center;
    margin: 1rem 0;
}

.date-label {
    background: var(--gray-200);
    color: var(--gray-600);
    padding: 0.375rem 1rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.message-group {
    display: flex;
    gap: 0.75rem;
    max-width: 65%;
    width: fit-content;
    animation: fadeIn 0.3s ease;
}

.message-group.own-message {
    margin-left: auto;
    flex-direction: row-reverse;
}

.message-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    color: #1e40af;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.875rem;
    flex-shrink: 0;
    box-shadow: var(--shadow-sm);
}

.message-content-wrapper {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    flex: 1;
    min-width: 0;
}

.message-sender {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--gray-600);
    padding-left: 0.75rem;
}

/* .message-bubble {
    background: linear-gradient(135deg, #a3ccff, #b9baff);
    border-radius: 16px;
    padding: 0.75rem 1rem;
    box-shadow: var(--shadow-sm);
    border: 2px solid var(--gray-200);
} */

.message-bubble {
    background: linear-gradient(135deg, #eef6ff, #f6f6fb);
    border-radius: 10px;
    padding: 6px 15px; /* <--- Reduces vertical space significantly */
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--gray-200);
    min-height: auto; /* Ensures it doesn't force a minimum height */
}

.own-message .message-bubble {
    background: linear-gradient(135deg, #eef6ff, #f6f6fb);
    color: black;
    border-color: transparent;
    box-shadow: var(--shadow);
}

.message-text {
    font-size: 0.9375rem;
    line-height: 1.5;
    word-wrap: break-word;
    white-space: pre-wrap;
}

:deep(.message-text .message-link) {
    border-radius: 2px;
    text-decoration: underline;
    cursor: pointer;
    transition: all 0.2s;
    word-break: break-all;
    /* background-color: #e0e7ff; */
    color: #4338ca !important;
    padding: 2px 6px;
    border-radius: 6px;
    font-weight: 600;
    display: inline-block;
    line-height: 1.2;
    margin: 0 1px;
    /* border: 1px solid #c7d2fe; */
}

:deep(.message-text .message-link:hover) {
    color: #1d4ed8;
    text-decoration: none;
    font-weight: 500;
}

:deep(.own-message .message-text .message-link) {
    color: #bfdbfe;
    text-decoration: underline;
}

:deep(.own-message .message-text .message-link:hover) {
    color: #1d4ed8;
    text-decoration: none;
    font-weight: 500;
}

.message-attachments {
    margin-top: 0.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.attachment-item {
    border-radius: 12px;
    overflow: hidden;
}

.attachment-image {
    max-width: 420px;
    max-height: min(480px, 60vh);
    width: auto;
    height: auto;
    display: block;
    object-fit: contain;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.15s ease;
    box-shadow: var(--shadow);
}

.attachment-image:hover {
    transform: scale(1.02);
    box-shadow: var(--shadow-lg);
}

.attachment-file {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    background: var(--gray-100);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.own-message .attachment-file {
    background: #ffffff;
    color: rgb(0, 0, 0);
}

.attachment-file:hover {
    background: var(--gray-200);
}

.own-message .attachment-file:hover {
    background: rgba(255, 255, 255, 0.3);
}

.message-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.375rem;
    font-size: 0.75rem;
    color: var(--gray-400);
}

.own-message .message-meta {
    color: black;
}

.message-time {
    font-weight: 500;
}

/* Time outside message bubble */
.message-time-outside {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.375rem;
    padding: 0 1rem;
    font-size: 0.75rem;
    color: var(--gray-500);
    font-weight: 500;
}

.own-message + .message-time-outside {
    justify-content: flex-end;
    color: var(--gray-600);
}

.message-status {
    display: flex;
    align-items: center;
}

.message-status i.read {
    color: #60a5fa;
}

/* Scroll to Bottom */
.scroll-to-bottom {
    position: absolute;
    bottom: 100px;
    right: 2rem;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--primary);
    color: white;
    border: none;
    box-shadow: var(--shadow-lg);
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.scroll-to-bottom:hover {
    background: var(--primary-dark);
    transform: scale(1.1);
}

/* Message Input */
.message-input-container {
    border-top: 2px solid var(--gray-200);
    padding: 1.25rem 1.5rem;
    background: white;
    /* Keep the input visible when scrolling messages on small screens */
    position: sticky;
    bottom: 0;
    z-index: 30;
}

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
    background: var(--gray-100);
    border-radius: 8px;
    font-size: 0.875rem;
    color: var(--gray-700);
}

.remove-attachment {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--gray-300);
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

.remove-attachment:hover {
    background: var(--danger);
}

.input-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--gray-50);
    border: 2px solid var(--gray-200);
    border-radius: 16px;
    padding: 0.5rem;
    box-shadow: var(--shadow-sm);
}

.btn-attach {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: transparent;
    border: none;
    color: var(--gray-600);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 1.15rem;
    flex-shrink: 0;
}

.btn-attach:hover {
    background: var(--gray-200);
    border-color: var(--gray-300);
}

.message-textarea {
    flex: 1;
    padding: 0.625rem 0.75rem;
    border: none;
    border-radius: 10px;
    font-size: 0.95rem;
    font-family: inherit;
    resize: none;
    min-height: 22px;
    max-height: 176px;
    overflow-y: auto;
    line-height: 22px;
    background: transparent;
}

.message-textarea:focus {
    outline: none;
}

/* Mentions and reply UI */
.input-with-suggestions {
    position: relative;
    flex: 1;
}

.input-with-suggestions.has-reply {
    padding-top: 64px;
}

.mention-popover {
    position: absolute;
    left: 0;
    bottom: 100%;
    margin-bottom: 6px;
    background: white;
    border: 2px solid var(--gray-200);
    border-radius: 12px;
    box-shadow: var(--shadow);
    width: 100%;
    max-height: 220px;
    overflow-y: auto;
    z-index: 20;
}

.mention-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    cursor: pointer;
}

.mention-item.active,
.mention-item:hover {
    background: var(--primary-light);
}

.mention-avatar {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: var(--gray-100);
    color: var(--gray-700);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

.mention-name {
    font-weight: 600;
}

.mention-email {
    font-size: 0.75rem;
    color: var(--gray-500);
}

:deep(.mention-token) {
    background-color: #e0e7ff; /* Light Purple Background */
    color: #4338ca; /* Dark Purple Text */
    padding: 2px 6px;
    border-radius: 6px; /* Rounded Corners */
    font-weight: 600;
    display: inline-block;
    line-height: 1.2;
    margin: 0 1px;
    border: 1px solid #c7d2fe;
}

:deep(.mention-token:hover) {
    background-color: #c7d2fe; /* Darker on hover */
    cursor: pointer;
}

.reply-chip {
    position: absolute;
    left: 8px;
    right: 8px;
    top: 6px;
    background: var(--gray-100);
    border: 1px solid var(--gray-200);
    border-radius: 10px;
    padding: 8px 36px 8px 12px;
    font-size: 0.8125rem;
    color: var(--gray-700);
}

.reply-title {
    font-weight: 700;
    margin-bottom: 2px;
}

.reply-preview {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.reply-cancel {
    position: absolute;
    right: 6px;
    top: 6px;
    border: none;
    background: transparent;
    cursor: pointer;
    color: var(--gray-600);
}

.reply-inline {
    display: flex;
    gap: 8px;
    align-items: flex-start;
    margin-bottom: 6px;
    transition: all 0.2s;
}

.reply-inline:hover {
    background: #e5e7eb !important;
    transform: translateX(2px);
}

.reply-inline-bar {
    width: 3px;
    background: var(--primary);
    border-radius: 2px;
}

.reply-inline-content {
    flex: 1;
    padding-left: 4px;
    padding-right: 8px;
}

.reply-inline-title {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--gray-600);
}

.reply-inline-text {
    font-size: 0.8125rem;
    color: var(--gray-600);
}

/* Message highlight animation when jumping to replied message */
.message-highlight {
    animation: highlightPulse 2s ease-in-out;
}

@keyframes highlightPulse {
    0%,
    100% {
        background: transparent;
    }
    25% {
        background: #fef3c7;
    }
    50% {
        background: #fef3c7;
    }
}

.meta-action {
    border: none;
    background: transparent;
    color: inherit;
    cursor: pointer;
    padding: 0 4px;
}

.btn-send {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    border: none;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 1.05rem;
    flex-shrink: 0;
    box-shadow: var(--shadow);
}

.btn-send:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.btn-send:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Info Sidebar */
.info-sidebar {
    width: 320px;
    border-left: 2px solid var(--gray-200);
    background: white;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
}

.info-profile {
    padding: 2rem 1.5rem;
    text-align: center;
    border-bottom: 2px solid var(--gray-200);
    background: linear-gradient(180deg, var(--primary-light) 0%, white 100%);
    position: relative;
}

.profile-avatar-large {
    display: flex;
    justify-content: center;
    margin-bottom: 1rem;
}

.avatar-large {
    width: 80px;
    height: 80px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.75rem;
    box-shadow: var(--shadow-lg);
}

.avatar-large.group {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
}

.avatar-large.personal {
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    color: #1e40af;
}

/* Close button inside info panel */
.info-close-btn {
    position: absolute;
    left: 14px;
    top: 14px;
    width: 38px;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
    border: 1px solid rgba(226, 232, 240, 0.7);
    cursor: pointer;
}

.info-close-btn i {
    font-size: 0.95rem;
    color: #374151;
}

.profile-name {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--gray-900);
    margin: 0 0 0.25rem;
}

.profile-type {
    font-size: 0.875rem;
    color: var(--gray-500);
    margin: 0;
}

.info-sections {
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.info-section {
    background: white;
    border: 2px solid var(--gray-200);
    border-radius: 12px;
    overflow: hidden;
}

.section-toggle {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    background: transparent;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    font-weight: 600;
    color: var(--gray-900);
}

.section-toggle:hover {
    background: var(--gray-50);
}

.section-header-content {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-content {
    padding: 1rem;
    border-top: 2px solid var(--gray-200);
    background: var(--gray-50);
}

.info-item {
    margin-bottom: 1rem;
}

.info-item:last-child {
    margin-bottom: 0;
}

.info-item label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--gray-500);
    margin-bottom: 0.25rem;
}

.info-item p {
    font-size: 0.9375rem;
    color: var(--gray-700);
    margin: 0;
}

.member-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: white;
    border-radius: 8px;
    margin-bottom: 0.5rem;
}

.member-item:last-child {
    margin-bottom: 0;
}

.member-avatar {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    color: #1e40af;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.875rem;
    flex-shrink: 0;
    box-shadow: var(--shadow-sm);
}

.member-info {
    flex: 1;
    min-width: 0;
}

.member-name {
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--gray-900);
    margin: 0 0 0.125rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.member-email {
    font-size: 0.8125rem;
    color: var(--gray-500);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.media-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem;
}

.media-item {
    aspect-ratio: 1;
    border-radius: 8px;
    overflow: hidden;
    display: block;
    transition: all 0.2s;
    box-shadow: var(--shadow-sm);
}

.media-item:hover {
    transform: scale(1.05);
    box-shadow: var(--shadow);
}

.media-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.file-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: white;
    border-radius: 8px;
    margin-bottom: 0.5rem;
    color: var(--gray-700);
    text-decoration: none;
    transition: all 0.2s;
}

.file-link:last-child {
    margin-bottom: 0;
}

.file-link:hover {
    background: var(--primary-light);
    color: var(--primary);
}

.file-link i {
    font-size: 1.25rem;
    color: var(--gray-400);
}

.file-link:hover i {
    color: var(--primary);
}

.file-link span {
    flex: 1;
    font-size: 0.875rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.empty-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
    color: var(--gray-400);
}

.empty-section i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.empty-section p {
    font-size: 0.875rem;
    margin: 0;
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    backdrop-filter: blur(4px);
}

.modal-container {
    background: white;
    border-radius: 20px;
    width: 90%;
    max-width: 600px;
    max-height: 80vh;
    display: flex;
    flex-direction: column;
    box-shadow: var(--shadow-xl);
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem;
    border-bottom: 2px solid var(--gray-200);
}

.modal-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--gray-900);
    margin: 0;
}

.modal-close {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: var(--gray-100);
    border: none;
    color: var(--gray-600);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 1rem;
}

.modal-close:hover {
    background: var(--gray-200);
    color: var(--gray-900);
}

.modal-body {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
}

.modal-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1.5rem;
    border-top: 2px solid var(--gray-200);
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--gray-700);
    margin-bottom: 0.5rem;
}

.form-input,
.form-textarea {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid var(--gray-200);
    border-radius: 12px;
    font-size: 0.9375rem;
    font-family: inherit;
    transition: all 0.2s;
}

.form-input:focus,
.form-textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 4px var(--primary-light);
}

.form-textarea {
    resize: vertical;
}

.members-list {
    max-height: 300px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.member-checkbox-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: var(--gray-50);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
    border: 2px solid transparent;
}

.member-checkbox-item:hover {
    background: var(--primary-light);
    border-color: var(--primary);
}

.member-checkbox {
    width: 20px;
    height: 20px;
    border-radius: 6px;
    cursor: pointer;
    flex-shrink: 0;
}

.member-avatar.small {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    color: #1e40af;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.member-details {
    flex: 1;
    min-width: 0;
}

.you-badge {
    display: inline-block;
    background: var(--primary);
    color: white;
    padding: 0.125rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-left: 0.5rem;
}

.btn-primary,
.btn-secondary {
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    border: none;
    font-size: 0.9375rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    box-shadow: var(--shadow);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.btn-secondary {
    background: var(--gray-100);
    color: var(--gray-700);
}

.btn-secondary:hover {
    background: var(--gray-200);
}

.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 1rem;
    color: var(--gray-500);
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid var(--gray-200);
    border-top-color: var(--primary);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin-bottom: 1rem;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(8px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* SweetAlert2 Custom Theme Styles */
:deep(.swal-theme-popup) {
    border-radius: 20px !important;
    box-shadow: 0 20px 60px rgba(99, 102, 241, 0.2),
        0 0 0 1px rgba(99, 102, 241, 0.1) !important;
    font-family: inherit !important;
    padding: 2rem !important;
    border: 1px solid rgba(99, 102, 241, 0.1) !important;
}

:deep(.swal-theme-title) {
    background: linear-gradient(135deg, #4f46e5, #6366f1) !important;
    -webkit-background-clip: text !important;
    -webkit-text-fill-color: transparent !important;
    background-clip: text !important;
    font-size: 1.75rem !important;
    font-weight: 700 !important;
    margin-bottom: 0.5rem !important;
}

:deep(.swal-theme-text) {
    color: #4b5563 !important;
    font-size: 1rem !important;
}

:deep(.swal-theme-icon) {
    border-width: 3px !important;
}

:deep(.swal-theme-confirm) {
    background: linear-gradient(135deg, #6366f1, #4f46e5) !important;
    border: none !important;
    border-radius: 10px !important;
    padding: 12px 32px !important;
    font-weight: 600 !important;
    font-size: 0.95rem !important;
    box-shadow: 0 4px 16px rgba(99, 102, 241, 0.4) !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

:deep(.swal-theme-confirm:hover) {
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 24px rgba(99, 102, 241, 0.5) !important;
}

:deep(.swal-theme-cancel) {
    border-radius: 10px !important;
    padding: 12px 32px !important;
    font-weight: 600 !important;
    font-size: 0.95rem !important;
    background: #ffffff !important;
    color: #ff0000 !important;
    border: 2px solid #ff0000 !important;
    transition: all 0.2s !important;
}

:deep(.swal-theme-cancel:hover) {
    background: #f9fafb !important;
    color: #ff0000 !important;
    border-color: #ff0000 !important;
    transform: translateY(-1px) !important;
}

:deep(.swal2-icon.swal2-warning) {
    border-color: #f59e0b !important;
    color: #f59e0b !important;
}

:deep(.swal2-icon.swal2-success) {
    border-color: #10b981 !important;
}

:deep(.swal2-icon.swal2-success [class^="swal2-success-line"]) {
    background-color: #10b981 !important;
}

:deep(.swal2-icon.swal2-error) {
    border-color: #ef4444 !important;
}

:deep(.swal2-icon.swal2-error [class^="swal2-x-mark-line"]) {
    background-color: #ef4444 !important;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .info-sidebar {
        display: none;
    }
}

@media (max-width: 768px) {
    .channels-sidebar {
        width: 280px;
    }

    .message-group {
        max-width: 85%;
    }

    .modal-container {
        width: 95%;
        max-height: 90vh;
    }
}

@media (max-width: 640px) {
    .channels-sidebar {
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        z-index: 100;
        transform: translateX(-100%);
        transition: transform 0.3s;
    }

    .channels-sidebar.show {
        transform: translateX(0);
    }
}

/* Thread Styles */
.thread-panel {
    display: flex;
    flex-direction: column;
    height: 100%;
    position: relative;
    min-width: 300px;
    max-width: 800px;
    background: white;
    border-left: 2px solid var(--gray-200);
    flex-shrink: 0;
}

.thread-resize-handle {
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 6px;
    cursor: ew-resize;
    background: transparent;
    z-index: 10;
    transition: background 0.2s;
}

.thread-resize-handle:hover {
    background: var(--primary);
}

.thread-resize-handle::before {
    content: "";
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    width: 2px;
    height: 40px;
    background: var(--gray-300);
    border-radius: 2px;
    opacity: 0;
    transition: opacity 0.2s;
}

.thread-resize-handle:hover::before {
    opacity: 1;
}

.thread-header {
    padding: 1rem;
    padding-left: 1.5rem; /* Extra padding for resize handle */
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
}
.thread-content {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
    padding-left: 1.5rem; /* Extra padding for resize handle */
    width: 100%;
}
.thread-divider {
    padding: 1rem;
    padding-left: 1.5rem;
    text-align: center;
    font-size: 0.8rem;
    color: var(--gray-500);
}
.thread-input-area {
    padding: 1rem;
    padding-left: 1.5rem; /* Extra padding for resize handle */
    border-top: 1px solid var(--gray-200);
    background: white;
    width: 100%;
}
.thread-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.25rem;
    cursor: pointer;
    background: var(--primary-light);
    padding: 0.25rem 0.5rem;
    border-radius: 8px;
    font-size: 0.75rem;
    color: var(--primary);
    width: fit-content;
    transition: all 0.2s;
}
.thread-indicator:hover {
    background: var(--primary);
    color: white;
}

/* Updated Thread Styles */
.thread-header-left {
    display: flex;
    flex-direction: column;
}

.thread-title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.thread-subtitle {
    font-size: 0.8rem;
    color: var(--gray-500);
}

.thread-parent-message {
    padding: 1rem;
    background-color: var(--gray-50);
    border-bottom: 1px solid var(--gray-200);
    display: flex; /* Ensure flex for avatar alignment */
    gap: 1rem;
}

.thread-content .message-attachments {
    max-width: 100%;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    align-items: flex-start;
}

.thread-content .message-bubble {
    padding: 0;
    background: transparent;
    border: none;
    box-shadow: none;
    border-radius: 0;
    width: 100%;
}

.thread-content .attachment-image {
    width: 100%;
    max-width: 100%;
    max-height: min(480px, 60vh);
    height: auto;
    object-fit: contain;
    display: block;
    margin: 0 auto;
}

.thread-content .attachment-item {
    padding: 0;
    background: transparent;
    border: none;
    box-shadow: none;
}

.thread-replies {
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.thread-divider {
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 1rem 0;
    position: relative;
    color: var(--gray-500);
    font-size: 0.8rem;
}

.thread-divider::before,
.thread-divider::after {
    content: "";
    flex: 1;
    border-top: 1px solid var(--gray-200);
    margin: 0 1rem;
}

.thread-input-area {
    padding: 1rem;
    border-top: 1px solid var(--gray-200);
    background: white;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
}

.input-row {
    display: flex;
    align-items: center; /* Center align for single line */
    gap: 0.75rem;
    background: var(--gray-50);
    padding: 0.5rem;
    border-radius: 12px;
    border: 1px solid var(--gray-200);
}

.input-row:focus-within {
    border-color: var(--primary-light);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.thread-textarea {
    flex: 1;
    border: none;
    background: transparent;
    resize: none;
    padding: 0.5rem;
    font-family: inherit;
    font-size: 0.95rem;
    max-height: 100px;
}

.thread-textarea:focus {
    outline: none;
}

.btn-attach-thread {
    background: none;
    border: none;
    color: var(--gray-500);
    font-size: 1.2rem;
    cursor: pointer;
    padding: 0 0.5rem;
    transition: color 0.2s;
    height: 40px; /* Match button height */
    display: flex;
    align-items: center;
}

.btn-attach-thread:hover {
    color: var(--primary);
}

.btn-send-thread {
    background: var(--primary);
    color: white;
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 8px; /* Square with rounded corners */
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    flex-shrink: 0;
}

.btn-send-thread:hover:not(:disabled) {
    background: var(--primary-dark);
}

.btn-send-thread:disabled {
    background: var(--gray-300);
    cursor: not-allowed;
    opacity: 0.7;
}

.message-sender .message-time {
    font-size: 0.7rem;
    color: var(--gray-400);
    margin-left: 0.5rem;
    font-weight: normal;
}

/* PDF Viewer Modal Styles */
.pdf-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.85);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.pdf-modal-container {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 1200px;
    height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.pdf-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--gray-200);
    background: var(--gray-50);
    border-radius: 16px 16px 0 0;
}

.pdf-modal-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 600;
    color: var(--gray-800);
    font-size: 1rem;
    overflow: hidden;
}

.pdf-modal-title i {
    color: #ef4444;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.pdf-modal-title span {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.pdf-modal-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.pdf-action-btn {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    border: none;
    background: white;
    color: var(--gray-600);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    font-size: 1rem;
}

.pdf-action-btn:hover {
    background: var(--gray-100);
    color: var(--primary);
}

.pdf-action-btn.close-btn:hover {
    background: #fee2e2;
    color: #ef4444;
}

.pdf-zoom-level {
    font-size: 0.75rem;
    color: var(--gray-500);
    min-width: 45px;
    text-align: center;
    font-weight: 500;
}

.pdf-modal-body {
    flex: 1;
    overflow: hidden;
    padding: 0;
    background: var(--gray-100);
    display: flex;
    flex-direction: column;
}

.pdf-iframe {
    width: 100%;
    height: 100%;
    flex: 1;
    border: none;
    background: white;
}

.pdf-loading,
.pdf-error {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    padding: 2rem;
    text-align: center;
}

.pdf-loading .spinner {
    width: 48px;
    height: 48px;
    border: 4px solid var(--gray-200);
    border-top-color: var(--primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.pdf-loading p {
    color: var(--gray-500);
    font-size: 0.95rem;
}

.pdf-error i {
    font-size: 3rem;
    color: #f59e0b;
}

.pdf-error p {
    color: var(--gray-600);
    font-size: 0.95rem;
    max-width: 300px;
}

.pdf-canvas-wrapper {
    display: flex;
    align-items: flex-start;
    justify-content: center;
    width: 100%;
    overflow: auto;
    max-height: calc(95vh - 150px);
}

.pdf-canvas-wrapper canvas {
    display: block;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    background: white;
}

.pdf-modal-footer {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    border-top: 1px solid var(--gray-200);
    background: var(--gray-50);
    border-radius: 0 0 16px 16px;
}

.pdf-nav-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    border: 1px solid var(--gray-300);
    background: white;
    color: var(--gray-700);
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s;
}

.pdf-nav-btn:hover:not(:disabled) {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
}

.pdf-nav-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pdf-page-info {
    font-size: 0.875rem;
    color: var(--gray-600);
    font-weight: 500;
    min-width: 120px;
    text-align: center;
}

/* PDF attachment styling in chat */
.attachment-pdf {
    cursor: pointer;
    transition: all 0.2s;
}

.attachment-pdf:hover {
    background: var(--primary-light) !important;
    border-color: var(--primary) !important;
}

.attachment-pdf i {
    color: #ef4444;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .pdf-modal-container {
        max-width: 100%;
        max-height: 100vh;
        border-radius: 0;
    }

    .pdf-modal-header,
    .pdf-modal-footer {
        border-radius: 0;
    }

    .pdf-modal-title span {
        max-width: 150px;
    }

    .pdf-zoom-level {
        display: none;
    }
}

/* Image Lightbox Modal */
.image-lightbox-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.92);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.2s ease;
}

.lightbox-close,
.lightbox-download {
    position: absolute;
    top: 20px;
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border: none;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    transition: all 0.2s;
    z-index: 10001;
}

.lightbox-close {
    right: 20px;
}

.lightbox-download {
    right: 80px;
}

.lightbox-close:hover,
.lightbox-download:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: scale(1.1);
}

.lightbox-image {
    max-width: 90%;
    max-height: 90vh;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
}
</style>
