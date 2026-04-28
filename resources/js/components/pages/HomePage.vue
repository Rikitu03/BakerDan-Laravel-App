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
                class="flex h-10 w-10 items-center justify-center rounded-full bg-[#4B4643] text-white shadow-md transition hover:bg-[#383431]"
                aria-label="Send AI prompt"
              >
                <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h10m0 0l-4-4m4 4l-4 4" />
                </svg>
              </button>
            </div>
          </div>

          <div v-if="attachmentName" class="px-4 pb-1 pt-0.5">
            <span class="inline-flex items-center rounded-full bg-[#F6E8DC] px-3 py-1 text-xs font-semibold text-[#B06E43]">
              Attached: {{ attachmentName }}
            </span>
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

      <div v-else class="rounded-[28px] border border-dashed border-[#E1D4C8] bg-[#FCF8F4] px-6 py-12 text-center text-[#756A63]">
        <h2 class="text-2xl font-bold text-[#4C4641]" style="font-family: 'Urbanist', sans-serif;">No products available</h2>
        <p class="mt-2 text-sm">The catalog is waiting for products from the database.</p>
      </div>
    </section>

    <nav v-if="filteredProducts.length" class="mt-10 flex items-center justify-center gap-3" aria-label="Product pagination">
      <button
        type="button"
        class="flex h-10 w-10 items-center justify-center rounded-full border border-[#E1D6CC] bg-white text-[#71675F]"
      >
        1
      </button>
      <button
        v-for="page in totalPages - 1"
        :key="page + 1"
        type="button"
        class="text-sm font-semibold text-[#B7A59A]"
      >
        {{ page + 1 }}
      </button>
    </nav>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useSpaRouter } from '../../router';
import api from '../../services/api';
import { useCartStore } from '../../services/cartStore';
import ProductCard from '../shared/ProductCard.vue';

const props = defineProps({
  activeCategory: {
    type: String,
    default: 'Bread',
  },
});

const { push } = useSpaRouter();
const { addCatalogItem } = useCartStore();

const attachmentInput = ref(null);
const attachmentName = ref('');
const aiPrompt = ref('');
const searchQuery = ref('');
const sortBy = ref('popularity');
const currentPage = ref(1);
const perPage = 6;

const sortModes = [
  { id: 'popularity', label: 'Popularity' },
  { id: 'price', label: 'Price' },
  { id: 'low-to-high', label: 'Low to High' },
];

const products = ref([]);

watch(
  () => props.activeCategory,
  () => {
    currentPage.value = 1;
  },
);

const mapProduct = (product) => ({
  id: product.id ?? product.product_id,
  category: product.category ?? 'Bread',
  name: product.name ?? product.product_name ?? 'Untitled Product',
  description: product.description ?? '',
  price: Number(product.price ?? 0),
  priceLabel: product.price_label ?? '',
  image: product.image_url || product.image || '/images/bakerdan/Bread.png',
  liked: false,
  tag: product.is_active ? 'Available' : 'Inactive',
  rating: '5.0/5',
});

const loadProducts = async () => {
  try {
    const response = await api.getProducts();
    const catalog = response.data?.data || [];
    products.value = catalog.map(mapProduct);
  } catch (error) {
    products.value = [];
  }
};

onMounted(() => {
  loadProducts();
});

const filteredProducts = computed(() => {
  const query = searchQuery.value.trim().toLowerCase();

  let result = products.value.filter((product) => {
    const matchesCategory = props.activeCategory === 'Customize'
      ? product.category === 'Customize'
      : props.activeCategory === 'Bread' || props.activeCategory === 'Pastries' || props.activeCategory === 'Cakes'
        ? product.category === props.activeCategory
        : true;

    const matchesQuery = !query
      || product.name.toLowerCase().includes(query)
      || product.description.toLowerCase().includes(query)
      || product.tag.toLowerCase().includes(query);

    return matchesCategory && matchesQuery;
  });

  if (sortBy.value === 'low-to-high') {
    result = [...result].sort((a, b) => a.price - b.price);
  } else if (sortBy.value === 'price') {
    result = [...result].sort((a, b) => b.price - a.price);
  } else {
    result = [...result].sort((a, b) => Number(b.liked) - Number(a.liked));
  }

  return result;
});

const totalPages = computed(() => Math.max(1, Math.ceil(filteredProducts.value.length / perPage)));

const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return filteredProducts.value.slice(start, start + perPage);
});

const openAttachmentPicker = () => {
  attachmentInput.value?.click();
};

const handleAttachmentChange = (event) => {
  attachmentName.value = event.target.files?.[0]?.name || '';
};

const sendAiPrompt = () => {
  const prompt = aiPrompt.value.trim();

  if (!prompt && !attachmentName.value) {
    return;
  }

  searchQuery.value = prompt;
  currentPage.value = 1;
};

const submitSearch = () => {
  currentPage.value = 1;
};

const handleAddToCart = async (productId) => {
  const product = products.value.find((item) => item.id === productId);

  if (!product) {
    return;
  }

  try {
    const addedItem = await addCatalogItem(product.id, {
      quantity: 1,
    });

    if (addedItem?.id) {
      push(`/customer/cart?added=${addedItem.id}`);
    }
  } catch (error) {
    window.alert('Unable to add this item to the cart right now.');
  }
};

const handleToggleLike = (productId) => {
  const product = products.value.find((item) => item.id === productId);

  if (product) {
    product.liked = !product.liked;
  }
};
</script>
