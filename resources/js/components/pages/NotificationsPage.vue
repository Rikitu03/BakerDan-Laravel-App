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
              Stay updated with your latest orders, fresh releases, and bakery promos in one calm feed.
            </p>
          </div>

          <div class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-2xl bg-[#FFF6EF] px-4 py-3 ring-1 ring-[#F1DFCF]">
              <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#BD916F]">Unread</p>
              <p class="mt-1 text-2xl font-bold text-[#5F3B27]">{{ unreadCount }}</p>
            </div>
            <div class="rounded-2xl bg-[#FFF6EF] px-4 py-3 ring-1 ring-[#F1DFCF]">
              <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#BD916F]">Latest</p>
              <p class="mt-1 text-sm font-semibold text-[#5F3B27]">10 mins ago</p>
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
            class="rounded-[28px] bg-white/95 p-5 shadow-[0_20px_44px_-30px_rgba(122,82,54,0.42)] ring-1 ring-[#F0E1D6] md:p-6"
          >
            <div class="flex flex-col gap-4 md:flex-row md:items-start">
              <div
                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl"
                :class="alert.badgeClass"
              >
                <svg v-if="alert.icon === 'delivery'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 17h8M7 7h6m4 0h1.5a2 2 0 011.79 1.11l1.2 2.39A2 2 0 0122 11.39V15a2 2 0 01-2 2h-1m-12 0H4a2 2 0 01-2-2V9a2 2 0 012-2h1m2 10a2 2 0 11-4 0 2 2 0 014 0zm14 0a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <svg v-else-if="alert.icon === 'recipe'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 6h10M8 10h10M8 14h6M5 4h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z" />
                </svg>
                <svg v-else-if="alert.icon === 'promo'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h.01M17 17h.01M6 12l6-6 6 6-6 6-6-6z" />
                </svg>
                <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m-4-8h8m-9 10h10a2 2 0 002-2V8.83a2 2 0 00-.59-1.41l-2.83-2.83A2 2 0 0014.17 4H7a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>

              <div class="min-w-0 flex-1">
                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                  <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#C49F88]">
                      {{ alert.category }}
                    </p>
                    <h3 class="mt-1 text-xl font-bold text-[#4D2F1E]">
                      {{ alert.title }}
                    </h3>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-[#7D6D61]">
                      {{ alert.message }}
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

                <div v-if="alert.code" class="mt-4 inline-flex rounded-full bg-[#FFF4EA] px-4 py-2 text-sm font-semibold text-[#A95F35] ring-1 ring-[#F0D9C9]">
                  Use code: {{ alert.code }}
                </div>
              </div>
            </div>
          </article>
        </div>
      </section>

      <div class="mt-14 border-t border-[#E7D7CB] pt-8 text-center text-sm text-[#B19A8C]">
        You're all caught up for now.
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const previewBread = '/images/bakerdan/Bread.png';
const previewGarlic = '/images/bakerdan/Creme_Cheese_Garlic.png';

const filters = [
  { id: 'all', label: 'All Notifications' },
  { id: 'orders', label: 'Order Updates' },
  { id: 'bakery', label: 'Bakery News' },
  { id: 'promo', label: 'Promotions' },
];

const activeFilter = ref('all');

const alerts = ref([
  {
    id: 1,
    group: 'Today',
    type: 'orders',
    category: 'Order Update',
    title: 'Order #123 out for delivery',
    message: 'Your artisan sourdough loaf and morning pastries are on their way to your doorstep. Please stay nearby.',
    time: '10 mins ago',
    unread: true,
    icon: 'delivery',
    badgeClass: 'bg-[#FCE9DD] text-[#B56537]',
  },
  {
    id: 2,
    group: 'Today',
    type: 'bakery',
    category: 'Bakery News',
    title: 'New recipe: Blueberry Scones',
    message: 'Our master bakers just perfected a new blueberry scone recipe with a hint of lemon zest. Available tomorrow morning.',
    time: '21 mins ago',
    unread: true,
    icon: 'recipe',
    badgeClass: 'bg-[#F7EEE4] text-[#8F5F41]',
    image: previewGarlic,
  },
  {
    id: 3,
    group: 'Yesterday',
    type: 'promo',
    category: 'Promo Alert',
    title: '20% off your next loaf',
    message: 'A special gift for our regular bread lovers. Use the code below at checkout to redeem your discount.',
    time: 'Yesterday, 4:30 PM',
    unread: false,
    icon: 'promo',
    badgeClass: 'bg-[#FBE7DE] text-[#AD6134]',
    code: 'BREADSPREE',
  },
  {
    id: 4,
    group: 'Yesterday',
    type: 'bakery',
    category: 'Bakery News',
    title: 'Fresh batch ready!',
    message: "The morning sourdough batch has just come out of the oven. Come and grab yours while they're still warm.",
    time: 'Yesterday, 11:20 AM',
    unread: false,
    icon: 'recipe',
    badgeClass: 'bg-[#F7EEE4] text-[#8F5F41]',
    image: previewBread,
  },
]);

const unreadCount = computed(() => alerts.value.filter((alert) => alert.unread).length);

const filteredAlerts = computed(() => {
  if (activeFilter.value === 'all') {
    return alerts.value;
  }

  return alerts.value.filter((alert) => alert.type === activeFilter.value);
});

const groupedAlerts = computed(() => {
  const groups = ['Today', 'Yesterday'];

  return groups
    .map((label) => ({
      label,
      items: filteredAlerts.value.filter((alert) => alert.group === label),
    }))
    .filter((group) => group.items.length > 0);
});
</script>
