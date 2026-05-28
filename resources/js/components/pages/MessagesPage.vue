<template>
  <div class="min-h-full bg-[linear-gradient(180deg,#F9F2EB_0%,#F4E8DC_42%,#F7F0E9_100%)] px-5 py-8 md:px-8 xl:px-10">
    <div class="mx-auto max-w-7xl">
      <section class="overflow-hidden rounded-[34px] border border-[#E8D7CA] bg-[linear-gradient(140deg,#FFFDFB_0%,#FFF6EE_46%,#FCF7F2_100%)] p-4 shadow-[0_26px_60px_-38px_rgba(122,82,54,0.42)] md:p-5">
        <div class="grid gap-4 xl:h-[76vh] xl:grid-cols-[340px_minmax(0,1fr)]">
          <aside class="overflow-hidden rounded-[28px] border border-[#EEE2D7] bg-[linear-gradient(180deg,#FBF7F3_0%,#F7EFE7_100%)] xl:flex xl:min-h-0 xl:flex-col">
            <div class="border-b border-[#EADDD2] px-5 py-5">
              <div class="flex items-center justify-between gap-3">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.28em] text-[#C19370]">Support Desk</p>
                  <h1 class="mt-2 text-3xl font-black text-[#4E3527]" style="font-family: 'Urbanist', sans-serif;">
                    Inbox
                  </h1>
                </div>

                <span class="rounded-full bg-[#FDEBDC] px-3 py-1 text-xs font-semibold text-[#B96D3F]">
                  {{ unreadCount }} unread
                </span>
              </div>

              <div class="mt-5 grid grid-cols-2 gap-3">
                <div class="rounded-[22px] border border-[#E7D8CB] bg-white/90 px-4 py-3 shadow-[0_10px_24px_-22px_rgba(101,67,43,0.7)]">
                  <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#B79A85]">Threads</p>
                  <p class="mt-2 text-2xl font-black text-[#4E3527]">{{ totalConversationCount }}</p>
                </div>
                <div class="rounded-[22px] border border-[#E7D8CB] bg-white/70 px-4 py-3 shadow-[0_10px_24px_-22px_rgba(101,67,43,0.6)]">
                  <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#B79A85]">Status</p>
                  <p class="mt-2 text-sm font-bold text-[#6B5547]">{{ activeStatusLabel }}</p>
                </div>
              </div>

              <label class="relative mt-5 block">
                <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-[#BBA89A]">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </span>
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Search conversations"
                  class="h-12 w-full rounded-full border border-[#E4D6CB] bg-white pr-4 pl-11 text-sm text-[#4F4944] outline-none transition focus:border-transparent focus:ring-2 focus:ring-[#C9876C]"
                />
              </label>
            </div>

            <div class="max-h-[28rem] overflow-y-auto px-3 py-3 xl:max-h-none xl:min-h-0 xl:flex-1">
              <button
                v-for="conversation in filteredConversations"
                :key="conversation.id"
                type="button"
                @click="selectConversation(conversation.id)"
                class="group relative mb-2 flex w-full items-start gap-3 overflow-hidden rounded-[24px] border px-3 py-3 text-left transition-all"
                :class="conversation.id === activeConversationId
                  ? 'border-[#E1D0C0] bg-white shadow-[0_18px_36px_-30px_rgba(122,82,54,0.48)] ring-1 ring-[#EADACD]'
                  : 'border-transparent bg-white/55 hover:border-[#E8D7CA] hover:bg-white/85'"
              >
                <div
                  v-if="conversation.id === activeConversationId"
                  class="absolute inset-y-3 left-0 w-1 rounded-r-full bg-[#C9876C]"
                ></div>

                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-[#C9876C] to-[#B8765B] text-sm font-bold text-white shadow-sm transition-transform group-hover:scale-105">
                  {{ conversation.avatar }}
                </div>

                <div class="min-w-0 flex-1">
                  <div class="flex items-center justify-between gap-3">
                    <p class="truncate text-sm font-semibold text-[#453A35]">
                      {{ conversation.name }}
                    </p>
                    <span class="shrink-0 text-[11px] font-semibold uppercase tracking-[0.16em] text-[#B79F8F]">
                      {{ conversation.time }}
                    </span>
                  </div>

                  <p class="mt-1 text-xs font-medium uppercase tracking-[0.16em] text-[#C68D64]">
                    {{ conversation.label }}
                  </p>
                  <p class="mt-1 truncate text-sm text-[#796F68]">
                    {{ conversation.preview }}
                  </p>
                </div>

                <div class="flex shrink-0 flex-col items-end gap-2">
                  <span
                    v-if="conversation.unread"
                    class="rounded-full bg-[#D96D45] px-2 py-1 text-[10px] font-bold uppercase tracking-[0.14em] text-white"
                  >
                    New
                  </span>
                  <span
                    v-else
                    class="text-[10px] font-semibold uppercase tracking-[0.14em] text-[#C1AB9C]"
                  >
                    Read
                  </span>
                </div>
              </button>

              <div
                v-if="!filteredConversations.length"
                class="rounded-[24px] border border-dashed border-[#E4D5C8] bg-white px-5 py-8 text-center text-sm text-[#7F746D]"
              >
                No conversations match your search.
              </div>
            </div>
          </aside>

          <section
            v-if="activeConversation"
            class="flex min-h-[60vh] flex-col overflow-hidden rounded-[30px] border border-[#E9DDD3] bg-[linear-gradient(180deg,#FFFCF9_0%,#FFF7EF_100%)] xl:h-full xl:min-h-0"
          >
            <div class="shrink-0 border-b border-[#EEE1D7] bg-[linear-gradient(120deg,#FFFFFF_0%,#FFF6EE_100%)] px-6 py-5">
              <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                  <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-[#D59A72] to-[#C27A51] text-base font-bold text-white shadow-md">
                    {{ activeConversation.avatar }}
                  </div>

                  <div>
                    <h2 class="text-2xl font-bold text-[#4C3326]" style="font-family: 'Urbanist', sans-serif;">
                      {{ activeConversation.name }}
                    </h2>
                    <p class="mt-1 text-sm text-[#8A7A6E]">
                      {{ activeConversation.subtitle }}
                    </p>
                  </div>
                </div>

                <div class="flex flex-wrap gap-2">
                  <span class="rounded-full bg-[#F8EADF] px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#B46C42]">
                    {{ activeConversation.label }}
                  </span>
                  <span class="rounded-full bg-[#F3EEE8] px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#7E736B]">
                    {{ activeConversation.messages.length }} messages
                  </span>
                </div>
              </div>
            </div>

            <div
              ref="messageScroller"
              class="flex-1 overflow-y-auto bg-[radial-gradient(circle_at_top,#fffaf5_0%,#f8f1e8_58%,#f3e8dd_100%)] px-4 py-5 md:px-6"
            >
              <div class="mx-auto flex min-h-full max-w-4xl flex-col justify-end gap-4">
                <template v-if="activeConversation.messages.length > 0">
                  <div class="self-center rounded-full border border-white/80 bg-white/85 px-4 py-1.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#B7A08F] shadow-sm">
                    Conversation active
                  </div>

                  <div
                    v-for="message in activeConversation.messages"
                    :key="message.id"
                    class="flex"
                    :class="message.sender === 'me' ? 'justify-end' : 'justify-start'"
                  >
                    <div
                      class="max-w-[85%] rounded-[24px] px-4 py-3 shadow-[0_18px_36px_-32px_rgba(95,59,35,0.5)] md:max-w-[70%]"
                      :class="message.sender === 'me'
                        ? 'rounded-br-[8px] bg-[#C9876C] text-white'
                        : 'rounded-bl-[8px] bg-white text-[#5D534D] ring-1 ring-[#E9DDD3]'"
                    >
                      <p class="text-sm leading-6">
                        {{ message.text }}
                      </p>
                      <p
                        class="mt-2 text-[11px] font-semibold uppercase tracking-[0.14em]"
                        :class="message.sender === 'me' ? 'text-white/75' : 'text-[#B49A89]'"
                      >
                        {{ message.time }}
                      </p>
                    </div>
                  </div>
                </template>
                <div v-else-if="isLoadingMessages" class="flex flex-1 items-center justify-center py-20 text-sm font-semibold text-[#9B806E]">
                  Loading messages...
                </div>
                <div v-else class="flex flex-1 flex-col items-center justify-center py-20 text-center">
                  <div class="flex h-20 w-20 items-center justify-center rounded-full bg-white shadow-sm ring-1 ring-[#E8D7CA]">
                    <svg class="h-10 w-10 text-[#C9876C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                  </div>
                  <h3 class="mt-6 text-xl font-bold text-[#4E3527]" style="font-family: 'Urbanist', sans-serif;">
                    Start a conversation
                  </h3>
                  <p class="mt-2 max-w-xs text-sm text-[#8A7A6E]">
                    Our team is here to help with your orders, custom cake designs, or any questions you have.
                  </p>
                </div>
              </div>
            </div>

            <div class="shrink-0 border-t border-[#EEE1D7] bg-[linear-gradient(180deg,#FFF8F2_0%,#FFFFFF_100%)] px-4 py-4 md:px-6">
              <div class="rounded-[28px] border border-[#E7D8CB] bg-white/95 p-3 shadow-[0_18px_42px_-36px_rgba(122,82,54,0.55)]">
                <div class="flex items-start gap-3">
                  <div class="hidden h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#F6E7DA] text-[#B8744E] md:flex">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 8h10M7 12h6m-7 8 3.6-3H19a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v9a2 2 0 002 2h1v3z" />
                    </svg>
                  </div>

                  <div class="min-w-0 flex-1">
                    <label class="block">
                      <span class="sr-only">Type a message</span>
                      <textarea
                        ref="draftTextarea"
                        v-model="draftMessage"
                        rows="1"
                        @keydown="handleDraftKeydown"
                        placeholder="Write a message to the bakery team"
                        class="min-h-[56px] w-full resize-none rounded-[22px] border border-[#E1D5CA] bg-[#FCFAF8] px-5 py-4 text-sm text-[#4F4944] outline-none transition focus:border-transparent focus:ring-2 focus:ring-[#C9876C]"
                        style="max-height: 160px;"
                      ></textarea>
                    </label>

                    <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                      <p class="text-xs font-medium text-[#9B8473]">
                        Press Enter to send. Use Shift + Enter for a new line.
                      </p>

                      <button
                        type="button"
                        @click="sendMessage"
                        :disabled="isSendingMessage"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-[#4A4541] px-6 py-3 font-semibold text-white shadow-md transition hover:bg-[#383431] disabled:cursor-wait disabled:opacity-70"
                      >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                        {{ isSendingMessage ? 'Sending...' : 'Send message' }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <section
            v-else
            class="flex min-h-[60vh] items-center justify-center rounded-[30px] border border-dashed border-[#E7D9CF] bg-[#FFFDFB] p-10 text-center xl:h-full"
          >
            <div>
              <h2 class="text-2xl font-bold text-[#50382A]" style="font-family: 'Urbanist', sans-serif;">
                No conversation selected
              </h2>
              <p class="mt-3 text-sm text-[#7F746C]">
                Pick a message thread from the left to continue chatting.
              </p>
            </div>
          </section>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
});

const searchQuery = ref('');
const draftMessage = ref('');
const draftTextarea = ref(null);
const messageScroller = ref(null);
const conversations = ref([]);
const activeConversationId = ref(null);
const isLoadingConversations = ref(true);
const isLoadingMessages = ref(false);
const isSendingMessage = ref(false);
let conversationPollTimer = null;
let messagePollTimer = null;
let conversationsRequest = null;
let messagesRequest = null;

const ACTIVE_MESSAGE_POLL_MS = 5000;
const CONVERSATION_POLL_MS = 10000;
const currentUserId = computed(() => Number(props.user.user_id || props.user.id || 0));

// Placeholder for new conversations
const newConversationPlaceholder = {
  id: 'new',
  name: 'BakerDan Admin',
  avatar: 'AD',
  label: 'Customer Support',
  subtitle: 'Replies within the day',
  time: 'Now',
  unread: false,
  preview: 'Start a new conversation with our team.',
  messages: [],
  loaded: true
};

const formatMessageTime = (dateValue) => {
  if (!dateValue) {
    return 'Now';
  }

  return new Date(dateValue).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const newestMessageId = (conversation) => Math.max(
  0,
  ...(conversation?.messages || []).map((message) => Number(message.id) || 0),
);

const normalizeConversation = (conv, existing = null) => ({
  id: conv.id,
  name: 'BakerDan Admin',
  avatar: 'AD',
  label: 'Customer Support',
  subtitle: 'Replies as soon as possible',
  time: conv.last_message_at_human || conv.time || 'No messages',
  unread: Number(conv.unread_count || 0) > 0 || Boolean(conv.unread),
  preview: conv.last_message_content || conv.preview || 'No messages yet',
  messages: existing?.messages || [],
  loaded: existing?.loaded || false,
  latestMessageId: Number(conv.latest_message_id || existing?.latestMessageId || newestMessageId(existing)) || 0,
});

const normalizeMessage = (msg) => ({
  id: msg.id,
  conversation_id: msg.conversation_id,
  sender: Number(msg.sender_id) === currentUserId.value ? 'me' : 'them',
  text: msg.content,
  time: formatMessageTime(msg.created_at),
});

const sortConversations = () => {
  conversations.value = [...conversations.value].sort((a, b) => Number(b.latestMessageId || 0) - Number(a.latestMessageId || 0));
};

const mergeMessages = (conversation, incomingMessages = []) => {
  if (!conversation || !incomingMessages.length) {
    return false;
  }

  const seen = new Set(conversation.messages.map((message) => Number(message.id)));
  let changed = false;

  incomingMessages.forEach((message) => {
    const normalized = normalizeMessage(message);
    if (seen.has(Number(normalized.id))) {
      return;
    }

    conversation.messages.push(normalized);
    conversation.latestMessageId = Math.max(Number(conversation.latestMessageId || 0), Number(normalized.id || 0));
    conversation.preview = normalized.text;
    conversation.time = 'Just now';
    seen.add(Number(normalized.id));
    changed = true;
  });

  if (changed) {
    conversation.messages.sort((a, b) => Number(a.id) - Number(b.id));
  }

  return changed;
};

const upsertConversation = (summary) => {
  const existing = conversations.value.find((conversation) => Number(conversation.id) === Number(summary.id));
  const normalized = normalizeConversation(summary, existing);

  if (existing) {
    Object.assign(existing, normalized, {
      messages: existing.messages,
      loaded: existing.loaded,
      latestMessageId: Math.max(Number(existing.latestMessageId || 0), Number(normalized.latestMessageId || 0)),
    });
    return existing;
  }

  conversations.value.push(normalized);
  return normalized;
};

const fetchConversations = async ({ silent = false } = {}) => {
  if (conversationsRequest) {
    return;
  }

  conversationsRequest = new AbortController();

  if (!silent) {
    isLoadingConversations.value = true;
  }

  try {
    const response = await axios.get('/api/conversations', { signal: conversationsRequest.signal });
    const summaries = Array.isArray(response.data) ? response.data : [];

    summaries.forEach(upsertConversation);
    sortConversations();
    
    if (conversations.value.length > 0) {
      if (!activeConversationId.value) {
        activeConversationId.value = conversations.value[0].id;
      }
    } else {
      activeConversationId.value = 'new';
    }
  } catch (error) {
    if (error.name !== 'CanceledError' && error.code !== 'ERR_CANCELED') {
      console.error('Failed to fetch conversations:', error);
    }
  } finally {
    isLoadingConversations.value = false;
    conversationsRequest = null;
  }
};

const fetchMessages = async (conversationId, { incremental = false, silent = false } = {}) => {
  if (conversationId === 'new') return;
  
  const conversation = conversations.value.find(c => c.id === conversationId);
  if (!conversation || (conversation.loaded && !incremental)) return;

  if (messagesRequest) {
    if (incremental) {
      return;
    }

    messagesRequest.abort();
  }

  messagesRequest = new AbortController();

  if (!silent) {
    isLoadingMessages.value = true;
  }

  try {
    const params = incremental ? { after_id: newestMessageId(conversation) } : {};
    const response = await axios.get(`/api/conversations/${conversationId}/messages`, {
      params,
      signal: messagesRequest.signal,
    });
    const incoming = Array.isArray(response.data) ? response.data : [];
    const changed = incremental
      ? mergeMessages(conversation, incoming)
      : (() => {
          conversation.messages = incoming.map(normalizeMessage);
          conversation.latestMessageId = newestMessageId(conversation);
          return true;
        })();

    conversation.loaded = true;
    if (changed) {
      scrollMessagesToBottom();
    }
  } catch (error) {
    if (error.name !== 'CanceledError' && error.code !== 'ERR_CANCELED') {
      console.error('Failed to fetch messages:', error);
    }
  } finally {
    isLoadingMessages.value = false;
    messagesRequest = null;
  }
};

const displayedConversations = computed(() => {
  if (conversations.value.length === 0) {
    return [newConversationPlaceholder];
  }
  return conversations.value;
});

const filteredConversations = computed(() => {
  const query = searchQuery.value.trim().toLowerCase();
  if (!query) return displayedConversations.value;

  return displayedConversations.value.filter((conversation) =>
    conversation.name.toLowerCase().includes(query)
    || conversation.label.toLowerCase().includes(query)
    || conversation.preview?.toLowerCase().includes(query)
  );
});

const activeConversation = computed(() => {
  if (activeConversationId.value === 'new') {
    return newConversationPlaceholder;
  }
  return conversations.value.find((conversation) => conversation.id === activeConversationId.value) || null;
});

const totalConversationCount = computed(() => conversations.value.length || 1);
const unreadCount = computed(() => conversations.value.filter((conversation) => conversation.unread).length);
const activeStatusLabel = computed(() => {
  if (isSendingMessage.value) {
    return 'Sending';
  }

  if (activeConversationId.value === 'new') {
    return 'Ready to start';
  }

  return activeConversation.value?.unread ? 'Unread updates' : 'Connected';
});

const resizeDraftTextarea = () => {
  nextTick(() => {
    if (!draftTextarea.value) {
      return;
    }

    draftTextarea.value.style.height = 'auto';
    draftTextarea.value.style.height = `${draftTextarea.value.scrollHeight}px`;
  });
};

const scrollMessagesToBottom = () => {
  nextTick(() => {
    if (messageScroller.value) {
      messageScroller.value.scrollTop = messageScroller.value.scrollHeight;
    }
  });
};

const selectConversation = (conversationId) => {
  activeConversationId.value = conversationId;
};

const handleDraftKeydown = (event) => {
  if (event.key === 'Enter' && !event.shiftKey) {
    event.preventDefault();
    sendMessage();
  }
};

watch(activeConversationId, (newId) => {
  if (newId && newId !== 'new') {
    fetchMessages(newId);
    markAsRead(newId);
  }
}, { immediate: true });

watch(draftMessage, () => {
  resizeDraftTextarea();
});

const markAsRead = async (conversationId) => {
  if (conversationId === 'new') return;
  try {
    await axios.post(`/api/conversations/${conversationId}/read`);
    const conversation = conversations.value.find(c => c.id === conversationId);
    if (conversation) conversation.unread = false;
  } catch (error) {
    console.error('Failed to mark as read:', error);
  }
};

const sendMessage = async () => {
  const text = draftMessage.value.trim();
  if (!text || !activeConversation.value || isSendingMessage.value) return;

  const tempId = Date.now();
  const currentConvId = activeConversationId.value;
  const isNew = currentConvId === 'new';
  isSendingMessage.value = true;

  // Optimistic update
  activeConversation.value.messages.push({
    id: tempId,
    sender: 'me',
    text: text,
    time: 'Sending...',
  });
  
  const messageContent = text;
  draftMessage.value = '';
  resizeDraftTextarea();
  scrollMessagesToBottom();

  try {
    const payload = {
      content: messageContent,
    };
    if (!isNew) {
      payload.conversation_id = currentConvId;
    }

    const response = await axios.post('/api/messages', payload);
    const savedMessage = response.data;
    
    if (isNew) {
      await fetchConversations({ silent: true });
      activeConversationId.value = savedMessage.conversation_id;
      const conversation = conversations.value.find((item) => Number(item.id) === Number(savedMessage.conversation_id));
      if (conversation) {
        mergeMessages(conversation, [savedMessage]);
        conversation.loaded = true;
      }
    } else {
      // Update temp message with real data
      const msgIndex = activeConversation.value.messages.findIndex(m => m.id === tempId);
      if (msgIndex !== -1) {
        activeConversation.value.messages[msgIndex] = normalizeMessage(savedMessage);
      }
      activeConversation.value.preview = messageContent;
      activeConversation.value.time = 'Just now';
      activeConversation.value.latestMessageId = Math.max(Number(activeConversation.value.latestMessageId || 0), Number(savedMessage.id || 0));
    }
  } catch (error) {
    console.error('Failed to send message:', error);
    // Remove optimistic message on error
    activeConversation.value.messages = activeConversation.value.messages.filter(m => m.id !== tempId);
    draftMessage.value = messageContent; // Restore draft
    resizeDraftTextarea();
  } finally {
    isSendingMessage.value = false;
  }
};

const handleIncomingMessage = (event) => {
  const message = event.message;
  let conversation = conversations.value.find(c => c.id === message.conversation_id);
  
  if (!conversation) {
    fetchConversations({ silent: true });
    return;
  }

  // Update preview
  conversation.preview = message.content;
  conversation.time = 'Just now';

  // If it's the active conversation, add to feed
  if (conversation.id === activeConversationId.value) {
    if (mergeMessages(conversation, [message])) {
      scrollMessagesToBottom();
      markAsRead(conversation.id);
    }
  } else {
    conversation.unread = true;
  }
};

const pollMessages = () => {
  if (document.hidden) {
    return;
  }

  if (activeConversationId.value && activeConversationId.value !== 'new') {
    fetchMessages(activeConversationId.value, { incremental: true, silent: true });
  }
};

const startPolling = () => {
  stopPolling();

  messagePollTimer = window.setInterval(pollMessages, ACTIVE_MESSAGE_POLL_MS);
  conversationPollTimer = window.setInterval(() => {
    if (!document.hidden) {
      fetchConversations({ silent: true });
    }
  }, CONVERSATION_POLL_MS);
};

const stopPolling = () => {
  if (messagePollTimer) {
    window.clearInterval(messagePollTimer);
    messagePollTimer = null;
  }

  if (conversationPollTimer) {
    window.clearInterval(conversationPollTimer);
    conversationPollTimer = null;
  }
};

const handleVisibilityChange = () => {
  if (!document.hidden) {
    pollMessages();
  }
};

onMounted(() => {
  fetchConversations();
  startPolling();
  resizeDraftTextarea();
  document.addEventListener('visibilitychange', handleVisibilityChange);

  if (window.Echo) {
    window.Echo.private(`user.${props.user.user_id}`)
      .listen('.message.sent', (e) => {
        handleIncomingMessage(e);
      });
  }
});

onUnmounted(() => {
  stopPolling();
  conversationsRequest?.abort();
  messagesRequest?.abort();
  document.removeEventListener('visibilitychange', handleVisibilityChange);

  if (window.Echo) {
    window.Echo.leave(`user.${props.user.user_id}`);
  }
});
</script>
