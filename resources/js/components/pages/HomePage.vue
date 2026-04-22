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
      <div class="grid grid-cols-1 gap-5 md:grid-cols-2 2xl:grid-cols-3">
        <ProductCard
          v-for="product in paginatedProducts"
          :key="product.id"
          :product="product"
          @add-to-cart="handleAddToCart"
          @toggle-like="handleToggleLike"
        />
      </div>
    </section>

    <nav class="mt-10 flex items-center justify-center gap-3" aria-label="Product pagination">
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
import { computed, ref, watch } from 'vue';
import { useSpaRouter } from '../../router';
import { useCartStore } from '../../services/cartStore';
import ProductCard from '../shared/ProductCard.vue';

const props = defineProps({
  activeCategory: {
    type: String,
    default: 'Bread',
  },
});

const { push } = useSpaRouter();
const { addCartItem } = useCartStore();

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

const products = ref([
  {
    id: 1,
    category: 'Bread',
    name: 'Korean Garlic Cream Cheese Bun',
    description: 'Soft enriched dough with a rich cream cheese center and a glossy garlic finish.',
    price: 499.0,
    image: '/images/bakerdan/Creme_Cheese_Garlic.png',
    liked: true,
    tag: 'Best Seller',
    rating: '4.9/5',
  },
  {
    id: 2,
    category: 'Pastries',
    name: 'Classic Cream Puffs',
    description: 'Airy pastry shells filled with silky vanilla cream and a light sugar dusting.',
    price: 299.0,
    image: '/images/bakerdan/Creme_Puffs.png',
    liked: false,
    tag: 'Fresh Batch',
    rating: '4.8/5',
  },
  {
    id: 3,
    category: 'Cakes',
    name: 'Celebration Butter Cake',
    description: 'A soft layered cake made for birthdays, milestones, and everyday sweet cravings.',
    price: 899.0,
    image: '/images/bakerdan/Cake_Celebration.png',
    liked: true,
    tag: 'Party Pick',
    rating: '4.9/5',
  },
  {
    id: 4,
    category: 'Pastries',
    name: 'Fudgy Brownie Squares',
    description: 'Rich chocolate brownie bars with a chewy middle and delicate crackly top.',
    price: 240.0,
    image: '/images/bakerdan/Brownies.png',
    liked: false,
    tag: 'Chocolate',
    rating: '4.7/5',
  },
  {
    id: 5,
    category: 'Customize',
    name: 'Customized Cookies Box',
    description: 'Personalized cookies prepared with your chosen colors, message, and event theme.',
    price: 650.0,
    image: '/images/bakerdan/Customized_Cookies.png',
    liked: true,
    tag: 'Made to Order',
    rating: '4.9/5',
  },
  {
    id: 6,
    category: 'Bread',
    name: 'Daily Artisan Bread Loaf',
    description: 'Golden crust outside, fluffy crumb inside, and baked fresh each morning.',
    price: 180.0,
    image: '/images/bakerdan/Bread.png',
    liked: false,
    tag: 'Daily Fresh',
    rating: '4.8/5',
  },
  {
    id: 7,
    category: 'Bread',
    name: 'Garlic Cream Cheese Bun Trio',
    description: 'A trio of creamy garlic buns for sharing with the whole table.',
    price: 760.0,
    image: '/images/bakerdan/Creme_Cheese_Garlic.png',
    liked: true,
    tag: 'Bundle',
    rating: '4.9/5',
  },
  {
    id: 8,
    category: 'Pastries',
    name: 'Vanilla Puff Treats',
    description: 'A bakery favorite with airy pastry and a balanced vanilla filling.',
    price: 315.0,
    image: '/images/bakerdan/Creme_Puffs.png',
    liked: false,
    tag: 'Customer Pick',
    rating: '4.7/5',
  },
  {
    id: 9,
    category: 'Cakes',
    name: 'Buttercream Celebration Cake',
    description: 'Smooth buttercream edges with a rich sponge and a festive presentation.',
    price: 990.0,
    image: '/images/bakerdan/Cake_Celebration.png',
    liked: true,
    tag: 'Signature',
    rating: '5.0/5',
  },
  {
    id: 10,
    category: 'Bread',
    name: 'Bakerdan House Loaf',
    description: 'Lightly crisp, deeply comforting, and ideal for sandwiches or breakfast toast.',
    price: 165.0,
    image: '/images/bakerdan/Bread.png',
    liked: false,
    tag: 'House Favorite',
    rating: '4.8/5',
  },
  {
    id: 11,
    category: 'Pastries',
    name: 'Chocolate Brownie Bites',
    description: 'Compact brownie pieces with intense cocoa flavor and a tender crumb.',
    price: 210.0,
    image: '/images/bakerdan/Brownies.png',
    liked: false,
    tag: 'Snack Box',
    rating: '4.6/5',
  },
  {
    id: 12,
    category: 'Customize',
    name: 'Celebration Cookie Set',
    description: 'Color-coordinated sugar cookies that can match birthdays, weddings, and gifts.',
    price: 720.0,
    image: '/images/bakerdan/Customized_Cookies.png',
    liked: true,
    tag: 'Custom Gift',
    rating: '4.9/5',
  },
]);

watch(
  () => props.activeCategory,
  () => {
    currentPage.value = 1;
  },
);

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

const handleAddToCart = (productId) => {
  const product = products.value.find((item) => item.id === productId);

  if (!product) {
    return;
  }

  const addedId = addCartItem({
    ...product,
    productKey: `catalog-${product.id}`,
    source: 'catalog',
    quantity: 1,
  });

  push(`/customer/cart?added=${addedId}`);
};

const handleToggleLike = (productId) => {
  const product = products.value.find((item) => item.id === productId);

  if (product) {
    product.liked = !product.liked;
  }
};
</script>
