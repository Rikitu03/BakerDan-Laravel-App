<template>
  <aside class="w-[272px] border-r border-[#EFE5DC] bg-white px-6 py-7">
    <div class="mb-8">
      <h2 class="mb-4 text-xs font-semibold uppercase tracking-[0.22em] text-gray-500">
        All Categories
      </h2>

      <div class="space-y-2">
        <button
          v-for="category in categories"
          :key="category.name"
          @click="selectCategory(category.name)"
          class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 transition-all duration-200"
          :class="category.active
            ? 'bg-[#C9876C] text-white shadow-md'
            : 'text-gray-700 hover:bg-[#F7F2EC]'"
        >
          <svg
            v-if="getCategoryIcon(category.name) === 'bread'"
            class="h-5 w-5"
            fill="currentColor"
            viewBox="0 0 24 24"
          >
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" />
          </svg>
          <svg
            v-else-if="getCategoryIcon(category.name) === 'pastry'"
            class="h-5 w-5"
            fill="currentColor"
            viewBox="0 0 24 24"
          >
            <path d="M21 3H3v18h18V3zm-2 16H5V5h14v14z" />
          </svg>
          <svg
            v-else-if="getCategoryIcon(category.name) === 'cake'"
            class="h-5 w-5"
            fill="currentColor"
            viewBox="0 0 24 24"
          >
            <path d="M12 6c1.11 0 2-.9 2-2 0-.38-.1-.73-.29-1.03L12 0l-1.71 2.97c-.19.3-.29.65-.29 1.03 0 1.1.9 2 2 2zm4.6 9.99l-1.07-1.07-1.08 1.07c-1.3 1.3-3.58 1.31-4.89 0l-1.07-1.07-1.09 1.07C6.75 16.64 5.88 17 4.96 17c-.73 0-1.4-.23-1.96-.61V21c0 .55.45 1 1 1h16c.55 0 1-.45 1-1v-4.61c-.56.38-1.23.61-1.96.61-.92 0-1.79-.36-2.44-1.01zM18 9h-5V7h-2v2H6c-1.66 0-3 1.34-3 3v1.54c0 1.08.88 1.96 1.96 1.96.52 0 1.02-.2 1.38-.57l2.14-2.13 2.13 2.13c.74.74 2.03.74 2.77 0l2.14-2.13 2.13 2.13c.37.37.86.57 1.38.57 1.08 0 1.96-.88 1.96-1.96V12C21 10.34 19.66 9 18 9z" />
          </svg>
          <svg
            v-else
            class="h-5 w-5"
            fill="currentColor"
            viewBox="0 0 24 24"
          >
            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
          </svg>
          <span class="font-medium">{{ category.name }}</span>
        </button>
      </div>
    </div>

    <div class="rounded-[28px] border border-[#F0E2D7] bg-[#FBF7F2] p-5">
      <h3 class="mb-4 text-xs font-semibold uppercase tracking-[0.22em] text-gray-500">
        Price Range
      </h3>

      <div class="space-y-3">
        <div>
          <label class="mb-1 block text-xs text-gray-600">Minimum</label>
          <div class="relative">
            <span class="absolute top-1/2 left-3 -translate-y-1/2 text-xs text-gray-500">PHP</span>
            <input
              v-model="minPrice"
              type="number"
              placeholder="0"
              class="w-full rounded-full border border-[#E4D8CD] py-2.5 pr-3 pl-11 outline-none focus:border-transparent focus:ring-2 focus:ring-[#C9876C]"
            />
          </div>
        </div>

        <div>
          <label class="mb-1 block text-xs text-gray-600">Maximum</label>
          <div class="relative">
            <span class="absolute top-1/2 left-3 -translate-y-1/2 text-xs text-gray-500">PHP</span>
            <input
              v-model="maxPrice"
              type="number"
              placeholder="10000"
              class="w-full rounded-full border border-[#E4D8CD] py-2.5 pr-3 pl-11 outline-none focus:border-transparent focus:ring-2 focus:ring-[#C9876C]"
            />
          </div>
        </div>

        <button
          @click="applyFilter"
          class="w-full rounded-full bg-[#C9876C] px-4 py-2.5 font-medium text-white shadow-md transition-colors duration-200 hover:bg-[#B8765B]"
        >
          Apply
        </button>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref } from 'vue';

defineProps({
  categories: {
    type: Array,
    required: true,
  },
  activeCategory: {
    type: String,
    default: 'Bread',
  },
});

const emit = defineEmits(['category-select', 'filter-apply']);
const minPrice = ref('');
const maxPrice = ref('');

const getCategoryIcon = (name) => {
  const icons = {
    Bread: 'bread',
    Pastries: 'pastry',
    Cakes: 'cake',
    Customize: 'star',
  };

  return icons[name] || 'star';
};

const selectCategory = (categoryName) => {
  emit('category-select', categoryName);
};

const applyFilter = () => {
  emit('filter-apply', {
    min: minPrice.value,
    max: maxPrice.value,
  });
};
</script>
