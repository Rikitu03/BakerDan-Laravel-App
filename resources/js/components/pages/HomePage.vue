<template>
  <div class="px-6 py-7 md:px-8 xl:px-10">
    <section class="rounded-[32px] border border-[#E7DED7] bg-white px-6 py-7 shadow-[0_18px_40px_-34px_rgba(118,79,49,0.35)] md:px-8">
      <div class="mx-auto max-w-3xl text-center">
        <h1 class="text-4xl font-black tracking-tight text-[#44413E]" style="font-family: 'Urbanist', sans-serif;">
          Welcome to Bakerdan!
        </h1>
        <p class="mt-2 text-sm text-[#766C65]">
          Find the best bread and pastries just for you!
        </p>
      </div>

      <div class="mx-auto mt-7 max-w-5xl">
        <div class="rounded-[22px] border border-[#E6DDD5] bg-[#FCFBF9] p-2.5 shadow-[0_12px_30px_-26px_rgba(118,79,49,0.35)]">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <label class="block min-w-0 flex-1">
              <span class="sr-only">Ask the bakery AI</span>
              <input
                v-model="aiPrompt"
                type="text"
                placeholder="Describe what you like"
                class="h-11 w-full bg-transparent px-4 text-sm text-[#4F4944] outline-none"
                @keydown.enter.prevent="sendAiPrompt"
              />
            </label>

            <div class="flex items-center justify-end gap-2">
              <input
                ref="attachmentInput"
                type="file"
                class="hidden"
                accept="image/*,.pdf,.txt"
                @change="handleAttachmentChange"
              />

              <button
                type="button"
                @click="openAttachmentPicker"
                class="flex h-10 w-10 items-center justify-center rounded-full border border-[#E6DDD5] bg-white text-[#7D746D] transition hover:border-[#D9B89B] hover:text-[#B06E43]"
                aria-label="Attach a file"
              >
                <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.5 6.5l-7.78 7.78a3 3 0 104.24 4.24l8.49-8.48a5 5 0 10-7.07-7.07L5.2 12.15" />
                </svg>
              </button>

              <button
                type="button"
                @click="sendAiPrompt"
                :disabled="aiLoading"
                class="flex h-10 w-10 items-center justify-center rounded-full bg-[#4B4643] text-white shadow-md transition hover:bg-[#383431]"
                :class="aiLoading ? 'cursor-not-allowed opacity-70' : ''"
                aria-label="Send AI prompt"
              >
                <svg v-if="!aiLoading" class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h10m0 0l-4-4m4 4l-4 4" />
                </svg>
                <svg v-else class="h-4.5 w-4.5 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
              </button>
            </div>
          </div>

          <div v-if="attachmentName" class="px-4 pb-1 pt-0.5">
            <span class="inline-flex items-center rounded-full bg-[#F6E8DC] px-3 py-1 text-xs font-semibold text-[#B06E43]">
              Attached: {{ attachmentName }}
            </span>
          </div>

          <div v-if="aiResponse || aiError" class="px-4 pb-3 pt-2 text-left">
            <p
              class="rounded-[18px] px-4 py-3 text-sm leading-6"
              :class="aiError ? 'border border-[#F0DCCC] bg-[#FFF4EB] text-[#8E5632]' : 'border border-[#E1D7CD] bg-white text-[#554C45]'"
            >
              {{ aiError || aiResponse }}
            </p>
          </div>
        </div>
      </div>
    </section>

    <section class="mt-6 rounded-[32px] border border-[#E7DED7] bg-white px-5 py-5 shadow-[0_18px_40px_-34px_rgba(118,79,49,0.25)] md:px-6">
      <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div class="flex flex-1 flex-col gap-3 md:flex-row md:items-center">
          <label class="relative block min-w-0 flex-1">
            <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-[#B7AAA0]">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </span>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search"
              class="h-12 w-full rounded-full border border-[#E6DDD5] bg-white pr-4 pl-11 text-sm text-[#4F4944] outline-none transition focus:border-transparent focus:ring-2 focus:ring-[#C9876C]"
            />
          </label>

          <button
            type="button"
            @click="submitSearch"
            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#4B4643] text-white shadow-md transition hover:bg-[#383431]"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-5-5l5 5-5 5" />
            </svg>
          </button>
        </div>

        <div class="flex flex-wrap items-center gap-3 text-sm text-[#6F665F]">
          <span class="font-medium">Sort by</span>
          <button
            v-for="mode in sortModes"
            :key="mode.id"
            type="button"
            @click="sortBy = mode.id"
            class="rounded-full px-4 py-2 text-sm font-semibold transition-all"
            :class="sortBy === mode.id
              ? 'bg-[#B47A52] text-white shadow-md'
              : 'bg-[#F7F2EC] text-[#6E6259] hover:bg-[#EFE2D6]'"
          >
            {{ mode.label }}
          </button>
        </div>
      </div>
    </section>

    <section class="relative mt-6 min-h-[420px]" aria-live="polite" :aria-busy="isCatalogLoading">
      <div
        v-if="isCatalogLoading && paginatedProducts.length"
        class="absolute inset-0 z-10 flex items-start justify-center rounded-[28px] bg-[#FBF8F4]/70 pt-16 backdrop-blur-[2px]"
      >
        <div class="flex items-center gap-3 rounded-full border border-[#E4D6CB] bg-white px-5 py-3 text-sm font-semibold text-[#7A5C48] shadow-[0_18px_42px_-28px_rgba(118,79,49,0.45)]">
          <svg class="h-5 w-5 animate-spin text-[#C9876C]" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-80" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
          </svg>
          Loading products
        </div>
      </div>

      <div
        v-if="paginatedProducts.length"
        class="grid grid-cols-1 gap-5 transition duration-150 md:grid-cols-2 2xl:grid-cols-3"
        :class="isCatalogLoading ? 'pointer-events-none opacity-45' : 'opacity-100'"
      >
        <ProductCard
          v-for="(product, index) in paginatedProducts"
          :key="product.id"
          :product="product"
          :eager="currentPage === 1 && index < 3"
          @add-to-cart="handleAddToCart"
          @toggle-like="handleToggleLike"
        />
      </div>

      <div v-else-if="isCatalogLoading" class="flex min-h-[420px] items-center justify-center rounded-[28px] border border-dashed border-[#E1D4C8] bg-[#FCF8F4] px-6 py-12 text-center text-[#756A63]">
        <div>
          <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-white shadow-[0_18px_34px_-24px_rgba(118,79,49,0.55)]">
            <svg class="h-7 w-7 animate-spin text-[#C9876C]" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-80" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
          </div>
          <p class="mt-4 text-sm font-semibold text-[#695F57]">Loading products</p>
        </div>
      </div>

      <div v-else class="mt-6 rounded-[28px] border border-dashed border-[#E1D4C8] bg-[#FCF8F4] px-6 py-12 text-center text-[#756A63]">
        <h2 class="text-2xl font-bold text-[#4C4641]" style="font-family: 'Urbanist', sans-serif;">{{ emptyStateTitle }}</h2>
        <p class="mt-2 text-sm">{{ emptyStateMessage }}</p>
        <button
          v-if="catalogError"
          type="button"
          @click="loadProducts({ force: true })"
          class="mt-5 rounded-full bg-[#C9876C] px-5 py-2.5 text-sm font-semibold text-white shadow-md transition hover:bg-[#B8765B]"
        >
          Retry
        </button>
      </div>
    </section>

    <nav v-if="totalPages > 1" class="mt-10 flex items-center justify-center gap-2" aria-label="Product pagination">
      <button
        type="button"
        :disabled="currentPage <= 1"
        class="flex h-10 w-10 items-center justify-center rounded-full border border-[#E1D6CC] bg-white text-[#71675F] transition hover:bg-[#F7F2EC] disabled:cursor-not-allowed disabled:opacity-40"
        @click="currentPage = Math.max(1, currentPage - 1)"
      >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>

      <template v-for="token in paginationTokens" :key="token.key">
        <button
          v-if="token.type === 'page'"
          type="button"
          class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-semibold transition"
          :class="currentPage === token.value
            ? 'border border-[#B47A52] bg-[#B47A52] text-white shadow-md'
            : 'border border-[#E1D6CC] bg-white text-[#71675F] hover:bg-[#F7F2EC]'"
          @click="currentPage = token.value"
        >
          {{ token.value }}
        </button>

        <span
          v-else
          class="flex h-10 w-10 items-center justify-center text-sm font-semibold text-[#8A8078]"
          aria-hidden="true"
        >
          ...
        </span>
      </template>

      <button
        type="button"
        :disabled="currentPage >= totalPages"
        class="flex h-10 w-10 items-center justify-center rounded-full border border-[#E1D6CC] bg-white text-[#71675F] transition hover:bg-[#F7F2EC] disabled:cursor-not-allowed disabled:opacity-40"
        @click="currentPage = Math.min(totalPages, currentPage + 1)"
      >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>
    </nav>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import api from '../../services/api';
import { preloadImages } from '../../services/imageCache';
import ProductCard from '../shared/ProductCard.vue';

const PRODUCT_CACHE_TTL = 45 * 1000;
const PRODUCT_PAGE_SIZE = 10;
const SEARCH_DEBOUNCE_DELAY = 250;

const cachedCatalogPages = new Map();
let productLoadSequence = 0;
let productAbortController = null;

const props = defineProps({
  activeCategory: {
    type: String,
    default: 'All',
  },
});

const attachmentInput = ref(null);
const attachmentName = ref('');
const aiError = ref('');
const aiHistory = ref([]);
const aiLoading = ref(false);
const aiPrompt = ref('');
const aiResponse = ref('');
const catalogError = ref('');
const searchQuery = ref('');
const debouncedSearchQuery = ref('');
const sortBy = ref('popularity');
const currentPage = ref(1);
const isCatalogLoading = ref(true);
const perPage = PRODUCT_PAGE_SIZE;
let searchDebounceHandle = null;

const sortModes = [
  { id: 'popularity', label: 'Popularity' },
  { id: 'price', label: 'Price' },
  { id: 'low-to-high', label: 'Low to High' },
];

const products = ref([]);
const catalogPagination = ref({
  total: 0,
  per_page: PRODUCT_PAGE_SIZE,
  current_page: 1,
  last_page: 1,
  from: null,
  to: null,
});
const NEUTRAL_FALLBACK_IMAGE = '/images/logo/BAKERDAN%20LOGO.jpg';

const categoryFallbackImage = (category) => {
  return NEUTRAL_FALLBACK_IMAGE;
};

const versionedImage = (image, cacheKey = '') => {
  if (!image || !cacheKey || image.startsWith('data:') || image.startsWith('blob:')) {
    return image;
  }

  if (image.startsWith('http://') || image.startsWith('https://')) {
    return image;
  }

  return `${image}${image.includes('?') ? '&' : '?'}v=${encodeURIComponent(cacheKey)}`;
};

const normalizeImage = (image, category, cacheKey = '') => versionedImage(image || categoryFallbackImage(category), cacheKey);

const normalizeOptions = (options = [], category = 'Bread', productImage = '', cacheKey = '') => {
  if (!Array.isArray(options)) {
    return [];
  }

  return options.map((option) => ({
    ...option,
    price: Number(option.price || 0),
    minimum_quantity: Number(option.minimum_quantity || 1),
    image_url: option.image_url
      ? normalizeImage(option.image_url, category, cacheKey)
      : (productImage || normalizeImage(categoryFallbackImage(category), category, cacheKey)),
  }));
};

const mapProduct = (product) => {
  const category = product.category ?? 'Bread';
  const cacheKey = product.image_cache_key || product.updated_at || product.id || '';
  const image = normalizeImage(product.image_url || product.image || product.category_fallback_image, category, cacheKey);
  const options = normalizeOptions(product.options, category, image, cacheKey);

  return {
    id: product.id ?? product.product_id,
    category,
    name: product.name ?? product.product_name ?? 'Untitled Product',
    description: product.description ?? '',
    price: Number(product.price ?? 0),
    priceLabel: product.price_label ?? '',
    image,
    fallbackImage: normalizeImage(product.category_fallback_image, category, cacheKey),
    liked: false,
    tag: product.order_mode === 'custom' ? 'Custom Order' : (product.is_active ? 'Available' : 'Inactive'),
    rating: '5.0/5',
    orderMode: product.order_mode || 'catalog',
    orderingGuide: product.ordering_guide || null,
    flavorsAvailable: product.flavors_available || '',
    sizesAvailable: product.sizes_available || '',
    options,
    minimumQuantity: Number(product.minimum_quantity || 1),
    sourceProductIds: [product.id ?? product.product_id],
    searchableText: [
      product.name ?? product.product_name ?? '',
      product.description ?? '',
      category,
      product.order_mode === 'custom' ? 'Custom Order' : (product.is_active ? 'Available' : 'Inactive'),
      product.flavors_available || '',
      product.sizes_available || '',
      options.map((option) => `${option.label} ${option.flavor || ''} ${option.size || ''} ${option.price_label || ''}`).join(' '),
    ]
      .filter(Boolean)
      .join(' ')
      .toLowerCase(),
  };
};

const groupProducts = (items) => {
  const grouped = new Map();
  const standalone = [];

  for (const product of items) {
    const dashIndex = product.name.indexOf(' - ');
    if (dashIndex === -1) {
      standalone.push(product);
      continue;
    }

    const baseName = product.name.substring(0, dashIndex).trim();
    const variantLabel = product.name.substring(dashIndex + 3).trim();

    if (!grouped.has(baseName)) {
      grouped.set(baseName, {
        baseName,
        firstProduct: product,
        variants: [],
      });
    }

    grouped.get(baseName).variants.push({
      label: variantLabel,
      id: product.id,
      image: product.image,
      price: product.price,
      priceLabel: product.priceLabel,
      description: product.description,
      liked: product.liked,
    });
  }

  const result = standalone.map((product) => ({
    ...product,
    sourceProductIds: product.sourceProductIds || [product.id],
  }));

  for (const [, group] of grouped) {
    if (group.variants.length === 1) {
      result.push({
        ...group.firstProduct,
        sourceProductIds: group.firstProduct.sourceProductIds || [group.firstProduct.id],
      });
    } else {
      const first = group.variants[0];
      const prices = group.variants.map((v) => v.price);
      const minP = Math.min(...prices);
      const maxP = Math.max(...prices);
      const priceRange = minP === maxP
        ? `PHP ${minP.toFixed(2)}`
        : `PHP ${minP.toFixed(2)} - ${maxP.toFixed(2)}`;

      result.push({
        id: `group-${group.firstProduct.id}`,
        category: group.firstProduct.category,
        name: group.baseName,
        description: first.description,
        price: minP,
        priceLabel: priceRange,
        image: first.image,
        liked: group.variants.some((variant) => variant.liked),
        tag: group.firstProduct.tag,
        rating: group.firstProduct.rating,
        flavorsAvailable: group.firstProduct.flavorsAvailable,
        sizesAvailable: group.firstProduct.sizesAvailable,
        sourceProductIds: group.variants.map((variant) => variant.id),
        searchableText: [
          group.baseName,
          group.firstProduct.description,
          group.firstProduct.category,
          group.firstProduct.tag,
          group.firstProduct.flavorsAvailable,
          group.firstProduct.sizesAvailable,
          group.variants.map((variant) => `${variant.label} ${variant.description}`).join(' '),
        ]
          .filter(Boolean)
          .join(' ')
          .toLowerCase(),
        variants: group.variants,
      });
    }
  }

  return result;
};

const collectCatalogImageSources = (items) => {
  return [...new Set(
    (items || []).flatMap((product) => [
      product.image,
      NEUTRAL_FALLBACK_IMAGE,
      ...(product.options || []).map((option) => option.image_url),
      ...(product.variants || []).map((variant) => variant.image),
    ]),
  )].filter(Boolean);
};

const productCategoryParam = () => {
  if (props.activeCategory === 'All') {
    return null;
  }

  return props.activeCategory;
};

const shouldLoadCatalog = () => props.activeCategory !== 'Customize Order';

const runWhenBrowserIdle = (callback, timeout = 900) => {
  if (typeof window === 'undefined') {
    return;
  }

  if ('requestIdleCallback' in window) {
    window.requestIdleCallback(callback, { timeout });
    return;
  }

  window.setTimeout(callback, 120);
};

const productRequestParams = () => {
  const params = {
    page: currentPage.value,
    per_page: perPage,
    sort: sortBy.value,
  };
  const category = productCategoryParam();
  const search = debouncedSearchQuery.value.trim();

  if (category) {
    params.category = category;
  }

  if (search) {
    params.search = search;
  }

  return params;
};

const productCacheKey = (params) => JSON.stringify(
  Object.keys(params)
    .sort()
    .reduce((payload, key) => {
      payload[key] = params[key];
      return payload;
    }, {}),
);

const normalizePagination = (pagination = {}) => ({
  total: Number(pagination.total || 0),
  per_page: Number(pagination.per_page || perPage),
  current_page: Number(pagination.current_page || currentPage.value),
  last_page: Math.max(1, Number(pagination.last_page || 1)),
  from: pagination.from ?? null,
  to: pagination.to ?? null,
});

const cacheProductPayload = (cacheKey, data, pagination) => {
  cachedCatalogPages.set(cacheKey, {
    payload: {
      data,
      pagination: normalizePagination(pagination),
    },
    expiresAt: Date.now() + PRODUCT_CACHE_TTL,
  });
};

const applyProductPayload = (payload) => {
  products.value = payload.data.map(mapProduct);
  catalogPagination.value = normalizePagination(payload.pagination);
};

const prefetchProductPage = (page) => {
  if (!shouldLoadCatalog() || page < 1 || page > totalPages.value) {
    return;
  }

  const params = {
    ...productRequestParams(),
    page,
  };
  const cacheKey = productCacheKey(params);
  const cached = cachedCatalogPages.get(cacheKey);

  if (cached?.payload && Date.now() < cached.expiresAt) {
    return;
  }

  runWhenBrowserIdle(async () => {
    const latestCached = cachedCatalogPages.get(cacheKey);

    if (latestCached?.payload && Date.now() < latestCached.expiresAt) {
      return;
    }

    try {
      const response = await api.getProducts(params);
      cacheProductPayload(
        cacheKey,
        Array.isArray(response.data?.data) ? response.data.data : [],
        response.data?.pagination,
      );
    } catch (error) {
      // Adjacent-page prefetch is an optimization only.
    }
  });
};

const prefetchAdjacentProductPages = () => {
  prefetchProductPage(currentPage.value + 1);
  prefetchProductPage(currentPage.value - 1);
};

const resetAndLoadProducts = () => {
  if (currentPage.value !== 1) {
    currentPage.value = 1;
    return;
  }

  loadProducts();
};

const loadProducts = async ({ force = false } = {}) => {
  if (!shouldLoadCatalog()) {
    products.value = [];
    catalogPagination.value = normalizePagination({ total: 0, per_page: perPage, current_page: 1, last_page: 1 });
    isCatalogLoading.value = false;
    return;
  }

  const params = productRequestParams();
  const cacheKey = productCacheKey(params);
  const cached = cachedCatalogPages.get(cacheKey);
  const requestId = ++productLoadSequence;
  const now = Date.now();
  const hasFreshCache = Boolean(cached?.payload && now < cached.expiresAt && !force);

  catalogError.value = '';

  if (hasFreshCache) {
    applyProductPayload(cached.payload);
    isCatalogLoading.value = false;
    prefetchAdjacentProductPages();
    return;
  }

  isCatalogLoading.value = true;

  if (productAbortController) {
    productAbortController.abort();
  }
  productAbortController = new AbortController();

  try {
    const response = await api.getProducts(params, { signal: productAbortController.signal });
    cacheProductPayload(
      cacheKey,
      Array.isArray(response.data?.data) ? response.data.data : [],
      response.data?.pagination,
    );
    const payload = cachedCatalogPages.get(cacheKey)?.payload || { data: [], pagination: normalizePagination() };

    if (requestId !== productLoadSequence) {
      return;
    }

    applyProductPayload(payload);
    prefetchAdjacentProductPages();
  } catch (error) {
    if (error.code === 'ERR_CANCELED' || error.name === 'CanceledError' || error.name === 'AbortError') {
      return;
    }

    if (requestId === productLoadSequence) {
      catalogError.value = error.response?.data?.message || 'Unable to load the catalog right now.';
      products.value = [];
      catalogPagination.value = normalizePagination();
    }
  } finally {
    if (requestId === productLoadSequence) {
      isCatalogLoading.value = false;
    }

    if (requestId === productLoadSequence || productAbortController?.signal.aborted) {
      productAbortController = null;
    }
  }
};

onMounted(() => {
  loadProducts();
});

onBeforeUnmount(() => {
  if (searchDebounceHandle) {
    clearTimeout(searchDebounceHandle);
  }

  if (productAbortController) {
    productAbortController.abort();
  }
});

watch(
  searchQuery,
  (value) => {
    if (searchDebounceHandle) {
      clearTimeout(searchDebounceHandle);
    }

    searchDebounceHandle = setTimeout(() => {
      debouncedSearchQuery.value = value;
    }, SEARCH_DEBOUNCE_DELAY);
  },
  { immediate: true },
);

watch(
  [() => props.activeCategory, sortBy, debouncedSearchQuery],
  resetAndLoadProducts,
);

watch(
  currentPage,
  () => {
    loadProducts();
  },
);

const groupedProducts = computed(() => {
  return groupProducts(products.value);
});

const filteredProducts = computed(() => {
  return groupedProducts.value;
});

const emptyStateTitle = computed(() => {
  if (catalogError.value) return 'Unable to load products';
  if (debouncedSearchQuery.value.trim() || props.activeCategory !== 'All') return 'No matching products';

  return 'No products available';
});

const emptyStateMessage = computed(() => {
  if (catalogError.value) return catalogError.value;
  if (debouncedSearchQuery.value.trim()) return 'Try another keyword, flavor, or option name.';
  if (props.activeCategory !== 'All') return 'This category has no active products right now.';

  return 'The catalog is waiting for products from the database.';
});

const totalPages = computed(() => Math.max(1, Number(catalogPagination.value.last_page || 1)));

const paginatedProducts = computed(() => {
  return filteredProducts.value;
});

watch(
  paginatedProducts,
  (visibleProducts) => {
    preloadImages(collectCatalogImageSources(visibleProducts), {
      concurrency: 5,
      limit: 24,
    });
  },
  { immediate: true },
);

const paginationTokens = computed(() => {
  if (totalPages.value <= 7) {
    return Array.from({ length: totalPages.value }, (_, index) => ({
      type: 'page',
      value: index + 1,
      key: `page-${index + 1}`,
    }));
  }

  const pages = new Set([1, totalPages.value, currentPage.value]);

  if (currentPage.value <= 3) {
    pages.add(2);
    pages.add(3);
  }

  if (currentPage.value >= totalPages.value - 2) {
    pages.add(totalPages.value - 1);
    pages.add(totalPages.value - 2);
  }

  if (currentPage.value > 2) {
    pages.add(currentPage.value - 1);
  }

  if (currentPage.value < totalPages.value - 1) {
    pages.add(currentPage.value + 1);
  }

  const sortedPages = [...pages]
    .filter((page) => page >= 1 && page <= totalPages.value)
    .sort((a, b) => a - b);

  const tokens = [];
  let previousPage = null;

  for (const page of sortedPages) {
    if (previousPage !== null && page - previousPage > 1) {
      tokens.push({
        type: 'ellipsis',
        key: `ellipsis-${previousPage}-${page}`,
      });
    }

    tokens.push({
      type: 'page',
      value: page,
      key: `page-${page}`,
    });

    previousPage = page;
  }

  return tokens;
});

const productLookup = computed(() => {
  return new Map(products.value.map((product) => [product.id, product]));
});

const displayProductLookup = computed(() => {
  return new Map(filteredProducts.value.map((product) => [product.id, product]));
});

const openAttachmentPicker = () => {
  attachmentInput.value?.click();
};

const handleAttachmentChange = (event) => {
  attachmentName.value = event.target.files?.[0]?.name || '';
};

const sendAiPrompt = async () => {
  const prompt = aiPrompt.value.trim();

  if (!prompt && !attachmentName.value) {
    return;
  }

  const message = attachmentName.value
    ? [prompt || 'Help me choose a BakerDan product for this attachment.', `Attached file name: ${attachmentName.value}`].join('\n')
    : prompt;

  aiError.value = '';
  aiResponse.value = '';
  aiLoading.value = true;

  try {
    const response = await api.chat({
      message,
      history: aiHistory.value,
    });
    const reply = response.data?.reply || 'I could not generate a response right now.';

    aiResponse.value = reply;
    aiHistory.value = [
      ...aiHistory.value,
      { role: 'user', text: message },
      { role: 'model', text: reply },
    ].slice(-12);

    if (prompt) {
      searchQuery.value = prompt;
      submitSearch();
    }
  } catch (error) {
    aiError.value = error.response?.data?.message || 'Unable to reach the BakerDan assistant right now.';
  } finally {
    aiLoading.value = false;
  }
};

const submitSearch = () => {
  if (searchDebounceHandle) {
    clearTimeout(searchDebounceHandle);
  }

  debouncedSearchQuery.value = searchQuery.value;
  currentPage.value = 1;
};

const handleAddToCart = (payload) => {
  const productId = typeof payload === 'object' ? payload.productId : payload;
  const optionId = typeof payload === 'object' ? payload.optionId : null;
  const query = new URLSearchParams({ preview: String(productId) });

  if (optionId) {
    query.set('option', optionId);
  }

  const product = productLookup.value.get(productId) || displayProductLookup.value.get(productId);

  if (product) {
    try {
      window.sessionStorage.setItem(
        `bakerdan.cartPreview.${productId}`,
        JSON.stringify({
          ...product,
          cachedAt: Date.now(),
        }),
      );
    } catch (error) {
      // Session storage is only a speed hint; navigation should continue if it is unavailable.
    }
  }

  window.location.assign(`/customer/cart?${query.toString()}`);
};

const handleToggleLike = (productId) => {
  const displayProduct = displayProductLookup.value.get(productId);
  const sourceProductIds = displayProduct?.sourceProductIds?.length
    ? displayProduct.sourceProductIds
    : [productId];
  const nextLikedState = sourceProductIds.some((sourceProductId) => !productLookup.value.get(sourceProductId)?.liked);

  for (const sourceProductId of sourceProductIds) {
    const product = productLookup.value.get(sourceProductId);

    if (product) {
      product.liked = nextLikedState;
    }
  }
};

</script>
