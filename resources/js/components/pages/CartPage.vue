<template>
  <div class="flex h-full flex-col xl:flex-row">
    <div class="w-full border-b border-[#ECE1D8] bg-white p-8 xl:w-[39%] xl:border-r xl:border-b-0">
      <div v-if="featuredProduct" class="sticky top-8">
        <div
          v-if="checkoutNotice.visible"
          class="mb-5 rounded-[22px] border px-5 py-4 shadow-sm"
          :class="checkoutNotice.wrapperClass"
        >
          <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.18em]" :class="checkoutNotice.eyebrowClass">{{ checkoutNotice.title }}</p>
              <p class="mt-1 text-sm font-medium">{{ checkoutNotice.message }}</p>
            </div>
            <button
              v-if="canContinuePayment"
              type="button"
              @click="continuePayment"
              class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-[#6D4A35] transition-colors hover:bg-[#FFF7F1]"
            >
              Continue payment
            </button>
          </div>
        </div>

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
                @change="setQuantity($event.target.value)"
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

        <div class="mt-6 rounded-[24px] border border-[#EFE5DC] bg-[#FCFAF8] p-5">
          <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B59884]">Payment Method</p>
              <p class="mt-2 text-sm text-[#695F57]">Use PayMongo to pay with your preferred wallet.</p>
            </div>
            <span class="inline-flex w-fit rounded-full bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-[#9B6A48]">
              PayMongo
            </span>
          </div>

          <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <button
              v-for="option in paymentMethodOptions"
              :key="`featured-${option.value}`"
              type="button"
              @click="selectedPaymentMethod = option.value"
              class="rounded-[22px] border px-4 py-4 text-left transition-all"
              :class="paymentMethodButtonClass(option.value)"
            >
              <div class="flex items-center justify-between gap-3">
                <div>
                  <p class="font-semibold text-[#4D4742]">{{ option.label }}</p>
                  <p class="mt-1 text-xs text-[#766D67]">{{ option.description }}</p>
                </div>
                <span
                  class="flex h-6 w-6 items-center justify-center rounded-full border"
                  :class="paymentMethodIndicatorClass(option.value)"
                >
                  <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M5 13l4 4L19 7" />
                  </svg>
                </span>
              </div>
            </button>
          </div>
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
            Checkout with {{ selectedPaymentMethodLabel }}
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
          <div
            v-if="checkoutNotice.visible"
            class="mb-5 rounded-[22px] border px-5 py-4 text-left shadow-sm"
            :class="checkoutNotice.wrapperClass"
          >
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em]" :class="checkoutNotice.eyebrowClass">{{ checkoutNotice.title }}</p>
                <p class="mt-1 text-sm font-medium">{{ checkoutNotice.message }}</p>
              </div>
              <button
                v-if="canContinuePayment"
                type="button"
                @click="continuePayment"
                class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-[#6D4A35] transition-colors hover:bg-[#FFF7F1]"
              >
                Continue payment
              </button>
            </div>
          </div>
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
            <div class="flex flex-col gap-5 xl:items-end">
              <div class="rounded-[24px] border border-[#EAE0D8] bg-[#FCFAF8] p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B59884]">Payment Method</p>
                <div class="mt-3 flex flex-wrap gap-3">
                  <button
                    v-for="option in paymentMethodOptions"
                    :key="`summary-${option.value}`"
                    type="button"
                    @click="selectedPaymentMethod = option.value"
                    class="rounded-full border px-4 py-2 text-sm font-semibold transition-all"
                    :class="paymentMethodButtonClass(option.value)"
                  >
                    {{ option.label }}
                  </button>
                </div>
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
                  Checkout with {{ selectedPaymentMethodLabel }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useSpaRouter } from '../../router';
import { useCartStore } from '../../services/cartStore';
import CartItem from '../shared/CartItem.vue';

const props = defineProps({
  added: {
    type: [String, Number],
    default: null,
  },
  ordered: {
    type: [String, Number],
    default: null,
  },
  paymentReturn: {
    type: String,
    default: null,
  },
});

const { push } = useSpaRouter();
const {
  cartItems,
  lastAddedId,
  lastCheckoutOrderId,
  loadCart,
  addCatalogItem,
  addCustomItem,
  updateCartItemQuantity,
  removeCartItem,
  checkout: checkoutCartItems,
  syncOrderPaymentStatus,
} = useCartStore();

const selectedItems = ref([]);
const cartSearch = ref('');
const cartSort = ref('recent');
const activeFeaturedId = ref(null);
const selectedPaymentMethod = ref('gcash');
const paymentStatusDetails = ref(null);
const isSyncingPayment = ref(false);
const paymentSyncError = ref('');
const paymentMethodOptions = [
  {
    value: 'gcash',
    label: 'GCash',
    description: 'Pay through the GCash wallet.',
  },
  {
    value: 'maya',
    label: 'Maya',
    description: 'Pay through the Maya wallet.',
  },
];

const addedItemId = computed(() => Number(props.added) || Number(lastAddedId.value) || null);
const checkoutOrderId = computed(() => Number(props.ordered) || Number(lastCheckoutOrderId.value) || null);

watch(
  cartItems,
  (items) => {
    selectedItems.value = selectedItems.value.filter((itemId) => items.some((item) => item.id === itemId));
  },
  { deep: true },
);

watch(
  [addedItemId, cartItems],
  () => {
    if (addedItemId.value && cartItems.value.some((item) => item.id === addedItemId.value)) {
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
    .reduce((total, item) => total + Number(item.price) * Number(item.quantity), 0),
);
const syncedOrder = computed(() => paymentStatusDetails.value?.order || null);
const paymentCheckoutUrl = computed(() => paymentStatusDetails.value?.payment?.checkout_url || syncedOrder.value?.checkout_url || null);
const resolvedPaymentMethod = computed(() => syncedOrder.value?.payment_method || selectedPaymentMethod.value);
const containsCustomOrder = computed(() => Boolean(syncedOrder.value?.contains_custom));
const resolvedPaymentStatus = computed(() => {
  if (syncedOrder.value?.payment_status) {
    return syncedOrder.value.payment_status;
  }

  return checkoutOrderId.value ? 'pending' : null;
});
const selectedPaymentMethodLabel = computed(() => formatPaymentMethod(selectedPaymentMethod.value));
const canContinuePayment = computed(() => Boolean(paymentCheckoutUrl.value) && resolvedPaymentStatus.value === 'pending');
const checkoutNotice = computed(() => {
  if (!checkoutOrderId.value) {
    return { visible: false };
  }

  if (isSyncingPayment.value && !syncedOrder.value) {
    return {
      visible: true,
      title: 'Checking payment',
      message: `We're verifying Order #${checkoutOrderId.value} with PayMongo right now.`,
      wrapperClass: 'border-[#D8E8F6] bg-[#F4FAFF] text-[#2F5F87]',
      eyebrowClass: 'text-[#5E94C2]',
    };
  }

  if (resolvedPaymentStatus.value === 'paid') {
    return {
      visible: true,
      title: 'Payment confirmed',
      message: containsCustomOrder.value
        ? `Order #${checkoutOrderId.value} was paid successfully via ${formatPaymentMethod(resolvedPaymentMethod.value)}. The bakery will review your custom brief before production starts.`
        : `Order #${checkoutOrderId.value} was paid successfully via ${formatPaymentMethod(resolvedPaymentMethod.value)}.`,
      wrapperClass: 'border-[#DDEED8] bg-[#F4FFF0] text-[#3E6B35]',
      eyebrowClass: 'text-[#6EAA57]',
    };
  }

  if (resolvedPaymentStatus.value === 'failed' || resolvedPaymentStatus.value === 'expired') {
    return {
      visible: true,
      title: 'Payment incomplete',
      message: resolvedPaymentStatus.value === 'expired'
        ? `Order #${checkoutOrderId.value} expired before the payment was completed.`
        : `Order #${checkoutOrderId.value} was not paid successfully.`,
      wrapperClass: 'border-[#F0DCCC] bg-[#FFF4EB] text-[#8E5632]',
      eyebrowClass: 'text-[#C9876C]',
    };
  }

  if (props.paymentReturn === 'cancelled') {
    return {
      visible: true,
      title: 'Payment pending',
      message: `Order #${checkoutOrderId.value} is still waiting for payment. You can continue the PayMongo checkout anytime.`,
      wrapperClass: 'border-[#F0DCCC] bg-[#FFF4EB] text-[#8E5632]',
      eyebrowClass: 'text-[#C9876C]',
    };
  }

  if (props.paymentReturn === 'success' && paymentSyncError.value) {
    return {
      visible: true,
      title: 'Verification pending',
      message: paymentSyncError.value,
      wrapperClass: 'border-[#D8E8F6] bg-[#F4FAFF] text-[#2F5F87]',
      eyebrowClass: 'text-[#5E94C2]',
    };
  }

  return {
    visible: true,
    title: 'Order created',
    message: containsCustomOrder.value
      ? `Order #${checkoutOrderId.value} is awaiting payment via ${formatPaymentMethod(resolvedPaymentMethod.value)}. Once paid, the bakery will review your custom design details.`
      : `Order #${checkoutOrderId.value} is awaiting payment via ${formatPaymentMethod(resolvedPaymentMethod.value)}.`,
    wrapperClass: 'border-[#D8E8F6] bg-[#F4FAFF] text-[#2F5F87]',
    eyebrowClass: 'text-[#5E94C2]',
  };
});

const formatPrice = (price) => Number(price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
const formatPaymentMethod = (value) => (value === 'maya' ? 'Maya' : 'GCash');
const paymentMethodButtonClass = (value) => (
  selectedPaymentMethod.value === value
    ? 'border-[#C9876C] bg-[#FFF4EB] text-[#7E4E32] shadow-[0_12px_30px_-26px_rgba(201,135,108,0.7)]'
    : 'border-[#E5D8CC] bg-white text-[#5C5651] hover:border-[#D6B9A5]'
);
const paymentMethodIndicatorClass = (value) => (
  selectedPaymentMethod.value === value
    ? 'border-[#C9876C] bg-[#C9876C] text-white'
    : 'border-[#D8CCC2] bg-white text-transparent'
);

const refreshCart = async () => {
  try {
    await loadCart({ force: true });
  } catch (error) {
    window.alert('Unable to load your cart right now.');
  }
};

const refreshOrderPaymentStatus = async () => {
  if (!checkoutOrderId.value) {
    paymentStatusDetails.value = null;
    paymentSyncError.value = '';
    return;
  }

  isSyncingPayment.value = true;
  paymentSyncError.value = '';

  try {
    paymentStatusDetails.value = await syncOrderPaymentStatus(checkoutOrderId.value);
  } catch (error) {
    paymentSyncError.value = error.response?.data?.message || 'We returned from PayMongo, but payment confirmation is still pending.';
  } finally {
    isSyncingPayment.value = false;
  }
};

const incrementQuantity = async () => {
  if (!featuredProduct.value) {
    return;
  }

  try {
    await updateCartItemQuantity(featuredProduct.value.id, featuredProduct.value.quantity + 1);
  } catch (error) {
    window.alert('Unable to update the cart quantity right now.');
  }
};

const decrementQuantity = async () => {
  if (!featuredProduct.value) {
    return;
  }

  try {
    await updateCartItemQuantity(featuredProduct.value.id, Math.max(1, featuredProduct.value.quantity - 1));
  } catch (error) {
    window.alert('Unable to update the cart quantity right now.');
  }
};

const setQuantity = async (value) => {
  if (!featuredProduct.value) {
    return;
  }

  const normalizedQuantity = Math.max(1, Number(value) || 1);
  try {
    await updateCartItemQuantity(featuredProduct.value.id, normalizedQuantity);
  } catch (error) {
    window.alert('Unable to update the cart quantity right now.');
  }
};

const addFeaturedToCart = async () => {
  if (!featuredProduct.value) {
    return;
  }

  try {
    if (featuredProduct.value.source === 'custom') {
      const addedItem = await addCustomItem({
        size: featuredProduct.value.size,
        quantity: 1,
        flavor: featuredProduct.value.flavor,
        designDescription: featuredProduct.value.designDescription,
        dedicationMessage: featuredProduct.value.dedicationMessage,
        imageUrl: featuredProduct.value.image,
      });

      if (addedItem?.id) {
        push(`/customer/cart?added=${addedItem.id}`);
      }

      return;
    }

    const addedItem = await addCatalogItem(featuredProduct.value.product_id, {
      quantity: 1,
      size: featuredProduct.value.size,
      flavor: featuredProduct.value.flavor,
    });

    if (addedItem?.id) {
      push(`/customer/cart?added=${addedItem.id}`);
    }
  } catch (error) {
    window.alert('Unable to add this item to the cart right now.');
  }
};

const checkout = async () => {
  if (!featuredProduct.value) {
    return;
  }

  try {
    const featuredItemId = featuredProduct.value.id;
    const order = await checkoutCartItems([featuredItemId], {
      paymentMethod: selectedPaymentMethod.value,
    });
    selectedItems.value = selectedItems.value.filter((itemId) => itemId !== featuredItemId);

    if (order?.checkout_url) {
      window.location.href = order.checkout_url;
      return;
    }

    if (order?.id) {
      push(`/customer/cart?ordered=${order.id}`);
    }
  } catch (error) {
    window.alert(error.response?.data?.message || 'Unable to complete checkout right now.');
  }
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

const removeItem = async (itemId) => {
  try {
    await removeCartItem(itemId);
    selectedItems.value = selectedItems.value.filter((selectedId) => selectedId !== itemId);

    if (activeFeaturedId.value === itemId) {
      activeFeaturedId.value = cartItems.value[0]?.id || null;
    }
  } catch (error) {
    window.alert('Unable to remove this item right now.');
  }
};

const deleteSelected = async () => {
  const itemsToDelete = [...selectedItems.value];

  try {
    for (const itemId of itemsToDelete) {
      await removeCartItem(itemId);
    }

    selectedItems.value = [];
    activeFeaturedId.value = cartItems.value[0]?.id || null;
  } catch (error) {
    window.alert('Unable to remove the selected items right now.');
  }
};

const proceedToCheckout = async () => {
  if (!selectedItems.value.length) {
    return;
  }

  try {
    const order = await checkoutCartItems(selectedItems.value, {
      paymentMethod: selectedPaymentMethod.value,
    });
    selectedItems.value = [];

    if (order?.checkout_url) {
      window.location.href = order.checkout_url;
      return;
    }

    if (order?.id) {
      push(`/customer/cart?ordered=${order.id}`);
    }
  } catch (error) {
    window.alert(error.response?.data?.message || 'Unable to complete checkout right now.');
  }
};

const continuePayment = () => {
  if (paymentCheckoutUrl.value) {
    window.location.href = paymentCheckoutUrl.value;
  }
};

watch(
  checkoutOrderId,
  () => {
    refreshOrderPaymentStatus();
  },
  { immediate: true },
);

onMounted(() => {
  refreshCart();
});
</script>
