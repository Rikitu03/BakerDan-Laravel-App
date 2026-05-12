<template>
  <div class="min-h-full bg-[#F6EFE8] px-5 py-8 md:px-8 xl:px-10">
    <div class="mx-auto max-w-7xl">
      <section class="rounded-[34px] border border-[#E8D7CA] bg-white p-4 shadow-[0_26px_60px_-38px_rgba(122,82,54,0.42)] md:p-5">
        <div class="grid gap-4 xl:h-[76vh] xl:grid-cols-[340px_minmax(0,1fr)]">
          <aside class="overflow-hidden rounded-[28px] border border-[#EEE2D7] bg-[#FBF7F3] xl:flex xl:min-h-0 xl:flex-col">
            <div class="border-b border-[#EADDD2] px-5 py-5">
              <div class="flex items-center justify-between gap-3">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.28em] text-[#C19370]">Messages</p>
                  <h1 class="mt-2 text-3xl font-black text-[#4E3527]" style="font-family: 'Urbanist', sans-serif;">
                    Inbox
                  </h1>
                </div>

                <span class="rounded-full bg-[#FDEBDC] px-3 py-1 text-xs font-semibold text-[#B96D3F]">
                  {{ unreadCount }} unread
                </span>
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
                @click="activeConversationId = conversation.id"
                class="mb-2 flex w-full items-start gap-3 rounded-[22px] px-3 py-3 text-left transition-all"
                :class="conversation.id === activeConversationId
                  ? 'bg-white shadow-[0_18px_36px_-30px_rgba(122,82,54,0.48)] ring-1 ring-[#EADACD]'
                  : 'hover:bg-white/80'"
              >
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-[#C9876C] to-[#B8765B] text-sm font-bold text-white shadow-sm">
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

                <span
                  v-if="conversation.unread"
                  class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-[#D96D45]"
                ></span>
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
            class="flex min-h-[60vh] flex-col overflow-hidden rounded-[30px] border border-[#E9DDD3] bg-[#FFFCF9] xl:h-full xl:min-h-0"
          >
            <div class="shrink-0 border-b border-[#EEE1D7] bg-white px-6 py-5">
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
                    Online reply demo
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
                  <div class="self-center rounded-full bg-white/85 px-4 py-1.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#B7A08F] shadow-sm">
                    Today
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

            <div class="shrink-0 border-t border-[#EEE1D7] bg-white px-4 py-4 md:px-6">
              <div class="flex flex-col gap-3 md:flex-row md:items-end">
                <label class="block flex-1">
                  <span class="sr-only">Type a message</span>
                  <textarea
                    v-model="draftMessage"
                    rows="3"
                    @keydown.enter.prevent="sendMessage"
                    placeholder="Write a message to the bakery team"
                    class="w-full rounded-[24px] border border-[#E1D5CA] bg-[#FCFAF8] px-5 py-4 text-sm text-[#4F4944] outline-none transition focus:border-transparent focus:ring-2 focus:ring-[#C9876C]"
                  ></textarea>
                </label>

                <div class="flex items-center gap-3">
                  <button
                    type="button"
                    class="flex h-12 w-12 items-center justify-center rounded-full border border-[#E2D6CC] bg-white text-[#786D66] transition hover:border-[#D3B6A0] hover:text-[#B36F46]"
                    aria-label="Attach file"
                  >
                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.5 6.5l-7.78 7.78a3 3 0 104.24 4.24l8.49-8.48a5 5 0 10-7.07-7.07L5.2 12.15" />
                    </svg>
                  </button>

                  <button
                    type="button"
                    @click="sendMessage"
                    class="rounded-full bg-[#4A4541] px-6 py-3 font-semibold text-white shadow-md transition hover:bg-[#383431]"
                  >
                    Send
                  </button>
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
const messageScroller = ref(null);
const conversations = ref([]);
const activeConversationId = ref(null);
const isLoadingConversations = ref(true);
const isLoadingMessages = ref(false);

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

const fetchConversations = async () => {
  try {
    const response = await axios.get('/api/conversations');
    conversations.value = response.data.map(conv => ({
      id: conv.id,
      name: 'BakerDan Admin',
      avatar: 'AD',
      label: 'Customer Support',
      subtitle: 'Replies within the day',
      time: conv.last_message_at_human,
      unread: conv.unread_count > 0,
      preview: conv.last_message_content,
      messages: [],
      loaded: false
    }));
    
    if (conversations.value.length > 0) {
      if (!activeConversationId.value) {
        activeConversationId.value = conversations.value[0].id;
      }
    } else {
      activeConversationId.value = 'new';
    }
  } catch (error) {
    console.error('Failed to fetch conversations:', error);
  } finally {
    isLoadingConversations.value = false;
  }
};

const fetchMessages = async (conversationId) => {
  if (conversationId === 'new') return;
  
  const conversation = conversations.value.find(c => c.id === conversationId);
  if (!conversation || conversation.loaded) return;

  isLoadingMessages.value = true;
  try {
    const response = await axios.get(`/api/conversations/${conversationId}/messages`);
    conversation.messages = response.data.map(msg => ({
      id: msg.id,
      sender: Number(msg.sender_id) === Number(props.user.id) ? 'me' : 'them',
      text: msg.content,
      time: new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    }));
    conversation.loaded = true;
    scrollMessagesToBottom();
  } catch (error) {
    console.error('Failed to fetch messages:', error);
  } finally {
    isLoadingMessages.value = false;
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

const unreadCount = computed(() => conversations.value.filter((conversation) => conversation.unread).length);

const scrollMessagesToBottom = () => {
  nextTick(() => {
    if (messageScroller.value) {
      messageScroller.value.scrollTop = messageScroller.value.scrollHeight;
    }
  });
};

watch(activeConversationId, (newId) => {
  if (newId && newId !== 'new') {
    fetchMessages(newId);
    markAsRead(newId);
  }
}, { immediate: true });

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
  if (!text || !activeConversation.value) return;

  const tempId = Date.now();
  const currentConvId = activeConversationId.value;
  const isNew = currentConvId === 'new';

  // Optimistic update
  activeConversation.value.messages.push({
    id: tempId,
    sender: 'me',
    text: text,
    time: 'Sending...',
  });
  
  const messageContent = text;
  draftMessage.value = '';
  scrollMessagesToBottom();

  try {
    const payload = {
      content: messageContent,
    };
    if (!isNew) {
      payload.conversation_id = currentConvId;
    }

    const response = await axios.post('/api/messages', payload);
    
    if (isNew) {
      // If it was a new conversation, we need to refresh the list to get the real ID
      await fetchConversations();
      activeConversationId.value = response.data.conversation_id;
    } else {
      // Update temp message with real data
      const msgIndex = activeConversation.value.messages.findIndex(m => m.id === tempId);
      if (msgIndex !== -1) {
        activeConversation.value.messages[msgIndex].id = response.data.id;
        activeConversation.value.messages[msgIndex].time = new Date(response.data.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
      }
      activeConversation.value.preview = messageContent;
      activeConversation.value.time = 'Just now';
    }
  } catch (error) {
    console.error('Failed to send message:', error);
    // Remove optimistic message on error
    activeConversation.value.messages = activeConversation.value.messages.filter(m => m.id !== tempId);
    draftMessage.value = messageContent; // Restore draft
  }
};

const handleIncomingMessage = (event) => {
  const message = event.message;
  let conversation = conversations.value.find(c => c.id === message.conversation_id);
  
  if (!conversation) {
    fetchConversations();
    return;
  }

  // Update preview
  conversation.preview = message.content;
  conversation.time = 'Just now';

  // If it's the active conversation, add to feed
  if (conversation.id === activeConversationId.value) {
    if (!conversation.messages.find(m => m.id === message.id)) {
      conversation.messages.push({
        id: message.id,
        sender: Number(message.sender_id) === Number(props.user.id) ? 'me' : 'them',
        text: message.content,
        time: new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
      });
      scrollMessagesToBottom();
      markAsRead(conversation.id);
    }
  } else {
    conversation.unread = true;
  }
};

onMounted(() => {
  fetchConversations();

  if (window.Echo) {
    window.Echo.private(`user.${props.user.user_id}`)
      .listen('.message.sent', (e) => {
        handleIncomingMessage(e);
      });
  }
});

onUnmounted(() => {
  if (window.Echo) {
    window.Echo.leave(`user.${props.user.user_id}`);
  }
});
</script>
