<template>
  <!-- Mobile overlay backdrop -->
  <transition
    enter-active-class="transition-opacity duration-300"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition-opacity duration-200"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="open"
      class="fixed inset-0 z-40 bg-black/40 lg:hidden"
      @click="emit('close')"
    ></div>
  </transition>

  <!-- Sidebar panel -->
  <aside
    class="
      fixed inset-y-0 left-0 z-50 w-[272px] transform border-r border-[#EFE5DC] bg-white px-6 py-7 transition-transform duration-300 ease-in-out overflow-y-auto
      lg:static lg:z-auto lg:translate-x-0 lg:transition-none
    "
    :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
  >
    <!-- Close button (mobile only) -->
    <div class="mb-4 flex items-center justify-between lg:hidden">
      <h2 class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-500">Menu</h2>
      <button
        type="button"
        aria-label="Close sidebar"
        class="rounded-lg p-1.5 text-gray-500 transition-colors hover:bg-[#F7F2EC]"
        @click="emit('close')"
      >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <div class="mb-8">
      <h2 class="mb-4 text-xs font-semibold uppercase tracking-[0.22em] text-gray-500">
        All Categories
      </h2>

      <div class="space-y-2">
        <button
          v-for="category in categories"
          :key="category.name"
          @click="selectCategory(category.name)"
          class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 transition-all duration-200"
          :class="category.active
            ? 'bg-[#C9876C] text-white shadow-md'
            : 'text-gray-700 hover:bg-[#F7F2EC]'"
        >
          <svg
            v-if="getCategoryIcon(category.name) === 'all'"
            class="h-5 w-5"
            fill="currentColor"
            viewBox="0 0 24 24"
          >
            <path d="M4 4h7v7H4V4zm9 0h7v7h-7V4zM4 13h7v7H4v-7zm9 0h7v7h-7v-7z" />
          </svg>
          <svg
            v-else-if="getCategoryIcon(category.name) === 'bread'"
            class="h-5 w-5"
            fill="currentColor"
            viewBox="0 0 24 24"
          >
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" />
          </svg>
          <svg
            v-else-if="getCategoryIcon(category.name) === 'pastry' || getCategoryIcon(category.name) === 'tart'"
            class="h-5 w-5"
            fill="currentColor"
            viewBox="0 0 24 24"
          >
            <path d="M21 3H3v18h18V3zm-2 16H5V5h14v14z" />
          </svg>
          <svg
            v-else-if="getCategoryIcon(category.name) === 'cake'"
            class="h-5 w-5"
            fill="currentColor"
            viewBox="0 0 24 24"
          >
            <path d="M12 6c1.11 0 2-.9 2-2 0-.38-.1-.73-.29-1.03L12 0l-1.71 2.97c-.19.3-.29.65-.29 1.03 0 1.1.9 2 2 2zm4.6 9.99l-1.07-1.07-1.08 1.07c-1.3 1.3-3.58 1.31-4.89 0l-1.07-1.07-1.09 1.07C6.75 16.64 5.88 17 4.96 17c-.73 0-1.4-.23-1.96-.61V21c0 .55.45 1 1 1h16c.55 0 1-.45 1-1v-4.61c-.56.38-1.23.61-1.96.61-.92 0-1.79-.36-2.44-1.01zM18 9h-5V7h-2v2H6c-1.66 0-3 1.34-3 3v1.54c0 1.08.88 1.96 1.96 1.96.52 0 1.02-.2 1.38-.57l2.14-2.13 2.13 2.13c.74.74 2.03.74 2.77 0l2.14-2.13 2.13 2.13c.37.37.86.57 1.38.57 1.08 0 1.96-.88 1.96-1.96V12C21 10.34 19.66 9 18 9z" />
          </svg>
          <svg
            v-else-if="getCategoryIcon(category.name) === 'customize'"
            class="h-5 w-5"
            fill="currentColor"
            viewBox="0 0 24 24"
          >
            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34a.9959.9959 0 00-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" />
          </svg>
          <svg
            v-else
            class="h-5 w-5"
            fill="currentColor"
            viewBox="0 0 24 24"
          >
            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
          </svg>
          <span class="font-medium">{{ category.name }}</span>
        </button>
      </div>
    </div>

    <!-- Mobile-only quick links -->
    <div class="mb-6 space-y-2 lg:hidden">
      <h2 class="mb-3 text-xs font-semibold uppercase tracking-[0.22em] text-gray-500">Quick Links</h2>
      <button
        v-for="link in mobileLinks"
        :key="link.path"
        @click="navigateAndClose(link.path)"
        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-gray-700 transition-all duration-200 hover:bg-[#F7F2EC]"
      >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="link.icon" />
        </svg>
        <span class="font-medium">{{ link.label }}</span>
      </button>
    </div>

    <div class="rounded-[28px] border border-[#F0E2D7] bg-[#FBF7F2] p-5">
      <h3 class="mb-4 text-xs font-semibold uppercase tracking-[0.22em] text-gray-500">
        Price Range
      </h3>

      <div class="space-y-3">
        <div>
          <label class="mb-1 block text-xs text-gray-600">Minimum</label>
          <div class="relative">
            <span class="absolute top-1/2 left-3 -translate-y-1/2 text-xs text-gray-500">PHP</span>
            <input
              v-model="minPrice"
              type="number"
              placeholder="0"
              class="w-full rounded-full border border-[#E4D8CD] py-2.5 pr-3 pl-11 outline-none focus:border-transparent focus:ring-2 focus:ring-[#C9876C]"
            />
          </div>
        </div>

        <div>
          <label class="mb-1 block text-xs text-gray-600">Maximum</label>
          <div class="relative">
            <span class="absolute top-1/2 left-3 -translate-y-1/2 text-xs text-gray-500">PHP</span>
            <input
              v-model="maxPrice"
              type="number"
              placeholder="10000"
              class="w-full rounded-full border border-[#E4D8CD] py-2.5 pr-3 pl-11 outline-none focus:border-transparent focus:ring-2 focus:ring-[#C9876C]"
            />
          </div>
        </div>

        <button
          @click="applyFilter"
          class="w-full rounded-full bg-[#C9876C] px-4 py-2.5 font-medium text-white shadow-md transition-colors duration-200 hover:bg-[#B8765B]"
        >
          Apply
        </button>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref } from 'vue';
import { navigate } from '../../router';

const props = defineProps({
  categories: {
    type: Array,
    required: true,
  },
  activeCategory: {
    type: String,
    default: 'All',
  },
  open: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['category-select', 'filter-apply', 'close']);
const minPrice = ref('');
const maxPrice = ref('');

const mobileLinks = [
  { label: 'Notifications', path: '/customer/notifications', icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9' },
  { label: 'Messages', path: '/customer/messages', icon: 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z' },
  { label: 'Orders', path: '/customer/orders', icon: 'M9 5h6m-7 4h8m-9 4h10m-1 6H8a2 2 0 01-2-2V7a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 01-2 2z' },
  { label: 'Purchase History', path: '/customer/purchases', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4' },
];

const getCategoryIcon = (name) => {
  const icons = {
    All: 'all',
    Bread: 'bread',
    Pastries: 'pastry',
    Tarts: 'tart',
    'Sugar Cookies': 'star',
    'Brazos and Cakes': 'cake',
    'Customize Order': 'customize',
  };

  return icons[name] || 'star';
};

const selectCategory = (categoryName) => {
  emit('category-select', categoryName);
  emit('close');
};

const navigateAndClose = (path) => {
  navigate(path);
  emit('close');
};

const applyFilter = () => {
  emit('filter-apply', {
    min: minPrice.value,
    max: maxPrice.value,
  });
};
</script>
