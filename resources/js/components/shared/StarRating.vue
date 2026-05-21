<template>
  <div class="flex items-center gap-2">
    <div class="flex items-center gap-0.5">
      <template v-for="i in 5" :key="i">
        <div class="relative" :class="starSizeClass">
          <!-- Empty Star -->
          <svg class="absolute inset-0 h-full w-full text-[#E5E7EB]" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
          </svg>
          <!-- Filled Star -->
          <div
            class="absolute inset-0 overflow-hidden text-[#F59E0B]"
            :style="{ width: getStarWidth(i) }"
          >
            <svg class="h-full w-full" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
          </div>
        </div>
      </template>
    </div>
    <div v-if="showText" class="flex items-center gap-1.5">
      <span :class="ratingTextClass">{{ Number(rating).toFixed(1) }}</span>
      <span v-if="reviewCount !== null" class="h-1 w-1 rounded-full bg-[#D1C6BE]"></span>
      <span v-if="reviewCount !== null" :class="reviewCountClass">{{ reviewCount }} reviews</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  rating: {
    type: [Number, String],
    default: 0
  },
  reviewCount: {
    type: [Number, String],
    default: null
  },
  size: {
    type: String,
    default: 'md' // sm, md, lg
  },
  showText: {
    type: Boolean,
    default: true
  }
});

const getStarWidth = (index) => {
  const diff = Number(props.rating) - (index - 1);
  if (diff >= 1) return '100%';
  if (diff <= 0) return '0%';
  return `${diff * 100}%`;
};

const starSizeClass = computed(() => {
  return {
    'h-3 w-3': props.size === 'sm',
    'h-4 w-4': props.size === 'md',
    'h-5 w-5': props.size === 'lg'
  };
});

const ratingTextClass = computed(() => {
  return {
    'text-[10px] font-bold text-[#443A34]': props.size === 'sm',
    'text-sm font-black text-[#443A34]': props.size === 'md',
    'text-base font-black text-[#443A34]': props.size === 'lg'
  };
});

const reviewCountClass = computed(() => {
  return {
    'text-[10px] font-medium text-[#7C746E]': props.size === 'sm',
    'text-sm font-medium text-[#7C746E]': props.size === 'md',
    'text-base font-medium text-[#7C746E]': props.size === 'lg'
  };
});
</script>
