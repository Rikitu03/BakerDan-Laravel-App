<template>
  <div class="p-8">
    <!-- Welcome Section -->
    <div class="mb-8">
      <h1 class="text-4xl font-bold text-gray-800 mb-2" style="font-family: 'Urbanist', sans-serif;">
        Welcome to Bakerdan!
      </h1>
      <p class="text-gray-600">Find the best bread and pastries just for you!</p>
    </div>

    <!-- Search and Filters -->
    <div class="flex items-center gap-4 mb-8">
      <!-- Search Input -->
      <div class="relative flex-1 max-w-md">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Describe what you like"
          class="w-full pl-4 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#C9876C] focus:border-transparent outline-none"
        />
      </div>

      <!-- Search Button -->
      <button class="bg-gray-800 hover:bg-gray-900 text-white p-3 rounded-full transition-colors shadow-md">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </button>

      <!-- Sort By -->
      <div class="flex items-center gap-2">
        <span class="text-sm text-gray-600 whitespace-nowrap">Sort by</span>
        <select 
          v-model="sortBy"
          class="px-4 py-2 border border-gray-300 rounded-lg bg-[#C9876C] text-white font-medium focus:ring-2 focus:ring-[#B8765B] outline-none cursor-pointer"
        >
          <option value="popularity">Popularity</option>
          <option value="price">Price</option>
          <option value="low-to-high">Low to High</option>
        </select>
      </div>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <ProductCard
        v-for="product in products"
        :key="product.id"
        :product="product"
        @add-to-cart="handleAddToCart"
        @toggle-like="handleToggleLike"
      />
    </div>

    <!-- Pagination -->
    <div class="flex justify-center items-center gap-2 mt-12">
      <button
        v-for="page in totalPages"
        :key="page"
        @click="currentPage = page"
        class="w-10 h-10 rounded-lg font-medium transition-all duration-200"
        :class="currentPage === page 
          ? 'bg-[#C9876C] text-white shadow-md transform scale-110' 
          : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'"
      >
        {{ page }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import ProductCard from '../shared/ProductCard.vue'
import api from '../../services/api'

const searchQuery = ref('')
const sortBy = ref('popularity')
const currentPage = ref(1)
const totalPages = ref(5)

const products = ref([
  {
    id: 1,
    name: 'Korean Garlic Cream Cheese Bun',
    description: 'Soft enriched dough with a rich cream cheese filling and glossy golden top.',
    price: 499.00,
    image: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&h=300&fit=crop',
    liked: true
  },
  {
    id: 2,
    name: 'Korean Garlic Cream Cheese Bun',
    description: 'Soft enriched dough with a rich cream cheese filling and glossy golden top.',
    price: 499.00,
    image: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&h=300&fit=crop',
    liked: false
  },
  {
    id: 3,
    name: 'Korean Garlic Cream Cheese Bun',
    description: 'Soft enriched dough with a rich cream cheese filling and glossy golden top.',
    price: 499.00,
    image: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&h=300&fit=crop',
    liked: true
  },
  {
    id: 4,
    name: 'Korean Garlic Cream Cheese Bun',
    description: 'Soft enriched dough with a rich cream cheese filling and glossy golden top.',
    price: 499.00,
    image: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&h=300&fit=crop',
    liked: false
  },
  {
    id: 5,
    name: 'Korean Garlic Cream Cheese Bun',
    description: 'Soft enriched dough with a rich cream cheese filling and glossy golden top.',
    price: 499.00,
    image: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&h=300&fit=crop',
    liked: true
  },
  {
    id: 6,
    name: 'Korean Garlic Cream Cheese Bun',
    description: 'Soft enriched dough with a rich cream cheese filling and glossy golden top.',
    price: 499.00,
    image: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&h=300&fit=crop',
    liked: false
  }
])

const handleAddToCart = async (productId) => {
  try {
    // await api.addToCart(productId, { quantity: 1 })
    console.log('Added to cart:', productId)
  } catch (error) {
    console.error('Failed to add to cart:', error)
  }
}

const handleToggleLike = (productId) => {
  const product = products.value.find(p => p.id === productId)
  if (product) {
    product.liked = !product.liked
  }
}

onMounted(async () => {
  try {
    // const response = await api.getProducts()
    // products.value = response.data
  } catch (error) {
    console.error('Failed to load products:', error)
  }
})
</script>
