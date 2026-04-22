<template>
  <div class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow flex items-center gap-4">
    <!-- Checkbox -->
    <input
      type="checkbox"
      :checked="selected"
      @change="$emit('toggle-select', item.id)"
      class="w-5 h-5 text-[#C9876C] rounded focus:ring-[#C9876C]"
    />

    <!-- Product Image -->
    <div class="w-24 h-24 rounded-lg overflow-hidden flex-shrink-0">
      <img
        :src="item.image"
        :alt="item.name"
        class="w-full h-full object-cover"
      />
    </div>

    <!-- Product Info -->
    <div class="flex-1 min-w-0">
      <h3 class="font-semibold text-gray-800 mb-1 truncate">
        {{ item.name }}
      </h3>
      <p class="text-sm text-gray-600 line-clamp-2">
        {{ item.description }}
      </p>
    </div>

    <!-- Price -->
    <div class="text-right flex-shrink-0">
      <p class="text-xl font-bold text-gray-800">
        ₱ {{ formatPrice(item.price) }}
      </p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  item: {
    type: Object,
    required: true
  },
  selected: {
    type: Boolean,
    default: false
  }
})

defineEmits(['toggle-select', 'remove'])

const formatPrice = (price) => {
  return price.toFixed(2)
}
</script>
