<template>
  <div class="flex h-full">
    <!-- Left: Product Detail -->
    <div class="w-2/5 bg-white p-8 border-r border-gray-200">
      <div class="sticky top-8">
        <!-- Product Image -->
        <div class="rounded-2xl overflow-hidden mb-6">
          <img
            :src="featuredProduct.image"
            :alt="featuredProduct.name"
            class="w-full aspect-[4/3] object-cover"
          />
        </div>

        <!-- Product Info -->
        <h2 class="text-3xl font-bold text-gray-800 mb-3" style="font-family: 'Urbanist', sans-serif;">
          {{ featuredProduct.name }}
        </h2>
        <p class="text-gray-600 mb-6">
          {{ featuredProduct.description }}
        </p>

        <!-- Price -->
        <div class="text-5xl font-bold text-gray-800 mb-8">
          ₱ {{ formatPrice(featuredProduct.price) }}
        </div>

        <!-- Size and Quantity -->
        <div class="grid grid-cols-2 gap-4 mb-6">
          <div>
            <label class="block text-sm text-gray-600 mb-2">Size</label>
            <select 
              v-model="featuredProduct.size"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#C9876C] focus:border-transparent outline-none"
            >
              <option>Small</option>
              <option>Medium</option>
              <option>Large</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm text-gray-600 mb-2">Quantity</label>
            <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
              <button
                @click="decrementQuantity"
                class="px-4 py-3 hover:bg-gray-100 transition-colors"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                </svg>
              </button>
              <input
                v-model.number="featuredProduct.quantity"
                type="number"
                min="1"
                class="flex-1 text-center border-x border-gray-300 py-3 outline-none"
              />
              <button
                @click="incrementQuantity"
                class="px-4 py-3 hover:bg-gray-100 transition-colors"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-3">
          <button
            @click="addToCart"
            class="w-full bg-[#C9876C] hover:bg-[#B8765B] text-white font-semibold py-4 rounded-lg transition-colors shadow-md"
          >
            Add to cart
          </button>
          <button
            @click="checkout"
            class="w-full bg-[#8B6F47] hover:bg-[#7A5F3A] text-white font-semibold py-4 rounded-lg transition-colors shadow-md"
          >
            Checkout
          </button>
          <button
            @click="continueBrowsing"
            class="w-full bg-white hover:bg-gray-50 text-gray-700 font-semibold py-4 rounded-lg border border-gray-300 transition-colors"
          >
            Continue browsing
          </button>
        </div>
      </div>
    </div>

    <!-- Right: Cart List -->
    <div class="flex-1 bg-gray-50 p-8">
      <!-- Cart Header -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-2">
          <h2 class="text-2xl font-bold text-gray-800">Cart List</h2>
          <span class="bg-red-500 text-white text-sm font-bold px-2 py-1 rounded-full">
            {{ cartItems.length }}
          </span>
        </div>

        <!-- Search and Sort -->
        <div class="flex items-center gap-3">
          <div class="relative">
            <input
              v-model="cartSearch"
              type="text"
              placeholder="Search"
              class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#C9876C] focus:border-transparent outline-none"
            />
            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>

          <select 
            v-model="cartSort"
            class="px-4 py-2 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-[#C9876C] outline-none"
          >
            <option>Price</option>
            <option>Name</option>
            <option>Date Added</option>
          </select>
        </div>
      </div>

      <!-- Cart Items -->
      <div class="space-y-4 mb-6">
        <CartItem
          v-for="item in cartItems"
          :key="item.id"
          :item="item"
          :selected="selectedItems.includes(item.id)"
          @toggle-select="toggleItemSelect"
          @remove="removeItem"
        />
      </div>

      <!-- Cart Footer -->
      <div class="bg-white rounded-2xl p-6 shadow-md">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                type="checkbox"
                :checked="allSelected"
                @change="toggleSelectAll"
                class="w-5 h-5 text-[#C9876C] rounded focus:ring-[#C9876C]"
              />
              <span class="font-medium text-gray-700">Select All</span>
            </label>

            <button
              @click="deleteSelected"
              :disabled="selectedItems.length === 0"
              class="bg-red-500 hover:bg-red-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-medium px-6 py-2 rounded-lg transition-colors"
            >
              Delete
            </button>
          </div>

          <div class="flex items-center gap-6">
            <div class="text-right">
              <p class="text-sm text-gray-600">Total ({{ selectedItems.length }} item)</p>
              <p class="text-3xl font-bold text-gray-800">₱ {{ formatPrice(cartTotal) }}</p>
            </div>

            <button
              @click="proceedToCheckout"
              :disabled="selectedItems.length === 0"
              class="bg-[#C9876C] hover:bg-[#B8765B] disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold px-8 py-4 rounded-lg transition-colors shadow-md"
            >
              Checkout
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import CartItem from '../shared/CartItem.vue'

const router = useRouter()

const featuredProduct = ref({
  name: 'Korean Garlic Cream Cheese Bun',
  description: 'Soft enriched dough with a rich cream cheese filling and glossy golden top.',
  price: 499.00,
  image: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&h=300&fit=crop',
  size: 'Medium',
  quantity: 5
})

const cartItems = ref([
  {
    id: 1,
    name: 'Korean Garlic Cream Cheese Bun',
    description: 'Soft enriched dough with a rich cream cheese filling and glossy golden top.',
    price: 499.00,
    image: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&h=300&fit=crop',
    quantity: 1
  },
  {
    id: 2,
    name: 'Korean Garlic Cream Cheese Bun',
    description: 'Soft enriched dough with a rich cream cheese filling and glossy golden top.',
    price: 499.00,
    image: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&h=300&fit=crop',
    quantity: 1
  },
  {
    id: 3,
    name: 'Korean Garlic Cream Cheese Bun',
    description: 'Soft enriched dough with a rich cream cheese filling and glossy golden top.',
    price: 499.00,
    image: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&h=300&fit=crop',
    quantity: 1
  },
  {
    id: 4,
    name: 'Korean Garlic Cream Cheese Bun',
    description: 'Soft enriched dough with a rich cream cheese filling and glossy golden top.',
    price: 499.00,
    image: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&h=300&fit=crop',
    quantity: 1
  },
  {
    id: 5,
    name: 'Korean Garlic Cream Cheese Bun',
    description: 'Soft enriched dough with a rich cream cheese filling and glossy golden top.',
    price: 499.00,
    image: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&h=300&fit=crop',
    quantity: 1
  }
])

const selectedItems = ref([])
const cartSearch = ref('')
const cartSort = ref('Price')

const allSelected = computed(() => {
  return selectedItems.value.length === cartItems.value.length && cartItems.value.length > 0
})

const cartTotal = computed(() => {
  return cartItems.value
    .filter(item => selectedItems.value.includes(item.id))
    .reduce((total, item) => total + (item.price * item.quantity), 0)
})

const formatPrice = (price) => {
  return price.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}

const incrementQuantity = () => {
  featuredProduct.value.quantity++
}

const decrementQuantity = () => {
  if (featuredProduct.value.quantity > 1) {
    featuredProduct.value.quantity--
  }
}

const addToCart = () => {
  console.log('Added to cart:', featuredProduct.value)
}

const checkout = () => {
  console.log('Checkout featured product')
}

const continueBrowsing = () => {
  router.push('/customer')
}

const toggleItemSelect = (itemId) => {
  const index = selectedItems.value.indexOf(itemId)
  if (index > -1) {
    selectedItems.value.splice(index, 1)
  } else {
    selectedItems.value.push(itemId)
  }
}

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedItems.value = []
  } else {
    selectedItems.value = cartItems.value.map(item => item.id)
  }
}

const removeItem = (itemId) => {
  const index = cartItems.value.findIndex(item => item.id === itemId)
  if (index > -1) {
    cartItems.value.splice(index, 1)
    const selectedIndex = selectedItems.value.indexOf(itemId)
    if (selectedIndex > -1) {
      selectedItems.value.splice(selectedIndex, 1)
    }
  }
}

const deleteSelected = () => {
  cartItems.value = cartItems.value.filter(item => !selectedItems.value.includes(item.id))
  selectedItems.value = []
}

const proceedToCheckout = () => {
  console.log('Proceeding to checkout with items:', selectedItems.value)
}
</script>
