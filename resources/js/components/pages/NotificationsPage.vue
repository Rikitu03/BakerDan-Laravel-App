<template>
  <div class="min-h-full bg-[#FBF6F0] px-5 py-8 md:px-8 xl:px-12">
    <div class="mx-auto max-w-6xl">
      <div class="mb-8 rounded-[32px] bg-white px-6 py-7 shadow-[0_24px_50px_-34px_rgba(113,72,44,0.48)] ring-1 ring-[#F2E2D6] md:px-8">
        <div class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#C09472]">Notifications Center</p>
            <h1 class="mt-3 text-4xl font-black text-[#8E4F2A]" style="font-family: 'Urbanist', sans-serif;">
              Recent Alerts
            </h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-[#7E6C60]">
              Stay updated with your latest orders and fresh bakery releases in one calm feed.
            </p>
          </div>

          <div class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-2xl bg-[#FFF6EF] px-4 py-3 ring-1 ring-[#F1DFCF]">
              <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#BD916F]">Unread</p>
              <p class="mt-1 text-2xl font-bold text-[#5F3B27]">{{ unreadCount }}</p>
            </div>
            <div class="rounded-2xl bg-[#FFF6EF] px-4 py-3 ring-1 ring-[#F1DFCF]">
              <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#BD916F]">Latest</p>
              <p class="mt-1 text-sm font-semibold text-[#5F3B27]">{{ latestNotificationTime }}</p>
            </div>
          </div>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
          <button
            v-for="filter in filters"
            :key="filter.id"
            type="button"
            @click="activeFilter = filter.id"
            class="rounded-full px-4 py-2 text-sm font-semibold transition-all"
            :class="activeFilter === filter.id
              ? 'bg-[#B96C3D] text-white shadow-md'
              : 'bg-[#F6EEE7] text-[#7F6757] hover:bg-[#F0E2D5]'"
          >
            {{ filter.label }}
          </button>
        </div>
      </div>

      <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-[24px] bg-white/85 px-5 py-4 shadow-[0_16px_36px_-30px_rgba(122,82,54,0.38)] ring-1 ring-[#F0E1D6]">
        <p class="text-sm text-[#7D6D61]">
          Tap a notification to open its related page and mark it as read.
        </p>
        <button
          type="button"
          @click="handleMarkAllRead"
          :disabled="unreadCount === 0"
          class="rounded-full px-4 py-2 text-sm font-semibold transition"
          :class="unreadCount > 0
            ? 'bg-[#B96C3D] text-white hover:bg-[#A75C2F]'
            : 'bg-[#F6EEE7] text-[#B7A091]'"
        >
          Mark all as read
        </button>
      </div>

      <div v-if="isLoading" class="rounded-[28px] bg-white/95 p-6 text-sm text-[#7D6D61] shadow-[0_20px_44px_-30px_rgba(122,82,54,0.42)] ring-1 ring-[#F0E1D6]">
        Loading your latest notifications...
      </div>

      <div v-else-if="groupedAlerts.length === 0" class="rounded-[28px] bg-white/95 p-8 text-center shadow-[0_20px_44px_-30px_rgba(122,82,54,0.42)] ring-1 ring-[#F0E1D6]">
        <h2 class="text-2xl font-bold text-[#4D2F1E]">You're all caught up</h2>
        <p class="mt-2 text-sm text-[#7D6D61]">New order and product notifications will appear here once they arrive.</p>
      </div>

      <section
        v-for="group in groupedAlerts"
        :key="group.label"
        class="mb-10"
      >
        <h2 class="mb-4 px-1 text-xs font-semibold uppercase tracking-[0.28em] text-[#C3A08A]">
          {{ group.label }}
        </h2>

        <div class="space-y-4">
          <article
            v-for="alert in group.items"
            :key="alert.id"
            role="button"
            tabindex="0"
            @click="openNotification(alert)"
            @keydown.enter.prevent="openNotification(alert)"
            @keydown.space.prevent="openNotification(alert)"
            class="rounded-[28px] p-5 shadow-[0_20px_44px_-30px_rgba(122,82,54,0.42)] ring-1 transition-all hover:-translate-y-0.5 hover:shadow-[0_24px_48px_-30px_rgba(122,82,54,0.48)] md:p-6"
            :class="alert.unread
              ? 'bg-[#FFF8F2] ring-[#EBCDB8]'
              : 'bg-white/95 ring-[#F0E1D6]'"
          >
            <div class="flex flex-col gap-4 md:flex-row md:items-start">
              <div
                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl"
                :class="alert.badgeClass"
              >
                <svg v-if="alert.icon === 'delivery'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 17h8M7 7h6m4 0h1.5a2 2 0 011.79 1.11l1.2 2.39A2 2 0 0122 11.39V15a2 2 0 01-2 2h-1m-12 0H4a2 2 0 01-2-2V9a2 2 0 012-2h1m2 10a2 2 0 11-4 0 2 2 0 014 0zm14 0a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 6h10M8 10h10M8 14h6M5 4h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z" />
                </svg>
              </div>

              <div class="min-w-0 flex-1">
                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                  <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#C49F88]">
                      {{ alert.category }}
                    </p>
                    <div class="mt-1 flex flex-wrap items-center gap-2">
                      <h3 class="text-xl font-bold text-[#4D2F1E]">
                        {{ alert.title }}
                      </h3>
                      <span
                        v-if="alert.unread"
                        class="rounded-full bg-[#B96C3D] px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-white"
                      >
                        New
                      </span>
                    </div>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-[#7D6D61]">
                      {{ alert.message }}
                    </p>
                    <p class="mt-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#B96C3D]">
                      {{ alert.actionLabel }}
                    </p>
                  </div>

                  <p class="shrink-0 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#B9A090]">
                    {{ alert.time }}
                  </p>
                </div>

                <img
                  v-if="alert.image"
                  :src="alert.image"
                  :alt="alert.title"
                  class="mt-4 h-36 w-full max-w-md rounded-[22px] object-cover"
                />
              </div>
            </div>
          </article>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useSpaRouter } from '../../router';
import { useNotificationStore } from '../../services/notificationStore';

const filters = [
  { id: 'all', label: 'All Notifications' },
  { id: 'orders', label: 'Order Updates' },
  { id: 'bakery', label: 'Bakery News' },
];

const activeFilter = ref('all');
const { push } = useSpaRouter();
const { notifications, unreadCount, isLoading, loadNotifications, markAsRead, markAllAsRead } = useNotificationStore();

const latestNotificationTime = computed(() => notifications.value[0]?.time || 'No updates yet');

const normalizedAlerts = computed(() => notifications.value.map((notification) => {
  const isOrder = notification.type === 'order';
  const createdAt = notification.created_at ? new Date(notification.created_at) : new Date();
  const today = new Date();
  const yesterday = new Date();
  yesterday.setDate(today.getDate() - 1);

  let group = 'Earlier';
  if (createdAt.toDateString() === today.toDateString()) {
    group = 'Today';
  } else if (createdAt.toDateString() === yesterday.toDateString()) {
    group = 'Yesterday';
  }

  return {
    ...notification,
    actionLabel: notification.type === 'order' ? 'Open order details' : 'Open notifications center',
    group,
    category: isOrder ? 'Order Update' : 'Bakery News',
    icon: isOrder ? 'delivery' : 'recipe',
    badgeClass: isOrder ? 'bg-[#FCE9DD] text-[#B56537]' : 'bg-[#F7EEE4] text-[#8F5F41]',
    filterType: isOrder ? 'orders' : 'bakery',
  };
}));

const filteredAlerts = computed(() => {
  if (activeFilter.value === 'all') {
    return normalizedAlerts.value;
  }

  return normalizedAlerts.value.filter((alert) => alert.filterType === activeFilter.value);
});

const groupedAlerts = computed(() => {
  const groups = ['Today', 'Yesterday', 'Earlier'];

  return groups
    .map((label) => ({
      label,
      items: filteredAlerts.value.filter((alert) => alert.group === label),
    }))
    .filter((group) => group.items.length > 0);
  });

onMounted(async () => {
  await loadNotifications({ force: true }).catch(() => {});
});

const handleMarkAllRead = async () => {
  await markAllAsRead().catch(() => {});
};

const resolveNotificationTarget = (alert) => {
  if (alert.type === 'order' && alert.payload?.order_id) {
    return `/customer/cart?ordered=${alert.payload.order_id}`;
  }

  return '/customer/notifications';
};

const openNotification = async (alert) => {
  if (alert.unread) {
    await markAsRead(alert.id).catch(() => {});
  }

  push(resolveNotificationTarget(alert));
};
</script>
