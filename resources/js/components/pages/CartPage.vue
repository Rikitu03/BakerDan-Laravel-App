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

        <div v-if="addError" class="mb-5 rounded-[22px] border border-[#F0DCCC] bg-[#FFF4EB] px-5 py-4 text-[#8E5632] shadow-sm">
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#C9876C]">Cart update</p>
          <p class="mt-1 text-sm font-medium">{{ addError }}</p>
        </div>

        <div class="mb-6 overflow-hidden rounded-[28px] border border-[#EADFD6] bg-[#FBF8F5] p-3">
          <img
            :src="featuredImage"
            :alt="featuredProduct.name"
            loading="eager"
            decoding="async"
            class="aspect-[4/3] w-full rounded-[22px] object-cover"
            @error="handleFeaturedImageError"
          />
        </div>

        <StarRating
          v-if="featuredProduct.review_count !== undefined || featuredProduct.reviewCount !== undefined"
          class="mb-3"
          :rating="featuredProduct.average_rating ?? featuredProduct.averageRating ?? 0"
          :review-count="featuredProduct.review_count ?? featuredProduct.reviewCount ?? 0"
        />
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
          {{ featuredDescription }}
        </p>

        <div class="mt-8 text-5xl font-bold text-gray-800">
          PHP {{ formatPrice(featuredUnitPrice) }}
        </div>

        <div class="mt-8 grid grid-cols-1 gap-4 md:grid-cols-2">
          <div v-if="optionChoices.length > 1">
            <label class="mb-2 block text-sm text-gray-600">Flavor / Option</label>
            <select
              v-model="selectedOptionId"
              class="h-12 w-full rounded-full border border-[#DED4CB] bg-[#FCFBF9] px-5 text-sm font-medium text-[#58514B] outline-none focus:ring-2 focus:ring-[#C9876C]"
            >
              <option v-for="option in optionChoices" :key="option.id" :value="option.id">
                {{ option.label }} - {{ option.price_label }}
              </option>
            </select>
          </div>

          <div v-else>
            <label class="mb-2 block text-sm text-gray-600">Size / Option</label>
            <select
              v-if="sizeOptions.length > 1"
              v-model="selectedSize"
              class="h-12 w-full rounded-full border border-[#DED4CB] bg-[#FCFBF9] px-5 text-sm font-medium text-[#58514B] outline-none focus:ring-2 focus:ring-[#C9876C]"
            >
              <option v-for="opt in sizeOptions" :key="opt" :value="opt">{{ opt }}</option>
            </select>
            <div v-else class="flex h-12 items-center rounded-full border border-[#DED4CB] bg-[#FCFBF9] px-5 text-sm font-medium text-[#58514B]">
              {{ sizeOptions[0] || 'Standard' }}
            </div>
          </div>

          <div>
            <div class="mb-2 flex items-center justify-between gap-3">
              <label class="block text-sm text-gray-600">Quantity</label>
              <span v-if="minimumQuantity > 1" class="text-xs font-semibold text-[#A06F50]">
                Min. {{ minimumQuantity }} pcs
              </span>
            </div>
            <div class="flex items-center overflow-hidden rounded-full border border-[#DED4CB] bg-[#FCFBF9]">
              <button
                type="button"
                @click="decrementQuantity"
                :disabled="!canDecreaseQuantity"
                aria-label="Decrease quantity"
                class="flex h-12 w-12 items-center justify-center transition-colors"
                :class="canDecreaseQuantity
                  ? 'text-[#5C534D] hover:bg-[#F2ECE5]'
                  : 'cursor-not-allowed text-[#C9BCB1] opacity-60'"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                </svg>
              </button>
              <output class="flex min-w-0 flex-1 flex-col items-center justify-center px-2 py-2 text-center">
                <span class="text-base font-bold leading-5 text-[#4D4742]">{{ currentQuantity }}</span>
                <span class="text-[10px] font-semibold uppercase tracking-[0.14em] text-[#A99A8D]">pcs</span>
              </output>
              <button
                type="button"
                @click="incrementQuantity"
                aria-label="Increase quantity"
                class="m-1 flex h-10 w-10 items-center justify-center rounded-full bg-[#C9876C] text-white shadow-sm transition-colors hover:bg-[#B8765B]"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
              </button>
            </div>
            <p v-if="minimumQuantity > 1" class="mt-2 text-xs leading-5 text-[#8A7464]">
              Use the plus button to add pieces above the {{ minimumQuantity }} pcs minimum.
            </p>
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
          <form
            v-if="canUseNativeCatalogAdd"
            :action="catalogAddAction"
            method="POST"
            class="w-full"
            @submit="handleNativeCatalogAdd"
          >
            <input type="hidden" name="_token" :value="csrfToken" />
            <input type="hidden" name="return_to" :value="currentCustomerPath" />
            <input type="hidden" name="quantity" :value="catalogAddQuantity" />
            <input v-if="catalogAddOptionId" type="hidden" name="option_id" :value="catalogAddOptionId" />
            <input v-if="catalogAddSize" type="hidden" name="size" :value="catalogAddSize" />
            <input v-if="catalogAddFlavor" type="hidden" name="flavor" :value="catalogAddFlavor" />
            <button
              type="submit"
              :disabled="isAddButtonDisabled"
              class="w-full rounded-full py-4 font-semibold text-white shadow-md transition-colors"
              :class="isAddButtonDisabled
                ? 'cursor-not-allowed bg-[#CFA48F] opacity-80'
                : 'bg-[#C9876C] hover:bg-[#B8765B]'"
            >
              <span class="inline-flex items-center justify-center gap-2">
                <svg
                  v-if="isAddingFeaturedToCart"
                  class="h-4 w-4 animate-spin"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                {{ addToCartButtonLabel }}
              </span>
            </button>
          </form>
          <button
            v-else
            type="button"
            @click="addFeaturedToCart"
            :disabled="isAddButtonDisabled"
            class="w-full rounded-full py-4 font-semibold text-white shadow-md transition-colors"
            :class="isAddButtonDisabled
              ? 'cursor-not-allowed bg-[#CFA48F] opacity-80'
              : 'bg-[#C9876C] hover:bg-[#B8765B]'"
          >
            <span class="inline-flex items-center justify-center gap-2">
              <svg
                v-if="isAddingFeaturedToCart"
                class="h-4 w-4 animate-spin"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
              </svg>
              {{ addToCartButtonLabel }}
            </span>
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
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:gap-6">
              <div class="text-right">
                <p class="text-sm text-gray-600">Total ({{ selectedItems.length }} item{{ selectedItems.length === 1 ? '' : 's' }})</p>
                <p class="text-3xl font-bold text-gray-800">PHP {{ formatPrice(cartTotal) }}</p>
              </div>

              <button
                type="button"
                @click="proceedToCheckout"
                :disabled="selectedItems.length === 0"
                class="rounded-full bg-[#C9876C] px-8 py-4 font-semibold text-white shadow-md transition-colors hover:bg-[#B8765B] disabled:cursor-not-allowed disabled:bg-gray-300"
              >
                Proceed to checkout
              </button>
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
import api from '../../services/api';
import { useCartStore } from '../../services/cartStore';
import CartItem from '../shared/CartItem.vue';
import StarRating from '../shared/StarRating.vue';

const PREVIEW_PRODUCT_CACHE_TTL_MS = 60 * 1000;

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
  preview: {
    type: [String, Number],
    default: null,
  },
  option: {
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
  addCustomItem,
  updateCartItemQuantity,
  removeCartItem,
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
const previewProduct = ref(null);
const previewQuantity = ref(1);
const selectedSize = ref('');
const selectedOptionId = ref('');
const featuredImageFailed = ref(false);
const isAddingFeaturedToCart = ref(false);
const NEUTRAL_FALLBACK_IMAGE = '/images/logo/BAKERDAN%20LOGO.jpg';
const csrfToken = window.Laravel?.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const addError = ref(window.Laravel?.flash?.cartError || '');

const isPreviewMode = computed(() => Boolean(previewProduct.value));

const parseSizes = (sizesStr) => {
  if (!sizesStr) return [];
  return sizesStr.split('|').map((s) => s.trim()).filter(Boolean);
};

const normalizeOptions = (options = []) => {
  if (!Array.isArray(options)) return [];

  return options.map((option) => ({
    ...option,
    price: Number(option.price || 0),
    minimum_quantity: Number(option.minimum_quantity || 1),
  }));
};

const previewCacheKey = (productId) => `bakerdan.cartPreview.${productId}`;

const mapPreviewProduct = (p = {}) => ({
  id: p.id,
  product_id: p.product_id ?? p.productId ?? p.id,
  category: p.category || 'Bread',
  name: p.name ?? p.product_name ?? 'Bakerdan Product',
  description: p.description ?? '',
  price: Number(p.price ?? 0),
  image: p.image_url || p.image || p.category_fallback_image || p.fallbackImage || NEUTRAL_FALLBACK_IMAGE,
  fallbackImage: p.category_fallback_image || p.fallbackImage || NEUTRAL_FALLBACK_IMAGE,
  tag: p.is_active === false ? 'Inactive' : (p.tag || 'Available'),
  source: 'catalog',
  quantity: 1,
  sizesAvailable: p.sizes_available || p.sizesAvailable || '',
  flavorsAvailable: p.flavors_available || p.flavorsAvailable || '',
  options: normalizeOptions(p.options),
  averageRating: Number(p.average_rating || 0),
  reviewCount: Number(p.review_count || 0),
  minimumQuantity: Number(p.minimum_quantity || p.minimumQuantity || 1),
});

const applyPreviewProduct = (payload) => {
  previewProduct.value = mapPreviewProduct(payload);

  const firstOptionId = previewProduct.value.options[0]?.id || '';
  selectedOptionId.value = previewProduct.value.options.some((option) => option.id === props.option)
    ? props.option
    : firstOptionId;
  previewQuantity.value = Math.max(1, selectedOption.value?.minimum_quantity || previewProduct.value.minimumQuantity || 1);
  const sizes = parseSizes(previewProduct.value.sizesAvailable);
  selectedSize.value = selectedOption.value?.size || sizes[0] || 'Standard';
};

const loadCachedPreviewProduct = (previewId) => {
  try {
    const raw = window.sessionStorage.getItem(previewCacheKey(previewId));
    if (!raw) return false;

    const cached = JSON.parse(raw);
    if (!cached?.cachedAt || Date.now() - Number(cached.cachedAt) > PREVIEW_PRODUCT_CACHE_TTL_MS) {
      window.sessionStorage.removeItem(previewCacheKey(previewId));
      return false;
    }

    applyPreviewProduct(cached);
    return true;
  } catch (error) {
    return false;
  }
};

const loadPreviewProduct = async () => {
  const previewId = Number(props.preview);
  if (!previewId) {
    previewProduct.value = null;
    return;
  }

  const hasCachedPreview = loadCachedPreviewProduct(previewId);

  try {
    const response = await api.getProduct(previewId);
    const product = response.data?.data;
    if (product) {
      applyPreviewProduct(product);
    }
  } catch (error) {
    if (!hasCachedPreview) {
      previewProduct.value = null;
    }
  }
};

const addedItemId = computed(() => Number(props.added) || Number(lastAddedId.value) || null);
const checkoutOrderId = computed(() => {
  const routeOrderId = String(props.ordered || '').trim();
  const recentOrderId = String(lastCheckoutOrderId.value || '').trim();

  return routeOrderId || recentOrderId || null;
});

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

const featuredProduct = computed(() => {
  if (isPreviewMode.value) return previewProduct.value;
  return cartItems.value.find((item) => item.id === activeFeaturedId.value) || cartItems.value[0] || null;
});

const optionChoices = computed(() => featuredProduct.value?.options || []);

const selectedOption = computed(() => {
  if (!optionChoices.value.length) return null;

  return optionChoices.value.find((option) => option.id === selectedOptionId.value)
    || optionChoices.value[0];
});

const catalogAddProductId = computed(() => featuredProduct.value?.product_id || featuredProduct.value?.id || null);
const catalogAddAction = computed(() => (
  catalogAddProductId.value
    ? `/customer/cart/add/${encodeURIComponent(String(catalogAddProductId.value))}`
    : ''
));
const catalogAddQuantity = computed(() => (
  isPreviewMode.value
    ? Math.max(minimumQuantity.value, previewQuantity.value)
    : 1
));
const catalogAddOptionId = computed(() => selectedOption.value?.id || featuredProduct.value?.optionId || '');
const catalogAddSize = computed(() => selectedOption.value?.size || selectedSize.value || featuredProduct.value?.size || '');
const catalogAddFlavor = computed(() => selectedOption.value?.flavor || featuredProduct.value?.flavor || '');
const canUseNativeCatalogAdd = computed(() => (
  Boolean(featuredProduct.value)
    && featuredProduct.value.source !== 'custom'
    && Boolean(catalogAddAction.value)
));
const currentCustomerPath = computed(() => `${window.location.pathname}${window.location.search}`);

const minimumQuantity = computed(() => {
  return Number(selectedOption.value?.minimum_quantity || featuredProduct.value?.minimumQuantity || 1);
});

const currentQuantity = computed(() => {
  if (!featuredProduct.value) return minimumQuantity.value;

  return Number(isPreviewMode.value ? previewQuantity.value : featuredProduct.value.quantity) || minimumQuantity.value;
});

const canDecreaseQuantity = computed(() => currentQuantity.value > minimumQuantity.value);

const featuredFallbackImage = computed(() => {
  if (featuredProduct.value?.fallbackImage) return featuredProduct.value.fallbackImage;
  return NEUTRAL_FALLBACK_IMAGE;
});

const rawFeaturedImage = computed(() => {
  return selectedOption.value?.image_url || featuredProduct.value?.image || NEUTRAL_FALLBACK_IMAGE;
});

const featuredImage = computed(() => {
  return featuredImageFailed.value ? featuredFallbackImage.value : rawFeaturedImage.value;
});

const featuredDescription = computed(() => {
  if (!selectedOption.value) {
    return featuredProduct.value?.description || '';
  }

  return [
    featuredProduct.value?.description || '',
    selectedOption.value.size ? `Size: ${selectedOption.value.size}` : '',
    selectedOption.value.flavor ? `Flavor: ${selectedOption.value.flavor}` : '',
  ].filter(Boolean).join(' | ');
});

const featuredUnitPrice = computed(() => {
  return Number(selectedOption.value?.price ?? featuredProduct.value?.price ?? 0);
});

const sizeOptions = computed(() => {
  if (!featuredProduct.value) return [];
  if (selectedOption.value?.size) return [selectedOption.value.size];
  const raw = featuredProduct.value.sizesAvailable || featuredProduct.value.size || '';
  const sizes = parseSizes(raw);
  return sizes.length ? sizes : ['Standard'];
});

watch(featuredProduct, (prod) => {
  if (prod && !isPreviewMode.value) {
    const sizes = parseSizes(prod.sizesAvailable || prod.size || '');
    selectedSize.value = sizes[0] || prod.size || 'Standard';
  }
}, { immediate: true });

watch(selectedOption, (option) => {
  if (!option || !isPreviewMode.value) {
    return;
  }

  selectedSize.value = option.size || selectedSize.value || 'Standard';
  previewQuantity.value = Math.max(previewQuantity.value, Number(option.minimum_quantity || 1));
});

watch(rawFeaturedImage, () => {
  featuredImageFailed.value = false;
});

const handleFeaturedImageError = () => {
  if (featuredImage.value !== featuredFallbackImage.value) {
    featuredImageFailed.value = true;
  }
};

const isAddButtonDisabled = computed(() => (
  !featuredProduct.value || isAddingFeaturedToCart.value
));

const addToCartButtonLabel = computed(() => {
  if (isAddingFeaturedToCart.value) return 'Adding...';

  return 'Add to cart';
});

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

const redirectToAddedCart = (itemId) => {
  window.location.replace(`/customer/cart?added=${encodeURIComponent(String(itemId))}`);
};

const addCatalogItemForRedirect = async (productId, data = {}) => {
  try {
    const response = await api.addToCart(productId, {
      ...data,
      include_cart: false,
    });

    return response.data?.data?.item || null;
  } catch (error) {
    if (error.response?.status === 401) {
      window.location.href = '/login';
      return null;
    }

    throw error;
  }
};

const handleNativeCatalogAdd = () => {
  addError.value = '';
  isAddingFeaturedToCart.value = true;
};

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
  if (!featuredProduct.value) return;

  if (isPreviewMode.value) {
    previewQuantity.value = Math.max(minimumQuantity.value, previewQuantity.value + 1);
    return;
  }

  try {
    await updateCartItemQuantity(featuredProduct.value.id, featuredProduct.value.quantity + 1);
  } catch (error) {
    window.alert('Unable to update the cart quantity right now.');
  }
};

const decrementQuantity = async () => {
  if (!featuredProduct.value) return;

  if (isPreviewMode.value) {
    previewQuantity.value = Math.max(minimumQuantity.value, previewQuantity.value - 1);
    return;
  }

  try {
    await updateCartItemQuantity(featuredProduct.value.id, Math.max(minimumQuantity.value, featuredProduct.value.quantity - 1));
  } catch (error) {
    window.alert('Unable to update the cart quantity right now.');
  }
};

const addFeaturedToCart = async () => {
  if (isAddButtonDisabled.value) {
    return;
  }

  isAddingFeaturedToCart.value = true;

  try {
    if (featuredProduct.value.source === 'custom') {
      const addedItem = await addCustomItem({
        size: selectedSize.value || featuredProduct.value.size,
        quantity: isPreviewMode.value ? previewQuantity.value : 1,
        flavor: featuredProduct.value.flavor,
        designDescription: featuredProduct.value.designDescription,
        dedicationMessage: featuredProduct.value.dedicationMessage,
        imageUrl: featuredProduct.value.image,
        productName: featuredProduct.value.productName || featuredProduct.value.name,
        basePrice: featuredProduct.value.basePrice || featuredProduct.value.price,
      });

      if (addedItem?.id) {
        redirectToAddedCart(addedItem.id);
      }

      return;
    }

    const productId = featuredProduct.value.product_id || featuredProduct.value.id;
    const addedItem = await addCatalogItemForRedirect(productId, {
      quantity: isPreviewMode.value ? Math.max(minimumQuantity.value, previewQuantity.value) : 1,
      option_id: selectedOption.value?.id || null,
      size: selectedOption.value?.size || selectedSize.value || featuredProduct.value.size,
      flavor: selectedOption.value?.flavor || featuredProduct.value.flavor,
    });

    if (addedItem?.id) {
      redirectToAddedCart(addedItem.id);
    }
  } catch (error) {
    addError.value = error.response?.data?.message || 'Unable to add this item to the cart right now.';
  } finally {
    isAddingFeaturedToCart.value = false;
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

const proceedToCheckout = () => {
  if (!selectedItems.value.length) {
    return;
  }

  push(`/customer/checkout?items=${selectedItems.value.join(',')}`);
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
  loadPreviewProduct();
});

</script>
