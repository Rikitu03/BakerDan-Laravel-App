<template>
  <aside class="w-64 bg-white border-r border-gray-200 p-6">
    <!-- Categories Header -->
    <div class="mb-6">
      <h2 class="text-xs font-semibold text-gray-500 tracking-wider uppercase mb-4">
        ALL CATEGORIES
      </h2>
      
      <!-- Category List -->
      <div class="space-y-1">
        <button
          v-for="category in categories"
          :key="category.name"
          @click="selectCategory(category.name)"
          class="w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200"
          :class="category.active 
            ? 'bg-[#C9876C] text-white shadow-md transform scale-105' 
            : 'text-gray-700 hover:bg-gray-100'"
        >
          <svg 
            v-if="getCategoryIcon(category.name) === 'bread'"
            class="w-5 h-5" 
            fill="currentColor" 
            viewBox="0 0 24 24"
          >
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
          </svg>
          <svg 
            v-else-if="getCategoryIcon(category.name) === 'pastry'"
            class="w-5 h-5" 
            fill="currentColor" 
            viewBox="0 0 24 24"
          >
            <path d="M21 3H3v18h18V3zm-2 16H5V5h14v14z"/>
          </svg>
          <svg 
            v-else-if="getCategoryIcon(category.name) === 'cake'"
            class="w-5 h-5" 
            fill="currentColor" 
            viewBox="0 0 24 24"
          >
            <path d="M12 6c1.11 0 2-.9 2-2 0-.38-.1-.73-.29-1.03L12 0l-1.71 2.97c-.19.3-.29.65-.29 1.03 0 1.1.9 2 2 2zm4.6 9.99l-1.07-1.07-1.08 1.07c-1.3 1.3-3.58 1.31-4.89 0l-1.07-1.07-1.09 1.07C6.75 16.64 5.88 17 4.96 17c-.73 0-1.4-.23-1.96-.61V21c0 .55.45 1 1 1h16c.55 0 1-.45 1-1v-4.61c-.56.38-1.23.61-1.96.61-.92 0-1.79-.36-2.44-1.01zM18 9h-5V7h-2v2H6c-1.66 0-3 1.34-3 3v1.54c0 1.08.88 1.96 1.96 1.96.52 0 1.02-.2 1.38-.57l2.14-2.13 2.13 2.13c.74.74 2.03.74 2.77 0l2.14-2.13 2.13 2.13c.37.37.86.57 1.38.57 1.08 0 1.96-.88 1.96-1.96V12C21 10.34 19.66 9 18 9z"/>
          </svg>
          <svg 
            v-else
            class="w-5 h-5" 
            fill="currentColor" 
            viewBox="0 0 24 24"
          >
            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
          </svg>
          <span class="font-medium">{{ category.name }}</span>
        </button>
      </div>
    </div>

    <!-- Price Range Filter -->
    <div class="mt-8">
      <h3 class="text-xs font-semibold text-gray-500 tracking-wider uppercase mb-4">
        PRICE RANGE
      </h3>
      
      <div class="space-y-3">
        <div>
          <label class="block text-xs text-gray-600 mb-1">Minimum</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">₱</span>
            <input
              v-model="minPrice"
              type="number"
              placeholder="0"
              class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#C9876C] focus:border-transparent outline-none"
            />
          </div>
        </div>
        
        <div>
          <label class="block text-xs text-gray-600 mb-1">Maximum</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">₱</span>
            <input
              v-model="maxPrice"
              type="number"
              placeholder="10000"
              class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#C9876C] focus:border-transparent outline-none"
            />
          </div>
        </div>

        <button
          @click="applyFilter"
          class="w-full bg-[#C9876C] hover:bg-[#B8765B] text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 shadow-md"
        >
          Apply
        </button>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  categories: {
    type: Array,
    required: true
  },
  activeCategory: {
    type: String,
    default: 'Bread'
  }
})

const emit = defineEmits(['category-select', 'filter-apply'])

const minPrice = ref('')
const maxPrice = ref('')

const getCategoryIcon = (name) => {
  const icons = {
    'Bread': 'bread',
    'Patries': 'pastry',
    'Cakes': 'cake',
    'Customize': 'star'
  }
  return icons[name] || 'star'
}

const selectCategory = (categoryName) => {
  emit('category-select', categoryName)
}

const applyFilter = () => {
  emit('filter-apply', {
    min: minPrice.value,
    max: maxPrice.value
  })
}
</script>
