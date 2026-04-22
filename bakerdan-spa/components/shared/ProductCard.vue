<template>
  <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group">
    <!-- Product Image -->
    <div class="relative overflow-hidden aspect-[4/3]">
      <img
        :src="product.image"
        :alt="product.name"
        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
      />
      
      <!-- Like Button -->
      <button
        @click.stop="$emit('toggle-like', product.id)"
        class="absolute top-3 right-3 w-10 h-10 rounded-full bg-white shadow-md flex items-center justify-center hover:scale-110 transition-transform"
      >
        <svg 
          class="w-5 h-5 transition-colors"
          :class="product.liked ? 'text-red-500 fill-current' : 'text-gray-400'"
          fill="none" 
          stroke="currentColor" 
          viewBox="0 0 24 24"
        >
          <path 
            stroke-linecap="round" 
            stroke-linejoin="round" 
            stroke-width="2" 
            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" 
          />
        </svg>
      </button>
    </div>

    <!-- Product Info -->
    <div class="p-5">
      <h3 class="font-semibold text-gray-800 mb-2 line-clamp-1">
        {{ product.name }}
      </h3>
      <p class="text-sm text-gray-600 mb-4 line-clamp-2">
        {{ product.description }}
      </p>

      <!-- Price and Add to Cart -->
      <div class="flex items-center justify-between">
        <div>
          <p class="text-2xl font-bold text-gray-800">
            ₱ {{ formatPrice(product.price) }}
          </p>
        </div>
        
        <button
          @click="$emit('add-to-cart', product.id)"
          class="bg-[#C9876C] hover:bg-[#B8765B] text-white font-medium px-5 py-2.5 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
        >
          Add to cart
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  product: {
    type: Object,
    required: true
  }
})

defineEmits(['add-to-cart', 'toggle-like'])

const formatPrice = (price) => {
  return price.toFixed(2)
}
</script>
