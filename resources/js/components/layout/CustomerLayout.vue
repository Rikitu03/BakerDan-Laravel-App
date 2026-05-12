<template>
  <div class="min-h-screen bg-[radial-gradient(circle_at_top,#fffdf8_0%,#f1ebe3_42%,#dec7b2_100%)] p-1.5 sm:p-3 lg:p-5">
    <div class="mx-auto max-w-[1720px]">
      <div class="flex min-h-[95vh] flex-col overflow-hidden rounded-2xl sm:rounded-[2.2rem] border border-[#D79E72] bg-white shadow-[0_32px_80px_-42px_rgba(133,88,53,0.48)]">
        <AppHeader
          :user="user"
          @toggle-user-menu="showUserMenu = !showUserMenu"
          @toggle-sidebar="sidebarOpen = !sidebarOpen"
        />

        <div class="flex flex-1 overflow-hidden">
          <AppSidebar
            v-if="!layoutProps.hideSidebar"
            :categories="categories"
            :active-category="activeCategory"
            :open="sidebarOpen"
            @category-select="handleCategorySelect"
            @close="sidebarOpen = false"
          />

          <main class="flex-1 overflow-y-auto" :class="layoutProps.mainClass">
            <component
              :is="pageComponent"
              v-bind="pageProps"
              :user="user"
              :categories="categories"
              :active-category="activeCategory"
              @update-cart-count="updateCartCount"
            />
          </main>
        </div>
      </div>
    </div>

    <transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="showUserMenu"
        class="fixed top-16 right-3 z-50 w-48 rounded-lg bg-white py-2 shadow-xl sm:top-20 sm:right-8"
        @click.stop
      >
        <a
          href="/customer/orders"
          @click.prevent="push('/customer/orders'); showUserMenu = false"
          class="flex items-center gap-2 px-4 py-2 text-gray-700 transition-colors hover:bg-gray-100"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5h6m-7 4h8m-9 4h10m-1 6H8a2 2 0 01-2-2V7a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 01-2 2z" />
          </svg>
          Orders
        </a>
        <a
          href="/customer/purchases"
          @click.prevent="push('/customer/purchases'); showUserMenu = false"
          class="flex items-center gap-2 px-4 py-2 text-gray-700 transition-colors hover:bg-gray-100"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
          </svg>
          Purchase History
        </a>
        <a
          href="/customer/settings"
          @click.prevent="push('/customer/settings'); showUserMenu = false"
          class="flex items-center gap-2 px-4 py-2 text-gray-700 transition-colors hover:bg-gray-100"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          Settings
        </a>
        <div class="my-1 border-t"></div>
        <button
          @click="handleSignOut"
          class="flex w-full items-center gap-2 px-4 py-2 text-left text-red-600 transition-colors hover:bg-red-50"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          Sign out
        </button>
      </div>
    </transition>

    <div
      v-if="showUserMenu"
      class="fixed inset-0 z-40"
      @click="showUserMenu = false"
    ></div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { useSpaRouter } from '../../router';
import AppHeader from './AppHeader.vue';
import AppSidebar from './AppSidebar.vue';

defineProps({
  pageComponent: {
    type: Object,
    required: true,
  },
  pageProps: {
    type: Object,
    default: () => ({}),
  },
  layoutProps: {
    type: Object,
    default: () => ({
      hideSidebar: false,
      mainClass: 'bg-[#F6F4F1]',
    }),
  },
});

const { push, currentRoute } = useSpaRouter();
const bootUser = window.Laravel?.customer || window.Laravel?.user || {};
const rawUserName = bootUser.name || 'Guest Explorer';

const user = ref({
  id: bootUser.id || bootUser.user_id || 0,
  user_id: bootUser.id || bootUser.user_id || 0, // Fallback for components still using user_id
  name: rawUserName,
  email: bootUser.email || 'customer@bakerdan.local',
  avatar: bootUser.avatar || rawUserName
    .split(' ')
    .map((part) => part[0] || '')
    .join('')
    .slice(0, 2)
    .toUpperCase(),
});

const categories = ref([
  { name: 'All', active: true },
  { name: 'Bread', active: false },
  { name: 'Pastries', active: false },
  { name: 'Tarts', active: false },
  { name: 'Brazos and Cakes', active: false },
  { name: 'Customize Order', active: false },
]);

const activeCategory = ref('All');
const showUserMenu = ref(false);
const sidebarOpen = ref(false);

const handleCategorySelect = (categoryName) => {
  activeCategory.value = categoryName;
  categories.value.forEach((category) => {
    category.active = category.name === categoryName;
  });

  sidebarOpen.value = false;

  if (categoryName === 'Customize Order') {
    push('/customer/customize?guide=cakes');
  } else {
    push('/customer');
  }
};

const updateCartCount = (count) => {
  console.log('Cart count updated:', count);
};

const handleSignOut = async () => {
  try {
    await fetch('/logout', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      },
    });

    window.location.href = '/login';
  } catch (error) {
    console.error('Sign out failed:', error);
  }
};

const handleClickOutside = (event) => {
  if (showUserMenu.value && !event.target.closest('.user-menu')) {
    showUserMenu.value = false;
  }
};

const syncSidebarWithRoute = () => {
  const path = currentRoute.value?.path || window.location.pathname;
  if (path === '/customer/customize') {
    activeCategory.value = 'Customize Order';
    categories.value.forEach((category) => {
      category.active = category.name === 'Customize Order';
    });
  }
};

watch(currentRoute, () => {
  syncSidebarWithRoute();
  sidebarOpen.value = false;
});

onMounted(() => {
  syncSidebarWithRoute();
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
