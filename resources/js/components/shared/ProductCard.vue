<template>
  <div class="group overflow-hidden rounded-[26px] border border-[#E9DDD2] bg-white p-3 shadow-[0_18px_40px_-32px_rgba(118,79,49,0.45)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_26px_50px_-30px_rgba(118,79,49,0.4)]">
    <div class="relative aspect-[4/3] overflow-hidden rounded-[22px]">
      <img
        :src="product.image"
        :alt="product.name"
        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
      />

      <button
        @click.stop="$emit('toggle-like', product.id)"
        class="absolute top-3 right-3 flex h-9 w-9 items-center justify-center rounded-full bg-white/95 shadow-md transition-transform hover:scale-110"
      >
        <svg
          class="h-5 w-5 transition-colors"
          :class="product.liked ? 'fill-current text-red-500' : 'text-gray-400'"
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

    <div class="px-2 pb-2 pt-4">
      <div class="mb-3 flex flex-wrap items-center gap-2">
        <span class="rounded-full bg-[#FAEEE4] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#B66B40]">
          {{ product.tag || 'Best Seller' }}
        </span>
        <span class="text-xs font-semibold text-[#D07D52]">
          {{ product.rating || '4.9/5' }}
        </span>
      </div>

      <h3 class="mb-2 line-clamp-2 min-h-[3.2rem] text-[1.02rem] font-semibold leading-6 text-gray-800">
        {{ product.name }}
      </h3>
      <p class="mb-4 line-clamp-2 min-h-[2.7rem] text-sm leading-5 text-gray-600">
        {{ product.description }}
      </p>

      <div class="flex items-center justify-between gap-3">
        <p class="text-xl font-bold text-gray-800">
          PHP {{ formatPrice(product.price) }}
        </p>

        <button
          @click="$emit('add-to-cart', product.id)"
          class="rounded-full bg-[#C9876C] px-4 py-2.5 text-sm font-semibold text-white shadow-md transition-all duration-200 hover:bg-[#B8765B] hover:shadow-lg"
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
    required: true,
  },
});

defineEmits(['add-to-cart', 'toggle-like']);

const formatPrice = (price) => price.toFixed(2);
</script>
