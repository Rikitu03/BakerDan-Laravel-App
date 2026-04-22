<template>
  <div class="flex h-full flex-col xl:flex-row">
    <div class="w-full border-b border-[#ECE1D8] bg-white p-8 xl:w-[39%] xl:border-r xl:border-b-0">
      <div v-if="featuredProduct" class="sticky top-8">
        <div v-if="addedItem" class="mb-5 rounded-[22px] border border-[#F0DCCC] bg-[#FFF4EB] px-5 py-4 text-[#8E5632] shadow-sm">
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#C9876C]">Added to cart</p>
          <p class="mt-1 text-sm font-medium">{{ addedItem.name }} is now in your cart.</p>
        </div>

        <div class="mb-6 overflow-hidden rounded-[28px] border border-[#EADFD6] bg-[#FBF8F5] p-3">
          <img
            :src="featuredProduct.image"
            :alt="featuredProduct.name"
            class="aspect-[4/3] w-full rounded-[22px] object-cover"
          />
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <span class="rounded-full bg-[#FAEEE4] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-[#B66B40]">
            {{ featuredProduct.tag || 'Bakerdan Pick' }}
          </span>
          <span v-if="featuredProduct.source === 'custom'" class="rounded-full bg-[#F2EEEA] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-[#7A6E65]">
            Custom Order
          </span>
        </div>

        <h2 class="mt-4 text-3xl font-bold text-gray-800" style="font-family: 'Urbanist', sans-serif;">
          {{ featuredProduct.name }}
        </h2>
        <p class="mt-3 text-gray-600">
          {{ featuredProduct.description }}
        </p>

        <div class="mt-8 text-5xl font-bold text-gray-800">
          PHP {{ formatPrice(featuredProduct.price) }}
        </div>

        <div class="mt-8 grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label class="mb-2 block text-sm text-gray-600">Size</label>
            <div class="flex h-12 items-center rounded-full border border-[#DED4CB] bg-[#FCFBF9] px-5 text-sm font-medium text-[#58514B]">
              {{ featuredProduct.size || 'Medium' }}
            </div>
          </div>

          <div>
            <label class="mb-2 block text-sm text-gray-600">Quantity</label>
            <div class="flex items-center overflow-hidden rounded-full border border-[#DED4CB] bg-[#FCFBF9]">
              <button
                type="button"
                @click="decrementQuantity"
                class="px-4 py-3 transition-colors hover:bg-[#F2ECE5]"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                </svg>
              </button>
              <input
                :value="featuredProduct.quantity"
                type="number"
                min="1"
                class="flex-1 bg-transparent py-3 text-center outline-none"
                @input="setQuantity($event.target.value)"
              />
              <button
                type="button"
                @click="incrementQuantity"
                class="px-4 py-3 transition-colors hover:bg-[#F2ECE5]"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <div v-if="featuredProduct.designDescription" class="mt-6 rounded-[24px] border border-[#EFE5DC] bg-[#FCFAF8] p-5">
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B59884]">Design Description</p>
          <p class="mt-2 text-sm leading-6 text-[#695F57]">{{ featuredProduct.designDescription }}</p>
        </div>

        <div v-if="featuredProduct.dedicationMessage" class="mt-4 rounded-[24px] border border-[#EFE5DC] bg-[#FCFAF8] p-5">
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B59884]">Dedication Message</p>
          <p class="mt-2 text-sm leading-6 text-[#695F57]">{{ featuredProduct.dedicationMessage }}</p>
        </div>

        <div class="mt-6 space-y-3">
          <button
            type="button"
            @click="addFeaturedToCart"
            class="w-full rounded-full bg-[#C9876C] py-4 font-semibold text-white shadow-md transition-colors hover:bg-[#B8765B]"
          >
            Add to cart
          </button>
          <button
            type="button"
            @click="checkout"
            class="w-full rounded-full bg-[#8B6F47] py-4 font-semibold text-white shadow-md transition-colors hover:bg-[#7A5F3A]"
          >
            Checkout
          </button>
          <button
            type="button"
            @click="continueBrowsing"
            class="w-full rounded-full border border-[#DDD4CB] bg-white py-4 font-semibold text-gray-700 transition-colors hover:bg-gray-50"
          >
            Continue browsing
          </button>
        </div>
      </div>

      <div v-else class="flex h-full min-h-[420px] items-center justify-center rounded-[28px] border border-dashed border-[#E5D8CC] bg-[#FBF8F4] p-8 text-center">
        <div>
          <h2 class="text-2xl font-bold text-[#4D4742]" style="font-family: 'Urbanist', sans-serif;">Your cart is empty</h2>
          <p class="mt-2 text-sm text-[#766D67]">Start browsing and add your favorite breads, pastries, and custom orders.</p>
          <button
            type="button"
            @click="continueBrowsing"
            class="mt-6 rounded-full bg-[#C9876C] px-6 py-3 text-sm font-semibold text-white shadow-md transition-colors hover:bg-[#B8765B]"
          >
            Back to browsing
          </button>
        </div>
      </div>
    </div>

    <div class="flex-1 bg-[#F6F4F1] p-8">
      <div class="mb-6 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div class="flex items-center gap-2">
          <h2 class="text-2xl font-bold text-gray-800">Cart List</h2>
          <span class="rounded-full bg-red-500 px-2 py-1 text-sm font-bold text-white">
            {{ visibleCartItems.length }}
          </span>
        </div>

        <div class="flex flex-col gap-3 md:flex-row md:items-center">
          <div class="relative">
            <input
              v-model="cartSearch"
              type="text"
              placeholder="Search"
              class="rounded-full border border-[#E1D7CF] bg-white py-2.5 pr-4 pl-10 outline-none focus:border-transparent focus:ring-2 focus:ring-[#C9876C]"
            />
            <svg class="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>

          <select
            v-model="cartSort"
            class="rounded-full border border-[#E1D7CF] bg-white px-4 py-2.5 outline-none focus:ring-2 focus:ring-[#C9876C]"
          >
            <option value="price">Price</option>
            <option value="name">Name</option>
            <option value="recent">Date Added</option>
          </select>
        </div>
      </div>

      <div class="mb-6 space-y-4">
        <CartItem
          v-for="item in visibleCartItems"
          :key="item.id"
          :item="item"
          :selected="selectedItems.includes(item.id)"
          :active="item.id === featuredProduct?.id"
          :recent="item.id === addedItemId"
          @toggle-select="toggleItemSelect"
          @remove="removeItem"
          @open="openItem"
        />
      </div>

      <div class="rounded-[28px] bg-white p-6 shadow-[0_18px_40px_-34px_rgba(118,79,49,0.25)]">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">
          <div class="flex flex-col gap-4 md:flex-row md:items-center">
            <label class="flex cursor-pointer items-center gap-2">
              <input
                type="checkbox"
                :checked="allSelected"
                @change="toggleSelectAll"
                class="h-5 w-5 rounded text-[#C9876C] focus:ring-[#C9876C]"
              />
              <span class="font-medium text-gray-700">Select All</span>
            </label>

            <button
              type="button"
              @click="deleteSelected"
              :disabled="selectedItems.length === 0"
              class="rounded-full bg-red-500 px-6 py-2.5 font-medium text-white transition-colors hover:bg-red-600 disabled:cursor-not-allowed disabled:bg-gray-300"
            >
              Delete
            </button>
          </div>

          <div class="flex flex-col gap-4 md:flex-row md:items-center md:gap-6">
            <div class="text-right">
              <p class="text-sm text-gray-600">Total ({{ selectedItems.length }} item)</p>
              <p class="text-3xl font-bold text-gray-800">PHP {{ formatPrice(cartTotal) }}</p>
            </div>

            <button
              type="button"
              @click="proceedToCheckout"
              :disabled="selectedItems.length === 0"
              class="rounded-full bg-[#C9876C] px-8 py-4 font-semibold text-white shadow-md transition-colors hover:bg-[#B8765B] disabled:cursor-not-allowed disabled:bg-gray-300"
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
import { computed, ref, watch } from 'vue';
import { useSpaRouter } from '../../router';
import { useCartStore } from '../../services/cartStore';
import CartItem from '../shared/CartItem.vue';

const props = defineProps({
  added: {
    type: [String, Number],
    default: null,
  },
});

const { push } = useSpaRouter();
const { cartItems, lastAddedId, addCartItem, updateCartItemQuantity, removeCartItem } = useCartStore();

const selectedItems = ref([]);
const cartSearch = ref('');
const cartSort = ref('recent');
const activeFeaturedId = ref(null);

const addedItemId = computed(() => Number(props.added) || Number(lastAddedId.value) || null);

watch(
  [addedItemId, cartItems],
  () => {
    if (addedItemId.value) {
      activeFeaturedId.value = addedItemId.value;
      return;
    }

    if (!activeFeaturedId.value || !cartItems.value.some((item) => item.id === activeFeaturedId.value)) {
      activeFeaturedId.value = cartItems.value[0]?.id || null;
    }
  },
  { immediate: true, deep: true },
);

const visibleCartItems = computed(() => {
  const query = cartSearch.value.trim().toLowerCase();
  let result = cartItems.value.filter((item) => {
    if (!query) {
      return true;
    }

    return item.name.toLowerCase().includes(query)
      || item.description.toLowerCase().includes(query)
      || item.tag.toLowerCase().includes(query);
  });

  if (cartSort.value === 'price') {
    result = [...result].sort((a, b) => b.price - a.price);
  } else if (cartSort.value === 'name') {
    result = [...result].sort((a, b) => a.name.localeCompare(b.name));
  }

  return result;
});

const featuredProduct = computed(() =>
  cartItems.value.find((item) => item.id === activeFeaturedId.value) || cartItems.value[0] || null,
);

const addedItem = computed(() => cartItems.value.find((item) => item.id === addedItemId.value) || null);

const allSelected = computed(() => selectedItems.value.length === cartItems.value.length && cartItems.value.length > 0);
const cartTotal = computed(() =>
  cartItems.value
    .filter((item) => selectedItems.value.includes(item.id))
    .reduce((total, item) => total + item.price * item.quantity, 0),
);

const formatPrice = (price) => Number(price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

const incrementQuantity = () => {
  if (!featuredProduct.value) {
    return;
  }

  updateCartItemQuantity(featuredProduct.value.id, featuredProduct.value.quantity + 1);
};

const decrementQuantity = () => {
  if (!featuredProduct.value) {
    return;
  }

  updateCartItemQuantity(featuredProduct.value.id, Math.max(1, featuredProduct.value.quantity - 1));
};

const setQuantity = (value) => {
  if (!featuredProduct.value) {
    return;
  }

  updateCartItemQuantity(featuredProduct.value.id, value);
};

const addFeaturedToCart = () => {
  if (!featuredProduct.value) {
    return;
  }

  const addedId = addCartItem({
    ...featuredProduct.value,
    productKey: featuredProduct.value.source === 'custom'
      ? `custom-copy-${featuredProduct.value.id}-${Date.now()}`
      : featuredProduct.value.productKey,
    id: featuredProduct.value.source === 'custom' ? undefined : featuredProduct.value.id,
    quantity: 1,
  });

  push(`/customer/cart?added=${addedId}`);
};

const checkout = () => {
  console.log('Checkout featured product');
};

const continueBrowsing = () => {
  push('/customer');
};

const openItem = (itemId) => {
  activeFeaturedId.value = itemId;
};

const toggleItemSelect = (itemId) => {
  const index = selectedItems.value.indexOf(itemId);

  if (index > -1) {
    selectedItems.value.splice(index, 1);
    return;
  }

  selectedItems.value.push(itemId);
};

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedItems.value = [];
    return;
  }

  selectedItems.value = cartItems.value.map((item) => item.id);
};

const removeItem = (itemId) => {
  removeCartItem(itemId);
  selectedItems.value = selectedItems.value.filter((selectedId) => selectedId !== itemId);

  if (activeFeaturedId.value === itemId) {
    activeFeaturedId.value = cartItems.value[0]?.id || null;
  }
};

const deleteSelected = () => {
  selectedItems.value.forEach((itemId) => {
    removeCartItem(itemId);
  });

  selectedItems.value = [];
  activeFeaturedId.value = cartItems.value[0]?.id || null;
};

const proceedToCheckout = () => {
  console.log('Proceeding to checkout with items:', selectedItems.value);
};
</script>
