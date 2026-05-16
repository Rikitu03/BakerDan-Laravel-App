<template>
  <div class="group overflow-hidden rounded-[26px] border border-[#E9DDD2] bg-white p-3 shadow-[0_18px_40px_-32px_rgba(118,79,49,0.45)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_26px_50px_-30px_rgba(118,79,49,0.4)]">
    <div class="relative aspect-[4/3] overflow-hidden rounded-[22px] bg-[#F3ECE5]">
      <div v-if="isImageLoading" class="absolute inset-0 animate-pulse bg-gradient-to-br from-[#EFE3D8] via-[#F8F0E9] to-[#E8D9CE]"></div>
      <img
        :key="displayImage"
        ref="imageElement"
        :src="displayImage"
        :alt="product.name"
        :loading="eager ? 'eager' : 'lazy'"
        decoding="async"
        :fetchpriority="eager ? 'high' : 'low'"
        width="640"
        height="480"
        class="h-full w-full object-cover transition-all duration-500 group-hover:scale-110"
        :class="isImageLoading ? 'opacity-0' : 'opacity-100'"
        @load="handleImageLoad"
        @error="handleImageError"
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
      <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
        <span class="rounded-full bg-[#FAEEE4] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#B66B40]">
          {{ product.tag || 'Best Seller' }}
        </span>
        <StarRating
          v-if="product.average_rating !== undefined"
          size="sm"
          :rating="product.average_rating"
          :show-text="false"
        />
      </div>

      <h3 class="mb-2 line-clamp-2 min-h-[3.2rem] text-[1.02rem] font-semibold leading-6 text-gray-800">
        {{ product.name }}
      </h3>
      <p class="mb-4 line-clamp-2 min-h-[2.7rem] text-sm leading-5 text-gray-600">
        {{ activeDescription }}
      </p>

      <div v-if="hasOptions" class="mb-4">
        <label v-if="usesCompactOptionSelect" class="block">
          <span class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-[#A9846B]">Option</span>
          <select
            v-model="activeOptionId"
            class="h-10 w-full rounded-full border border-[#E6DDD5] bg-[#FCFBF9] px-4 text-sm font-semibold text-[#5C534D] outline-none transition focus:border-transparent focus:ring-2 focus:ring-[#C9876C]"
          >
            <option v-for="option in product.options" :key="option.id" :value="option.id">
              {{ option.label }}
            </option>
          </select>
        </label>

        <div v-else class="flex flex-wrap gap-2">
          <button
            v-for="option in product.options"
            :key="option.id"
            type="button"
            @click="activeOptionId = option.id"
            class="rounded-full px-3 py-1.5 text-xs font-semibold transition-all"
            :class="activeOptionId === option.id
              ? 'bg-[#B47A52] text-white shadow-sm'
              : 'bg-[#F7F2EC] text-[#6E6259] hover:bg-[#EFE2D6]'"
          >
            {{ option.label }}
          </button>
        </div>
      </div>

      <div v-else-if="hasVariants" class="mb-4 flex flex-wrap gap-2">
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
        <div>
          <p class="text-xl font-bold text-gray-800">
            {{ activePrice }}
          </p>
          <p v-if="activeMinimumQuantity > 1" class="mt-0.5 text-xs font-semibold text-[#A06F50]">
            Min. {{ activeMinimumQuantity }} pcs
          </p>
        </div>

        <button
          @click="emitAddToCart"
          class="rounded-full bg-[#C9876C] px-4 py-2.5 text-sm font-semibold text-white shadow-md transition-all duration-200 hover:bg-[#B8765B] hover:shadow-lg"
        >
          Add to cart
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import StarRating from './StarRating.vue';

const props = defineProps({
  product: {
    type: Object,
    required: true,
  },
  eager: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['add-to-cart', 'toggle-like']);

const formatPrice = (price) => {
  const normalizedPrice = Number(price || 0);

  if (!Number.isFinite(normalizedPrice)) {
    return '0.00';
  }

  return normalizedPrice.toFixed(2);
};

const hasAnyOptions = computed(() => Array.isArray(props.product.options) && props.product.options.length > 0);
const hasOptions = computed(() => hasAnyOptions.value && props.product.options.length > 1);
const usesCompactOptionSelect = computed(() => hasOptions.value && props.product.options.length > 4);
const hasVariants = computed(() => !hasOptions.value && props.product.variants && props.product.variants.length > 1);

const isImageLoading = ref(true);
const imageLoadFailed = ref(false);
const imageElement = ref(null);
const activeOptionId = ref(
  props.product.options?.[0]?.id ?? null,
);
const activeVariantId = ref(
  props.product.variants?.[0]?.id ?? props.product.id,
);

watch(
  () => props.product.id,
  () => {
    activeOptionId.value = props.product.options?.[0]?.id ?? null;
    activeVariantId.value = props.product.variants?.[0]?.id ?? props.product.id;
    imageLoadFailed.value = false;
    isImageLoading.value = true;
  },
);

const activeOption = computed(() => {
  if (!hasAnyOptions.value) return null;
  return props.product.options.find((option) => option.id === activeOptionId.value)
    || props.product.options[0];
});

const activeVariant = computed(() => {
  if (!hasVariants.value) return null;
  return props.product.variants.find((v) => v.id === activeVariantId.value)
    || props.product.variants[0];
});

const activeImage = computed(() => {
  return activeOption.value?.image_url || activeVariant.value?.image || props.product.image || fallbackImage.value;
});

const activeDescription = computed(() => {
  if (activeOption.value) {
    return [
      props.product.description,
      activeOption.value.size ? `Size: ${activeOption.value.size}` : '',
      activeOption.value.flavor ? `Flavor: ${activeOption.value.flavor}` : '',
    ].filter(Boolean).join(' | ');
  }

  return activeVariant.value?.description || props.product.description;
});

const activePrice = computed(() => {
  if (activeOption.value) {
    return activeOption.value.price_label || `PHP ${formatPrice(Number(activeOption.value.price || 0))}`;
  }

  if (activeVariant.value) {
    return activeVariant.value.priceLabel || `PHP ${formatPrice(activeVariant.value.price)}`;
  }
  return props.product.priceLabel || `PHP ${formatPrice(props.product.price)}`;
});

const activeProductId = computed(() => {
  return activeVariant.value?.id || props.product.id;
});

const activeMinimumQuantity = computed(() => Number(activeOption.value?.minimum_quantity || props.product.minimumQuantity || 1));
const NEUTRAL_FALLBACK_IMAGE = '/images/logo/BAKERDAN%20LOGO.jpg';

const fallbackImage = computed(() => {
  if (props.product.fallbackImage) return props.product.fallbackImage;
  return NEUTRAL_FALLBACK_IMAGE;
});

const displayImage = computed(() => {
  if (imageLoadFailed.value) {
    return fallbackImage.value;
  }

  return activeImage.value || fallbackImage.value;
});

watch(
  activeImage,
  () => {
    imageLoadFailed.value = false;
  },
);

watch(
  displayImage,
  () => {
    isImageLoading.value = true;
    nextTick(() => {
      if (imageElement.value?.complete && imageElement.value.naturalWidth > 0) {
        isImageLoading.value = false;
      }
    });
  },
  { immediate: true },
);

onMounted(() => {
  if (imageElement.value?.complete && imageElement.value.naturalWidth > 0) {
    isImageLoading.value = false;
  }
});

const handleImageLoad = () => {
  isImageLoading.value = false;
};

const handleImageError = () => {
  if (displayImage.value !== fallbackImage.value) {
    imageLoadFailed.value = true;
    isImageLoading.value = true;
    return;
  }

  isImageLoading.value = false;
};

const emitAddToCart = () => {
  emit('add-to-cart', {
    productId: activeProductId.value,
    optionId: activeOption.value?.id || null,
  });
};
</script>
