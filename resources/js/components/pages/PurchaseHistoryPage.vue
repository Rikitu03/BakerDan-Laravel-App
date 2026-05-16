<template>
  <div class="px-4 py-6 md:px-8 xl:px-10">
    <div class="mx-auto max-w-5xl">
      <div class="mb-6 md:mb-8">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B47A52]">
          Account
        </p>
        <h1 class="text-2xl md:text-4xl font-black tracking-tight text-[#44413E]" style="font-family: 'Urbanist', sans-serif;">
          Purchase History
        </h1>
        <p class="mt-2 md:mt-3 max-w-3xl text-sm leading-6 text-[#766C65]">
          View your paid orders with full details. Re-order catalog products directly from here.
        </p>
      </div>

      <div v-if="totalOrders > 0" class="mb-6 flex flex-wrap gap-3 md:gap-4">
        <div class="flex-1 min-w-[140px] rounded-[22px] border border-[#E8DDD3] bg-white px-5 py-4 shadow-sm">
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B59884]">Total Orders</p>
          <p class="mt-1 text-2xl font-bold text-[#4B4743]">{{ totalOrders }}</p>
        </div>
        <div class="flex-1 min-w-[140px] rounded-[22px] border border-[#E8DDD3] bg-white px-5 py-4 shadow-sm">
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B59884]">Total Spent</p>
          <p class="mt-1 text-2xl font-bold text-[#4B4743]">PHP {{ formatNumber(totalSpent) }}</p>
        </div>
      </div>

      <div v-if="loading" class="flex items-center justify-center py-20">
        <div class="h-8 w-8 animate-spin rounded-full border-4 border-[#E4D8CD] border-t-[#C9876C]"></div>
      </div>

      <div v-else-if="loadError" class="rounded-[28px] border border-[#F0DCCC] bg-[#FFF4EB] px-6 py-5 text-[#8E5632] shadow-sm">
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#C9876C]">Purchase history unavailable</p>
        <p class="mt-2 text-sm">{{ loadError }}</p>
      </div>

      <div v-else-if="purchases.length === 0" class="rounded-[32px] border border-[#E8DDD3] bg-white p-8 text-center shadow-sm md:p-12">
        <svg class="mx-auto h-16 w-16 text-[#C9B8A8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        <h2 class="mt-4 text-xl font-bold text-[#4B4743]">No purchases yet</h2>
        <p class="mt-2 text-sm text-[#766C65]">Once an order is paid, it will appear here.</p>
        <button
          type="button"
          @click="push('/customer')"
          class="mt-6 rounded-full bg-[#C9876C] px-6 py-3 font-semibold text-white shadow-md transition-colors hover:bg-[#B8765B]"
        >
          Browse Products
        </button>
      </div>

      <div v-else class="space-y-5">
        <div
          v-for="order in purchases"
          :key="order.id"
          class="rounded-[28px] border border-[#E8DDD3] bg-white shadow-sm transition-shadow hover:shadow-md"
        >
          <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#F0E5DC] px-5 py-4 md:px-6">
            <div class="flex flex-wrap items-center gap-x-5 gap-y-1">
              <span class="text-sm font-bold text-[#4B4743]">Order #{{ order.id }}</span>
              <span class="rounded-full bg-[#E8F5E9] px-3 py-0.5 text-xs font-semibold text-[#2E7D32]">
                {{ order.status_label }}
              </span>
              <span class="text-xs text-[#8A7E76]">{{ order.placed_at_label }}</span>
            </div>
            <div class="text-right">
              <p class="text-lg font-bold text-[#4B4743]">{{ order.total_amount_label }}</p>
              <p class="text-xs text-[#8A7E76]">{{ order.item_count }} item{{ order.item_count !== 1 ? 's' : '' }}</p>
            </div>
          </div>

          <div class="divide-y divide-[#F5EDE6]">
            <div
              v-for="item in order.items"
              :key="item.id"
              class="flex items-start gap-3 px-5 py-4 md:gap-4 md:px-6"
            >
              <img
                :src="item.image"
                :alt="item.name"
                class="h-16 w-16 flex-shrink-0 rounded-2xl border border-[#EFE5DC] object-cover md:h-20 md:w-20"
              />

              <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-[#4B4743] md:text-base">{{ item.name }}</p>
                <p v-if="item.summary" class="mt-0.5 text-xs text-[#8A7E76]">{{ item.summary }}</p>
                <p class="mt-1 text-xs text-[#766C65]">
                  {{ item.unit_price_label }} &times; {{ item.quantity }}
                </p>
                <p v-if="item.source === 'custom'" class="mt-1 inline-block rounded-full bg-[#FFF2E8] px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-[#B76539]">
                  Custom Order
                </p>
              </div>

              <div class="flex flex-col items-end gap-2">
                <p class="whitespace-nowrap text-sm font-bold text-[#4B4743]">{{ item.line_total_label }}</p>
                <div class="flex flex-col gap-2 items-end">
                  <button
                    v-if="item.source !== 'custom' && item.product_id"
                    type="button"
                    @click="addToCart(item.product_id)"
                    class="flex items-center gap-1.5 rounded-full border border-[#C9876C] px-3 py-1.5 text-xs font-semibold text-[#C9876C] transition-all hover:bg-[#C9876C] hover:text-white"
                  >
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Add to Cart
                  </button>

                  <button
                    v-if="order.can_review && item.product_id && !item.is_reviewed"
                    type="button"
                    @click="openReviewModal(order, item)"
                    class="flex items-center gap-1.5 rounded-full bg-[#F6F1ED] px-3 py-1.5 text-xs font-semibold text-[#8B6D57] transition-all hover:bg-[#EDE3DB]"
                  >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.382-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                    Review
                  </button>

                  <span v-else-if="item.is_reviewed" class="flex items-center gap-1 text-[11px] font-bold text-[#4E8A45]">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                    Reviewed
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div v-if="order.payment" class="flex flex-wrap items-center gap-x-4 gap-y-1 border-t border-[#F0E5DC] px-5 py-3 text-xs text-[#8A7E76] md:px-6">
            <span>{{ order.payment_method_label }}</span>
            <span>{{ order.payment_status_label }}</span>
            <span v-if="order.payment_paid_at">Paid {{ formatDate(order.payment_paid_at) }}</span>
          </div>
        </div>
      </div>

      <div v-if="purchases.length > 0" class="mt-8 text-center">
        <button
          type="button"
          @click="push('/customer')"
          class="rounded-full border border-[#CFC5BC] bg-white px-6 py-3 font-semibold text-[#4E4742] transition-colors hover:bg-[#FAF6F1]"
        >
          Continue Shopping
        </button>
      </div>
    </div>
  </div>

  <ReviewModal
    :show="showReviewModal"
    :order-id="activeReviewOrder?.id"
    :item="activeReviewItem"
    @close="showReviewModal = false"
    @submitted="handleReviewSubmitted"
  />

  <!-- Success Modal Overlay -->
  <div v-if="showSuccessModal" class="fixed inset-0 z-[110] overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
      <div
        class="fixed inset-0 bg-[#443A34]/40 backdrop-blur-sm transition-opacity"
        @click="showSuccessModal = false"
      ></div>

      <div class="relative transform overflow-hidden rounded-[32px] bg-white p-8 text-center shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md">
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-[#F0FDF4] mb-6">
          <svg class="h-10 w-10 text-[#22C55E]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
          </svg>
        </div>

        <h3 class="text-2xl font-black text-[#443A34] mb-2" style="font-family: 'Urbanist', sans-serif;">
          Review Submitted!
        </h3>

        <p class="text-[#7C746E] leading-relaxed">
          Thank you for your review {{ user.name }}. We hope you have a nice day!
        </p>

        <button
          type="button"
          @click="showSuccessModal = false"
          class="mt-8 w-full rounded-full bg-[#C9876C] py-3.5 px-6 text-sm font-bold text-white shadow-lg shadow-[#C9876C]/20 transition-all hover:bg-[#B8765B]"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useSpaRouter } from '../../router';
import api from '../../services/api';
import ReviewModal from '../shared/ReviewModal.vue';

const props = defineProps({
  user: {
    type: Object,
    default: () => ({ name: 'Customer' })
  }
});

const { push } = useSpaRouter();

const loading = ref(true);
const loadError = ref('');
const purchases = ref([]);
const totalSpent = ref(0);
const totalOrders = ref(0);

const showReviewModal = ref(false);
const showSuccessModal = ref(false);
const activeReviewOrder = ref(null);
const activeReviewItem = ref(null);

const openReviewModal = (order, item) => {
  activeReviewOrder.value = order;
  activeReviewItem.value = item;
  showReviewModal.value = true;
};

const handleReviewSubmitted = ({ orderId, productId }) => {
  // Update the local state to mark as reviewed
  purchases.value = purchases.value.map(order => {
    if (order.id === orderId) {
      return {
        ...order,
        items: order.items.map(item => {
          if (item.product_id === productId) {
            return { ...item, is_reviewed: true };
          }
          return item;
        })
      };
    }
    return order;
  });

  // Show success modal
  showSuccessModal.value = true;
};

const formatNumber = (num) => {
  return Number(num || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDate = (isoString) => {
  if (!isoString) return '';
  return new Date(isoString).toLocaleDateString('en-PH', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
};

const addToCart = (productId) => {
  push(`/customer/cart?preview=${productId}`);
};

const loadPurchases = async () => {
  loading.value = true;
  loadError.value = '';
  try {
    const response = await api.getPurchases();
    const data = response.data?.data || {};

    if (!Array.isArray(data.purchases)) {
      throw new Error('Invalid purchases payload');
    }

    purchases.value = data.purchases;
    totalSpent.value = Number(data.total_spent || 0);
    totalOrders.value = Number(data.total_orders || 0);
  } catch (error) {
    console.error('Failed to load purchase history:', error);
    loadError.value = error.response?.data?.message || 'We could not load your purchase history right now.';
  } finally {
    loading.value = false;
  }
};

onMounted(loadPurchases);
</script>
