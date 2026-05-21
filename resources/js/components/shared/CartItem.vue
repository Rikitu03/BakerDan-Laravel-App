<template>
  <div
    class="cursor-pointer rounded-[24px] border bg-white p-4 transition-all"
    :class="[
      active ? 'border-[#C9876C] shadow-[0_18px_40px_-34px_rgba(118,79,49,0.45)]' : 'border-[#E7DED6] shadow-sm hover:shadow-md',
      recent ? 'ring-2 ring-[#F5D7C2]' : '',
    ]"
    @click="$emit('open', item.id)"
  >
    <div class="flex flex-col gap-4 md:flex-row md:items-center">
      <div class="flex items-center gap-4">
        <input
          type="checkbox"
          :checked="selected"
          @change.stop="$emit('toggle-select', item.id)"
          class="h-5 w-5 rounded text-[#C9876C] focus:ring-[#C9876C]"
        />

        <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-[18px]">
          <img
            :src="item.image"
            :alt="item.name"
            class="h-full w-full object-cover"
          />
        </div>
      </div>

      <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-2">
          <span v-if="recent" class="rounded-full bg-[#FAE9DE] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-[#BA6A3F]">
            Just Added
          </span>
          <span v-if="item.source === 'custom'" class="rounded-full bg-[#F4F1EC] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-[#857567]">
            Custom Order
          </span>
        </div>

        <h3 class="mt-2 truncate text-base font-semibold text-gray-800">
          {{ item.name }}
        </h3>
        <p class="mt-1 line-clamp-2 text-sm text-gray-600">
          {{ item.description }}
        </p>

        <div class="mt-3 flex flex-wrap items-center gap-4 text-xs font-semibold text-[#8E8177]">
          <span>Qty {{ item.quantity }}</span>
          <span v-if="item.size">Size {{ item.size }}</span>
          <span v-if="item.flavor">Flavor {{ item.flavor }}</span>
        </div>
      </div>

      <div class="flex flex-col items-end justify-between gap-3">
        <p class="text-xl font-bold text-gray-800">
          PHP {{ formatPrice(item.price) }}
        </p>

        <button
          type="button"
          class="rounded-full border border-[#E6DDD5] px-4 py-2 text-xs font-semibold text-[#756B64] transition hover:border-[#D8B398] hover:text-[#B46F45]"
          @click.stop="$emit('remove', item.id)"
        >
          Remove
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  item: {
    type: Object,
    required: true,
  },
  selected: {
    type: Boolean,
    default: false,
  },
  active: {
    type: Boolean,
    default: false,
  },
  recent: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['toggle-select', 'remove', 'open']);

const formatPrice = (price) => Number(price).toFixed(2);
</script>
