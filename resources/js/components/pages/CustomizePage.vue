<template>
  <div class="px-6 py-8 md:px-8 xl:px-10">
    <div class="mx-auto max-w-6xl">
      <div class="mb-8">
        <h1 class="text-4xl font-black tracking-tight text-[#44413E]" style="font-family: 'Urbanist', sans-serif;">
          Custom order
        </h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-[#766C65]">
          Order products tailored to what you want. Upload a photo reference, choose your options, and tell us the details.
        </p>
      </div>

      <div class="mb-8 border-t border-[#E4D8CD]"></div>

      <section class="rounded-[32px] border border-[#E8DDD3] bg-white p-6 shadow-[0_18px_40px_-34px_rgba(118,79,49,0.35)] md:p-8">
        <h2 class="text-2xl font-bold text-[#4B4743]">Product Details</h2>

        <div class="mt-6 grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
          <div
            @click="triggerFileUpload"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
            class="flex min-h-[320px] cursor-pointer items-center justify-center rounded-[30px] border-2 border-dashed border-[#D8C7B8] bg-[#FCFAF8] p-6 transition-colors"
            :class="{ 'border-[#C9876C] bg-[#FBF1E8]': isDragging }"
          >
            <div v-if="!imagePreview" class="text-center">
              <svg class="mx-auto h-16 w-16 text-[#7A6A5F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
              </svg>
              <p class="mt-6 text-xl font-semibold tracking-wide text-[#4E4742]">UPLOAD IMAGE</p>
              <p class="text-xl font-semibold tracking-wide text-[#4E4742]">REFERENCE</p>
              <p class="mt-3 text-sm text-[#8B817A]">Drag & drop or click to upload</p>
            </div>

            <div v-else class="relative h-full w-full">
              <img :src="imagePreview" alt="Reference preview" class="h-full w-full rounded-[24px] object-cover" />
              <button
                type="button"
                @click.stop="removeImage"
                class="absolute top-4 right-4 rounded-full bg-red-500 p-2 text-white shadow-md transition hover:bg-red-600"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <input
              ref="fileInput"
              type="file"
              accept="image/*"
              class="hidden"
              @change="handleFileSelect"
            />
          </div>

          <div class="rounded-[30px] border border-[#E7D9CB] bg-[#FCFAF8] p-6">
            <div class="space-y-5">
              <div class="grid gap-2">
                <label class="text-sm font-medium text-[#66605B]">Size</label>
                <select
                  v-model="formData.size"
                  class="h-12 rounded-full border border-[#D8CCC2] bg-white px-5 outline-none focus:border-transparent focus:ring-2 focus:ring-[#C9876C]"
                >
                  <option>Small</option>
                  <option>Medium</option>
                  <option>Large</option>
                </select>
              </div>

              <div class="grid gap-2">
                <label class="text-sm font-medium text-[#66605B]">Quantity</label>
                <div class="flex items-center overflow-hidden rounded-full border border-[#D8CCC2] bg-white">
                  <button
                    type="button"
                    @click="decrementQuantity"
                    class="px-5 py-3 transition-colors hover:bg-[#F3ECE5]"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                  </button>
                  <input
                    v-model.number="formData.quantity"
                    type="number"
                    min="1"
                    class="flex-1 bg-transparent py-3 text-center outline-none"
                  />
                  <button
                    type="button"
                    @click="incrementQuantity"
                    class="px-5 py-3 transition-colors hover:bg-[#F3ECE5]"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                  </button>
                </div>
              </div>

              <div class="grid gap-2">
                <label class="text-sm font-medium text-[#66605B]">Flavor</label>
                <select
                  v-model="formData.flavor"
                  class="h-12 rounded-full border border-[#D8CCC2] bg-white px-5 outline-none focus:border-transparent focus:ring-2 focus:ring-[#C9876C]"
                >
                  <option>Chocolate</option>
                  <option>Vanilla</option>
                  <option>Strawberry</option>
                  <option>Mocha</option>
                  <option>Ube</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="mt-7 rounded-[32px] border border-[#E8DDD3] bg-white p-6 shadow-[0_18px_40px_-34px_rgba(118,79,49,0.25)] md:p-8">
        <h2 class="text-2xl font-bold text-[#4B4743]">Design Description</h2>
        <textarea
          v-model="formData.designDescription"
          placeholder="Describe how you like it to be"
          class="mt-5 min-h-[190px] w-full rounded-[26px] border border-[#DDD1C7] bg-[#FCFAF8] px-5 py-4 outline-none transition focus:border-transparent focus:ring-2 focus:ring-[#C9876C]"
          maxlength="300"
        ></textarea>
        <div class="mt-2 text-right text-sm text-gray-500">
          {{ formData.designDescription.length }}/300
        </div>
      </section>

      <section class="mt-7 rounded-[32px] border border-[#E8DDD3] bg-white p-6 shadow-[0_18px_40px_-34px_rgba(118,79,49,0.25)] md:p-8">
        <h2 class="text-2xl font-bold text-[#4B4743]">Dedication Message</h2>
        <textarea
          v-model="formData.dedicationMessage"
          placeholder="Write your message here"
          class="mt-5 min-h-[190px] w-full rounded-[26px] border border-[#DDD1C7] bg-[#FCFAF8] px-5 py-4 outline-none transition focus:border-transparent focus:ring-2 focus:ring-[#C9876C]"
          maxlength="300"
        ></textarea>
        <div class="mt-2 text-right text-sm text-gray-500">
          {{ formData.dedicationMessage.length }}/300
        </div>
      </section>

      <section class="mt-7 rounded-[32px] border border-[#E8DDD3] bg-white p-6 shadow-[0_18px_40px_-34px_rgba(118,79,49,0.25)] md:p-8">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
          <div>
            <h2 class="text-2xl font-bold text-[#4B4743]">Checkout Flow</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-[#766C65]">
              Choose whether this custom order should stay in your cart for a combined checkout later, or go straight to payment by itself.
            </p>
          </div>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
          <button
            v-for="option in checkoutFlowOptions"
            :key="option.value"
            type="button"
            @click="selectedCheckoutFlow = option.value"
            class="rounded-[26px] border px-5 py-5 text-left transition-all"
            :class="selectedCheckoutFlow === option.value
              ? 'border-[#C9876C] bg-[#FFF4EB] shadow-[0_18px_35px_-28px_rgba(201,135,108,0.65)]'
              : 'border-[#E7D9CB] bg-[#FCFAF8] hover:border-[#D9B8A4]'"
          >
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-lg font-bold text-[#4B4743]">{{ option.label }}</p>
                <p class="mt-1 text-sm text-[#766C65]">{{ option.description }}</p>
                <p class="mt-2 text-xs font-medium uppercase tracking-[0.14em] text-[#A07A62]">{{ option.helper }}</p>
              </div>
              <span
                class="mt-0.5 flex h-6 w-6 items-center justify-center rounded-full border"
                :class="selectedCheckoutFlow === option.value
                  ? 'border-[#C9876C] bg-[#C9876C] text-white'
                  : 'border-[#D8CCC2] bg-white text-transparent'"
              >
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M5 13l4 4L19 7" />
                </svg>
              </span>
            </div>
          </button>
        </div>
      </section>

      <section v-if="selectedCheckoutFlow === 'direct_checkout'" class="mt-7 rounded-[32px] border border-[#E8DDD3] bg-white p-6 shadow-[0_18px_40px_-34px_rgba(118,79,49,0.25)] md:p-8">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
          <div>
            <h2 class="text-2xl font-bold text-[#4B4743]">Payment Method</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-[#766C65]">
              PayMongo will handle the secure checkout. Choose the wallet you want to use for this custom order only.
            </p>
            <p class="mt-2 max-w-2xl text-xs leading-5 text-[#8A7E76]">
              Existing products already in your cart will stay there and will not be included in this direct checkout.
            </p>
          </div>
          <span class="inline-flex w-fit rounded-full bg-[#F6EFE7] px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#9B6A48]">
            PayMongo Checkout
          </span>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
          <button
            v-for="option in paymentMethodOptions"
            :key="option.value"
            type="button"
            @click="selectedPaymentMethod = option.value"
            class="rounded-[26px] border px-5 py-5 text-left transition-all"
            :class="selectedPaymentMethod === option.value
              ? 'border-[#C9876C] bg-[#FFF4EB] shadow-[0_18px_35px_-28px_rgba(201,135,108,0.65)]'
              : 'border-[#E7D9CB] bg-[#FCFAF8] hover:border-[#D9B8A4]'"
          >
            <div class="flex items-center justify-between gap-3">
              <div>
                <p class="text-lg font-bold text-[#4B4743]">{{ option.label }}</p>
                <p class="mt-1 text-sm text-[#766C65]">{{ option.description }}</p>
              </div>
              <span
                class="flex h-6 w-6 items-center justify-center rounded-full border"
                :class="selectedPaymentMethod === option.value
                  ? 'border-[#C9876C] bg-[#C9876C] text-white'
                  : 'border-[#D8CCC2] bg-white text-transparent'"
              >
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M5 13l4 4L19 7" />
                </svg>
              </span>
            </div>
          </button>
        </div>
      </section>

      <section v-else class="mt-7 rounded-[32px] border border-[#E8DDD3] bg-[#FFF9F4] p-6 shadow-[0_18px_40px_-34px_rgba(118,79,49,0.18)] md:p-8">
        <h2 class="text-2xl font-bold text-[#4B4743]">Checkout Later With Other Products</h2>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-[#766C65]">
          This custom order will be saved to your cart first. You can continue shopping, then choose this item together with breads, pastries, or other products when you are ready to checkout.
        </p>
      </section>

      <div class="mt-8 flex flex-wrap gap-4">
        <button
          type="button"
          @click="submitCustomOrder"
          class="rounded-full bg-[#C9876C] px-8 py-3.5 font-semibold text-white shadow-md transition-colors hover:bg-[#B8765B]"
        >
          {{ primaryActionLabel }}
        </button>
        <button
          type="button"
          @click="viewCart"
          class="rounded-full border border-[#CFC5BC] bg-white px-8 py-3.5 font-semibold text-[#4E4742] transition-colors hover:bg-[#FAF6F1]"
        >
          View Cart
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useSpaRouter } from '../../router';
import { useCartStore } from '../../services/cartStore';

const { push } = useSpaRouter();
const { addCustomItem, checkout } = useCartStore();
const checkoutFlowOptions = [
  {
    value: 'cart_first',
    label: 'Add to Cart First',
    description: 'Save this custom order in your cart, then checkout it later together with other products.',
    helper: 'Best if you are still shopping',
  },
  {
    value: 'direct_checkout',
    label: 'Direct Checkout',
    description: 'Checkout only this custom order right away without including the products already in your cart.',
    helper: 'Best if this order is ready now',
  },
];
const paymentMethodOptions = [
  {
    value: 'gcash',
    label: 'GCash',
    description: 'Redirect to PayMongo and complete the payment in GCash.',
  },
  {
    value: 'maya',
    label: 'Maya',
    description: 'Redirect to PayMongo and continue the payment in Maya.',
  },
];

const formData = ref({
  size: 'Medium',
  quantity: 5,
  flavor: 'Chocolate',
  designDescription: '',
  dedicationMessage: '',
  imageFile: null,
});

const imagePreview = ref(null);
const isDragging = ref(false);
const fileInput = ref(null);
const selectedCheckoutFlow = ref('cart_first');
const selectedPaymentMethod = ref('gcash');

const triggerFileUpload = () => {
  fileInput.value?.click();
};

const handleFileSelect = (event) => {
  const file = event.target.files?.[0];

  if (file) {
    processFile(file);
  }
};

const handleDrop = (event) => {
  isDragging.value = false;
  const file = event.dataTransfer.files?.[0];

  if (file && file.type.startsWith('image/')) {
    processFile(file);
  }
};

const processFile = (file) => {
  formData.value.imageFile = file;
  const reader = new FileReader();

  reader.onload = (loadEvent) => {
    imagePreview.value = loadEvent.target?.result || null;
  };

  reader.readAsDataURL(file);
};

const removeImage = () => {
  imagePreview.value = null;
  formData.value.imageFile = null;

  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

const incrementQuantity = () => {
  formData.value.quantity += 1;
};

const decrementQuantity = () => {
  if (formData.value.quantity > 1) {
    formData.value.quantity -= 1;
  }
};

const buildCustomPayload = () => ({
  size: formData.value.size,
  quantity: formData.value.quantity,
  flavor: formData.value.flavor,
  designDescription: formData.value.designDescription,
  dedicationMessage: formData.value.dedicationMessage,
  imageFile: formData.value.imageFile,
});

const ensureCustomRequestIsReady = () => {
  const hasDesignReference = Boolean(formData.value.imageFile) || formData.value.designDescription.trim().length > 0;

  if (!hasDesignReference) {
    window.alert('Add a reference image or describe the custom design before submitting your order.');
    return false;
  }

  return true;
};

const primaryActionLabel = computed(() => (
  selectedCheckoutFlow.value === 'direct_checkout'
    ? 'Direct Checkout This Custom Order'
    : 'Add to Cart for Combined Checkout'
));

const directCheckout = async () => {
  if (!ensureCustomRequestIsReady()) {
    return;
  }

  let addedItem = null;

  try {
    addedItem = await addCustomItem(buildCustomPayload());

    if (!addedItem?.id) {
      return;
    }

    const order = await checkout([addedItem.id], {
      paymentMethod: selectedPaymentMethod.value,
    });

    if (order?.checkout_url) {
      window.location.href = order.checkout_url;
      return;
    }

    if (order?.id) {
      push(`/customer/cart?ordered=${order.id}`);
    }
  } catch (error) {
    const message = error.response?.data?.message || 'Unable to complete checkout right now.';

    if (addedItem?.id) {
      window.alert(`${message} Your custom order was saved in the cart so you can checkout it later with your other products.`);
      push(`/customer/cart?added=${addedItem.id}`);
      return;
    }

    window.alert(message);
  }
};

const addToCart = async () => {
  if (!ensureCustomRequestIsReady()) {
    return;
  }

  try {
    const addedItem = await addCustomItem(buildCustomPayload());

    if (addedItem?.id) {
      push(`/customer/cart?added=${addedItem.id}`);
    }
  } catch (error) {
    window.alert('Unable to add this custom item to the cart right now.');
  }
};

const submitCustomOrder = async () => {
  if (selectedCheckoutFlow.value === 'direct_checkout') {
    await directCheckout();
    return;
  }

  await addToCart();
};

const viewCart = () => {
  push('/customer/cart');
};
</script>
