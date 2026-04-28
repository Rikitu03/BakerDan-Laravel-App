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
              </div>
            </div>

            <div class="shrink-0 border-t border-[#EEE1D7] bg-white px-4 py-4 md:px-6">
              <div class="flex flex-col gap-3 md:flex-row md:items-end">
                <label class="block flex-1">
                  <span class="sr-only">Type a message</span>
                  <textarea
                    v-model="draftMessage"
                    rows="3"
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
import { computed, nextTick, ref, watch } from 'vue';

const searchQuery = ref('');
const draftMessage = ref('');
const messageScroller = ref(null);

const conversations = ref([
  {
    id: 1,
    name: 'Bakerdan Support',
    avatar: 'BS',
    label: 'Order Help',
    subtitle: 'Replies within the day',
    time: '2m',
    unread: true,
    preview: 'We can reserve your cake slot for Saturday pickup.',
    messages: [
      { id: 1, sender: 'them', text: 'Hi! We saw your inquiry about the custom cake design.', time: '9:14 AM' },
      { id: 2, sender: 'me', text: 'Yes please, I wanted to confirm if Saturday pickup is still available.', time: '9:18 AM' },
      { id: 3, sender: 'them', text: 'We can reserve your cake slot for Saturday pickup. Just send the preferred theme and size.', time: '9:20 AM' },
    ],
  },
  {
    id: 2,
    name: 'Bakerdan Orders',
    avatar: 'BO',
    label: 'Order Status',
    subtitle: 'Tracking and updates',
    time: '18m',
    unread: true,
    preview: 'Order #145 is now being prepared by the kitchen.',
    messages: [
      { id: 1, sender: 'them', text: 'Order #145 is now being prepared by the kitchen.', time: '8:56 AM' },
      { id: 2, sender: 'me', text: 'Thank you. Please let me know once it is ready for dispatch.', time: '9:01 AM' },
    ],
  },
  {
    id: 3,
    name: 'Bakerdan Promos',
    avatar: 'BP',
    label: 'Bakery News',
    subtitle: 'Fresh releases and bundles',
    time: '1h',
    unread: false,
    preview: 'New pastry bundle launches tomorrow morning.',
    messages: [
      { id: 1, sender: 'them', text: 'New pastry bundle launches tomorrow morning with cream puffs and cinnamon rolls.', time: '8:02 AM' },
    ],
  },
]);

const activeConversationId = ref(conversations.value[0]?.id ?? null);

const filteredConversations = computed(() => {
  const query = searchQuery.value.trim().toLowerCase();

  if (!query) {
    return conversations.value;
  }

  return conversations.value.filter((conversation) =>
    conversation.name.toLowerCase().includes(query)
    || conversation.label.toLowerCase().includes(query)
    || conversation.preview.toLowerCase().includes(query),
  );
});

const activeConversation = computed(() =>
  conversations.value.find((conversation) => conversation.id === activeConversationId.value) || null,
);

const unreadCount = computed(() => conversations.value.filter((conversation) => conversation.unread).length);

const scrollMessagesToBottom = () => {
  nextTick(() => {
    if (!messageScroller.value) {
      return;
    }

    messageScroller.value.scrollTop = messageScroller.value.scrollHeight;
  });
};

watch(activeConversationId, () => {
  scrollMessagesToBottom();
}, { immediate: true });

const sendMessage = () => {
  const message = draftMessage.value.trim();

  if (!message || !activeConversation.value) {
    return;
  }

  activeConversation.value.messages.push({
    id: Date.now(),
    sender: 'me',
    text: message,
    time: 'Just now',
  });

  activeConversation.value.preview = message;
  activeConversation.value.time = 'now';
  activeConversation.value.unread = false;
  draftMessage.value = '';
  scrollMessagesToBottom();
};
</script>
