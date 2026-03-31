<template>
    <div class="chat-container">
        <!-- Channel List Sidebar -->
        <div class="channels-sidebar" :class="{ show: mobileSidebarOpen }">
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
        <div
            v-if="isMobile && mobileSidebarOpen"
            class="mobile-sidebar-overlay"
            @click="closeChannelList"
        ></div>

        <!-- Main Chat Area -->
        <div
            class="chat-main"
            :class="{ 'sidebar-open-mobile': isMobile && mobileSidebarOpen }"
            v-if="currentChannel"
            :style="chatMainStyle"
            @click="closeMenus"
            @dragenter="onDragEnter"
            @dragover="onDragOver"
            @dragleave="onDragLeave"
            @drop="onDrop"
        >
            <!-- Chat Header -->
            <div class="chat-header">
                <div class="header-left">
                    <button
                        v-if="isMobile"
                        class="btn-icon-secondary mobile-channels-toggle"
                        @click="openChannelList"
                        title="Open conversations"
                    >
                        <i class="bi bi-list"></i>
                    </button>
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
                        v-if="isSuperAdmin && !isMobile"
                        class="btn-icon-primary"
                        @click="openCreateChannel"
                        title="New Channel"
                    >
                        <i class="bi bi-plus-lg"></i>
                    </button>
                    <button
                        v-if="isSuperAdmin && !isMobile"
                        class="btn-icon-secondary"
                        @click="openManageMembers"
                        title="Manage Members"
                    >
                        <i class="bi bi-people"></i>
                    </button>
                    <button
                        class="btn-icon-secondary"
                        @click="toggleSavedPanel"
                        :class="{ active: showSavedPanel }"
                        title="Saved Messages"
                    >
                        <i class="bi bi-bookmark"></i>
                    </button>
                    <button
                        class="btn-icon-secondary"
                        @click="togglePinnedPanel"
                        :class="{ active: showPinnedPanel }"
                        title="Pinned Messages"
                    >
                        <i class="bi bi-pin-angle"></i>
                        <span v-if="pinnedMessages.length" class="pin-count">{{
                            pinnedMessages.length
                        }}</span>
                    </button>
                    <!-- Toggle info sidebar -->
                    <button
                        class="btn-icon-secondary info-toggle-btn"
                        :class="{ active: userInfoOpen }"
                        @click="userInfoOpen = !userInfoOpen"
                        title="Toggle Info"
                    >
                        <i class="bi bi-info-circle"></i>
                    </button>
                </div>
            </div>

            <div v-if="isDragging" class="drag-overlay">
                <div class="drag-overlay-content">
                    <i class="bi bi-cloud-arrow-up"></i>
                    <span>Drop files to attach</span>
                </div>
            </div>

            <!-- Messages Area -->
            <div v-if="showPinnedPanel" class="pinned-panel">
                <div class="pinned-header">
                    <i class="bi bi-pin-angle-fill"></i>
                    <span
                        >{{ pinnedMessages.length }} Pinned Message{{
                            pinnedMessages.length === 1 ? "" : "s"
                        }}</span
                    >
                    <button @click="showPinnedPanel = false" class="panel-close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div class="pinned-list">
                    <div v-if="!pinnedMessages.length" class="pinned-empty">
                        No pinned messages in this channel
                    </div>
                    <div
                        v-for="pin in pinnedMessages"
                        :key="`pin-${pin.id}`"
                        class="pinned-item"
                        @click="scrollToMessageById(pin.id)"
                    >
                        <div class="pinned-sender">{{ pin.sender }}</div>
                        <div class="pinned-body">
                            {{ pin.body?.slice(0, 100) || "Attachment" }}
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="showSavedPanel" class="pinned-panel saved-panel">
                <div class="pinned-header">
                    <i class="bi bi-bookmark-fill"></i>
                    <span>Saved Messages</span>
                    <button @click="showSavedPanel = false" class="panel-close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div class="pinned-list">
                    <div v-if="!savedMessages.length" class="pinned-empty">
                        No saved messages yet
                    </div>
                    <div
                        v-for="saved in savedMessages"
                        :key="`saved-${saved.id}`"
                        class="pinned-item"
                        @click="jumpToSavedMessage(saved)"
                    >
                        <div class="pinned-sender">
                            {{ saved.sender }} · {{ saved.channel_name }}
                        </div>
                        <div class="pinned-body">
                            {{ saved.body?.slice(0, 100) || "Attachment" }}
                        </div>
                    </div>
                </div>
            </div>

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
                        <!-- Load older messages -->
                        <div v-if="hasMoreMessages" class="load-older-messages">
                            <button
                                class="btn-load-older"
                                :disabled="loadingMessages"
                                @click="loadOlderMessages"
                            >
                                {{
                                    loadingMessages
                                        ? "Loading..."
                                        : "Load older messages"
                                }}
                            </button>
                        </div>

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
                                            @click="
                                                scrollToMessageById(
                                                    message.metadata
                                                        .reply_to_id,
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
                                                            message,
                                                        )?.body?.slice(
                                                            0,
                                                            100,
                                                        ) ||
                                                        "Attachment"
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Text Content -->
                                        <div
                                            v-if="hasRenderableMessageBody(message)"
                                            class="message-text"
                                            v-html="
                                                formatMessageWithMentions(
                                                    message,
                                                )
                                            "
                                        ></div>

                                        <div
                                            v-if="getOrderReferences(message).length"
                                            class="order-reference-stack"
                                        >
                                            <button
                                                v-for="orderRef in getOrderReferences(message)"
                                                :key="`${message.id}-${orderRef.id}`"
                                                type="button"
                                                class="order-reference-card"
                                                :class="{
                                                    'order-reference-card--missing':
                                                        !orderRef.order_url,
                                                }"
                                                :disabled="!orderRef.order_url"
                                                @click="openOrderReference(orderRef)"
                                            >
                                                <div class="order-reference-header">
                                                    <div class="order-reference-title">
                                                        <span class="order-reference-number">
                                                            #{{
                                                                orderRef.display_number
                                                            }}
                                                        </span>
                                                        <span class="order-reference-label-text">
                                                            Order
                                                        </span>
                                                    </div>
                                                    <span
                                                        class="order-reference-status"
                                                        :class="`status-${orderRef.status_color || 'secondary'}`"
                                                    >
                                                        {{
                                                            orderRef.status_label ||
                                                            "Unknown"
                                                        }}
                                                    </span>
                                                </div>

                                                <div class="order-reference-body">
                                                    <div class="order-reference-row">
                                                        <div class="order-reference-row-icon">
                                                            <i class="bi bi-person-badge"></i>
                                                        </div>
                                                        <div class="order-reference-row-content">
                                                            <span class="order-reference-row-label">
                                                                Client
                                                            </span>
                                                            <span class="order-reference-row-value">
                                                                {{
                                                                    orderRef.exists
                                                                        ? orderRef.client_name ||
                                                                          "Unknown client"
                                                                        : "Order not found"
                                                                }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <div class="order-reference-row">
                                                        <div class="order-reference-row-icon">
                                                            <i class="bi bi-calendar3"></i>
                                                        </div>
                                                        <div class="order-reference-row-content">
                                                            <span class="order-reference-row-label">
                                                                Created
                                                            </span>
                                                            <span class="order-reference-row-value">
                                                                {{
                                                                    orderRef.created_at
                                                                        ? formatOrderCreatedAt(
                                                                              orderRef.created_at,
                                                                          )
                                                                        : "Not available"
                                                                }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <div
                                                        v-if="
                                                            orderRef.shipping_company_name ||
                                                            orderRef.tracking_number ||
                                                            orderRef.dispatch_date ||
                                                            orderRef.tracking_status
                                                        "
                                                        class="order-reference-row"
                                                    >
                                                        <div class="order-reference-row-icon">
                                                            <i class="bi bi-truck"></i>
                                                        </div>
                                                        <div class="order-reference-row-content">
                                                            <span class="order-reference-row-label">
                                                                Shipping
                                                            </span>
                                                            <span class="order-reference-row-value">
                                                                {{
                                                                    orderRef.shipping_company_name ||
                                                                    orderRef.tracking_status ||
                                                                    "Tracking ready"
                                                                }}
                                                            </span>
                                                            <span
                                                                v-if="
                                                                    orderRef.tracking_number
                                                                "
                                                                class="order-reference-row-subvalue"
                                                            >
                                                                Tracking:
                                                                {{
                                                                    orderRef.tracking_number
                                                                }}
                                                            </span>
                                                            <span
                                                                v-if="
                                                                    orderRef.dispatch_date
                                                                "
                                                                class="order-reference-row-subvalue"
                                                            >
                                                                Dispatch:
                                                                {{
                                                                    formatOrderCreatedAt(
                                                                        orderRef.dispatch_date,
                                                                    )
                                                                }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </button>
                                        </div>

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
                                                            attachment,
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

                                        <div
                                            v-if="message.is_pinned"
                                            class="pin-indicator"
                                        >
                                            <i class="bi bi-pin-angle-fill"></i>
                                            Pinned
                                        </div>
                                    </div>

                                    <div
                                        v-if="groupedReactions(message).length"
                                        class="reaction-bar"
                                    >
                                        <button
                                            v-for="group in groupedReactions(message)"
                                            :key="`${message.id}-${group.emoji}`"
                                            class="reaction-chip"
                                            :class="{ mine: group.my }"
                                            @click.stop="reactToMessage(message, group.emoji)"
                                        >
                                            {{ group.emoji }}
                                            <span>{{ group.count }}</span>
                                        </button>
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

                                    <div class="message-meta">
                                        <span class="message-time">
                                            {{ formatDate(message.created_at) }}
                                        </span>
                                        <button
                                            class="meta-action"
                                            title="Reply Quote"
                                            @click.stop="replyToMessage(message)"
                                        >
                                            <i class="bi bi-reply"></i>
                                        </button>

                                        <div class="message-actions-float">
                                            <button
                                                class="meta-action"
                                                title="React"
                                                @click.stop="
                                                    toggleReactionPicker(
                                                        message.id,
                                                    )
                                                "
                                            >
                                                <i
                                                    class="bi bi-emoji-smile"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeReactionPickerId ===
                                                    message.id
                                                "
                                                class="message-reaction-picker"
                                                @click.stop
                                            >
                                                <button
                                                    v-for="emoji in QUICK_EMOJIS"
                                                    :key="`reaction-picker-${message.id}-${emoji}`"
                                                    class="reaction-picker-emoji"
                                                    :title="emoji"
                                                    @click.stop="
                                                        reactAndClose(
                                                            message,
                                                            emoji,
                                                        )
                                                    "
                                                >
                                                    {{ emoji }}
                                                </button>
                                            </div>
                                            <!-- Thread Button — beside emoji reaction btn -->
                                            <button
                                                class="meta-action"
                                                title="Reply in Thread"
                                                @click.stop="openThread(message)"
                                            >
                                                <i class="bi bi-chat-text"></i>
                                            </button>
                                            <button
                                                class="meta-action"
                                                title="More actions"
                                                @click.stop="
                                                    toggleMessageMenu(message.id)
                                                "
                                            >
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeMessageMenuId ===
                                                    message.id
                                                "
                                                class="message-actions-menu"
                                                @click.stop
                                            >
                                                <button
                                                    class="message-actions-item"
                                                    @click.stop="
                                                        toggleSaveMessage(
                                                            message,
                                                        );
                                                        closeMenus();
                                                    "
                                                >
                                                    <i
                                                        :class="
                                                            message.is_saved
                                                                ? 'bi bi-bookmark-fill'
                                                                : 'bi bi-bookmark'
                                                        "
                                                    ></i>
                                                    {{
                                                        message.is_saved
                                                            ? "Unsave"
                                                            : "Save"
                                                    }}
                                                </button>
                                                <button
                                                    class="message-actions-item"
                                                    @click.stop="
                                                        message.is_pinned
                                                            ? unpinMessage(
                                                                  message,
                                                              )
                                                            : pinMessage(
                                                                  message,
                                                              );
                                                        closeMenus();
                                                    "
                                                >
                                                    <i
                                                        :class="
                                                            message.is_pinned
                                                                ? 'bi bi-pin-angle-fill'
                                                                : 'bi bi-pin-angle'
                                                        "
                                                    ></i>
                                                    {{
                                                        message.is_pinned
                                                            ? "Unpin"
                                                            : "Pin"
                                                    }}
                                                </button>
                                            </div>
                                        </div>

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
            <div
                class="message-input-container"
                ref="inputContainer"
                v-show="!(isMobile && userInfoOpen)"
            >
                <ChatInput
                    v-model="newMessage"
                    :placeholder="mobileInputPlaceholder"
                    :files="attachmentFiles"
                    :reply-to="replyTo"
                    :emoji-picker-open="showEmojiPicker"
                    :sending="isSending"
                    :can-send="canSendMessage"
                    :mention-open="mentionOpen"
                    :mention-items="mentionItems"
                    :mention-index="mentionIndex"
                    :order-suggest-open="orderSuggestOpen"
                    :order-suggest-items="orderSuggestItems"
                    :order-suggest-index="orderSuggestIndex"
                    textarea-ref="messageInput"
                    file-input-ref="fileInput"
                    @send="sendMessage"
                    @attach-files="handleFiles"
                    @remove-file="removeAttachment"
                    @toggle-emoji="showEmojiPicker = !showEmojiPicker"
                    @editor-input="onEditorInput"
                    @editor-keydown="onKeyDownInEditor"
                    @editor-paste="handlePaste"
                    @pick-mention="pickMention"
                    @pick-order="pickOrderSuggest"
                    @cancel-reply="replyTo = null"
                >
                    <template #emoji-picker>
                        <EmojiPicker :data="emojiData" @emoji-select="appendEmoji" />
                    </template>
                </ChatInput>
            </div>
        </div>

        <!-- Right Sidebar (Channel Info) -->
        <!-- Mobile Info Overlay Backdrop -->
        <div
            v-if="isMobile && userInfoOpen && !threadPanelOpen"
            class="mobile-info-overlay"
            @click="userInfoOpen = false"
        ></div>
        <!-- Right Sidebar (Channel Info or Thread) -->
        <div
            class="info-sidebar"
            v-if="shouldShowInfoSidebar"
            :class="{ 'is-mobile': isMobile }"
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
                                    v-if="
                                        hasRenderableMessageBody(
                                            activeThreadMessage,
                                        )
                                    "
                                    v-html="
                                        formatMessageWithMentions(
                                            activeThreadMessage,
                                        )
                                    "
                                    class="message-text"
                                ></div>
                            <div
                                v-if="
                                    getOrderReferences(activeThreadMessage)
                                        .length
                                "
                                class="order-reference-stack"
                            >
                                <button
                                    v-for="orderRef in getOrderReferences(
                                        activeThreadMessage,
                                    )"
                                    :key="`${activeThreadMessage.id}-${orderRef.id}`"
                                    type="button"
                                    class="order-reference-card"
                                    :class="{
                                        'order-reference-card--missing':
                                            !orderRef.order_url,
                                    }"
                                    :disabled="!orderRef.order_url"
                                    @click="openOrderReference(orderRef)"
                                >
                                    <div class="order-reference-header">
                                        <div class="order-reference-title">
                                            <span class="order-reference-number">
                                                #{{ orderRef.display_number }}
                                            </span>
                                            <span class="order-reference-label-text">
                                                Order
                                            </span>
                                        </div>
                                        <span
                                            class="order-reference-status"
                                            :class="`status-${orderRef.status_color || 'secondary'}`"
                                        >
                                            {{
                                                orderRef.status_label ||
                                                "Unknown"
                                            }}
                                        </span>
                                    </div>
                                    <div class="order-reference-body">
                                        <div class="order-reference-row">
                                            <div class="order-reference-row-icon">
                                                <i class="bi bi-person-badge"></i>
                                            </div>
                                            <div
                                                class="order-reference-row-content"
                                            >
                                                <span class="order-reference-row-label">
                                                    Client
                                                </span>
                                                <span class="order-reference-row-value">
                                                    {{
                                                        orderRef.exists
                                                            ? orderRef.client_name ||
                                                              "Unknown client"
                                                            : "Order not found"
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="order-reference-row">
                                            <div class="order-reference-row-icon">
                                                <i class="bi bi-calendar3"></i>
                                            </div>
                                            <div
                                                class="order-reference-row-content"
                                            >
                                                <span class="order-reference-row-label">
                                                    Created
                                                </span>
                                                <span class="order-reference-row-value">
                                                    {{
                                                        orderRef.created_at
                                                            ? formatOrderCreatedAt(
                                                                  orderRef.created_at,
                                                              )
                                                            : "Not available"
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                        <div
                                            v-if="
                                                orderRef.shipping_company_name ||
                                                orderRef.tracking_number ||
                                                orderRef.dispatch_date ||
                                                orderRef.tracking_status
                                            "
                                            class="order-reference-row"
                                        >
                                            <div class="order-reference-row-icon">
                                                <i class="bi bi-truck"></i>
                                            </div>
                                            <div
                                                class="order-reference-row-content"
                                            >
                                                <span class="order-reference-row-label">
                                                    Shipping
                                                </span>
                                                <span class="order-reference-row-value">
                                                    {{
                                                        orderRef.shipping_company_name ||
                                                        orderRef.tracking_status ||
                                                        "Tracking ready"
                                                    }}
                                                </span>
                                                <span
                                                    v-if="
                                                        orderRef.tracking_number
                                                    "
                                                    class="order-reference-row-subvalue"
                                                >
                                                    Tracking:
                                                    {{ orderRef.tracking_number }}
                                                </span>
                                                <span
                                                    v-if="
                                                        orderRef.dispatch_date
                                                    "
                                                    class="order-reference-row-subvalue"
                                                >
                                                    Dispatch:
                                                    {{
                                                        formatOrderCreatedAt(
                                                            orderRef.dispatch_date,
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </button>
                            </div>
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
                                                    '_blank',
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
                                        v-if="
                                            hasRenderableMessageBody(reply)
                                        "
                                        v-html="
                                            formatMessageWithMentions(reply)
                                        "
                                        class="message-text"
                                    ></div>

                                    <div
                                        v-if="getOrderReferences(reply).length"
                                        class="order-reference-stack"
                                    >
                                        <button
                                            v-for="orderRef in getOrderReferences(reply)"
                                            :key="`${reply.id}-${orderRef.id}`"
                                            type="button"
                                            class="order-reference-card"
                                            :class="{
                                                'order-reference-card--missing':
                                                    !orderRef.order_url,
                                            }"
                                            :disabled="!orderRef.order_url"
                                            @click="openOrderReference(orderRef)"
                                        >
                                            <div class="order-reference-header">
                                                <div class="order-reference-title">
                                                    <span class="order-reference-number">
                                                        #{{ orderRef.display_number }}
                                                    </span>
                                                    <span class="order-reference-label-text">
                                                        Order
                                                    </span>
                                                </div>
                                                <span
                                                    class="order-reference-status"
                                                    :class="`status-${orderRef.status_color || 'secondary'}`"
                                                >
                                                    {{
                                                        orderRef.status_label ||
                                                        "Unknown"
                                                    }}
                                                </span>
                                            </div>

                                            <div class="order-reference-body">
                                                <div class="order-reference-row">
                                                    <div class="order-reference-row-icon">
                                                        <i class="bi bi-person-badge"></i>
                                                    </div>
                                                    <div
                                                        class="order-reference-row-content"
                                                    >
                                                        <span class="order-reference-row-label">
                                                            Client
                                                        </span>
                                                        <span class="order-reference-row-value">
                                                            {{
                                                                orderRef.exists
                                                                    ? orderRef.client_name ||
                                                                      "Unknown client"
                                                                    : "Order not found"
                                                            }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="order-reference-row">
                                                    <div class="order-reference-row-icon">
                                                        <i class="bi bi-calendar3"></i>
                                                    </div>
                                                    <div
                                                        class="order-reference-row-content"
                                                    >
                                                        <span class="order-reference-row-label">
                                                            Created
                                                        </span>
                                                        <span class="order-reference-row-value">
                                                            {{
                                                                orderRef.created_at
                                                                    ? formatOrderCreatedAt(
                                                                          orderRef.created_at,
                                                                      )
                                                                    : "Not available"
                                                            }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div
                                                    v-if="
                                                        orderRef.shipping_company_name ||
                                                        orderRef.tracking_number ||
                                                        orderRef.dispatch_date ||
                                                        orderRef.tracking_status
                                                    "
                                                    class="order-reference-row"
                                                >
                                                    <div class="order-reference-row-icon">
                                                        <i class="bi bi-truck"></i>
                                                    </div>
                                                    <div
                                                        class="order-reference-row-content"
                                                    >
                                                        <span class="order-reference-row-label">
                                                            Shipping
                                                        </span>
                                                        <span class="order-reference-row-value">
                                                            {{
                                                                orderRef.shipping_company_name ||
                                                                orderRef.tracking_status ||
                                                                "Tracking ready"
                                                            }}
                                                        </span>
                                                        <span
                                                            v-if="
                                                                orderRef.tracking_number
                                                            "
                                                            class="order-reference-row-subvalue"
                                                        >
                                                            Tracking:
                                                            {{ orderRef.tracking_number }}
                                                        </span>
                                                        <span
                                                            v-if="
                                                                orderRef.dispatch_date
                                                            "
                                                            class="order-reference-row-subvalue"
                                                        >
                                                            Dispatch:
                                                            {{
                                                                formatOrderCreatedAt(
                                                                    orderRef.dispatch_date,
                                                                )
                                                            }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </button>
                                    </div>

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
                                                        '_blank',
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
                    <ChatInput
                        v-model="threadEditor.input.value"
                        placeholder="Reply to thread... Use @ to mention"
                        :files="threadReplyFiles"
                        :emoji-picker-open="threadEditor.emojiPickerOpen.value"
                        :sending="isSendingThread"
                        :can-send="!!threadEditor.input.value.trim() || threadReplyFiles.length > 0"
                        :mention-open="threadEditor.mentionOpen.value"
                        :mention-items="threadEditor.mentionItems.value"
                        :mention-index="threadEditor.mentionIndex.value"
                        :order-suggest-open="threadEditor.orderSuggestOpen.value"
                        :order-suggest-items="threadEditor.orderSuggestItems.value"
                        :order-suggest-index="threadEditor.orderSuggestIndex.value"
                        textarea-ref="threadInput"
                        file-input-ref="threadFileInput"
                        @send="sendThreadReply"
                        @attach-files="handleThreadFiles"
                        @remove-file="removeThreadAttachment"
                        @toggle-emoji="threadEditor.emojiPickerOpen.value = !threadEditor.emojiPickerOpen.value"
                        @editor-input="threadEditor.onInput"
                        @editor-keydown="threadEditor.onKeyDown"
                        @pick-mention="threadEditor.pickMention"
                        @pick-order="threadEditor.pickOrderSuggest"
                    >
                        <template #emoji-picker>
                            <EmojiPicker :data="emojiData" @emoji-select="threadEditor.appendEmoji" />
                        </template>
                    </ChatInput>
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
                                                channelInfo.created_at,
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
                            class="section-content members-section-content"
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
                                    .includes(memberSearch.toLowerCase()),
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
                    <button
                        @click="saveMembers"
                        class="btn-primary"
                        :disabled="isSavingMembers"
                    >
                        {{ isSavingMembers ? "Saving..." : "Save Changes" }}
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
                                    .includes(createSearch.toLowerCase()),
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
                    <button
                        @click="saveCreateChannel"
                        class="btn-primary"
                        :disabled="isCreatingChannel"
                    >
                        {{
                            isCreatingChannel ? "Creating..." : "Create Channel"
                        }}
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
    defineComponent,
    h,
} from "vue";
import axios from "axios";
import { format } from "date-fns";
import debounce from "lodash/debounce";
import DOMPurify from "dompurify";
import MediaGallery from "./MediaGallery.vue";
import ChatInput from "./ChatInput.vue";
import { Picker as EmojiMartPicker } from "emoji-mart";
import emojiData from "@emoji-mart/data";
import * as pdfjsLib from "pdfjs-dist";
import { useChatEditor } from "../composables/useChatEditor";

// Set PDF.js worker
pdfjsLib.GlobalWorkerOptions.workerSrc = new URL(
    "pdfjs-dist/build/pdf.worker.mjs",
    import.meta.url,
).toString();

const EmojiPicker = defineComponent({
    name: "EmojiPicker",
    props: {
        data: { type: [Object, Array], required: true },
    },
    emits: ["emoji-select"],
    mounted() {
        this.picker = new EmojiMartPicker({
            data: this.data,
            onEmojiSelect: (emoji) => this.$emit("emoji-select", emoji),
        });
        this.$el.appendChild(this.picker);
    },
    beforeUnmount() {
        if (this.picker && this.picker.remove) {
            this.picker.remove();
        }
    },
    render() {
        return h("div", { class: "emoji-picker-host" });
    },
});

export default {
    components: { MediaGallery, EmojiPicker, ChatInput },
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
        const isSendingThread = ref(false);
        // Mentions state
        const mentionOpen = ref(false);
        const mentionQuery = ref("");
        const mentionItems = ref([]);
        const mentionIndex = ref(0);
        const pendingMentionIds = ref(new Set());
        const messageContainer = ref(null);
        const messageInput = ref(null);
        const threadInput = ref(null);
        const inputContainer = ref(null);
        const page = ref(1);
        const hasMoreMessages = ref(true);
        const loadingMessages = ref(false);
        const showScrollDown = ref(false);
        const isDragging = ref(false);
        const activeMessageMenuId = ref(null);
        const activeReactionPickerId = ref(null);
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
        const showEmojiPicker = ref(false);
        const pinnedMessages = ref([]);
        const showPinnedPanel = ref(false);
        const savedMessages = ref([]);
        const showSavedPanel = ref(false);
        const orderSuggestOpen = ref(false);
        const orderSuggestQuery = ref("");
        const orderSuggestItems = ref([]);
        const orderSuggestIndex = ref(0);
        const QUICK_EMOJIS = ["👍", "❤️", "😂", "😮", "😢", "🔥", "✅", "👀"];
        const manageOpen = ref(false);
        const membersLoading = ref(false);
        const isSavingMembers = ref(false);
        const members = ref([]);
        const memberIds = ref([]);
        const memberSearch = ref("");
        const canCreateChannel = ref(false);
        const createOpen = ref(false);
        const isCreatingChannel = ref(false);
        const createName = ref("");
        const createDescription = ref("");
        const createSearch = ref("");
        const createMemberIds = ref([]);
        const openPanel = ref("info");
        const viewportWidth = ref(
            typeof window !== "undefined" ? window.innerWidth : 1280,
        );
        const isMobile = computed(() => viewportWidth.value <= 768);
        const mobileSidebarOpen = ref(false);
        const deepLinkChannelId = ref(null);
        const deepLinkMessageId = ref(null);
        const deepLinkOpenThread = ref(false);
        const deepLinkConsumed = ref(false);

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

        const formatOrderCreatedAt = (date) => {
            if (!date) return "";
            try {
                return format(new Date(date), "dd MMM yyyy, hh:mm a");
            } catch {
                return "";
            }
        };

        const getOrderReferences = (message) =>
            Array.isArray(message?.metadata?.order_refs)
                ? message.metadata.order_refs
                : [];

        const hasRenderableMessageBody = (message) => {
            const text = String(message?.body ?? "");
            if (!text.trim()) return false;

            const withoutRefs = text
                .replace(/(^|[^\w])#(\d+)\b/g, "$1")
                .replace(/[\s|,;:!?.()\-\u2013\u2014]+/g, " ")
                .trim();

            return withoutRefs.length > 0;
        };

        const openOrderReference = (orderRef) => {
            if (!orderRef?.order_url) return;
            window.location.href = orderRef.order_url;
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
        const leftSidebarWidth = computed(() => {
            if (isMobile.value) return 0;
            if (viewportWidth.value <= 1024) return 280;
            if (viewportWidth.value <= 1200) return 300;
            return SIDEBAR_WIDTH;
        });

        const groupChannels = computed(() =>
            channels.value.filter(isGroupChannel),
        );
        const personalChannels = computed(() =>
            channels.value.filter((c) => !isGroupChannel(c)),
        );
        const showSidebar = computed(() => {
            const hint = channelInfo.value?.show_sidebar;
            if (typeof hint === "boolean") return hint;
            return (
                (currentChannel.value?.type || "").toLowerCase() !== "public"
            );
        });
        const shouldShowInfoSidebar = computed(() => {
            if (!currentChannel.value) return false;
            if (threadPanelOpen.value) return true;
            return isMobile.value ? userInfoOpen.value : showSidebar.value;
        });
        const mobileInputPlaceholder = computed(() =>
            isMobile.value
                ? "Type a message..."
                : "Type a message... Use @ to mention",
        );
        const canSendMessage = computed(
            () => newMessage.value.trim() || attachmentFiles.value.length > 0,
        );
        const isSuperAdmin = computed(() => !!window.authAdminIsSuper);
        const isPersonalChannel = computed(
            () =>
                (currentChannel.value?.type || "").toLowerCase() === "personal",
        );
        // Hide the About section in the sidebar (always hidden)
        const showAboutSection = computed(() => false);
        const showMembersSection = computed(
            // Hide Members section for personal/direct chats (1-to-1 conversations)
            () => !isPersonalChannel.value,
        );
        const typingLabel = computed(() => {
            const now = Date.now();
            Object.keys(typingUsers.value).forEach((uid) => {
                if (now - typingUsers.value[uid].at > 3500)
                    delete typingUsers.value[uid];
            });
            const users = Object.values(typingUsers.value).filter(Boolean);
            if (!users.length) return "";

            const names = users.map((x) => x.name).filter(Boolean);
            const nameStr = `${names.slice(0, 2).join(", ")}${
                names.length > 2 ? ` +${names.length - 2}` : ""
            }`;
            const channelName = users[0]?.channelName;
            const channelNote =
                channelName && channelName !== currentChannel.value?.name
                    ? ` in ${channelName}`
                    : "";
            const verb = names.length > 1 ? "are" : "is";
            return `${nameStr} ${verb} typing${channelNote}...`;
        });

        const groupedReactions = (message) => {
            if (!Array.isArray(message?.reactions)) return [];
            return message.reactions;
        };

        const applyMessageDefaults = (message) => {
            if (!message || typeof message !== "object") return message;
            message.reactions = Array.isArray(message.reactions)
                ? message.reactions
                : [];
            message.is_pinned = !!message.is_pinned;
            message.is_saved = !!message.is_saved;
            message.attachments = Array.isArray(message.attachments)
                ? message.attachments
                : [];
            message.reads = Array.isArray(message.reads) ? message.reads : [];
            return message;
        };

        const reactToMessage = async (message, emoji) => {
            if (!message?.id || !emoji) return;
            try {
                const { data } = await axios.post(
                    `/admin/chat/messages/${message.id}/react`,
                    {
                        emoji,
                    },
                );
                const target = messages.value.find((m) => m.id === message.id);
                if (target) {
                    target.reactions = Array.isArray(data?.reactions)
                        ? data.reactions
                        : [];
                }
            } catch (e) {
                if (import.meta.env.DEV)
                    console.error("Failed to react to message", e);
            }
        };

        const pinMessage = async (message) => {
            if (!message?.id || !currentChannel.value?.id) return;
            try {
                await axios.post(
                    `/admin/chat/channels/${currentChannel.value.id}/pin/${message.id}`,
                );
                message.is_pinned = true;
                await loadPinnedMessages();
            } catch (e) {
                if (import.meta.env.DEV)
                    console.error("Failed to pin message", e);
            }
        };

        const unpinMessage = async (message) => {
            if (!message?.id || !currentChannel.value?.id) return;
            try {
                await axios.delete(
                    `/admin/chat/channels/${currentChannel.value.id}/pin/${message.id}`,
                );
                message.is_pinned = false;
                await loadPinnedMessages();
            } catch (e) {
                if (import.meta.env.DEV)
                    console.error("Failed to unpin message", e);
            }
        };

        const loadPinnedMessages = async () => {
            if (!currentChannel.value?.id) {
                pinnedMessages.value = [];
                return;
            }
            try {
                const { data } = await axios.get(
                    `/admin/chat/channels/${currentChannel.value.id}/pins`,
                );
                pinnedMessages.value = data?.pins || [];
            } catch (e) {
                if (import.meta.env.DEV)
                    console.error("Failed to load pinned messages", e);
                pinnedMessages.value = [];
            }
        };

        const togglePinnedPanel = async () => {
            showPinnedPanel.value = !showPinnedPanel.value;
            showSavedPanel.value = false;
            if (showPinnedPanel.value) {
                await loadPinnedMessages();
            }
        };

        const toggleSaveMessage = async (message) => {
            if (!message?.id) return;
            try {
                if (message.is_saved) {
                    await axios.delete(`/admin/chat/messages/${message.id}/save`);
                    message.is_saved = false;
                } else {
                    await axios.post(`/admin/chat/messages/${message.id}/save`);
                    message.is_saved = true;
                }
                await loadSavedMessages();
            } catch (e) {
                if (import.meta.env.DEV)
                    console.error("Failed to toggle save message", e);
            }
        };

        const loadSavedMessages = async () => {
            try {
                const { data } = await axios.get("/admin/chat/saved-messages");
                savedMessages.value = data?.saved || [];
            } catch (e) {
                if (import.meta.env.DEV)
                    console.error("Failed to load saved messages", e);
                savedMessages.value = [];
            }
        };

        const toggleSavedPanel = async () => {
            showSavedPanel.value = !showSavedPanel.value;
            showPinnedPanel.value = false;
            if (showSavedPanel.value) {
                await loadSavedMessages();
            }
        };

        const jumpToSavedMessage = async (saved) => {
            if (!saved?.id) return;
            const inCurrentChannel = messages.value.some((m) => m.id === saved.id);
            showSavedPanel.value = false;
            if (inCurrentChannel) {
                scrollToMessageById(saved.id);
                return;
            }
            const msg = searchResults.value.find((m) => m.id === saved.id);
            if (msg) {
                await scrollToMessage(msg);
                return;
            }
            window.showToast?.("Open the related channel to view this message");
        };

        const closeMenus = () => {
            showEmojiPicker.value = false;
            mentionOpen.value = false;
            orderSuggestOpen.value = false;
            activeMessageMenuId.value = null;
            activeReactionPickerId.value = null;
        };

        const toggleMessageMenu = (messageId) => {
            if (!messageId) return;
            activeReactionPickerId.value = null;
            activeMessageMenuId.value =
                activeMessageMenuId.value === messageId ? null : messageId;
        };

        const toggleReactionPicker = (messageId) => {
            if (!messageId) return;
            activeMessageMenuId.value = null;
            activeReactionPickerId.value =
                activeReactionPickerId.value === messageId ? null : messageId;
        };

        const reactAndClose = async (message, emoji) => {
            await reactToMessage(message, emoji);
            activeReactionPickerId.value = null;
        };

        const appendEmoji = (emoji) => {
            const selectedEmoji =
                typeof emoji === "string" ? emoji : emoji?.native;
            if (!selectedEmoji) return;
            newMessage.value += selectedEmoji;
            closeMenus();
            nextTick(() => messageInput.value?.focus());
        };

        const handlePaste = (e) => {
            if (!e || e.__chatImagePasteHandled) return;
            const items = Array.from(e.clipboardData?.items || []);
            if (!items.length) return;

            const imageFiles = items
                .filter((item) => item.type?.startsWith("image/"))
                .map((item) => item.getAsFile())
                .filter(Boolean);

            if (!imageFiles.length) return;

            const normalizedImageFiles = imageFiles.map((file, index) => {
                const mimeType = file.type || "image/png";
                const rawExt = mimeType.split("/")[1] || "png";
                const normalizedExt = rawExt.split("+")[0].toLowerCase();
                const extension =
                    normalizedExt === "jpeg" ? "jpg" : normalizedExt;
                const hasExtension =
                    typeof file.name === "string" &&
                    /\.[a-z0-9]{2,6}$/i.test(file.name);

                if (hasExtension && file.name.trim() !== "") {
                    return file;
                }

                const generatedName = `pasted-image-${Date.now()}-${index + 1}.${extension}`;
                return new File([file], generatedName, {
                    type: mimeType,
                    lastModified: Date.now(),
                });
            });

            e.__chatImagePasteHandled = true;
            e.preventDefault();
            attachmentFiles.value = [
                ...attachmentFiles.value,
                ...normalizedImageFiles,
            ];
        };

        const isFileDragEvent = (e) => {
            const types = Array.from(e.dataTransfer?.types || []);
            return types.includes("Files");
        };

        const onDragEnter = (e) => {
            if (!isFileDragEvent(e)) return;
            e.preventDefault();
            e.stopPropagation();
            isDragging.value = true;
        };

        const onDragOver = (e) => {
            if (!isFileDragEvent(e)) return;
            e.preventDefault();
            e.stopPropagation();
            if (e.dataTransfer) {
                e.dataTransfer.dropEffect = "copy";
            }
            isDragging.value = true;
        };

        const onDragLeave = (e) => {
            if (!isFileDragEvent(e)) return;
            e.preventDefault();
            e.stopPropagation();
            isDragging.value = false;
        };

        const onDrop = (e) => {
            e.preventDefault();
            e.stopPropagation();
            isDragging.value = false;
            const files = Array.from(e.dataTransfer?.files || []);
            if (!files.length) return;
            attachmentFiles.value = [...attachmentFiles.value, ...files];
        };

        const updateOrderSuggest = async () => {
            const q = orderSuggestQuery.value.trim();
            try {
                const { data } = await axios.get("/admin/chat/orders/suggest", {
                    params: { q },
                });
                orderSuggestItems.value = data?.orders || [];
                orderSuggestIndex.value = 0;
                orderSuggestOpen.value = orderSuggestItems.value.length > 0;
            } catch (_) {
                orderSuggestItems.value = [];
                orderSuggestOpen.value = false;
            }
        };

        const pickOrderSuggest = (order) => {
            if (!order?.id) return;
            // Use activeElement since textarea is now inside ChatInput component
            const textarea = document.activeElement?.tagName === 'TEXTAREA'
                ? document.activeElement
                : messageInput.value;
            const val = newMessage.value;
            const caret = textarea?.selectionStart ?? val.length;
            const before = val.slice(0, caret);
            const after = val.slice(caret);
            const orderMatch = before.match(/(^|\s)#(\w*)$/);
            if (!orderMatch) return;

            const prefix = orderMatch[1] || "";
            const insert = `${prefix}#${order.id} `;
            newMessage.value =
                before.replace(/(^|\s)#(\w*)$/, insert) + after;
            orderSuggestOpen.value = false;
            orderSuggestItems.value = [];
            orderSuggestQuery.value = "";

            nextTick(() => {
                try {
                    const pos = (
                        before.replace(/(^|\s)#(\w*)$/, "") + insert
                    ).length;
                    textarea.focus();
                    textarea.setSelectionRange(pos, pos);
                } catch (_) {}
            });
        };

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

        const loadMessages = async (
            channelId,
            reset = false,
            markAsRead = true,
        ) => {
            if (reset) {
                messages.value = [];
                page.value = 1;
                hasMoreMessages.value = true;
            }
            if (!hasMoreMessages.value) return false;

            try {
                loadingMessages.value = true;
                const response = await axios.get(
                    `/admin/chat/channels/${channelId}/messages?page=${page.value}`,
                );

                const apiMessages = Array.isArray(response?.data?.data)
                    ? response.data.data
                    : [];
                // API returns newest-first; reverse for UI (oldest-first)
                const pageMessages = apiMessages
                    .slice()
                    .reverse()
                    .map((m) => applyMessageDefaults(m));

                if (reset) {
                    messages.value = pageMessages;
                } else {
                    // Prepend older messages; de-duplicate defensively (page-based pagination can overlap).
                    const existingIds = new Set(
                        (messages.value || []).map((m) => String(m.id)),
                    );
                    const uniqueToAdd = pageMessages.filter(
                        (m) => !existingIds.has(String(m.id)),
                    );
                    messages.value = [
                        ...uniqueToAdd,
                        ...(messages.value || []),
                    ];
                }

                if (messages.value.length) {
                    updatePreview(
                        channelId,
                        messages.value[messages.value.length - 1],
                    );
                }

                hasMoreMessages.value = response.data.next_page_url !== null;
                page.value++;

                if (markAsRead) {
                    await axios.post(`/admin/chat/channels/${channelId}/read`);
                    channels.value = channels.value.map((c) =>
                        c.id === channelId
                            ? { ...c, unread_messages_count: 0 }
                            : c,
                    );
                }
                return true;
            } catch (error) {
                if (error?.response?.status === 403) {
                    window.showToast?.("You don't have access to this channel");
                } else {
                    window.showToast?.(
                        error?.response?.data?.message ||
                            "Failed to load messages",
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
            typingUsers.value = {};
            showEmojiPicker.value = false;
            orderSuggestOpen.value = false;
            mentionOpen.value = false;
            if (isMobile.value) {
                mobileSidebarOpen.value = false;
                userInfoOpen.value = false;
            }

            // Persist the selected channel ID to localStorage for page refresh persistence
            try {
                localStorage.setItem(
                    "chat_current_channel_id",
                    String(channel.id),
                );
            } catch (e) {
                // Ignore localStorage errors (e.g., private browsing mode)
            }

            const ok = await loadMessages(channel.id, true);
            if (!ok) {
                // Keep the header visible and surface the error so the UI is not blank
                window.showToast?.(
                    "Could not load messages for this conversation",
                );
            }
            await loadSidebar(channel.id);
            await loadPinnedMessages();
            await loadSavedMessages();
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
                    String(replyTo.value.id),
                );
                if (replyTo.value?.body)
                    formData.append(
                        "metadata[reply_preview]",
                        replyTo.value.body.slice(0, 140),
                    );
                if (replyTo.value?.sender?.name)
                    formData.append(
                        "metadata[reply_sender]",
                        replyTo.value.sender.name,
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
                    { headers: { "Content-Type": "multipart/form-data" } },
                );

                messages.value.push({
                    ...data,
                    attachments: Array.isArray(data.attachments)
                        ? data.attachments
                        : [],
                    reads: Array.isArray(data.reads) ? data.reads : [],
                });
                applyMessageDefaults(messages.value[messages.value.length - 1]);
                messages.value.sort(
                    (a, b) => new Date(a.created_at) - new Date(b.created_at),
                );
                updatePreview(currentChannel.value.id, data);

                newMessage.value = "";
                attachmentFiles.value = [];
                replyTo.value = null;
                pendingMentionIds.value = new Set();
                showEmojiPicker.value = false;
                orderSuggestOpen.value = false;
                orderSuggestItems.value = [];
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
                window.showToast?.("Failed to send message. Please try again.");
            } finally {
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
                    (m) => m.id === message.metadata.reply_to_id,
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
                    (m.name + " " + (m.email || "")).toLowerCase().includes(q),
                )
                .slice(0, 8);
            mentionItems.value = items;
            mentionIndex.value = 0;
            mentionOpen.value = items.length > 0;
        };

        // Auto-resize textarea based on content (WhatsApp style)
        // Accepts optional target element; falls back to messageInput ref or event target
        const autoResizeTextarea = (el) => {
            const textarea = el || messageInput.value;
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

        // const onEditorInput = (e) => {
        //     handleTyping();
        //     autoResizeTextarea(); // Auto-resize on input
        //     const val = newMessage.value;
        //     const caret = e.target.selectionStart;
        //     const before = val.slice(0, caret);
        //     // Mention trigger: @ followed by name characters (no spaces)
        //     const match = before.match(/(^|\s)@([\w.\-]*)$/);
        //     if (match) {
        //         mentionQuery.value = match[2] || "";
        //         updateMentionList();
        //     } else {
        //         mentionOpen.value = false;
        //     }
        // };

        const onEditorInput = (e) => {
            handleTyping();
            autoResizeTextarea(e.target); // Pass element directly — messageInput ref no longer in scope

            const val = newMessage.value;
            const caret = e.target.selectionStart;
            const before = val.slice(0, caret);
            const mentionMatch = before.match(/(^|\s)@([\w.\-]*)$/);
            const orderMatch = before.match(/(^|\s)#(\w*)$/);

            if (mentionMatch) {
                mentionQuery.value = mentionMatch[2] || "";
                updateMentionList();
                orderSuggestOpen.value = false;
            } else {
                mentionOpen.value = false;
            }

            if (orderMatch) {
                orderSuggestQuery.value = orderMatch[2] || "";
                updateOrderSuggest();
            } else {
                orderSuggestOpen.value = false;
            }
        };

        const onKeyDownInEditor = (e) => {
            // If mention popover is open, intercept navigation/selection keys
            if (mentionOpen.value) {
                if (
                    ["ArrowDown", "ArrowUp", "Enter", "Tab", "Escape"].includes(
                        e.key,
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

            // If #order suggest popover is open, intercept navigation/selection keys
            if (orderSuggestOpen.value) {
                if (
                    ["ArrowDown", "ArrowUp", "Enter", "Tab", "Escape"].includes(
                        e.key,
                    )
                ) {
                    e.preventDefault();
                }
                if (orderSuggestItems.value.length === 0) {
                    orderSuggestOpen.value = false;
                    return;
                }
                if (e.key === "ArrowDown") {
                    orderSuggestIndex.value =
                        (orderSuggestIndex.value + 1) %
                        orderSuggestItems.value.length;
                    return;
                }
                if (e.key === "ArrowUp") {
                    orderSuggestIndex.value =
                        (orderSuggestIndex.value - 1 + orderSuggestItems.value.length) %
                        orderSuggestItems.value.length;
                    return;
                }
                if (e.key === "Enter" || e.key === "Tab") {
                    pickOrderSuggest(orderSuggestItems.value[orderSuggestIndex.value]);
                    return;
                }
                if (e.key === "Escape") {
                    orderSuggestOpen.value = false;
                    return;
                }
            }

            // When no popover is open, handle Enter key
            if (!mentionOpen.value && !orderSuggestOpen.value && e.key === "Enter") {
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
            // Use activeElement since textarea is now inside ChatInput component
            const textarea = document.activeElement?.tagName === 'TEXTAREA'
                ? document.activeElement
                : messageInput.value;
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
                        email,
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
                            "g",
                        );

                        content = content.replace(
                            pattern,
                            (match, prefix, name) => {
                                return `<span class="mention-token">${prefix}${name}</span>`;
                            },
                        );
                    }
                } catch (e) {
                    // Ignore mention format errors
                }

                // 6. Format order references as clickable order links
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
                                const ref = orderRefMap.get(
                                    String(orderNumber),
                                );
                                if (!ref) return match;

                                if (!ref.order_url) {
                                    return `${prefix}<span class="order-reference-token order-reference-token--missing">#${orderNumber}</span>`;
                                }

                                return `${prefix}<a href="${ref.order_url}" class="order-reference-token" target="_self" rel="noopener noreferrer">#${orderNumber}</a>`;
                            },
                        );
                    }
                } catch (e) {
                    // Ignore order reference format errors
                }

                // 7. Sanitize final HTML to prevent XSS
                return DOMPurify.sanitize(content, {
                    ALLOWED_TAGS: ["span", "a"],
                    ALLOWED_ATTR: ["class", "href", "target", "rel"],
                });
            } catch (e) {
                console.error("Message formatting failed", e);
                return message.body || "";
            }
        };

        const loadSidebar = async (channelId) => {
            if (!channelId) return;
            try {
                const { data } = await axios.get(
                    `/admin/chat/channels/${channelId}/sidebar`,
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
                    { params },
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

        const parseDeepLinkParams = () => {
            if (typeof window === "undefined") return;
            try {
                const params = new URLSearchParams(window.location.search);
                const channelParam = params.get("channel_id");
                const messageParam = params.get("message_id");
                const openThreadParam = params.get("open_thread");

                const parsedChannel = channelParam ? Number(channelParam) : NaN;
                const parsedMessage = messageParam ? Number(messageParam) : NaN;

                deepLinkChannelId.value = Number.isFinite(parsedChannel)
                    ? parsedChannel
                    : null;
                deepLinkMessageId.value = Number.isFinite(parsedMessage)
                    ? parsedMessage
                    : null;
                deepLinkOpenThread.value =
                    openThreadParam === "1" ||
                    openThreadParam === "true" ||
                    openThreadParam === "yes";
            } catch (_) {
                deepLinkChannelId.value = null;
                deepLinkMessageId.value = null;
                deepLinkOpenThread.value = false;
            }
        };

        const clearDeepLinkParamsFromUrl = () => {
            if (typeof window === "undefined") return;
            try {
                const url = new URL(window.location.href);
                url.searchParams.delete("channel_id");
                url.searchParams.delete("message_id");
                url.searchParams.delete("open_thread");
                window.history.replaceState({}, "", url.toString());
            } catch (_) {}
        };

        const scrollToMessage = async (message) => {
            if (!message) return;

            // If message belongs to a different channel, switch to it and wait for messages to load
            if (currentChannel.value?.id !== message.channel_id) {
                const channel = channels.value.find(
                    (c) => c.id === message.channel_id,
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
                    (m) => m.id === targetId,
                ) || { id: targetId };
                // Give DOM a moment to scroll and highlight
                setTimeout(() => openThread(msgObj), 400);
            }
        };

        const scrollToMessageById = (messageId) => {
            if (!messageId) return;

            // First check if message exists in current loaded messages
            const targetMessage = messages.value.find(
                (m) => m.id === messageId,
            );

            // Use Vue's nextTick to ensure DOM is updated
            nextTick(() => {
                const messageElement = document.querySelector(
                    `[data-message-id="${messageId}"]`,
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

        const applyDeepLinkIfNeeded = async () => {
            if (deepLinkConsumed.value) return;
            if (!currentChannel.value?.id) return;
            if (
                deepLinkChannelId.value &&
                Number(currentChannel.value.id) !== Number(deepLinkChannelId.value)
            ) {
                return;
            }

            deepLinkConsumed.value = true;

            if (deepLinkMessageId.value) {
                if (deepLinkOpenThread.value) {
                    await openThread({ id: deepLinkMessageId.value });
                }
                setTimeout(() => {
                    scrollToMessageById(deepLinkMessageId.value);
                }, 350);
            }

            clearDeepLinkParamsFromUrl();
        };

        const scrollToBottom = () => {
            setTimeout(() => {
                if (messageContainer.value) {
                    messageContainer.value.scrollTop =
                        messageContainer.value.scrollHeight;
                }
            }, 100);
        };

        const loadOlderMessages = async () => {
            const el = messageContainer.value;
            if (
                !el ||
                !currentChannel.value?.id ||
                loadingMessages.value ||
                !hasMoreMessages.value
            )
                return;

            const prevScrollHeight = el.scrollHeight;
            const prevScrollTop = el.scrollTop;

            const ok = await loadMessages(
                currentChannel.value.id,
                false,
                false,
            );
            if (ok) {
                await nextTick();
                el.scrollTop =
                    el.scrollHeight - prevScrollHeight + prevScrollTop;
            }
        };

        const onScrollMessages = async () => {
            const el = messageContainer.value;
            if (!el) return;
            const nearBottom =
                el.scrollHeight - el.scrollTop - el.clientHeight < 120;
            showScrollDown.value = !nearBottom;

            // Infinite scroll upwards to load older pages
            const nearTop = el.scrollTop < 120;
            if (
                nearTop &&
                currentChannel.value?.id &&
                hasMoreMessages.value &&
                !loadingMessages.value
            ) {
                const prevScrollHeight = el.scrollHeight;
                const prevScrollTop = el.scrollTop;

                const ok = await loadMessages(
                    currentChannel.value.id,
                    false,
                    false,
                );
                if (ok) {
                    await nextTick();
                    const newScrollHeight = el.scrollHeight;
                    // Preserve visual position after prepending older messages
                    el.scrollTop =
                        newScrollHeight - prevScrollHeight + prevScrollTop;
                }
            }
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
                (r) => r.user_id && r.user_id !== props.userId,
            );
        };

        const handleTyping = () => {
            const now = Date.now();
            if (now - lastTypingSentAt.value < 1500) return;
            lastTypingSentAt.value = now;
            if (currentChannel.value?.id && window.Echo) {
                try {
                    window.Echo.private(
                        `chat.channel.${currentChannel.value.id}`,
                    ).whisper("typing", {
                        userId: props.userId,
                        name: window?.authAdminName || "Someone",
                        channelId: currentChannel.value.id,
                        channelName: currentChannel.value.name || "",
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
                    `/admin/chat/channels/${currentChannel.value.id}/members`,
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
            if (!currentChannel.value?.id || isSavingMembers.value) return;
            isSavingMembers.value = true;
            try {
                const unique = Array.from(
                    new Set([...memberIds.value, props.userId]),
                );
                await axios.put(
                    `/admin/chat/channels/${currentChannel.value.id}/members`,
                    {
                        member_ids: unique,
                    },
                );
                window.showToast?.("Members updated");
                manageOpen.value = false;
                await loadChannels();
            } catch (e) {
                if (import.meta.env.DEV)
                    console.error("Failed to update members", e);
                window.showToast?.("Failed to update members");
            } finally {
                isSavingMembers.value = false;
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
            if (isCreatingChannel.value) return;
            isCreatingChannel.value = true;
            try {
                const users = Array.from(
                    new Set([...createMemberIds.value, props.userId]),
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
            } finally {
                isCreatingChannel.value = false;
            }
        };

        // Thread Logic
        const threadPanelOpen = ref(false);
        const activeThreadMessage = ref(null);
        const threadReplies = ref([]);
        const threadLoading = ref(false);
        const threadReplyFiles = ref([]);

        // Thread Editor Composable — isolated state, no sharing with main chat
        const threadEditor = useChatEditor({
            type: 'thread',
            getMembers: currentMembers,
            onSend: () => sendThreadReply(),
            onTyping: null, // threads don't broadcast typing indicator
            textareaRef: threadInput,
            fetchOrderSuggestions: async (q) => {
                const { data } = await axios.get('/admin/chat/orders/suggest', { params: { q } });
                return data?.orders || [];
            },
        });

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
            if (isMobile.value) {
                return threadPanelOpen.value
                    ? viewportWidth.value
                    : userInfoOpen.value
                      ? Math.min(380, Math.max(280, viewportWidth.value - 24))
                      : 0;
            }
            if (threadPanelOpen.value) return threadPanelWidth.value;
            return userInfoOpen.value ? Math.max(0, infoPanelWidth.value) : 0;
        });
        const chatMainStyle = computed(() => {
            if (isMobile.value) return { width: "100%" };
            return {
                width: `calc(100% - ${leftSidebarWidth.value}px - ${Math.max(
                    0,
                    infoSidebarWidth.value,
                )}px)`,
            };
        });
        const infoSidebarStyle = computed(() => ({
            width: `${Math.max(0, infoSidebarWidth.value)}px`,
        }));
        const openChannelList = () => {
            if (!isMobile.value) return;
            mobileSidebarOpen.value = true;
        };
        const closeChannelList = () => {
            mobileSidebarOpen.value = false;
        };
        const handleViewportChange = () => {
            if (typeof window === "undefined") return;
            viewportWidth.value = window.innerWidth;
            if (viewportWidth.value <= 768) {
                mobileSidebarOpen.value = !currentChannel.value;
                if (threadPanelOpen.value) userInfoOpen.value = true;
            } else {
                mobileSidebarOpen.value = false;
            }
        };

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
                    `/admin/chat/messages/${message.id}/thread`,
                );

                if (data.parent_message) {
                    const mainMsg = messages.value.find(
                        (m) => m.id === message.id,
                    );
                    if (mainMsg) {
                        mainMsg.thread_count = data.parent_message.thread_count;
                    }
                    activeThreadMessage.value = applyMessageDefaults(
                        data.parent_message,
                    );
                }
                threadReplies.value = Array.isArray(data.replies)
                    ? data.replies.map((reply) => applyMessageDefaults(reply))
                    : [];

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
            handleViewportChange();
            userInfoOpen.value = isMobile.value ? false : showSidebar.value;
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
                !threadEditor.input.value.trim() &&
                !threadReplyFiles.value.length
            )
                return;
            if (isSendingThread.value) return;
            isSendingThread.value = true;
            if (!activeThreadMessage.value) {
                console.warn("No active thread message");
                return;
            }

            const formData = new FormData();
            if (threadEditor.input.value.trim()) {
                formData.append("body", threadEditor.input.value);
            }
            threadReplyFiles.value.forEach((file) => {
                formData.append("attachments[]", file);
            });
            // Forward mention IDs so backend can send mention notifications
            if (threadEditor.pendingMentionIds.value.size) {
                Array.from(threadEditor.pendingMentionIds.value).forEach((id) => {
                    formData.append("metadata[mentions][]", String(id));
                });
            }

            try {
                const { data } = await axios.post(
                    `/admin/chat/messages/${activeThreadMessage.value.id}/thread/replies`,
                    formData,
                    { headers: { "Content-Type": "multipart/form-data" } },
                );

                if (data.success) {
                    threadReplies.value.push(applyMessageDefaults(data.reply));

                    if (activeThreadMessage.value) {
                        activeThreadMessage.value.thread_count =
                            data.parent_thread_count;
                    }
                    const mainMsg = messages.value.find(
                        (m) => m.id === activeThreadMessage.value.id,
                    );
                    if (mainMsg) {
                        mainMsg.thread_count = data.parent_thread_count;
                    }

                    // Reset thread editor state cleanly
                    threadEditor.reset();
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
            } finally {
                isSendingThread.value = false;
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
                const audioContext = new (
                    window.AudioContext || window.webkitAudioContext
                )();
                const oscillator = audioContext.createOscillator();
                const gain = audioContext.createGain();

                oscillator.connect(gain);
                gain.connect(audioContext.destination);

                oscillator.frequency.value = 800; // 800 Hz tone
                oscillator.type = "sine";

                gain.gain.setValueAtTime(0.3, audioContext.currentTime);
                gain.gain.exponentialRampToValueAtTime(
                    0.01,
                    audioContext.currentTime + 0.3,
                );

                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.3);
            } catch (_) {
                // Fallback: try audio element if Web Audio API fails
                try {
                    const beep = new Audio(
                        "data:audio/wav;base64,UklGRiYAAABXQVZFZm10IBAAAAABAAEAQB8AAAA=",
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
                                const replyMessage = applyMessageDefaults({
                                    ...e.message,
                                    attachments: Array.isArray(
                                        e.message.attachments,
                                    )
                                        ? e.message.attachments
                                        : [],
                                });
                                threadReplies.value.push(replyMessage);
                                nextTick(() => {
                                    const container =
                                        document.querySelector(
                                            ".thread-replies",
                                        );
                                    if (container)
                                        container.scrollTop =
                                            container.scrollHeight;
                                });
                            }

                            // 2. Update parent count in main list
                            const parent = messages.value.find(
                                (m) => m.id === e.message.reply_to_id,
                            );
                            if (parent) {
                                parent.thread_count =
                                    (parent.thread_count || 0) + 1;
                            }

                            // Do NOT add to main list
                            return;
                        }

                        const incomingMessage = applyMessageDefaults({
                            ...e.message,
                            attachments: Array.isArray(e.message.attachments)
                                ? e.message.attachments
                                : [],
                            reads: Array.isArray(e.message.reads)
                                ? e.message.reads
                                : [],
                        });
                        messages.value.push(incomingMessage);
                        messages.value.sort(
                            (a, b) =>
                                new Date(a.created_at) - new Date(b.created_at),
                        );
                        updatePreview(channelId, incomingMessage);

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
                    .listen("MessageReacted", (e) => {
                        if (!e?.message_id) return;
                        const applyReactionToMessage = (targetMessage) => {
                            if (!targetMessage || targetMessage.id !== e.message_id)
                                return;

                            const existing = Array.isArray(targetMessage.reactions)
                                ? [...targetMessage.reactions]
                                : [];
                            const idx = existing.findIndex(
                                (r) => r?.emoji === e.emoji,
                            );

                            if (e.action === "added") {
                                if (idx >= 0) {
                                    existing[idx] = {
                                        ...existing[idx],
                                        count: (existing[idx].count || 0) + 1,
                                    };
                                } else {
                                    existing.push({
                                        emoji: e.emoji,
                                        count: 1,
                                        my: false,
                                    });
                                }
                            } else if (e.action === "removed" && idx >= 0) {
                                const nextCount = (existing[idx].count || 1) - 1;
                                if (nextCount > 0) {
                                    existing[idx] = {
                                        ...existing[idx],
                                        count: nextCount,
                                    };
                                } else {
                                    existing.splice(idx, 1);
                                }
                            }

                            targetMessage.reactions = existing;
                        };

                        const topLevel = messages.value.find(
                            (m) => m.id === e.message_id,
                        );
                        if (topLevel) {
                            applyReactionToMessage(topLevel);
                        }

                        if (
                            activeThreadMessage.value?.id === e.message_id &&
                            activeThreadMessage.value
                        ) {
                            applyReactionToMessage(activeThreadMessage.value);
                        }

                        const threadReply = threadReplies.value.find(
                            (r) => r.id === e.message_id,
                        );
                        if (threadReply) {
                            applyReactionToMessage(threadReply);
                        }
                    })
                    .listen("MessagePinned", async (e) => {
                        if (!e?.message_id) return;

                        const isPinned = e.action === "pinned";
                        const applyPinToMessage = (targetMessage) => {
                            if (!targetMessage || targetMessage.id !== e.message_id)
                                return;
                            targetMessage.is_pinned = isPinned;
                        };

                        const topLevel = messages.value.find(
                            (m) => m.id === e.message_id,
                        );
                        if (topLevel) {
                            applyPinToMessage(topLevel);
                        }

                        if (
                            activeThreadMessage.value?.id === e.message_id &&
                            activeThreadMessage.value
                        ) {
                            applyPinToMessage(activeThreadMessage.value);
                        }

                        const threadReply = threadReplies.value.find(
                            (r) => r.id === e.message_id,
                        );
                        if (threadReply) {
                            applyPinToMessage(threadReply);
                        }

                        if (showPinnedPanel.value) {
                            await loadPinnedMessages();
                        }
                    })
                    .listenForWhisper("typing", (e) => {
                        if (!e || e.userId === props.userId) return;
                        typingUsers.value[e.userId] = {
                            name: e.name || "Someone",
                            channelName: e.channelName || "",
                            at: Date.now(),
                        };
                    })
                    .listen("MessagesRead", (e) => {
                        messages.value = messages.value.map((message) => {
                            if (
                                !message.reads.some(
                                    (read) => read.user_id === e.userId,
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
                "channels",
            );

            channels.value.forEach((channel) => {
                // Skip the current channel - it's already handled by setupChannelListeners
                if (
                    currentChannel.value &&
                    channel.id === currentChannel.value.id
                ) {
                    console.log(
                        "[Chat] Skipping current channel:",
                        channel.name,
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
                                    channel.name,
                                );

                                // Increment unread count
                                const ch = channels.value.find(
                                    (c) => c.id === channel.id,
                                );
                                if (ch) {
                                    ch.unread_messages_count =
                                        (ch.unread_messages_count || 0) + 1;
                                    console.log(
                                        "[Chat] Unread count for",
                                        ch.name,
                                        ":",
                                        ch.unread_messages_count,
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
                                    notificationMessage,
                                );

                                if (typeof window.showToast === "function") {
                                    window.showToast(notificationMessage);
                                }

                                // Play sound
                                playNotificationSound();
                            }
                        },
                    );
                    console.log(
                        "[Chat] Listener setup for channel:",
                        channel.name,
                    );
                } catch (err) {
                    console.error(
                        `Failed to setup listener for channel ${channel.id}`,
                        err,
                    );
                }
            });
        };

        // Lifecycle Hooks
        onMounted(() => {
            parseDeepLinkParams();
            handleViewportChange();
            window.addEventListener("resize", handleViewportChange, {
                passive: true,
            });
            window.addEventListener("paste", handlePaste);
            loadChannels();
            checkCreateCapability();

            try {
                if (window.Echo) {
                    const notificationChannel = window.Echo.private(
                        `admin.notifications.${props.userId}`,
                    );
                    notificationChannel.listen(
                        "ChannelMembershipChanged",
                        (e) => {
                            if (!e || !e.channelId || !e.action) return;
                            if (e.action === "removed") {
                                if (currentChannel.value?.id === e.channelId) {
                                    try {
                                        window.Echo.leave(
                                            `chat.channel.${e.channelId}`,
                                        );
                                    } catch (_) {}
                                    currentChannel.value = null;
                                }
                                channels.value = channels.value.filter(
                                    (c) => c.id !== e.channelId,
                                );
                                window.showToast?.(
                                    "You were removed from a channel",
                                );
                            } else if (e.action === "added") {
                                loadChannels();
                            }
                        },
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
                    onScrollMessages,
                );
            }
        });

        onBeforeUnmount(() => {
            window.removeEventListener("resize", handleViewportChange);
            window.removeEventListener("paste", handlePaste);
            if (messageContainer.value) {
                messageContainer.value.removeEventListener(
                    "scroll",
                    onScrollMessages,
                );
            }
        });

        // Adjust messages container bottom padding dynamically based on input container height
        let _resizeObserver = null;
        const ensureInputResizeObserver = () => {
            try {
                if (!window.ResizeObserver) return;
                if (!_resizeObserver) {
                    _resizeObserver = new ResizeObserver(() => {
                        nextTick(adjustMessagePadding);
                    });
                }
                if (inputContainer.value) {
                    _resizeObserver.disconnect();
                    _resizeObserver.observe(inputContainer.value);
                }
            } catch (_) {}
        };

        const adjustMessagePadding = () => {
            const mc = messageContainer.value;
            const ic = inputContainer.value;
            if (!mc || !ic) return;
            // Include composer height and fixed bottom offset (mobile floating composer)
            const rect = ic.getBoundingClientRect();
            const height = ic.offsetHeight || rect.height || 0;
            const style = window.getComputedStyle(ic);
            const bottomOffset =
                style.position === "fixed"
                    ? Math.max(0, parseFloat(style.bottom || "0") || 0)
                    : 0;
            const gap = isMobile.value ? 14 : 16;
            mc.style.paddingBottom = `${Math.ceil(height + bottomOffset + gap)}px`;
        };

        onMounted(() => {
            // existing onMounted logic above will run; ensure resize observer and initial adjust
            ensureInputResizeObserver();

            // adjust on window resize as well
            window.addEventListener("resize", adjustMessagePadding, {
                passive: true,
            });
            // initial adjust after DOM settled
            nextTick(() => {
                ensureInputResizeObserver();
                adjustMessagePadding();
            });
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
        watch(mobileSidebarOpen, () => {
            nextTick(adjustMessagePadding);
        });
        watch(
            () => inputContainer.value,
            () => {
                nextTick(() => {
                    ensureInputResizeObserver();
                    adjustMessagePadding();
                });
            },
        );

        watch(currentChannel, (newChannel, oldChannel) => {
            if (oldChannel?.id && window.Echo) {
                try {
                    window.Echo.leave(`chat.channel.${oldChannel.id}`);
                } catch (_) {}
            }
            if (newChannel?.id) {
                setupChannelListeners(newChannel.id);
                if (isMobile.value) mobileSidebarOpen.value = false;
                nextTick(() => {
                    ensureInputResizeObserver();
                    adjustMessagePadding();
                });
            } else if (isMobile.value) {
                mobileSidebarOpen.value = true;
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
                    let channelToSelect = null;

                    if (deepLinkChannelId.value) {
                        channelToSelect = list.find(
                            (c) =>
                                Number(c.id) === Number(deepLinkChannelId.value),
                        );
                    }

                    if (!channelToSelect) {
                        // Try to restore the last selected channel from localStorage
                        try {
                            const savedId = localStorage.getItem(
                                "chat_current_channel_id",
                            );
                            if (savedId) {
                                channelToSelect = list.find(
                                    (c) => String(c.id) === savedId,
                                );
                            }
                        } catch (e) {
                            // Ignore localStorage errors
                        }
                    }

                    selectChannel(channelToSelect || list[0]).then(() => {
                        if (
                            deepLinkChannelId.value &&
                            Number(channelToSelect?.id || list[0]?.id) !==
                                Number(deepLinkChannelId.value)
                        ) {
                            deepLinkConsumed.value = true;
                            clearDeepLinkParamsFromUrl();
                            return;
                        }
                        applyDeepLinkIfNeeded();
                    });
                }
            },
            { deep: false },
        );

        return {
            channels,
            currentChannel,
            messages,
            hasMoreMessages,
            loadingMessages,
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
            shouldShowInfoSidebar,
            mobileInputPlaceholder,
            chatMainStyle,
            infoSidebarStyle,
            isMobile,
            mobileSidebarOpen,
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
            showEmojiPicker,
            pinnedMessages,
            showPinnedPanel,
            savedMessages,
            showSavedPanel,
            activeMessageMenuId,
            activeReactionPickerId,
            orderSuggestOpen,
            orderSuggestItems,
            orderSuggestIndex,
            QUICK_EMOJIS,
            emojiData,
            isDragging,
            canSendMessage,
            isSuperAdmin,
            isPersonalChannel,
            showAboutSection,
            showMembersSection,
            isGroupChannel,
            avatarInitials,
            formatDate,
            formatFullDate,
            formatOrderCreatedAt,
            isImage,
            getAttachmentUrl,
            getOrderReferences,
            hasRenderableMessageBody,
            openOrderReference,
            storageUrl,
            storageThumbUrl,
            downloadAttachment,
            openAttachment,
            selectChannel,
            sendMessage,
            handleFiles,
            removeAttachment,
            handleTyping,
            groupedReactions,
            reactToMessage,
            pinMessage,
            unpinMessage,
            toggleSaveMessage,
            togglePinnedPanel,
            toggleSavedPanel,
            loadPinnedMessages,
            loadSavedMessages,
            closeMenus,
            toggleMessageMenu,
            toggleReactionPicker,
            appendEmoji,
            reactAndClose,
            pickOrderSuggest,
            jumpToSavedMessage,
            handlePaste,
            onDragEnter,
            onDragOver,
            onDragLeave,
            onDrop,
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
            loadOlderMessages,
            shouldShowDateSeparator,
            dayLabel,
            readByOthers,
            togglePanel,
            openManageMembers,
            toggleMember,
            saveMembers,
            isSavingMembers, // Added
            deleteChannel,
            openCreateChannel,
            toggleCreateMember,
            saveCreateChannel,
            isCreatingChannel, // Added
            startDirect,
            openChannelList,
            closeChannelList,
            // Thread Logic
            threadPanelOpen,
            activeThreadMessage,
            threadReplies,
            threadLoading,
            threadReplyFiles,
            isSendingThread,
            threadInput,
            // Thread Editor Composable state & handlers
            threadEditor,
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
    font-family:
        -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
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

[data-theme="dark"] .chat-container,
[data-theme="dark"] .chat-main,
[data-theme="dark"] .channels-sidebar {
    background: transparent !important;
}

[data-theme="dark"] .sidebar-header {
    background: transparent !important;
    border-color: var(--gray-700) !important;
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
    min-height: 0;
    position: relative;
}

.drag-overlay {
    position: absolute;
    inset: 0;
    z-index: 40;
    background: rgba(99, 102, 241, 0.15);
    border: 2px dashed var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}

.drag-overlay-content {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1rem;
    border-radius: 999px;
    background: white;
    color: var(--primary-dark);
    font-weight: 600;
    box-shadow: var(--shadow);
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

.btn-icon-secondary.active {
    background: var(--primary-light);
    color: var(--primary-dark);
}

.mobile-channels-toggle {
    display: none;
}

.mobile-sidebar-overlay {
    display: none;
}

/* Messages Container */
.messages-container {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 1.5rem;
    padding-bottom: calc(1.5rem + 140px);
    position: relative;
    min-height: 0;
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;
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

:deep(.message-text .order-reference-token) {
    display: inline-flex;
    align-items: center;
    padding: 0.1rem 0.45rem;
    border-radius: 999px;
    background: rgba(99, 102, 241, 0.12);
    color: var(--primary-dark);
    font-weight: 700;
    text-decoration: none;
    border: 1px solid rgba(99, 102, 241, 0.16);
    margin: 0 2px;
}

:deep(.message-text .order-reference-token:hover) {
    background: rgba(99, 102, 241, 0.2);
    color: var(--primary-dark);
}

:deep(.message-text .order-reference-token--missing) {
    background: rgba(148, 163, 184, 0.12);
    color: var(--gray-500);
    border-color: rgba(148, 163, 184, 0.18);
}

:deep(.own-message .message-text .order-reference-token) {
    background: rgba(255, 255, 255, 0.18);
    color: #1d4ed8;
    border-color: rgba(255, 255, 255, 0.22);
}

:deep(.own-message .message-text .order-reference-token:hover) {
    background: rgba(255, 255, 255, 0.24);
    color: #1d4ed8;
}

.order-reference-stack {
    margin-top: 0.75rem;
    display: grid;
    gap: 0.75rem;
}

.order-reference-card {
    appearance: none;
    -webkit-appearance: none;
    font: inherit;
    color: inherit;
    display: block;
    width: 100%;
    text-align: left;
    border: 1px solid rgba(99, 102, 241, 0.16);
    background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.92), rgba(247, 249, 255, 0.88));
    border-radius: 16px;
    padding: 0.95rem 1rem 0.9rem;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
    cursor: pointer;
    transition:
        transform 0.18s ease,
        box-shadow 0.18s ease,
        border-color 0.18s ease;
}

.own-message .order-reference-card {
    background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(239, 244, 255, 0.9));
}

.order-reference-card:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 14px 28px rgba(15, 23, 42, 0.12);
    border-color: rgba(99, 102, 241, 0.28);
}

.order-reference-card:disabled {
    cursor: default;
    opacity: 0.88;
}

.order-reference-card--missing {
    border-style: dashed;
    background: linear-gradient(180deg, #f8fafc, #f1f5f9);
    box-shadow: none;
}

.order-reference-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.order-reference-title {
    display: flex;
    align-items: baseline;
    flex-wrap: wrap;
    gap: 0.45rem;
    min-width: 0;
}

.order-reference-number {
    font-size: 1.02rem;
    font-weight: 800;
    color: var(--primary);
    letter-spacing: 0.01em;
}

.order-reference-label-text {
    font-size: 0.92rem;
    font-weight: 700;
    color: var(--gray-700);
}

.order-reference-status {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.3rem 0.75rem;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.01em;
    border: 1px solid transparent;
    white-space: nowrap;
}

.order-reference-status.status-info {
    background: rgba(59, 130, 246, 0.14);
    color: #1d4ed8;
}

.order-reference-status.status-success {
    background: rgba(16, 185, 129, 0.14);
    color: #047857;
}

.order-reference-status.status-warning {
    background: rgba(245, 158, 11, 0.16);
    color: #b45309;
}

.order-reference-status.status-danger {
    background: rgba(239, 68, 68, 0.14);
    color: #b91c1c;
}

.order-reference-status.status-dark {
    background: rgba(15, 23, 42, 0.1);
    color: #0f172a;
}

.order-reference-status.status-purple {
    background: rgba(124, 58, 237, 0.14);
    color: #6d28d9;
}

.order-reference-status.status-cyan {
    background: rgba(6, 182, 212, 0.14);
    color: #0e7490;
}

.order-reference-status.status-secondary {
    background: rgba(148, 163, 184, 0.14);
    color: var(--gray-600);
}

.order-reference-body {
    margin-top: 0.85rem;
    border-top: 1px solid rgba(148, 163, 184, 0.18);
    padding-top: 0.8rem;
    display: grid;
    gap: 0.75rem;
}

.order-reference-row {
    display: flex;
    align-items: flex-start;
    gap: 0.7rem;
}

.order-reference-row-icon {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: rgba(99, 102, 241, 0.1);
    color: var(--primary);
    font-size: 0.95rem;
}

.order-reference-row-content {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    min-width: 0;
    flex: 1;
}

.order-reference-row-label {
    font-size: 0.76rem;
    font-weight: 700;
    color: var(--gray-500);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.order-reference-row-value {
    font-size: 0.94rem;
    font-weight: 700;
    color: var(--gray-900);
    word-break: break-word;
}

.order-reference-row-subvalue {
    font-size: 0.8rem;
    color: var(--gray-600);
    word-break: break-word;
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
    padding: 0 1rem;
    flex-wrap: wrap;
}

.own-message .message-meta {
    color: black;
    justify-content: flex-end;
}

.message-time {
    font-weight: 500;
}

.meta-reactions {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.meta-emoji {
    border: 1px solid transparent;
    border-radius: 999px;
    background: transparent;
    font-size: 0.9rem;
    line-height: 1;
    padding: 0.12rem 0.28rem;
    cursor: pointer;
}

.meta-emoji:hover {
    border-color: var(--gray-300);
    background: white;
}

.reaction-bar {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    margin-top: 0.35rem;
    flex-wrap: wrap;
}

.message-actions-float {
    position: relative;
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 0.2rem;
}

.message-actions-menu {
    position: absolute;
    right: 0;
    top: calc(100% + 4px);
    min-width: 150px;
    border: 1px solid var(--gray-200);
    border-radius: 10px;
    background: white;
    box-shadow: var(--shadow);
    padding: 0.35rem;
    z-index: 50;
}

.message-reaction-picker {
    position: absolute;
    right: calc(100% + 6px);
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    align-items: center;
    gap: 0.2rem;
    border: 1px solid var(--gray-200);
    border-radius: 999px;
    background: white;
    box-shadow: var(--shadow);
    padding: 0.2rem 0.35rem;
    z-index: 55;
}

.reaction-picker-emoji {
    border: 1px solid transparent;
    border-radius: 999px;
    background: transparent;
    font-size: 0.95rem;
    line-height: 1;
    padding: 0.12rem 0.24rem;
    cursor: pointer;
}

.reaction-picker-emoji:hover {
    border-color: var(--gray-300);
    background: white;
}

.message-actions-item {
    width: 100%;
    border: none;
    background: transparent;
    display: flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.8rem;
    color: var(--gray-700);
    border-radius: 8px;
    padding: 0.32rem 0.45rem;
    cursor: pointer;
    text-align: left;
}

.message-actions-item:hover {
    background: var(--gray-100);
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
    border-top: 0px solid var(--gray-200);
    padding: 0rem 5px;
    position: sticky;
    bottom: 10px;
    z-index: 30;
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 0;
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

/* Unified input box: wraps reply-bar (optional) + input-row */
.input-box-wrapper {
    display: flex;
    flex-direction: column;
    background: var(--bs-body-bg, #ffffff);
    border: 1.5px solid var(--bs-border-color, #d9deea);
    border-radius: 20px;
    box-shadow:
        0 8px 22px rgba(15, 23, 42, 0.08),
        0 1px 2px rgba(15, 23, 42, 0.08);
    overflow: visible;
    transition:
        border-color 0.2s,
        box-shadow 0.2s,
        background-color 0.3s;
}

[data-theme="dark"] .input-box-wrapper {
    background: rgba(0, 0, 0, 0.1);
    border-color: var(--gray-700);
    box-shadow: none;
}

.input-box-wrapper:focus-within {
    border-color: #a5b4fc;
    box-shadow:
        0 0 0 4px rgba(99, 102, 241, 0.13),
        0 10px 24px rgba(15, 23, 42, 0.12);
}

[data-theme="dark"] .input-box-wrapper:focus-within {
    border-color: var(--bs-primary, #6366f1);
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
}

.message-input-container .input-row {
    display: flex;
    align-items: flex-end;
    gap: 0.5rem;
    min-height: 50px;
    padding: 0.5rem 0.58rem 0.5rem 0.62rem;
    background: transparent;
    border: none;
    box-shadow: none;
    align-items: center;
}

.btn-attach {
    width: 42px;
    height: 42px;
    border-radius: 14px;
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
    background: #f1f5ff;
    border-color: var(--gray-300);
}

.message-textarea {
    flex: 1;
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
    color: var(--gray-800);
}

.message-textarea:focus {
    outline: none;
}

/* Mentions and reply UI */
.input-with-suggestions {
    position: relative;
    flex: 1;
    min-width: 0; /* CRITICAL: prevents textarea intrinsic width from breaking flex container causing right-padding to disappear on mobile */
}

.mention-popover {
    position: absolute;
    left: 0;
    bottom: calc(100% + 10px);
    width: 100%;
    max-width: min(420px, calc(100vw - 24px));
    background: white;
    border: 2px solid var(--gray-200);
    border-radius: 12px;
    box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.15);
    max-height: 220px;
    overflow-y: auto;
    z-index: 999;
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

/* Reply bar — sits seamlessly integrated above the input */
.reply-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    background: transparent;
    border-bottom: none;
    border-radius: 16px 16px 0 0;
    padding: 10px 12px 2px 14px;
    margin-bottom: 0;
    position: relative;
    overflow: hidden;
    animation: slideDown 0.12s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.reply-bar-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--gray-500);
    font-size: 1.1rem;
    padding-bottom: 2px;
}

.reply-bar-content {
    flex: 1;
    min-width: 0;
}

.reply-bar-title {
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--primary);
    margin-bottom: 2px;
}

[data-theme="dark"] .reply-bar-title {
    color: var(--gray-400);
}

.reply-bar-preview {
    font-size: 0.88rem;
    color: var(--gray-600);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-style: italic;
}

[data-theme="dark"] .reply-bar-preview {
    color: var(--gray-500);
}

.reply-bar-cancel {
    border: none;
    background: transparent;
    cursor: pointer;
    color: var(--gray-500);
    padding: 4px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.25rem;
    width: 28px;
    height: 28px;
    transition: all 0.15s;
}

.reply-bar-cancel:hover {
    background: var(--gray-200);
    color: var(--gray-800);
}

[data-theme="dark"] .reply-bar-cancel:hover {
    background: rgba(255, 255, 255, 0.1);
    color: var(--gray-300);
}

/* Legacy classes kept for backward compat (message bubble reply display) */
.reply-title {
    font-weight: 700;
    margin-bottom: 2px;
}

.reply-preview {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.reply-inline {
    display: flex;
    gap: 8px;
    align-items: flex-start;
    margin-bottom: 6px;
    transition: all 0.2s;
    background: #f1f5f9;
    cursor: pointer;
    padding: 6px;
    border-radius: 8px;
}

.own-message .reply-inline {
    background: rgba(99, 102, 241, 0.1);
}

[data-theme="dark"] .reply-inline {
    background: rgba(255, 255, 255, 0.05);
}

[data-theme="dark"] .own-message .reply-inline {
    background: rgba(0, 0, 0, 0.2);
}

.reply-inline:hover {
    background: #e2e8f0;
    transform: translateX(2px);
}

.own-message .reply-inline:hover {
    background: rgba(255, 255, 255, 0.25);
}

[data-theme="dark"] .own-message .reply-inline:hover {
    background: rgba(0, 0, 0, 0.3);
}

.reply-inline-bar {
    width: 3px;
    background: var(--primary);
    border-radius: 2px;
    align-self: stretch;
}

[data-theme="dark"] .own-message .reply-inline-bar,
.own-message .reply-inline-bar {
    background: rgba(255, 255, 255, 0.6);
}

.reply-inline-content {
    flex: 1;
    padding-left: 2px;
    padding-right: 8px;
}

.reply-inline-title {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--gray-600);
    margin-bottom: 2px;
}

.own-message .reply-inline-title {
    color: var(--primary-dark);
}

[data-theme="dark"] .reply-inline-title {
    color: var(--gray-300);
}

[data-theme="dark"] .own-message .reply-inline-title {
    color: rgba(255, 255, 255, 0.9);
}

.reply-inline-text {
    font-size: 0.8125rem;
    color: var(--gray-600);
    line-height: 1.3;
}

.own-message .reply-inline-text {
    color: var(--gray-800);
}

[data-theme="dark"] .reply-inline-text {
    color: var(--gray-400);
}

[data-theme="dark"] .own-message .reply-inline-text {
    color: rgba(255, 255, 255, 0.8);
}

[data-theme="dark"] .reply-inline-text {
    color: var(--gray-400);
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
    width: 46px;
    height: 46px;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    border: none;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 1.12rem;
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
    min-height: 0;
    overflow: hidden;
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;
}

.info-profile {
    padding: 2rem 1.5rem;
    text-align: center;
    border-bottom: 2px solid var(--gray-200);
    background: linear-gradient(180deg, var(--primary-light) 0%, white 100%);
    position: relative;
    flex-shrink: 0;
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

.channel-info-panel {
    position: relative;
    height: 100%;
    display: flex;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
}

.info-resize-handle {
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 6px;
    cursor: ew-resize;
    background: transparent;
    z-index: 8;
    transition: background 0.2s;
}

.info-resize-handle:hover {
    background: var(--primary);
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
    flex: 1 1 auto;
    min-height: 0;
    overflow: hidden;
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
    min-height: 52px;
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
    min-width: 0;
    flex: 1;
}

.section-header-content span {
    min-width: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.section-toggle > i {
    flex-shrink: 0;
}

.section-content {
    padding: 1rem;
    border-top: 2px solid var(--gray-200);
    background: var(--gray-50);
    overflow: hidden;
}

.members-section-content {
    max-height: min(52vh, 420px);
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;
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

.member-item .btn-icon-secondary {
    flex-shrink: 0;
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
    box-shadow:
        0 20px 60px rgba(99, 102, 241, 0.2),
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
@media (max-width: 1200px) {
    .channels-sidebar {
        width: 300px;
    }

    .info-sidebar {
        width: 300px;
    }

    .message-group {
        max-width: 72%;
    }
}

@media (max-width: 1024px) {
    .channels-sidebar {
        width: 280px;
    }

    .chat-header {
        padding: 1rem 1.1rem;
    }

    .messages-container {
        padding: 1rem;
    }

    .message-group {
        max-width: 82%;
    }

    .modal-container {
        width: 95%;
        max-height: 90vh;
    }
}

@media (max-width: 768px) {
    .chat-container {
        position: relative;
    }

    .mobile-channels-toggle {
        display: inline-flex;
    }

    .channels-sidebar {
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: min(88vw, 340px);
        max-width: 340px;
        z-index: 90;
        transform: translateX(-102%);
        transition: transform 0.24s ease;
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.22);
        border-right: 1px solid var(--gray-200);
    }

    .channels-sidebar.show {
        transform: translateX(0);
    }

    .mobile-sidebar-overlay {
        display: block;
        position: absolute;
        inset: 0;
        z-index: 80;
        background: rgba(15, 23, 42, 0.34);
        backdrop-filter: blur(1.5px);
    }

    .chat-main {
        width: 100% !important;
    }

    .chat-header {
        padding: 0.75rem;
        gap: 0.55rem;
    }

    .header-left {
        gap: 0.6rem;
        min-width: 0;
        flex: 1;
    }

    .avatar-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        font-size: 1rem;
    }

    .status-dot {
        width: 10px;
        height: 10px;
        border-width: 1.5px;
    }

    .header-info {
        min-width: 0;
    }

    .header-title {
        font-size: 0.94rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 42vw;
    }

    .header-subtitle {
        font-size: 0.72rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .header-actions {
        gap: 0.35rem;
        flex-shrink: 0;
    }

    .header-actions .btn-icon-primary,
    .header-actions .btn-icon-secondary {
        width: 36px;
        height: 36px;
        border-radius: 9px;
        font-size: 1rem;
    }

    .messages-container {
        padding: 0.85rem;
        padding-bottom: calc(132px + env(safe-area-inset-bottom));
    }

    .message-group {
        max-width: 94%;
    }

    .message-avatar {
        width: 30px;
        height: 30px;
        font-size: 0.75rem;
    }

    .message-bubble {
        padding: 8px 12px;
    }

    .message-text {
        font-size: 0.88rem;
        line-height: 1.45;
    }

    .message-input-container {
        position: fixed;
        left: 12px;
        right: 12px;
        bottom: calc(60px + env(safe-area-inset-bottom));
        z-index: 90;
        padding: 0;
        background: transparent;
        border-top: none;
        box-shadow: none;
    }

    .chat-main.sidebar-open-mobile .message-input-container {
        opacity: 0;
        pointer-events: none;
        transform: translateY(8px);
    }

    /* Mobile: unified box gets the rounded pill style */
    .message-input-container .input-box-wrapper {
        border-radius: 28px;
        border-color: #d7ddea;
        box-shadow:
            0 8px 18px rgba(15, 23, 42, 0.08),
            0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .message-input-container .input-row {
        padding: 6px 12px 6px 16px;
        gap: 0.4rem;
        min-height: 52px;
        border: none;
        box-shadow: none;
        /* background: transparent; */
    }

    .btn-attach {
        width: 38px;
        height: 38px;
        border-radius: 50%;
    }

    .btn-send {
        width: 40px;
        height: 40px;
        border-radius: 50%; /* Make it perfectly circular on mobile for modern HIG/MD3 look */
        padding: 0;
        margin: 0;
    }

    .message-textarea {
        font-size: 1rem;
        line-height: 1.45;
        padding: 0.5rem 0.2rem; /* reduce inner padding so it doesn't constrain flex */
    }

    .info-sidebar.is-mobile {
        position: absolute;
        right: 0;
        top: 0;
        bottom: 0;
        z-index: 95;
        box-shadow: -10px 0 28px rgba(15, 23, 42, 0.2);
        border-left: 1px solid var(--gray-200);
        height: 100%;
        max-height: 100%;
        overflow: hidden;
    }

    .info-sidebar.is-mobile .channel-info-panel {
        height: 100%;
        min-height: 0;
        overflow: hidden;
    }

    .info-sidebar.is-mobile .info-sections {
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
        padding-bottom: calc(1rem + env(safe-area-inset-bottom));
    }

    .info-sidebar.is-mobile .members-section-content {
        max-height: min(50vh, 380px);
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
    }

    .info-sidebar.is-mobile .thread-panel {
        min-width: 0;
        max-width: none;
    }

    .info-sidebar.is-mobile .thread-resize-handle,
    .info-sidebar.is-mobile .info-resize-handle {
        display: none;
    }

    .thread-header,
    .thread-content,
    .thread-input-area {
        padding-left: 1rem;
    }
}

@media (max-width: 480px) {
    .channels-sidebar {
        width: 100%;
        max-width: none;
    }

    .header-title {
        max-width: 30vw;
    }

    .messages-container {
        padding: 0.72rem;
        padding-bottom: calc(126px + env(safe-area-inset-bottom));
    }

    .message-group {
        max-width: 98%;
    }

    .message-input-container {
        left: 0px;
        right: 10px;
        bottom: calc(60px + env(safe-area-inset-bottom));
        padding: 0;
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
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;
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
    /* border-top: 1px solid var(--gray-200);
    background: white;
    width: 100%; */
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

/* Thread input area — uses same input-box-wrapper and btn-attach/btn-send as main chat */
.thread-input-area {
    padding: 0px 6px 10px 6px;
    /* border-top: 1px solid var(--gray-200);
    background: white;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
    width: 100%; */
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

/* Chat enhancements: pins, saves, reactions, emoji picker, order suggest */
.pin-count {
    margin-left: 0.4rem;
    font-size: 0.72rem;
    padding: 0.1rem 0.45rem;
    border-radius: 999px;
    background: var(--primary-light);
    color: var(--primary-dark);
    font-weight: 600;
}

.pinned-panel {
    border: 1px solid var(--gray-200);
    border-radius: 12px;
    padding: 0.75rem;
    margin: 0.5rem 0.75rem 0;
    background: #fff;
}

.saved-panel {
    border-color: #c7d2fe;
    background: #f8faff;
}

.pinned-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.6rem;
}

.pinned-list {
    max-height: 180px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
}

.pinned-item {
    width: 100%;
    border: 1px solid var(--gray-200);
    border-radius: 10px;
    padding: 0.5rem 0.6rem;
    background: #fff;
    cursor: pointer;
    text-align: left;
}

.pinned-item:hover {
    border-color: var(--primary);
    background: var(--primary-light);
}

.pinned-item-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.75rem;
    color: var(--gray-500);
    margin-bottom: 0.2rem;
}

.pinned-item-body {
    font-size: 0.83rem;
    color: var(--gray-700);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pinned-empty {
    font-size: 0.8rem;
    color: var(--gray-500);
}

.panel-close {
    border: 1px solid var(--gray-300);
    border-radius: 999px;
    background: #fff;
    color: var(--gray-700);
    width: 28px;
    height: 28px;
}

.reaction-group {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.3rem;
    margin-top: 0.35rem;
}

.reaction-chip {
    border: 1px solid var(--gray-300);
    border-radius: 999px;
    background: #fff;
    padding: 0.12rem 0.45rem;
    font-size: 0.72rem;
    color: var(--gray-700);
}

.reaction-chip.mine {
    border-color: var(--primary);
    color: var(--primary-dark);
    background: var(--primary-light);
}

.quick-reactions {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.quick-reaction-btn {
    border: 1px solid transparent;
    border-radius: 999px;
    background: transparent;
    font-size: 0.95rem;
    line-height: 1;
    padding: 0.12rem 0.28rem;
    cursor: pointer;
}

.quick-reaction-btn:hover {
    border-color: var(--gray-300);
    background: #fff;
}

.pin-indicator {
    margin-right: 0.25rem;
    color: #f59e0b;
}

.emoji-mart-wrapper {
    position: absolute;
    left: 0;
    bottom: calc(100% + 10px);
    max-width: min(360px, calc(100vw - 24px));
    z-index: 999;
    box-shadow: var(--shadow);
}

.order-suggest-popover {
    max-height: 220px;
    overflow-y: auto;
}

.order-suggest-item.active {
    background: var(--primary-light);
    border-color: #c7d2fe;
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

/* Load older messages control */
.load-older-messages {
    display: flex;
    justify-content: center;
    padding: 0.75rem 0;
}

.btn-load-older {
    padding: 0.4rem 0.8rem;
    border-radius: 999px;
    border: 1px solid var(--gray-300);
    background: white;
    color: var(--gray-700);
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.15s ease;
}

.btn-load-older:hover:not(:disabled) {
    border-color: var(--primary);
    color: var(--primary);
}

.btn-load-older:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
