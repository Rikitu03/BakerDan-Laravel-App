<template>
  <header class="bg-white border-b border-gray-200 px-4 py-3 md:px-8 md:py-4">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2 md:gap-3">
        <!-- Hamburger (mobile) -->
        <button
          type="button"
          aria-label="Toggle sidebar"
          class="rounded-lg p-2 text-gray-600 transition-colors hover:bg-[#F7F2EC] lg:hidden"
          @click="$emit('toggle-sidebar')"
        >
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>

        <!-- Logo -->
        <a href="/customer" @click.prevent="navigateTo('/customer')" class="flex items-center gap-2 md:gap-3">
          <img
            :src="logoSrc"
            alt="Bakerdan logo"
            class="h-9 w-9 md:h-12 md:w-12 rounded-full object-cover shadow-md ring-2 ring-[#F4E2D3]"
          />
          <h1 class="text-lg md:text-2xl font-bold tracking-wider text-gray-800" style="font-family: 'Urbanist', sans-serif;">
            BAKERDAN
          </h1>
        </a>
      </div>

      <!-- Nav Icons -->
      <div class="flex items-center gap-3 md:gap-6">
        <!-- Notifications -->
        <button
          type="button"
          aria-label="Open notifications"
          @click="navigateTo('/customer/notifications')"
          class="relative hidden rounded-full p-1 transition-transform hover:scale-110 sm:block"
          :class="isNotificationsRoute ? 'bg-[#FFF2E8] text-[#B76539]' : 'text-gray-600'"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
          <span
            v-if="unreadCount > 0"
            class="absolute -top-2 -right-2 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white"
          >
            {{ unreadCount > 9 ? '9+' : unreadCount }}
          </span>
        </button>

        <!-- Messages -->
        <button
          type="button"
          aria-label="Open messages"
          @click="navigateTo('/customer/messages')"
          class="relative hidden rounded-full p-1 transition-transform hover:scale-110 sm:block"
          :class="isMessagesRoute ? 'bg-[#FFF2E8] text-[#B76539]' : 'text-gray-600'"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
          </svg>
          <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-[#D96D45]"></span>
        </button>

        <!-- Cart -->
        <a
          href="/customer/cart"
          @click.prevent="navigateTo('/customer/cart')"
          class="relative hover:scale-110 transition-transform"
          :class="isCartRoute ? 'text-[#B76539]' : 'text-gray-600'"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <span 
            v-if="cartCount > 0"
            class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center"
          >
            {{ cartCount }}
          </span>
        </a>

        <!-- Orders -->
        <button
          type="button"
          aria-label="Open orders"
          @click="navigateTo('/customer/orders')"
          class="relative hidden rounded-full p-1 transition-transform hover:scale-110 sm:block"
          :class="isOrdersRoute ? 'bg-[#FFF2E8] text-[#B76539]' : 'text-gray-600'"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5h6m-7 4h8m-9 4h10m-1 6H8a2 2 0 01-2-2V7a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 01-2 2z" />
          </svg>
        </button>

        <!-- User Profile -->
        <button 
          @click="$emit('toggle-user-menu')"
          class="flex items-center gap-1 md:gap-3 hover:bg-gray-50 rounded-full px-2 py-1.5 md:px-3 md:py-2 transition-colors user-menu"
        >
          <div class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-gradient-to-br from-[#C9876C] to-[#B8765B] flex items-center justify-center text-white text-sm md:text-base font-semibold shadow-md">
            {{ user.avatar }}
          </div>
          <div class="text-left hidden lg:block">
            <p class="text-sm font-semibold text-gray-800">{{ user.name }}</p>
          </div>
          <svg class="w-4 h-4 text-gray-500 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed, onMounted, onUnmounted } from 'vue'
import { navigate, useSpaRouter } from '../../router'
import { useCartStore } from '../../services/cartStore'
import { useNotificationStore } from '../../services/notificationStore'

defineProps({
  user: {
    type: Object,
    required: true
  }
})

defineEmits(['toggle-user-menu', 'toggle-sidebar'])

const logoSrc = '/images/logo/BAKERDAN%20LOGO.jpg'
const { currentRoute } = useSpaRouter()
const { cartCount, loadCart } = useCartStore()
const { unreadCount, loadNotifications } = useNotificationStore()
const isNotificationsRoute = computed(() => currentRoute.value.name === 'customer.notifications')
const isMessagesRoute = computed(() => currentRoute.value.name === 'customer.messages')
const isCartRoute = computed(() => currentRoute.value.name === 'customer.cart')
const isOrdersRoute = computed(() => currentRoute.value.name === 'customer.orders')
const navigateTo = (path) => navigate(path)
let headerPrefetchHandle = null
let headerPrefetchMode = null

const prefetchHeaderData = () => {
  loadCart().catch(() => {})
  loadNotifications().catch(() => {})
}

onMounted(() => {
  if (typeof window.requestIdleCallback === 'function') {
    headerPrefetchMode = 'idle'
    headerPrefetchHandle = window.requestIdleCallback(() => {
      prefetchHeaderData()
      headerPrefetchHandle = null
      headerPrefetchMode = null
    }, { timeout: 1200 })
    return
  }

  headerPrefetchMode = 'timeout'
  headerPrefetchHandle = window.setTimeout(() => {
    prefetchHeaderData()
    headerPrefetchHandle = null
    headerPrefetchMode = null
  }, 250)
})

onUnmounted(() => {
  if (!headerPrefetchHandle) {
    return
  }

  if (headerPrefetchMode === 'idle' && typeof window.cancelIdleCallback === 'function') {
    window.cancelIdleCallback(headerPrefetchHandle)
    return
  }

  window.clearTimeout(headerPrefetchHandle)
})
</script>
