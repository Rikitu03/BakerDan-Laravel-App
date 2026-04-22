<template>
  <div class="min-h-screen bg-gradient-to-br from-[#C9A582] via-[#D4B193] to-[#C9A582] p-4">
    <div class="max-w-[1400px] mx-auto">
      <!-- Main Container with rounded corners -->
      <div class="bg-white rounded-[2rem] shadow-2xl overflow-hidden min-h-[95vh] flex flex-col">
        
        <!-- Header -->
        <AppHeader 
          :user="user"
          @toggle-user-menu="showUserMenu = !showUserMenu"
        />

        <!-- Main Content Area -->
        <div class="flex flex-1 overflow-hidden">
          <!-- Sidebar -->
          <AppSidebar 
            :categories="categories"
            :active-category="activeCategory"
            @category-select="handleCategorySelect"
          />

          <!-- Page Content -->
          <main class="flex-1 overflow-y-auto bg-gray-50">
            <router-view 
              :user="user"
              :categories="categories"
              :active-category="activeCategory"
              @update-cart-count="updateCartCount"
            />
          </main>
        </div>
      </div>
    </div>

    <!-- User Dropdown Menu -->
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
        class="fixed top-20 right-8 bg-white rounded-lg shadow-xl py-2 w-48 z-50"
        @click.stop
      >
        <router-link
          to="/customer"
          class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors flex items-center gap-2"
          @click="showUserMenu = false"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
          </svg>
          Purchase history
        </router-link>
        <router-link
          to="/customer/settings"
          class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors flex items-center gap-2"
          @click="showUserMenu = false"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          Settings
        </router-link>
        <div class="border-t my-1"></div>
        <button
          @click="handleSignOut"
          class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 transition-colors flex items-center gap-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          Sign out
        </button>
      </div>
    </transition>

    <!-- Click outside to close menu -->
    <div
      v-if="showUserMenu"
      class="fixed inset-0 z-40"
      @click="showUserMenu = false"
    ></div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import AppHeader from './AppHeader.vue'
import AppSidebar from './AppSidebar.vue'
import api from '../../services/api'

const router = useRouter()

const user = ref({
  name: 'Jason Jay Recto',
  email: 'recto_jasonjay@plpasig.edu.ph',
  avatar: 'JR'
})

const categories = ref([
  { name: 'Bread', icon: '🍞', active: true },
  { name: 'Patries', icon: '🥐', active: false },
  { name: 'Cakes', icon: '🎂', active: false },
  { name: 'Customize', icon: '✨', active: false }
])

const activeCategory = ref('Bread')
const showUserMenu = ref(false)

const handleCategorySelect = (categoryName) => {
  activeCategory.value = categoryName
  categories.value.forEach(cat => {
    cat.active = cat.name === categoryName
  })
  
  if (categoryName === 'Customize') {
    router.push('/customer/customize')
  } else {
    router.push('/customer')
  }
}

const updateCartCount = (count) => {
  // Update cart count in header
  console.log('Cart count updated:', count)
}

const handleSignOut = async () => {
  try {
    await fetch('/logout', { 
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    })
    window.location.href = '/login'
  } catch (error) {
    console.error('Sign out failed:', error)
  }
}

const handleClickOutside = (event) => {
  if (showUserMenu.value && !event.target.closest('.user-menu')) {
    showUserMenu.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
