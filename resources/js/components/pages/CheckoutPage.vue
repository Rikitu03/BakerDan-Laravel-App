<template>
  <div class="px-6 py-7 md:px-8 xl:px-10">
    <div class="mx-auto max-w-[1380px]">
      <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#B47A52]">Checkout</p>
          <h1 class="mt-2 text-4xl font-black tracking-tight text-[#443A34]" style="font-family: 'Urbanist', sans-serif;">
            Review your order before payment.
          </h1>
          <p class="mt-2 max-w-2xl text-sm leading-6 text-[#6E6259]">
            Confirm your fulfillment details, review the selected items, and choose how you want to pay.
          </p>
        </div>

        <button
          type="button"
          @click="backToCart"
          class="inline-flex items-center justify-center rounded-full border border-[#E1D3C5] bg-white px-5 py-3 text-sm font-semibold text-[#6E6259] transition-colors hover:bg-[#FFF8F2]"
        >
          Back to cart
        </button>
      </div>

      <div v-if="loadError" class="rounded-[28px] border border-[#F0DCCC] bg-[#FFF4EB] px-6 py-5 text-[#8E5632] shadow-sm">
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#C9876C]">Checkout unavailable</p>
        <p class="mt-2 text-sm">{{ loadError }}</p>
      </div>

      <div v-else-if="isLoading" class="space-y-5">
        <div
          v-for="placeholder in 4"
          :key="placeholder"
          class="h-40 animate-pulse rounded-[30px] border border-[#ECE1D8] bg-white shadow-[0_18px_40px_-34px_rgba(118,79,49,0.18)]"
        ></div>
      </div>

      <div v-else-if="!checkoutItems.length" class="rounded-[30px] border border-dashed border-[#E5D7CA] bg-[#FCF8F4] px-6 py-10 text-center">
        <h2 class="text-2xl font-black text-[#473D36]" style="font-family: 'Urbanist', sans-serif;">No items selected for checkout</h2>
        <p class="mt-2 text-sm text-[#766B63]">Go back to the cart, select the items you want, then continue to checkout again.</p>
        <button
          type="button"
          @click="backToCart"
          class="mt-6 rounded-full bg-[#C9876C] px-5 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#B8765B]"
        >
          Return to cart
        </button>
      </div>

      <div v-else class="grid gap-6 xl:grid-cols-[1.18fr_0.82fr]">
        <div class="space-y-5">
          <section class="rounded-[30px] border border-[#ECE1D8] bg-white p-6 shadow-[0_18px_40px_-34px_rgba(118,79,49,0.18)]">
            <div class="flex flex-col gap-4 border-b border-[#F0E5DC] pb-5 md:flex-row md:items-start md:justify-between">
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B59884]">Fulfillment Details</p>
                <h2 class="mt-2 text-xl font-black text-[#473D36]" style="font-family: 'Urbanist', sans-serif;">
                  {{ isDelivery ? 'Where should we deliver?' : 'Where will you pick up?' }}
                </h2>
              </div>

              <button
                v-if="isDelivery"
                type="button"
                @click="toggleAddressMode"
                class="rounded-full border border-[#E4D6C9] bg-[#FFF9F4] px-4 py-2 text-sm font-semibold text-[#7B5A43] transition-colors hover:bg-white"
              >
                {{ useCustomAddress ? 'Use current address' : 'Change address' }}
              </button>
            </div>

            <div class="pt-5">
              <p class="mb-3 text-sm font-semibold text-[#5E544E]">Choose fulfillment method</p>
              <div class="flex flex-col gap-3 sm:flex-row">
                <button
                  v-for="option in fulfillmentOptions"
                  :key="option.value"
                  type="button"
                  @click="selectFulfillmentMethod(option.value)"
                  class="flex flex-1 items-center justify-between rounded-[22px] border px-4 py-4 text-left transition-all"
                  :class="fulfillmentOptionClass(option.value)"
                >
                  <div class="flex items-center gap-3">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full border" :class="fulfillmentIndicatorClass(option.value)">
                      <span v-if="fulfillmentMethod === option.value" class="block h-2.5 w-2.5 rounded-full bg-current"></span>
                    </span>
                    <div>
                      <p class="font-semibold text-[#4B413B]">{{ option.label }}</p>
                      <p class="mt-1 text-sm text-[#756A62]">{{ option.description }}</p>
                    </div>
                  </div>
                </button>
              </div>

              <div v-if="isDelivery" class="mt-5">
                <div v-if="!useCustomAddress" class="rounded-[24px] border border-[#EFE3D8] bg-[#FCF8F4] p-5">
                  <p class="font-semibold text-[#4B413B]">{{ profile.name || 'Bakerdan Customer' }}</p>
                  <p class="mt-2 whitespace-pre-line text-sm leading-6 text-[#746961]">
                    {{ currentAddress || 'No current address saved yet. Click "Change address" to enter one for this order.' }}
                  </p>
                  <p v-if="profile.phone" class="mt-3 text-sm font-medium text-[#8A7768]">{{ profile.phone }}</p>
                </div>

                <div v-else class="space-y-4">
                  <div>
                    <label for="checkout-address" class="mb-2 block text-sm font-semibold text-[#5E544E]">Delivery address</label>
                    <textarea
                      id="checkout-address"
                      v-model="customAddress"
                      rows="4"
                      placeholder="Enter the delivery address for this order"
                      class="w-full rounded-[22px] border border-[#DED4CB] bg-[#FCFBF9] px-4 py-3 text-sm text-[#4B413B] outline-none transition focus:border-[#D4A27E] focus:ring-2 focus:ring-[#EFD7C4]"
                    ></textarea>
                  </div>

                  <div>
                    <label for="checkout-pin-link" class="mb-2 block text-sm font-semibold text-[#5E544E]">Pin link of the address (optional)</label>
                    <input
                      id="checkout-pin-link"
                      v-model="customPinLink"
                      type="text"
                      placeholder="Paste a map pin link if available"
                      class="h-12 w-full rounded-full border border-[#DED4CB] bg-[#FCFBF9] px-4 text-sm text-[#4B413B] outline-none transition focus:border-[#D4A27E] focus:ring-2 focus:ring-[#EFD7C4]"
                    />
                  </div>
                </div>
              </div>

              <div v-else class="mt-5 rounded-[24px] border border-[#EFE3D8] bg-[#FCF8F4] p-5">
                <p class="font-semibold text-[#4B413B]">{{ shopLocation.name }}</p>
                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-[#746961]">{{ shopLocation.address }}</p>
                <a
                  :href="shopLocation.pinLink"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="mt-3 inline-flex text-sm font-semibold text-[#B26C41] transition-colors hover:text-[#9B5931]"
                >
                  Open shop pin link
                </a>
                <p class="mt-3 text-sm font-medium text-[#8A7768]">Pickup is free. Bring your order reference when claiming.</p>
              </div>

              <p v-if="addressError" class="mt-4 text-sm font-medium text-[#B85D4A]">{{ addressError }}</p>
            </div>
          </section>

          <section class="rounded-[30px] border border-[#ECE1D8] bg-white p-6 shadow-[0_18px_40px_-34px_rgba(118,79,49,0.18)]">
            <div class="border-b border-[#F0E5DC] pb-5">
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B59884]">Products Ordered</p>
              <h2 class="mt-2 text-xl font-black text-[#473D36]" style="font-family: 'Urbanist', sans-serif;">Selected cart items</h2>
            </div>

            <div class="mt-5 hidden overflow-hidden rounded-[24px] border border-[#F0E5DC] md:block">
              <table class="min-w-full divide-y divide-[#F0E5DC]">
                <thead class="bg-[#FBF6F1]">
                  <tr>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.16em] text-[#9D8674]">Product Name</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.16em] text-[#9D8674]">Unit Price</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.16em] text-[#9D8674]">Quantity</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.16em] text-[#9D8674]">Size</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.16em] text-[#9D8674]">Flavor</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-[0.16em] text-[#9D8674]">Subtotal</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-[#F4EBE3] bg-white">
                  <tr v-for="item in checkoutItems" :key="item.id">
                    <td class="px-4 py-4 align-top">
                      <p class="font-semibold text-[#4B413B]">{{ item.name }}</p>
                      <p class="mt-1 text-sm text-[#7A6E65]">{{ item.description || 'Freshly prepared by Bakerdan.' }}</p>
                    </td>
                    <td class="px-4 py-4 text-sm text-[#5E544E]">PHP {{ formatPrice(item.price) }}</td>
                    <td class="px-4 py-4 text-sm text-[#5E544E]">{{ item.quantity }}</td>
                    <td class="px-4 py-4 text-sm text-[#5E544E]">{{ item.size || 'Standard' }}</td>
                    <td class="px-4 py-4 text-sm text-[#5E544E]">{{ item.flavor || 'N/A' }}</td>
                    <td class="px-4 py-4 text-right text-sm font-semibold text-[#4B413B]">PHP {{ formatPrice(item.line_total) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="mt-5 space-y-4 md:hidden">
              <article
                v-for="item in checkoutItems"
                :key="`mobile-${item.id}`"
                class="rounded-[24px] border border-[#F0E5DC] bg-[#FCF8F4] p-4"
              >
                <p class="font-semibold text-[#4B413B]">{{ item.name }}</p>
                <p class="mt-1 text-sm text-[#7A6E65]">{{ item.description || 'Freshly prepared by Bakerdan.' }}</p>
                <div class="mt-4 grid grid-cols-2 gap-3 text-sm text-[#5E544E]">
                  <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[#9D8674]">Unit Price</p>
                    <p class="mt-1">PHP {{ formatPrice(item.price) }}</p>
                  </div>
                  <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[#9D8674]">Quantity</p>
                    <p class="mt-1">{{ item.quantity }}</p>
                  </div>
                  <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[#9D8674]">Size</p>
                    <p class="mt-1">{{ item.size || 'Standard' }}</p>
                  </div>
                  <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[#9D8674]">Flavor</p>
                    <p class="mt-1">{{ item.flavor || 'N/A' }}</p>
                  </div>
                  <div class="col-span-2">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[#9D8674]">Subtotal</p>
                    <p class="mt-1 font-semibold text-[#4B413B]">PHP {{ formatPrice(item.line_total) }}</p>
                  </div>
                </div>
              </article>
            </div>
          </section>

          <section class="rounded-[30px] border border-[#ECE1D8] bg-white p-6 shadow-[0_18px_40px_-34px_rgba(118,79,49,0.18)]">
            <div class="border-b border-[#F0E5DC] pb-5">
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B59884]">Payment Method</p>
              <h2 class="mt-2 text-xl font-black text-[#473D36]" style="font-family: 'Urbanist', sans-serif;">Choose your payment option</h2>
            </div>

            <div class="mt-5 space-y-3">
              <button
                v-for="option in paymentOptions"
                :key="option.value"
                type="button"
                :disabled="option.disabled"
                @click="selectPaymentMethod(option)"
                class="flex w-full items-center justify-between rounded-[22px] border px-4 py-4 text-left transition-all"
                :class="paymentOptionClass(option)"
              >
                <div class="flex items-center gap-3">
                  <span
                    class="flex h-5 w-5 items-center justify-center rounded-full border text-[10px]"
                    :class="paymentIndicatorClass(option)"
                  >
                    <span v-if="selectedPaymentMethod === option.value && !option.disabled" class="block h-2.5 w-2.5 rounded-full bg-current"></span>
                  </span>
                  <div>
                    <p class="font-semibold text-[#4B413B]">{{ option.label }}</p>
                    <p class="mt-1 text-sm text-[#756A62]">{{ option.description }}</p>
                  </div>
                </div>

                <span
                  v-if="option.disabled"
                  class="rounded-full bg-[#F5EEE7] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-[#9A8472]"
                >
                  Coming soon
                </span>
              </button>
            </div>
          </section>
        </div>

        <div>
          <section class="rounded-[30px] border border-[#ECE1D8] bg-white p-6 shadow-[0_18px_40px_-34px_rgba(118,79,49,0.2)] xl:sticky xl:top-8">
            <div class="border-b border-[#F0E5DC] pb-5">
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B59884]">Summary</p>
              <h2 class="mt-2 text-xl font-black text-[#473D36]" style="font-family: 'Urbanist', sans-serif;">Order total</h2>
            </div>

            <div class="mt-5 space-y-4">
              <div class="flex items-center justify-between text-sm text-[#6E6259]">
                <span>Subtotal Items</span>
                <span class="font-semibold text-[#4B413B]">PHP {{ formatPrice(subtotalItems) }}</span>
              </div>

              <div class="flex items-center justify-between text-sm text-[#6E6259]">
                <span>Shipping Costs</span>
                <span class="font-semibold text-[#4B413B]">PHP {{ formatPrice(shippingCost) }}</span>
              </div>

              <div class="border-t border-dashed border-[#E7DBD0] pt-4">
                <div class="flex items-center justify-between">
                  <span class="text-base font-semibold text-[#5C514A]">Order Total</span>
                  <span class="text-3xl font-black text-[#473D36]" style="font-family: 'Urbanist', sans-serif;">
                    PHP {{ formatPrice(orderTotal) }}
                  </span>
                </div>
              </div>
            </div>

            <p v-if="checkoutError" class="mt-5 rounded-[20px] border border-[#F0DCCC] bg-[#FFF4EB] px-4 py-3 text-sm font-medium text-[#8E5632]">
              {{ checkoutError }}
            </p>

            <button
              type="button"
              @click="placeOrder"
              :disabled="isPlacingOrder || !checkoutItems.length"
              class="mt-6 w-full rounded-full bg-[#C9876C] px-5 py-4 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#B8765B] disabled:cursor-not-allowed disabled:bg-gray-300"
            >
              {{ isPlacingOrder ? 'Redirecting to payment...' : 'Place order' }}
            </button>

            <p class="mt-4 text-xs leading-5 text-[#8A7E76]">
              You'll be redirected to the selected payment gateway after placing the order.
            </p>
          </section>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useSpaRouter } from '../../router';
import api from '../../services/api';
import { useCartStore } from '../../services/cartStore';

const SHIPPING_COST = 150;
const PICKUP_SHOP_NAME = 'Bakerdan Shop';
const PICKUP_SHOP_ADDRESS = 'Bakerdan Shop, 28 Market Avenue, San Nicolas, Pasig City, Metro Manila';
const PICKUP_SHOP_PIN_LINK = 'https://maps.app.goo.gl/bakerdan-pickup-placeholder';

const props = defineProps({
  items: {
    type: String,
    default: null,
  },
});

const { push } = useSpaRouter();
const {
  cartItems,
  isSubmitting,
  loadCart,
  checkout: checkoutCartItems,
} = useCartStore();

const bootCustomer = window.Laravel?.customer || window.Laravel?.user || {};
const profile = ref({
  name: bootCustomer.name || 'Bakerdan Customer',
  phone: bootCustomer.phone || '',
  address: bootCustomer.address || '',
});
const isLoading = ref(true);
const loadError = ref('');
const checkoutError = ref('');
const addressError = ref('');
const useCustomAddress = ref(false);
const customAddress = ref('');
const customPinLink = ref('');
const fulfillmentMethod = ref('delivery');
const selectedPaymentMethod = ref('gcash');

const fulfillmentOptions = [
  {
    value: 'delivery',
    label: 'Delivery',
    description: 'Deliver the order to your saved or custom address.',
  },
  {
    value: 'pickup',
    label: 'Pickup',
    description: 'Pick up the order directly from the Bakerdan shop.',
  },
];

const paymentOptions = [
  {
    value: 'gcash',
    label: 'GCash',
    description: 'Pay using your GCash e-wallet.',
    disabled: false,
  },
  {
    value: 'maya',
    label: 'Maya',
    description: 'Pay using your Maya wallet.',
    disabled: false,
  },
  {
    value: 'card',
    label: 'Credit / Debit Card',
    description: 'Card payments will be added in a future update.',
    disabled: true,
  },
];

const shopLocation = {
  name: PICKUP_SHOP_NAME,
  address: PICKUP_SHOP_ADDRESS,
  pinLink: PICKUP_SHOP_PIN_LINK,
};

const selectedItemIds = computed(() =>
  String(props.items || '')
    .split(',')
    .map((value) => Number(value.trim()))
    .filter((value, index, values) => Number.isInteger(value) && value > 0 && values.indexOf(value) === index),
);

const checkoutItems = computed(() => {
  if (!selectedItemIds.value.length) {
    return [];
  }

  return cartItems.value
    .filter((item) => selectedItemIds.value.includes(item.id))
    .map((item) => ({
      ...item,
      line_total: Number(item.price) * Number(item.quantity),
    }));
});

const checkoutItemIds = computed(() => checkoutItems.value.map((item) => item.id));
const isDelivery = computed(() => fulfillmentMethod.value === 'delivery');
const subtotalItems = computed(() =>
  checkoutItems.value.reduce((total, item) => total + Number(item.line_total || 0), 0),
);
const shippingCost = computed(() => (checkoutItems.value.length && isDelivery.value ? SHIPPING_COST : 0));
const orderTotal = computed(() => subtotalItems.value + shippingCost.value);
const currentAddress = computed(() => String(profile.value.address || '').trim());
const activeAddress = computed(() =>
  isDelivery.value
    ? (useCustomAddress.value ? String(customAddress.value || '').trim() : currentAddress.value)
    : shopLocation.address,
);
const activePinLink = computed(() =>
  isDelivery.value
    ? (useCustomAddress.value ? String(customPinLink.value || '').trim() : '')
    : shopLocation.pinLink,
);
const isPlacingOrder = computed(() => Boolean(isSubmitting.value));

const formatPrice = (price) => Number(price || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

const fulfillmentOptionClass = (value) => (
  fulfillmentMethod.value === value
    ? 'border-[#C9876C] bg-[#FFF4EB] shadow-[0_12px_30px_-24px_rgba(201,135,108,0.65)]'
    : 'border-[#E5D8CC] bg-white hover:border-[#D6B9A5]'
);

const fulfillmentIndicatorClass = (value) => (
  fulfillmentMethod.value === value
    ? 'border-[#C9876C] bg-white text-[#C9876C]'
    : 'border-[#D8CCC2] bg-white text-transparent'
);

const paymentOptionClass = (option) => {
  if (option.disabled) {
    return 'cursor-not-allowed border-[#EEE3D9] bg-[#FAF7F3] opacity-75';
  }

  return selectedPaymentMethod.value === option.value
    ? 'border-[#C9876C] bg-[#FFF4EB] shadow-[0_12px_30px_-24px_rgba(201,135,108,0.65)]'
    : 'border-[#E5D8CC] bg-white hover:border-[#D6B9A5]';
};

const paymentIndicatorClass = (option) => {
  if (option.disabled) {
    return 'border-[#DCCEC2] bg-white text-transparent';
  }

  return selectedPaymentMethod.value === option.value
    ? 'border-[#C9876C] bg-[#C9876C] text-white'
    : 'border-[#D8CCC2] bg-white text-transparent';
};

const selectPaymentMethod = (option) => {
  if (option.disabled) {
    return;
  }

  selectedPaymentMethod.value = option.value;
};

const selectFulfillmentMethod = (value) => {
  fulfillmentMethod.value = value;
  addressError.value = '';

  if (value === 'delivery' && !currentAddress.value) {
    useCustomAddress.value = true;
  }
};

const toggleAddressMode = () => {
  if (useCustomAddress.value) {
    useCustomAddress.value = false;
    addressError.value = '';
    return;
  }

  customAddress.value = currentAddress.value;
  useCustomAddress.value = true;
};

const validateCheckout = () => {
  checkoutError.value = '';
  addressError.value = '';

  if (!checkoutItems.value.length) {
    checkoutError.value = 'There are no cart items ready for checkout.';
    return false;
  }

  if (isDelivery.value && !activeAddress.value) {
    addressError.value = 'Please provide a delivery address before placing the order.';
    useCustomAddress.value = true;
    return false;
  }

  return true;
};

const loadProfile = async () => {
  try {
    const response = await api.getProfile();
    const payload = response.data?.data || {};

    profile.value = {
      name: payload.name || profile.value.name,
      phone: payload.phone || profile.value.phone,
      address: payload.address || profile.value.address,
    };
  } catch (error) {
    // Keep using the bootstrapped profile if the API profile cannot be fetched.
  }
};

const backToCart = () => {
  push('/customer/cart');
};

const placeOrder = async () => {
  if (!validateCheckout()) {
    return;
  }

  try {
    if (!checkoutItemIds.value.length) {
      checkoutError.value = 'There are no cart items ready for checkout.';
      return;
    }

    const order = await checkoutCartItems(checkoutItemIds.value, {
      paymentMethod: selectedPaymentMethod.value,
      fulfillmentMethod: fulfillmentMethod.value,
      shippingAddress: activeAddress.value,
      shippingPinLink: activePinLink.value,
    });

    if (order?.checkout_url) {
      window.location.href = order.checkout_url;
      return;
    }

    if (order?.id) {
      push(`/customer/orders?highlight=${order.id}`);
      return;
    }

    checkoutError.value = 'Unable to start checkout right now.';
  } catch (error) {
    checkoutError.value = error.response?.data?.message || 'Unable to complete checkout right now.';
  }
};

onMounted(async () => {
  isLoading.value = true;
  loadError.value = '';

  try {
    await Promise.all([
      loadCart({ force: true }),
      loadProfile(),
    ]);

    if (!currentAddress.value) {
      useCustomAddress.value = true;
      customAddress.value = '';
    }
  } catch (error) {
    loadError.value = error.response?.data?.message || 'We could not load your checkout details right now.';
  } finally {
    isLoading.value = false;
  }
});
</script>
