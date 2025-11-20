<template>
  <div class="chat-container">
    <!-- Channel List Sidebar -->
    <div class="channels-sidebar">
      <!-- Search Header -->
      <div class="sidebar-header">
        <div class="search-wrapper">
          <i class="bi bi-search search-icon"></i>
          <input type="text" v-model="searchQuery" @keyup="debounceSearch" placeholder="Search conversations..."
            class="search-input" />
        </div>
      </div>

      <!-- Channel Sections -->
      <div class="channels-scroll">
        <!-- Group Chats -->
        <div class="channel-section" v-if="groupChannels.length">
          <div class="section-header">
            <i class="bi bi-people-fill"></i>
            <span>Group Chats</span>
            <span class="count-badge">{{ groupChannels.length }}</span>
          </div>
          <div v-for="channel in groupChannels" :key="channel.id" @click="selectChannel(channel)"
            :class="['channel-item', { active: currentChannel?.id === channel.id }]">
            <div class="channel-avatar group">
              <i class="bi bi-people"></i>
            </div>
            <div class="channel-info">
              <div class="channel-header-row">
                <h5 class="channel-title">
                  {{ channel.name }}
                </h5>
                <span class="channel-time" v-if="lastMessagePreview[channel.id]?.time">
                  {{ lastMessagePreview[channel.id].time }}
                </span>
              </div>
              <div class="channel-preview-row">
                <p class="channel-preview">
                  {{ lastMessagePreview[channel.id]?.text || "No messages yet" }}
                </p>
                <span v-if="channel.unread_messages_count" class="unread-badge">
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
            <span class="count-badge">{{ personalChannels.length }}</span>
          </div>
          <div v-for="channel in personalChannels" :key="channel.id" @click="selectChannel(channel)"
            :class="['channel-item', { active: currentChannel?.id === channel.id }]">
            <div class="channel-avatar personal">
              {{ avatarInitials(channel.name) }}
            </div>
            <div class="channel-info">
              <div class="channel-header-row">
                <h5 class="channel-title">
                  {{ channel.name }}
                </h5>
                <span class="channel-time" v-if="lastMessagePreview[channel.id]?.time">
                  {{ lastMessagePreview[channel.id].time }}
                </span>
              </div>
              <div class="channel-preview-row">
                <p class="channel-preview">
                  {{ lastMessagePreview[channel.id]?.text || "No messages yet" }}
                </p>
                <span v-if="channel.unread_messages_count" class="unread-badge">
                  {{ channel.unread_messages_count }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Chat Area -->
    <div class="chat-main" v-if="currentChannel">
      <!-- Chat Header -->
      <div class="chat-header">
        <div class="header-left">
          <div class="header-avatar">
            <div v-if="isGroupChannel(currentChannel)" class="avatar-icon group">
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
            <p class="header-subtitle" v-else>
              {{ channelInfo.members?.length || 0 }} members
            </p>
          </div>
        </div>
        <div class="header-actions">
          <button v-if="canCreateChannel" class="btn-icon-primary" @click="openCreateChannel" title="New Channel">
            <i class="bi bi-plus-lg"></i>
          </button>
          <button v-if="currentChannel.can_manage_members" class="btn-icon-secondary" @click="openManageMembers"
            title="Manage Members">
            <i class="bi bi-people"></i>
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
              <span>{{ searchResults.length }} results found</span>
            </div>
            <button @click="clearSearch" class="btn-clear">
              <i class="bi bi-x-lg"></i>
              Clear
            </button>
          </div>
          <div class="search-results-list">
            <div v-for="message in searchResults" :key="message.id" @click="scrollToMessage(message)"
              class="search-result-item">
              <div class="result-channel">
                {{ message.channel.name }}
              </div>
              <div class="result-content">
                <strong>{{ message.sender?.name }}</strong>
                <span class="result-text">{{ message.body }}</span>
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
            <div v-for="(message, idx) in messages" :key="message.id" class="message-wrapper">
              <!-- Date Separator -->
              <div v-if="shouldShowDateSeparator(idx)" class="date-separator">
                <span class="date-label">{{ dayLabel(message.created_at) }}</span>
              </div>

              <!-- Message -->
              <div :class="[
                'message-group',
                {
                  'own-message': message.sender_id === userId,
                },
              ]">
                <!-- Avatar (for received messages) -->
                <div v-if="message.sender_id !== userId" class="message-avatar">
                  {{ avatarInitials(message.sender?.name) }}
                </div>

                <div class="message-content-wrapper">
                  <!-- Sender Name -->
                  <div v-if="message.sender && message.sender_id !== userId" class="message-sender">
                    {{ message.sender.name }}
                  </div>

                  <!-- Message Bubble -->
                  <div class="message-bubble">
                    <div v-if="message.metadata?.reply_to_id" class="reply-inline rounded-2"
                      style="background: whitesmoke">
                      <div class="reply-inline-bar"></div>
                      <div class="reply-inline-content">
                        <div class="reply-inline-title">
                          Replying to
                          {{
                            message.metadata?.reply_sender ||
                            resolveReply(message)?.sender?.name ||
                            "message"
                          }}
                        </div>
                        <div class="reply-inline-text">
                          {{
                            message.metadata?.reply_preview ||
                            resolveReply(message)?.body?.slice(0, 100) ||
                            "Attachment"
                          }}
                        </div>
                      </div>
                    </div>
                    <!-- Text Content -->
                    <div v-if="message.body" class="message-text" v-html="formatMessageWithMentions(message)"></div>

                    <!-- Attachments -->
                    <div v-if="message.attachments && message.attachments.length" class="message-attachments">
                      <div v-for="attachment in message.attachments" :key="attachment.id" class="attachment-item">
                        <img v-if="isImage(attachment)" :src="getAttachmentUrl(attachment)"
                          @click="openAttachment(attachment)" class="attachment-image" />
                        <div v-else class="attachment-file" @click="downloadAttachment(attachment)">
                          <i class="bi bi-file-earmark"></i>
                          <span>{{ attachment.filename }}</span>
                        </div>
                      </div>
                    </div>

                    <!-- Message Meta -->
                    <div class="message-meta">
                      <span class="message-time">{{
                        formatDate(message.created_at)
                      }}</span>
                      <button class="meta-action" title="Reply" @click="replyToMessage(message)">
                        <i class="bi bi-reply"></i>
                      </button>
                      <span v-if="message.sender_id === userId" class="message-status"
                        :title="readByOthers(message) ? 'Read' : 'Sent'">
                        <i v-if="readByOthers(message)" class="bi bi-check-all read"></i>
                        <i v-else class="bi bi-check-all"></i>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>

      <!-- Scroll to Bottom Button -->
      <button v-if="showScrollDown" @click="scrollToBottom" class="scroll-to-bottom" title="Scroll to latest">
        <i class="bi bi-arrow-down"></i>
      </button>

      <!-- Message Input -->
      <div class="message-input-container">
        <!-- Attachment Preview -->
        <div v-if="attachmentFiles.length" class="attachments-preview">
          <div v-for="(file, index) in attachmentFiles" :key="index" class="attachment-preview-item">
            <i class="bi bi-paperclip"></i>
            <span>{{ file.name }}</span>
            <button @click="removeAttachment(index)" class="remove-attachment">
              <i class="bi bi-x"></i>
            </button>
          </div>
        </div>

        <!-- Input Row -->
        <div class="input-row">
          <input type="file" ref="fileInput" @change="handleFiles" multiple style="display: none" />
          <button @click="$refs.fileInput.click()" class="btn-attach" title="Attach files">
            <i class="bi bi-paperclip"></i>
          </button>
          <div :class="['input-with-suggestions', { 'has-reply': !!replyTo }]">
            <div v-if="replyTo" class="reply-chip">
              <div class="reply-title">
                Replying to
                {{ replyTo.sender?.name || "message" }}
              </div>
              <div class="reply-preview">
                {{ replyTo.body?.slice(0, 80) || "Attachment" }}
              </div>
              <button class="reply-cancel" @click="replyTo = null" title="Cancel reply">
                <i class="bi bi-x"></i>
              </button>
            </div>
            <textarea v-model="newMessage" @keydown="onKeyDownInEditor" @input="onEditorInput"
              placeholder="Type a message... Use @ to mention" ref="messageInput" class="message-textarea"
              style="width: 100%" rows="1"></textarea>
              
            <div v-if="mentionOpen && mentionItems.length" class="mention-popover">
              <div v-for="(m, i) in mentionItems" :key="m.id" :class="['mention-item', { active: i === mentionIndex }]"
                @mousedown.prevent="pickMention(m)">
                <span class="mention-avatar">{{ avatarInitials(m.name) }}</span>
                <div class="mention-info">
                  <div class="mention-name">{{ m.name }}</div>
                  <div class="mention-email">
                    {{ m.email }}
                  </div>
                </div>
              </div>
            </div>
          </div>
          <button @click="sendMessage" :disabled="!canSendMessage" class="btn-send" title="Send message">
            <i class="bi bi-send-fill"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Right Sidebar (Channel Info) -->
    <div class="info-sidebar" v-if="currentChannel && showSidebar">
      <!-- Profile Card -->
      <div class="info-profile">
        <div class="profile-avatar-large">
          <div v-if="isGroupChannel(currentChannel)" class="avatar-large group">
            <i class="bi bi-people"></i>
          </div>
          <div v-else class="avatar-large personal">
            {{ avatarInitials(currentChannel.name) }}
          </div>
        </div>
        <h4 class="profile-name">{{ currentChannel.name }}</h4>
        <p class="profile-type">
          {{ isGroupChannel(currentChannel) ? "Group Chat" : "Direct Message" }}
        </p>
      </div>

      <!-- Info Sections -->
      <div class="info-sections">
        <!-- About Section (hidden in personal DMs for normal admins) -->
        <div class="info-section" v-if="showAboutSection">
          <button @click="togglePanel('info')" class="section-toggle">
            <div class="section-header-content">
              <i class="bi bi-info-circle"></i>
              <span>About</span>
            </div>
            <i :class="['bi', openPanel === 'info' ? 'bi-chevron-up' : 'bi-chevron-down']"></i>
          </button>
          <div v-show="openPanel === 'info'" class="section-content">
            <div v-if="channelInfo.description" class="info-item">
              <p>{{ channelInfo.description }}</p>
            </div>
            <div class="info-item">
              <label>Created by</label>
              <strong>
                <p>{{ channelInfo.creator?.name || "—" }}</p>
              </strong>
            </div>
            <div class="info-item">
              <label>Created on</label>
              <strong>
                <p>{{ formatFullDate(channelInfo.created_at) }}</p>
              </strong>
            </div>
          </div>
        </div>

        <!-- Members Section (hidden in personal DMs for normal admins) -->
        <div class="info-section" v-if="showMembersSection">
          <button @click="togglePanel('members')" class="section-toggle">
            <div class="section-header-content">
              <i class="bi bi-people"></i>
              <span>Members ({{ channelInfo.members?.length || 0 }})</span>
            </div>
            <i :class="[
              'bi',
              openPanel === 'members' ? 'bi-chevron-up' : 'bi-chevron-down',
            ]"></i>
          </button>
          <div v-show="openPanel === 'members'" class="section-content">
            <div v-for="member in channelInfo.members" :key="member.id" class="member-item">
              <div class="member-avatar">
                {{ avatarInitials(member.name) }}
              </div>
              <div class="member-info">
                <p class="member-name">{{ member.name }}</p>
                <p class="member-email">{{ member.email }}</p>
              </div>
              <button class="btn-secondary" @click="startDirect(member.id)">
                Message
              </button>
            </div>
          </div>
        </div>

        <!-- Media Section -->
        <div class="info-section">
          <button @click="togglePanel('media')" class="section-toggle">
            <div class="section-header-content">
              <i class="bi bi-image"></i>
              <span>Media ({{ sidebarImages.length }})</span>
            </div>
            <i :class="['bi', openPanel === 'media' ? 'bi-chevron-up' : 'bi-chevron-down']"></i>
          </button>
          <div v-show="openPanel === 'media'" class="section-content">
            <div v-if="!sidebarImages.length" class="empty-section">
              <i class="bi bi-image"></i>
              <p>No media yet</p>
            </div>
            <div v-else class="media-grid">
              <a v-for="img in sidebarImages" :key="img.id" :href="storageUrl(img)" target="_blank" class="media-item">
                <img :src="storageThumbUrl(img)" :alt="img.filename" />
              </a>
            </div>
          </div>
        </div>

        <!-- Files Section -->
        <div class="info-section">
          <button @click="togglePanel('files')" class="section-toggle">
            <div class="section-header-content">
              <i class="bi bi-file-earmark"></i>
              <span>Files ({{ sidebarFiles.length }})</span>
            </div>
            <i :class="['bi', openPanel === 'files' ? 'bi-chevron-up' : 'bi-chevron-down']"></i>
          </button>
          <div v-show="openPanel === 'files'" class="section-content">
            <div v-if="!sidebarFiles.length" class="empty-section">
              <i class="bi bi-file-earmark"></i>
              <p>No files yet</p>
            </div>
            <a v-else v-for="file in sidebarFiles" :key="file.id" :href="storageUrl(file)" target="_blank"
              class="file-link">
              <i class="bi bi-file-earmark"></i>
              <span>{{ file.filename }}</span>
            </a>
          </div>
        </div>

        <!-- Links Section -->
        <div class="info-section">
          <button @click="togglePanel('links')" class="section-toggle">
            <div class="section-header-content">
              <i class="bi bi-link-45deg"></i>
              <span>Links ({{ sidebarLinks.length }})</span>
            </div>
            <i :class="['bi', openPanel === 'links' ? 'bi-chevron-up' : 'bi-chevron-down']"></i>
          </button>
          <div v-show="openPanel === 'links'" class="section-content">
            <div v-if="!sidebarLinks.length" class="empty-section">
              <i class="bi bi-link-45deg"></i>
              <p>No links yet</p>
            </div>
            <a v-else v-for="link in sidebarLinks" :key="link.message_id + '-' + link.url" :href="link.url"
              target="_blank" class="file-link">
              <i class="bi bi-link-45deg"></i>
              <span>{{ link.url }}</span>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Manage Members Modal -->
    <div v-if="manageOpen" class="modal-overlay" @click.self="manageOpen = false">
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
            <input v-model="memberSearch" class="search-input" placeholder="Search members..." />
          </div>
          <div v-if="membersLoading" class="loading-state">
            <div class="spinner"></div>
            <p>Loading members...</p>
          </div>
          <div v-else class="members-list">
            <label v-for="admin in members.filter((a) =>
              (a.name + ' ' + a.email)
                .toLowerCase()
                .includes(memberSearch.toLowerCase())
            )" :key="admin.id" class="member-checkbox-item">
              <input type="checkbox" :checked="memberIds.includes(admin.id)" :disabled="admin.id === userId"
                @change="toggleMember(admin.id)" class="member-checkbox" />
              <div class="member-avatar small">
                {{ avatarInitials(admin.name) }}
              </div>
              <div class="member-details">
                <p class="member-name">
                  {{ admin.name }}
                  <span v-if="admin.id === userId" class="you-badge">You</span>
                </p>
                <p class="member-email">{{ admin.email }}</p>
              </div>
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="manageOpen = false" class="btn-secondary">Cancel</button>
          <button @click="saveMembers" class="btn-primary">Save Changes</button>
        </div>
      </div>
    </div>

    <!-- Create Channel Modal -->
    <div v-if="createOpen" class="modal-overlay" @click.self="createOpen = false">
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
            <input v-model="createName" class="form-input" placeholder="Enter channel name" />
          </div>
          <div class="form-group">
            <label class="form-label">Description (Optional)</label>
            <textarea v-model="createDescription" class="form-textarea" placeholder="Enter channel description"
              rows="3"></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Add Members</label>
            <div class="search-wrapper">
              <i class="bi bi-search search-icon"></i>
              <input v-model="createSearch" class="search-input" placeholder="Search members..." />
            </div>
          </div>
          <div class="members-list">
            <label v-for="admin in members.filter((a) =>
              (a.name + ' ' + a.email)
                .toLowerCase()
                .includes(createSearch.toLowerCase())
            )" :key="admin.id" class="member-checkbox-item">
              <input type="checkbox" :checked="createMemberIds.includes(admin.id)" :disabled="admin.id === userId"
                @change="toggleCreateMember(admin.id)" class="member-checkbox" />
              <div class="member-avatar small">
                {{ avatarInitials(admin.name) }}
              </div>
              <div class="member-details">
                <p class="member-name">
                  {{ admin.name }}
                  <span v-if="admin.id === userId" class="you-badge">You</span>
                </p>
                <p class="member-email">{{ admin.email }}</p>
              </div>
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="createOpen = false" class="btn-secondary">Cancel</button>
          <button @click="saveCreateChannel" class="btn-primary">Create Channel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount, watch, computed, nextTick } from "vue";
import axios from "axios";
import { format } from "date-fns";
import debounce from "lodash/debounce";
import DOMPurify from "dompurify";

const Echo = window.Echo;

export default {
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

    // Utility Functions
    const avatarInitials = (name) => {
      if (!name) return "?";
      const parts = name.trim().split(/\s+/);
      return ((parts[0]?.[0] || "") + (parts[1]?.[0] || "")).toUpperCase();
    };

    const isGroupChannel = (c) => {
      const t = (c?.type || "").toLowerCase();
      return t === "group" || t === "public";
    };

    const formatDate = (date) => format(new Date(date), "HH:mm");

    const formatFullDate = (date) => {
      if (!date) return "";
      try {
        return format(new Date(date), "dd MMM yyyy HH:mm");
      } catch {
        return "";
      }
    };

    const isImage = (attachment) => attachment.mime_type.startsWith("image/");

    const getAttachmentUrl = (attachment) =>
      `/storage/${attachment.thumbnail_path || attachment.path}`;

    const storageUrl = (item) => (item?.path ? `/storage/${item.path}` : "#");

    const storageThumbUrl = (item) => {
      const p = item?.thumbnail_path || item?.path;
      return p ? `/storage/${p}` : "";
    };

    const downloadAttachment = (attachment) => {
      window.open(`/storage/${attachment.path}`, "_blank");
    };

    const openAttachment = (attachment) => {
      window.open(getAttachmentUrl(attachment), "_blank");
    };

    // Computed Properties
    const groupChannels = computed(() => channels.value.filter(isGroupChannel));
    const personalChannels = computed(() =>
      channels.value.filter((c) => !isGroupChannel(c))
    );
    const showSidebar = computed(() => {
      const hint = channelInfo.value?.show_sidebar;
      if (typeof hint === "boolean") return hint;
      return (currentChannel.value?.type || "").toLowerCase() !== "public";
    });
    const canSendMessage = computed(
      () => newMessage.value.trim() || attachmentFiles.value.length > 0
    );
    const isSuperAdmin = computed(() => !!window.authAdminIsSuper);
    const isPersonalChannel = computed(() => (currentChannel.value?.type || '').toLowerCase() === 'personal');
    const showAboutSection = computed(() => !isPersonalChannel.value || isSuperAdmin.value);
    const showMembersSection = computed(() => !isPersonalChannel.value || isSuperAdmin.value);
    const typingLabel = computed(() => {
      const now = Date.now();
      Object.keys(typingUsers.value).forEach((uid) => {
        if (now - typingUsers.value[uid].at > 3500) delete typingUsers.value[uid];
      });
      const names = Object.values(typingUsers.value)
        .map((x) => x.name)
        .filter(Boolean);
      if (!names.length) return "";
      return `${names.slice(0, 2).join(", ")}${names.length > 2 ? ` +${names.length - 2}` : ""
        } is typing...`;
    });

    // Core Functions
    const loadChannels = async () => {
      try {
        const response = await axios.get("/admin/chat/channels");
        channels.value = (response.data || []).map((c) => ({
          unread_messages_count: 0,
          can_manage_members: false,
          ...c,
        }));
      } catch (error) {
        if (import.meta.env.DEV) console.error("Error loading channels:", error);
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
        messages.value.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

        if (messages.value.length) {
          updatePreview(channelId, messages.value[messages.value.length - 1]);
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
          if (import.meta.env.DEV) console.error("Error loading messages:", error);
        }
        return false;
      } finally {
        loadingMessages.value = false;
      }
    };

    const selectChannel = async (channel) => {
      currentChannel.value = channel;
      const ok = await loadMessages(channel.id, true);
      if (!ok) {
        currentChannel.value = null;
        return;
      }
      await loadSidebar(channel.id);
      scrollToBottom();
    };

    const sendMessage = async () => {
      if (!canSendMessage.value || !currentChannel.value?.id) return;

      const formData = new FormData();
      if (newMessage.value.trim()) {
        formData.append("body", newMessage.value);
      }
      attachmentFiles.value.forEach((file) => {
        formData.append("attachments[]", file);
      });
      // Append metadata as nested fields so Laravel treats it as array in multipart
      if (replyTo.value?.id) {
        formData.append("metadata[reply_to_id]", String(replyTo.value.id));
        if (replyTo.value?.body)
          formData.append("metadata[reply_preview]", replyTo.value.body.slice(0, 140));
        if (replyTo.value?.sender?.name)
          formData.append("metadata[reply_sender]", replyTo.value.sender.name);
      }
      if (pendingMentionIds.value.size) {
        Array.from(pendingMentionIds.value).forEach((id) => {
          formData.append("metadata[mentions][]", String(id));
        });
      }

      try {
        const {
          data,
        } = await axios.post(
          `/admin/chat/channels/${currentChannel.value.id}/messages`,
          formData,
          { headers: { "Content-Type": "multipart/form-data" } }
        );

        messages.value.push({
          ...data,
          attachments: Array.isArray(data.attachments) ? data.attachments : [],
          reads: Array.isArray(data.reads) ? data.reads : [],
        });
        messages.value.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
        updatePreview(currentChannel.value.id, data);

        newMessage.value = "";
        attachmentFiles.value = [];
        replyTo.value = null;
        pendingMentionIds.value = new Set();
        await loadSidebar(currentChannel.value.id);
        scrollToBottom();
      } catch (error) {
        if (import.meta.env.DEV) console.error("Error sending message:", error);
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
      return messages.value.find((m) => m.id === message.metadata.reply_to_id) || null;
    };

    const replyToMessage = (message) => {
      replyTo.value = message;
      nextTick(() => {
        try {
          messageInput.value?.focus();
        } catch (_) { }
      });
    };

    // Mention helpers
    const currentMembers = () =>
      (channelInfo.value?.members || []).filter((m) => m && m.id);
    const updateMentionList = () => {
      const q = mentionQuery.value.trim().toLowerCase();
      const items = currentMembers()
        .filter((m) => (m.name + " " + (m.email || "")).toLowerCase().includes(q))
        .slice(0, 8);
      mentionItems.value = items;
      mentionIndex.value = 0;
      mentionOpen.value = items.length > 0;
    };

    const onEditorInput = (e) => {
      handleTyping();
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
        if (["ArrowDown", "ArrowUp", "Enter", "Tab", "Escape"].includes(e.key)) {
          if (mentionItems.value.length === 0) {
            mentionOpen.value = false;
            return;
          }
          e.preventDefault();
        }
        if (e.key === "ArrowDown")
          mentionIndex.value = (mentionIndex.value + 1) % mentionItems.value.length;
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

      // When mention popover is not open, Enter should send message (preserve previous behavior)
      if (!mentionOpen.value && e.key === "Enter") {
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
      newMessage.value = before.replace(/(^|\s)@([\w.\- ]*)$/, insert) + after;
      // Move caret to just after the inserted mention
      nextTick(() => {
        try {
          const pos = (before.replace(/(^|\s)@([\w.\- ]*)$/, "") + insert).length;
          textarea.focus();
          textarea.setSelectionRange(pos, pos);
        } catch (_) { }
      });
      pendingMentionIds.value.add(m.id);
      mentionOpen.value = false;
    };

    const formatMessageWithMentions = (message) => {
      const text = message.body || "";
      const members = currentMembers();
      const byName = new Map(members.map((m) => [m.name, m]));
      const escaped = text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");
      // Allow single-character names as well; match spaces in the name
      const withMentions = escaped.replace(/(^|\s)@([\w.\- ]{1,50})/g, (all, sp, name) => {
        const m = byName.get(name.trim());
        if (!m) return all;
        return `${sp}<span class=\"mention-token\">@${name}</span>`;
      });
      // Sanitize the limited markup (only allow span with class)
      return DOMPurify.sanitize(withMentions, {
        ALLOWED_TAGS: ["span"],
        ALLOWED_ATTR: ["class"],
      });
    };

    const loadSidebar = async (channelId) => {
      if (!channelId) return;
      try {
        const { data } = await axios.get(`/admin/chat/channels/${channelId}/sidebar`);
        channelInfo.value = data.channel || {
          creator: null,
          members: [],
          created_at: null,
        };
        sidebarImages.value = Array.isArray(data.images) ? data.images : [];
        sidebarFiles.value = Array.isArray(data.files) ? data.files : [];
        sidebarLinks.value = Array.isArray(data.links) ? data.links : [];
      } catch (e) {
        if (import.meta.env.DEV) console.error("Failed to load sidebar", e);
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
        const response = await axios.get("/admin/chat/messages/search", { params });
        searchResults.value = response.data;
      } catch (error) {
        if (import.meta.env.DEV) console.error("Error searching messages:", error);
      } finally {
        isSearching.value = false;
      }
    }, 300);

    const clearSearch = () => {
      searchQuery.value = "";
      searchResults.value = [];
    };

    const scrollToMessage = (message) => {
      if (currentChannel.value?.id !== message.channel_id) {
        const channel = channels.value.find((c) => c.id === message.channel_id);
        if (channel) selectChannel(channel);
      }
      clearSearch();
    };

    const scrollToBottom = () => {
      setTimeout(() => {
        if (messageContainer.value) {
          messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
        }
      }, 100);
    };

    const onScrollMessages = () => {
      const el = messageContainer.value;
      if (!el) return;
      const nearBottom = el.scrollHeight - el.scrollTop - el.clientHeight < 120;
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
      return msg.reads.some((r) => r.user_id && r.user_id !== props.userId);
    };

    const handleTyping = () => {
      const now = Date.now();
      if (now - lastTypingSentAt.value < 1500) return;
      lastTypingSentAt.value = now;
      if (currentChannel.value?.id) {
        try {
          Echo.private(`chat.channel.${currentChannel.value.id}`).whisper("typing", {
            userId: props.userId,
            name: window?.authAdminName || "Someone",
          });
        } catch (e) { }
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
        if (import.meta.env.DEV) console.error("Failed to load members", e);
        window.showToast?.("Failed to load members");
        manageOpen.value = false;
      } finally {
        membersLoading.value = false;
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
        const unique = Array.from(new Set([...memberIds.value, props.userId]));
        await axios.put(`/admin/chat/channels/${currentChannel.value.id}/members`, {
          member_ids: unique,
        });
        window.showToast?.("Members updated");
        manageOpen.value = false;
        await loadChannels();
      } catch (e) {
        if (import.meta.env.DEV) console.error("Failed to update members", e);
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
      } catch (e) { }
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
        if (import.meta.env.DEV) console.error("Failed to start direct chat", e);
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
        const users = Array.from(new Set([...createMemberIds.value, props.userId]));
        await axios.post("/admin/chat/channels", {
          name: createName.value.trim(),
          description: createDescription.value.trim() || null,
          users,
        });
        window.showToast?.("Channel created");
        createOpen.value = false;
        await loadChannels();
      } catch (e) {
        if (import.meta.env.DEV) console.error("Failed to create channel", e);
        window.showToast?.("Failed to create channel");
      }
    };

    // WebSocket Setup
    const setupChannelListeners = (channelId) => {
      if (!channelId) return;

      return Echo.private(`chat.channel.${channelId}`)
        .listen("MessageSent", (e) => {
          if (e?.message?.sender_id === props.userId) return;
          messages.value.push({
            ...e.message,
            attachments: Array.isArray(e.message.attachments)
              ? e.message.attachments
              : [],
            reads: Array.isArray(e.message.reads) ? e.message.reads : [],
          });
          messages.value.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
          updatePreview(channelId, e.message);

          const hasAttachments =
            Array.isArray(e.message.attachments) && e.message.attachments.length > 0;
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
            if (!message.reads.some((read) => read.user_id === e.userId)) {
              message.reads.push({
                user_id: e.userId,
                read_at: new Date(),
              });
            }
            return message;
          });
        });
    };

    // Lifecycle Hooks
    onMounted(() => {
      loadChannels();
      checkCreateCapability();

      try {
        const notificationChannel = Echo.private(`admin.notifications.${props.userId}`);
        notificationChannel.listen("ChannelMembershipChanged", (e) => {
          if (!e || !e.channelId || !e.action) return;
          if (e.action === "removed") {
            if (currentChannel.value?.id === e.channelId) {
              try {
                Echo.leave(`chat.channel.${e.channelId}`);
              } catch (_) { }
              currentChannel.value = null;
            }
            channels.value = channels.value.filter((c) => c.id !== e.channelId);
            window.showToast?.("You were removed from a channel");
          } else if (e.action === "added") {
            loadChannels();
          }
        });

        // Listen for mention notifications directed at this admin
        notificationChannel.listen("UserMentioned", (e) => {
          try {
            const sender = e?.message?.sender?.name || 'Someone';
            const channelName = e?.message?.channel?.name || '';
            const msg = channelName ? `${sender} mentioned you in ${channelName}` : `${sender} mentioned you`;
            window.showToast?.(msg);
          } catch (_) { }
        });
      } catch (_) { }

      if (currentChannel.value?.id) {
        setupChannelListeners(currentChannel.value.id);
        loadSidebar(currentChannel.value.id);
      }

      if (messageContainer.value) {
        messageContainer.value.addEventListener("scroll", onScrollMessages);
      }
    });

    onBeforeUnmount(() => {
      if (messageContainer.value) {
        messageContainer.value.removeEventListener("scroll", onScrollMessages);
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
      } catch (_) { }

      // adjust on window resize as well
      window.addEventListener('resize', adjustMessagePadding);
      // initial adjust after DOM settled
      nextTick(adjustMessagePadding);
    });

    onBeforeUnmount(() => {
      try {
        if (_resizeObserver && inputContainer.value) {
          _resizeObserver.unobserve(inputContainer.value);
          _resizeObserver.disconnect();
        }
      } catch (_) { }
      window.removeEventListener('resize', adjustMessagePadding);
    });

    // Watch attachment changes / reply changes to adjust layout
    watch(attachmentFiles, () => nextTick(adjustMessagePadding), { deep: true });
    watch(replyTo, () => nextTick(adjustMessagePadding));

    watch(currentChannel, (newChannel, oldChannel) => {
      if (oldChannel?.id) {
        Echo.leave(`chat.channel.${oldChannel.id}`);
      }
      if (newChannel?.id) {
        setupChannelListeners(newChannel.id);
      }
    });

    watch(
      channels,
      (list) => {
        if (!currentChannel.value && Array.isArray(list) && list.length) {
          selectChannel(list[0]);
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
      scrollToBottom,
      shouldShowDateSeparator,
      dayLabel,
      readByOthers,
      togglePanel,
      openManageMembers,
      toggleMember,
      saveMembers,
      openCreateChannel,
      toggleCreateMember,
      saveCreateChannel,
      startDirect,
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
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
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
}

.channel-item:hover {
  background: var(--gray-50);
}

.channel-item.active {
  background: var(--primary-light);
  border-left-color: var(--primary);
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
.chat-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  background: linear-gradient(180deg, #fafbfc 0%, white 100%);
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
  /* Reserve space at the bottom so the last message isn't hidden behind the input area
     This accounts for the variable height of the input area (attachments, reply chip, etc.) */
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
  max-width: 70%;
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

.message-bubble {
  background: white;
  border-radius: 16px;
  padding: 0.75rem 1rem;
  box-shadow: var(--shadow-sm);
  border: 2px solid var(--gray-200);
}

.own-message .message-bubble {
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  color: white;
  border-color: transparent;
  box-shadow: var(--shadow);
}

.message-text {
  font-size: 0.9375rem;
  line-height: 1.5;
  word-wrap: break-word;
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
  max-width: 300px;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s;
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
  background: rgba(255, 255, 255, 0.2);
  color: white;
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
  color: rgba(255, 255, 255, 0.8);
}

.message-time {
  font-weight: 500;
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
  max-height: 140px;
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

.mention-token {
  background: rgba(99, 102, 241, 0.15);
  color: var(--primary-dark);
  padding: 0 4px;
  border-radius: 4px;
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
}

.reply-inline-bar {
  width: 3px;
  background: var(--primary);
  border-radius: 2px;
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
</style>
