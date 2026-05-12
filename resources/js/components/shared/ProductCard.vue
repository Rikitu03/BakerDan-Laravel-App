<template>
  <div class="group overflow-hidden rounded-[26px] border border-[#E9DDD2] bg-white p-3 shadow-[0_18px_40px_-32px_rgba(118,79,49,0.45)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_26px_50px_-30px_rgba(118,79,49,0.4)]">
    <div class="relative aspect-[4/3] overflow-hidden rounded-[22px]">
      <img
        :src="activeImage"
        :alt="product.name"
        loading="lazy"
        decoding="async"
        fetchpriority="low"
        width="640"
        height="480"
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
        {{ activeDescription }}
      </p>

      <div v-if="hasVariants" class="mb-4 flex flex-wrap gap-2">
        <button
          v-for="variant in product.variants"
          :key="variant.id"
          type="button"
          @click="activeVariantId = variant.id"
          class="rounded-full px-3 py-1.5 text-xs font-semibold transition-all"
          :class="activeVariantId === variant.id
            ? 'bg-[#B47A52] text-white shadow-sm'
            : 'bg-[#F7F2EC] text-[#6E6259] hover:bg-[#EFE2D6]'"
        >
          {{ variant.label }}
        </button>
      </div>

      <div class="flex items-center justify-between gap-3">
        <p class="text-xl font-bold text-gray-800">
          {{ activePrice }}
        </p>

        <button
          @click="$emit('add-to-cart', activeProductId)"
          class="rounded-full bg-[#C9876C] px-4 py-2.5 text-sm font-semibold text-white shadow-md transition-all duration-200 hover:bg-[#B8765B] hover:shadow-lg"
        >
          Add to cart
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
  product: {
    type: Object,
    required: true,
  },
});

defineEmits(['add-to-cart', 'toggle-like']);

const formatPrice = (price) => price.toFixed(2);

const hasVariants = computed(() => props.product.variants && props.product.variants.length > 1);

const activeVariantId = ref(
  props.product.variants?.[0]?.id ?? props.product.id,
);

watch(
  () => props.product.id,
  () => {
    activeVariantId.value = props.product.variants?.[0]?.id ?? props.product.id;
  },
);

const activeVariant = computed(() => {
  if (!hasVariants.value) return null;
  return props.product.variants.find((v) => v.id === activeVariantId.value)
    || props.product.variants[0];
});

const activeImage = computed(() => {
  return activeVariant.value?.image || props.product.image;
});

const activeDescription = computed(() => {
  return activeVariant.value?.description || props.product.description;
});

const activePrice = computed(() => {
  if (activeVariant.value) {
    return activeVariant.value.priceLabel || `PHP ${formatPrice(activeVariant.value.price)}`;
  }
  return props.product.priceLabel || `PHP ${formatPrice(props.product.price)}`;
});

const activeProductId = computed(() => {
  return activeVariant.value?.id || props.product.id;
});
</script>
