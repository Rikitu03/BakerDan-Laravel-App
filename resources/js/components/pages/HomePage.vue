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

    <section class="mt-6">
      <div v-if="paginatedProducts.length" class="grid grid-cols-1 gap-5 md:grid-cols-2 2xl:grid-cols-3">
        <ProductCard
          v-for="product in paginatedProducts"
          :key="product.id"
          :product="product"
          @add-to-cart="handleAddToCart"
          @toggle-like="handleToggleLike"
        />
      </div>

      <div v-else class="mt-6 rounded-[28px] border border-dashed border-[#E1D4C8] bg-[#FCF8F4] px-6 py-12 text-center text-[#756A63]">
        <h2 class="text-2xl font-bold text-[#4C4641]" style="font-family: 'Urbanist', sans-serif;">No products available</h2>
        <p class="mt-2 text-sm">The catalog is waiting for products from the database.</p>
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
import { useSpaRouter } from '../../router';
import api from '../../services/api';
import ProductCard from '../shared/ProductCard.vue';

const PRODUCT_CACHE_TTL = 5 * 60 * 1000;
const SEARCH_DEBOUNCE_DELAY = 250;

let cachedCatalogPayload = null;
let cachedCatalogExpiresAt = 0;

const props = defineProps({
  activeCategory: {
    type: String,
    default: 'All',
  },
});

const { push } = useSpaRouter();

const attachmentInput = ref(null);
const attachmentName = ref('');
const aiError = ref('');
const aiHistory = ref([]);
const aiLoading = ref(false);
const aiPrompt = ref('');
const aiResponse = ref('');
const searchQuery = ref('');
const debouncedSearchQuery = ref('');
const sortBy = ref('popularity');
const currentPage = ref(1);
const perPage = 10;
let searchDebounceHandle = null;

const sortModes = [
  { id: 'popularity', label: 'Popularity' },
  { id: 'price', label: 'Price' },
  { id: 'low-to-high', label: 'Low to High' },
];

const products = ref([]);

const mapProduct = (product) => ({
  id: product.id ?? product.product_id,
  category: product.category ?? 'Bread',
  name: product.name ?? product.product_name ?? 'Untitled Product',
  description: product.description ?? '',
  price: Number(product.price ?? 0),
  priceLabel: product.price_label ?? '',
  image: product.image_url || product.image || '/images/bakerdan/Bread.png',
  liked: false,
  tag: product.order_mode === 'custom' ? 'Custom Order' : (product.is_active ? 'Available' : 'Inactive'),
  rating: '5.0/5',
  orderMode: product.order_mode || 'catalog',
  orderingGuide: product.ordering_guide || null,
  flavorsAvailable: product.flavors_available || '',
  sizesAvailable: product.sizes_available || '',
  sourceProductIds: [product.id ?? product.product_id],
  searchableText: [
    product.name ?? product.product_name ?? '',
    product.description ?? '',
    product.category ?? '',
    product.order_mode === 'custom' ? 'Custom Order' : (product.is_active ? 'Available' : 'Inactive'),
    product.flavors_available || '',
    product.sizes_available || '',
  ]
    .filter(Boolean)
    .join(' ')
    .toLowerCase(),
});

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

const matchesSidebarCategory = (product) => {
  switch (props.activeCategory) {
    case 'All':
      return true;
    case 'Bread':
      return product.category === 'Bread';
    case 'Pastries':
      return product.category === 'Pastries';
    case 'Tarts':
      return product.category === 'Tarts';
    case 'Brazos and Cakes':
      return product.category === 'Brazos and Cakes';
    case 'Customize Order':
      return false;
    default:
      return true;
  }
};

const loadProducts = async () => {
  try {
    const now = Date.now();
    if (!Array.isArray(cachedCatalogPayload) || now >= cachedCatalogExpiresAt) {
      const response = await api.getProducts();
      cachedCatalogPayload = Array.isArray(response.data?.data) ? response.data.data : [];
      cachedCatalogExpiresAt = Date.now() + PRODUCT_CACHE_TTL;
    }

    products.value = cachedCatalogPayload.map(mapProduct);
  } catch (error) {
    products.value = [];
  }
};

onMounted(() => {
  loadProducts();
});

onBeforeUnmount(() => {
  if (searchDebounceHandle) {
    clearTimeout(searchDebounceHandle);
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
  () => {
    currentPage.value = 1;
  },
);

const groupedProducts = computed(() => {
  let result = groupProducts(products.value.filter(matchesSidebarCategory));

  if (sortBy.value === 'low-to-high') {
    result = [...result].sort((a, b) => a.price - b.price);
  } else if (sortBy.value === 'price') {
    result = [...result].sort((a, b) => b.price - a.price);
  } else {
    result = [...result].sort((a, b) => Number(b.liked) - Number(a.liked));
  }

  return result;
});

const filteredProducts = computed(() => {
  const query = debouncedSearchQuery.value.trim().toLowerCase();

  if (!query) {
    return groupedProducts.value;
  }

  return groupedProducts.value.filter((product) => product.searchableText?.includes(query));
});

const totalPages = computed(() => Math.max(1, Math.ceil(filteredProducts.value.length / perPage)));

const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return filteredProducts.value.slice(start, start + perPage);
});

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

const handleAddToCart = (productId) => {
  push(`/customer/cart?preview=${productId}`);
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
