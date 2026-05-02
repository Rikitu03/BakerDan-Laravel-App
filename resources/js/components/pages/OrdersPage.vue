<template>
  <div class="px-6 py-7 md:px-8 xl:px-10">
    <section class="overflow-hidden rounded-[34px] border border-[#E6D9CD] bg-[linear-gradient(135deg,#fffaf5_0%,#f7efe6_52%,#f1dfce_100%)] px-6 py-7 shadow-[0_22px_50px_-36px_rgba(134,86,46,0.45)] md:px-8">
      <div class="grid gap-6 xl:grid-cols-[1.3fr_0.9fr]">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#B47A52]">Customer Orders</p>
          <h1 class="mt-3 text-4xl font-black tracking-tight text-[#443A34]" style="font-family: 'Urbanist', sans-serif;">
            Stay on top of every order in progress.
          </h1>
          <p class="mt-3 max-w-2xl text-sm leading-6 text-[#6E6259]">
            This view follows the customer flow from the diagram: after checkout and payment, the customer lands here to track status, review payment, and cancel only while the order is still pending.
          </p>

          <div
            v-if="notice.visible"
            class="mt-6 rounded-[26px] border px-5 py-4 shadow-sm"
            :class="notice.wrapperClass"
          >
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em]" :class="notice.eyebrowClass">{{ notice.title }}</p>
                <p class="mt-1 text-sm font-medium">{{ notice.message }}</p>
              </div>
              <div class="flex flex-wrap gap-2">
                <button
                  v-if="highlightedOrder?.can_continue_payment"
                  type="button"
                  @click="continuePayment(highlightedOrder)"
                  class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-[#6D4A35] transition-colors hover:bg-[#FFF7F1]"
                >
                  Continue payment
                </button>
                <button
                  v-if="highlightedOrder?.can_refresh_payment"
                  type="button"
                  @click="refreshPayment(highlightedOrder.id)"
                  class="rounded-full border border-white/70 bg-white/70 px-4 py-2 text-sm font-semibold text-[#6D4A35] transition-colors hover:bg-white"
                >
                  Refresh status
                </button>
              </div>
            </div>
          </div>

          <div class="mt-7 grid gap-4 sm:grid-cols-3">
            <div class="rounded-[26px] border border-[#EADBCB] bg-white/80 p-5 backdrop-blur">
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#AE8B6F]">Current Orders</p>
              <p class="mt-3 text-3xl font-black text-[#463B35]">{{ currentOrders.length }}</p>
              <p class="mt-1 text-sm text-[#74685F]">Pending, preparing, or ready for release.</p>
            </div>

            <div class="rounded-[26px] border border-[#EADBCB] bg-white/80 p-5 backdrop-blur">
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#AE8B6F]">Pending Payment</p>
              <p class="mt-3 text-3xl font-black text-[#463B35]">{{ pendingPaymentCount }}</p>
              <p class="mt-1 text-sm text-[#74685F]">Orders still waiting for successful payment.</p>
            </div>

            <div class="rounded-[26px] border border-[#EADBCB] bg-white/80 p-5 backdrop-blur">
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#AE8B6F]">Custom Briefs</p>
              <p class="mt-3 text-3xl font-black text-[#463B35]">{{ customOrderCount }}</p>
              <p class="mt-1 text-sm text-[#74685F]">Orders that include custom design instructions.</p>
            </div>
          </div>
        </div>

        <div class="rounded-[30px] border border-[#E8D8C8] bg-white/85 p-6 backdrop-blur">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B47A52]">Order Flow</p>
              <h2 class="mt-2 text-2xl font-black text-[#473C35]" style="font-family: 'Urbanist', sans-serif;">Status tracker</h2>
            </div>
            <button
              type="button"
              @click="openCart"
              class="rounded-full border border-[#E1D3C5] bg-[#FFF9F4] px-4 py-2 text-sm font-semibold text-[#785640] transition-colors hover:bg-white"
            >
              Open cart
            </button>
          </div>

          <div class="mt-6 space-y-4">
            <div
              v-for="step in flowSteps"
              :key="step.label"
              class="flex items-start gap-4 rounded-[22px] border border-[#EFE3D8] bg-[#FCF8F4] px-4 py-4"
            >
              <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[#C9876C] text-sm font-bold text-white shadow-sm">
                {{ step.number }}
              </div>
              <div>
                <p class="font-semibold text-[#4C4039]">{{ step.label }}</p>
                <p class="mt-1 text-sm leading-6 text-[#776C63]">{{ step.description }}</p>
              </div>
            </div>
          </div>

          <div class="mt-6 rounded-[22px] border border-dashed border-[#E5D7CA] bg-[#FFFDFB] px-4 py-4 text-sm text-[#786E67]">
            Cancellation stays available only while the order is unpaid and still in the pending stage.
          </div>
        </div>
      </div>
    </section>

    <section class="mt-8">
      <div class="mb-5 flex items-center justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B59884]">In Progress</p>
          <h2 class="mt-1 text-2xl font-black text-[#483F39]" style="font-family: 'Urbanist', sans-serif;">Current orders</h2>
        </div>
        <button
          type="button"
          @click="loadOrders"
          class="rounded-full border border-[#E3D7CD] bg-white px-4 py-2 text-sm font-semibold text-[#6C6057] transition-colors hover:bg-[#FFF8F2]"
        >
          Refresh all
        </button>
      </div>

      <div v-if="isLoading" class="space-y-4">
        <div
          v-for="placeholder in 2"
          :key="placeholder"
          class="rounded-[30px] border border-[#ECE1D8] bg-white p-6 shadow-[0_18px_40px_-34px_rgba(118,79,49,0.25)]"
        >
          <div class="h-5 w-40 animate-pulse rounded-full bg-[#EEE3D8]"></div>
          <div class="mt-4 h-4 w-72 animate-pulse rounded-full bg-[#F4EBE3]"></div>
          <div class="mt-6 grid gap-3 md:grid-cols-2">
            <div class="h-28 animate-pulse rounded-[24px] bg-[#F6EFE8]"></div>
            <div class="h-28 animate-pulse rounded-[24px] bg-[#F6EFE8]"></div>
          </div>
        </div>
      </div>

      <div v-else-if="loadError" class="rounded-[28px] border border-[#F0DCCC] bg-[#FFF4EB] px-6 py-5 text-[#8E5632] shadow-sm">
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#C9876C]">Orders unavailable</p>
        <p class="mt-2 text-sm">{{ loadError }}</p>
      </div>

      <div v-else-if="currentOrders.length" class="space-y-5">
        <article
          v-for="order in currentOrders"
          :key="order.id"
          class="rounded-[30px] border border-[#ECE1D8] bg-white p-6 shadow-[0_18px_40px_-34px_rgba(118,79,49,0.25)]"
        >
          <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div>
              <div class="flex flex-wrap items-center gap-2">
                <p class="text-lg font-black text-[#443A34]" style="font-family: 'Urbanist', sans-serif;">Order #{{ order.id }}</p>
                <span class="rounded-full px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em]" :class="statusClass(order.flow_status)">
                  {{ order.status_label }}
                </span>
                <span class="rounded-full px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em]" :class="paymentClass(order.payment_status)">
                  {{ order.payment_status_label }}
                </span>
                <span v-if="order.contains_custom" class="rounded-full bg-[#F6EEE7] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-[#8B6D57]">
                  Custom
                </span>
              </div>
              <p class="mt-2 text-sm text-[#70655D]">
                {{ order.placed_at_label || 'Recently placed' }}
                <span v-if="order.reference"> | Ref {{ order.reference }}</span>
                <span v-if="order.item_count"> | {{ order.item_count }} item{{ order.item_count > 1 ? 's' : '' }}</span>
              </p>
              <p class="mt-3 max-w-3xl text-sm leading-6 text-[#776B63]">{{ order.status_note }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
              <button
                v-if="order.can_continue_payment"
                type="button"
                @click="continuePayment(order)"
                class="rounded-full bg-[#C9876C] px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#B8765B]"
              >
                Continue payment
              </button>
              <button
                v-if="order.can_refresh_payment"
                type="button"
                @click="refreshPayment(order.id)"
                :disabled="refreshingOrderId === order.id"
                class="rounded-full border border-[#E2D4C7] bg-white px-4 py-2 text-sm font-semibold text-[#6D6259] transition-colors hover:bg-[#FFF8F2] disabled:cursor-not-allowed disabled:opacity-60"
              >
                {{ refreshingOrderId === order.id ? 'Refreshing...' : 'Refresh status' }}
              </button>
              <button
                v-if="order.can_cancel"
                type="button"
                @click="cancelOrder(order)"
                :disabled="cancellingOrderId === order.id"
                class="rounded-full border border-[#E7C7C0] bg-[#FFF4F1] px-4 py-2 text-sm font-semibold text-[#B85D4A] transition-colors hover:bg-[#FFEAE5] disabled:cursor-not-allowed disabled:opacity-60"
              >
                {{ cancellingOrderId === order.id ? 'Cancelling...' : 'Cancel order' }}
              </button>
            </div>
          </div>

          <div class="mt-6 grid gap-5 xl:grid-cols-[1.2fr_0.8fr]">
            <div class="rounded-[26px] border border-[#EEE3D9] bg-[#FCFAF7] p-5">
              <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B59884]">Order Items</p>
                <p class="text-sm font-semibold text-[#5E544E]">{{ order.total_amount_label }}</p>
              </div>

              <div class="mt-4 space-y-4">
                <div
                  v-for="item in order.items"
                  :key="item.id"
                  class="flex gap-4 rounded-[22px] border border-[#F0E6DE] bg-white p-4"
                >
                  <img
                    :src="item.image"
                    :alt="item.name"
                    class="h-20 w-20 rounded-[18px] object-cover"
                  />
                  <div class="min-w-0 flex-1">
                    <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                      <div class="min-w-0">
                        <p class="truncate font-semibold text-[#4B413B]">{{ item.name }}</p>
                        <p class="mt-1 text-sm text-[#756A62]">{{ item.description || 'Freshly prepared by Bakerdan.' }}</p>
                      </div>
                      <p class="text-sm font-semibold text-[#5A5049]">x{{ item.quantity }}</p>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                      <span v-if="item.summary" class="rounded-full bg-[#F7F1EA] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-[#8A7768]">
                        {{ item.summary }}
                      </span>
                      <span class="rounded-full bg-[#FFF4EA] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-[#B26C41]">
                        {{ item.line_total_label }}
                      </span>
                    </div>
                    <p v-if="item.designDescription" class="mt-3 text-sm leading-6 text-[#756A62]">
                      Design brief: {{ item.designDescription }}
                    </p>
                    <p v-if="item.dedicationMessage" class="mt-2 text-sm leading-6 text-[#756A62]">
                      Dedication: {{ item.dedicationMessage }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <div class="space-y-4">
              <div class="rounded-[26px] border border-[#EDE1D7] bg-[#FFFDFB] p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B59884]">Flowchart Status</p>
                <p class="mt-3 text-xl font-black text-[#473D36]" style="font-family: 'Urbanist', sans-serif;">{{ order.flow_status_label }}</p>
                <p class="mt-2 text-sm text-[#746961]">Payment method: {{ order.payment_method_label }}</p>
                <p class="mt-1 text-sm text-[#746961]">Payment status: {{ order.payment_status_label }}</p>
              </div>

              <div class="rounded-[26px] border border-[#EDE1D7] bg-[#FFFDFB] p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B59884]">Delivery Details</p>
                <p class="mt-3 text-sm leading-6 text-[#746961]">
                  {{ order.shipping_address || 'The bakery will confirm your address details before release if needed.' }}
                </p>
              </div>

              <div class="rounded-[26px] border border-[#EDE1D7] bg-[#FFFDFB] p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B59884]">Total Amount</p>
                <p class="mt-3 text-3xl font-black text-[#473D36]" style="font-family: 'Urbanist', sans-serif;">{{ order.total_amount_label }}</p>
              </div>
            </div>
          </div>
        </article>
      </div>

      <div v-else class="rounded-[30px] border border-dashed border-[#E5D7CA] bg-[#FCF8F4] px-6 py-10 text-center">
        <h2 class="text-2xl font-black text-[#473D36]" style="font-family: 'Urbanist', sans-serif;">No current orders yet</h2>
        <p class="mt-2 text-sm text-[#766B63]">Add something to the cart, checkout, and it will show up here after payment starts.</p>
        <div class="mt-6 flex flex-wrap justify-center gap-3">
          <button
            type="button"
            @click="browseMenu"
            class="rounded-full bg-[#C9876C] px-5 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#B8765B]"
          >
            Browse menu
          </button>
          <button
            type="button"
            @click="openCart"
            class="rounded-full border border-[#E1D3C5] bg-white px-5 py-3 text-sm font-semibold text-[#6E6259] transition-colors hover:bg-[#FFF8F2]"
          >
            Review cart
          </button>
        </div>
      </div>
    </section>

    <section v-if="historyOrders.length" class="mt-9">
      <div class="mb-5">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B59884]">Recent Activity</p>
        <h2 class="mt-1 text-2xl font-black text-[#483F39]" style="font-family: 'Urbanist', sans-serif;">Order history</h2>
      </div>

      <div class="grid gap-4 xl:grid-cols-2">
        <article
          v-for="order in historyOrders"
          :key="`history-${order.id}`"
          class="rounded-[28px] border border-[#ECE1D8] bg-white p-5 shadow-[0_18px_40px_-34px_rgba(118,79,49,0.2)]"
        >
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-lg font-black text-[#443A34]" style="font-family: 'Urbanist', sans-serif;">Order #{{ order.id }}</p>
              <p class="mt-1 text-sm text-[#70655D]">{{ order.placed_at_label || 'Recently updated' }}</p>
            </div>
            <span class="rounded-full px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em]" :class="statusClass(order.flow_status)">
              {{ order.status_label }}
            </span>
          </div>

          <div class="mt-4 flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-[#F7F1EA] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-[#88776A]">
              {{ order.payment_method_label }}
            </span>
            <span class="rounded-full px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em]" :class="paymentClass(order.payment_status)">
              {{ order.payment_status_label }}
            </span>
            <span v-if="order.contains_custom" class="rounded-full bg-[#F6EEE7] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-[#8B6D57]">
              Custom
            </span>
          </div>

          <p class="mt-4 text-sm leading-6 text-[#756A62]">{{ order.status_note }}</p>
          <p class="mt-4 text-sm font-semibold text-[#5A5049]">{{ order.total_amount_label }}</p>
        </article>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useSpaRouter } from '../../router';
import api from '../../services/api';

const props = defineProps({
  highlight: {
    type: [String, Number],
    default: null,
  },
  paymentReturn: {
    type: String,
    default: null,
  },
});

const { push } = useSpaRouter();
const orders = ref([]);
const isLoading = ref(false);
const loadError = ref('');
const refreshingOrderId = ref(null);
const cancellingOrderId = ref(null);

const flowSteps = [
  {
    number: '01',
    label: 'Pending',
    description: 'The order exists, but it can still be cancelled while payment is unpaid and production has not started.',
  },
  {
    number: '02',
    label: 'Preparing',
    description: 'The bakery has started work on the order, so cancellation is no longer available.',
  },
  {
    number: '03',
    label: 'Ready',
    description: 'The order is finished and ready for release, pickup, or the final handoff.',
  },
  {
    number: '04',
    label: 'Completed',
    description: 'The order has been completed and moves into the customer history section.',
  },
];

const highlightedOrderId = computed(() => {
  const parsed = Number(props.highlight);
  return Number.isFinite(parsed) && parsed > 0 ? parsed : null;
});

const highlightedOrder = computed(() =>
  orders.value.find((order) => order.id === highlightedOrderId.value) || null,
);

const currentOrders = computed(() => orders.value.filter((order) => order.is_current));
const historyOrders = computed(() => orders.value.filter((order) => !order.is_current));
const pendingPaymentCount = computed(() =>
  currentOrders.value.filter((order) => ['pending', 'unpaid'].includes(order.payment_status)).length,
);
const customOrderCount = computed(() => orders.value.filter((order) => order.contains_custom).length);

const notice = computed(() => {
  if (!highlightedOrder.value && !props.paymentReturn) {
    return { visible: false };
  }

  if (!highlightedOrder.value) {
    return {
      visible: true,
      title: 'Order update',
      message: 'We could not find the highlighted order, but your latest orders are listed below.',
      wrapperClass: 'border-[#F0DCCC] bg-[#FFF4EB] text-[#8E5632]',
      eyebrowClass: 'text-[#C9876C]',
    };
  }

  if (props.paymentReturn === 'success') {
    if (highlightedOrder.value.payment_status === 'paid') {
      return {
        visible: true,
        title: 'Payment confirmed',
        message: `Order #${highlightedOrder.value.id} has been paid successfully. You can now track its progress from pending to ready here.`,
        wrapperClass: 'border-[#DDEED8] bg-[#F4FFF0] text-[#3E6B35]',
        eyebrowClass: 'text-[#6EAA57]',
      };
    }

    return {
      visible: true,
      title: 'Payment verification in progress',
      message: `Order #${highlightedOrder.value.id} has returned from PayMongo, but the final payment confirmation is still syncing.`,
      wrapperClass: 'border-[#D8E8F6] bg-[#F4FAFF] text-[#2F5F87]',
      eyebrowClass: 'text-[#5E94C2]',
    };
  }

  if (props.paymentReturn === 'cancelled') {
    return {
      visible: true,
      title: 'Payment still pending',
      message: `Order #${highlightedOrder.value.id} is still awaiting payment. You can continue checkout any time from this page.`,
      wrapperClass: 'border-[#F0DCCC] bg-[#FFF4EB] text-[#8E5632]',
      eyebrowClass: 'text-[#C9876C]',
    };
  }

  return {
    visible: true,
    title: 'Order created',
    message: `Order #${highlightedOrder.value.id} is now listed in your active orders. Track payment and production status here.`,
    wrapperClass: 'border-[#D8E8F6] bg-[#F4FAFF] text-[#2F5F87]',
    eyebrowClass: 'text-[#5E94C2]',
  };
});

const mergeOrder = (nextOrder, paymentPayload = null) => {
  if (!nextOrder?.id) {
    return;
  }

  const existingIndex = orders.value.findIndex((order) => order.id === nextOrder.id);
  const existingOrder = existingIndex >= 0 ? orders.value[existingIndex] : {};
  const mergedOrder = {
    ...existingOrder,
    ...nextOrder,
    items: nextOrder.items || existingOrder.items || [],
    payment: paymentPayload
      ? { ...(existingOrder.payment || {}), ...paymentPayload }
      : { ...(existingOrder.payment || {}), ...(nextOrder.payment || {}) },
  };

  if (existingIndex >= 0) {
    orders.value.splice(existingIndex, 1, mergedOrder);
    return;
  }

  orders.value.unshift(mergedOrder);
};

const loadOrders = async () => {
  isLoading.value = true;
  loadError.value = '';

  try {
    const response = await api.getOrders();
    orders.value = response.data?.data?.orders || [];
  } catch (error) {
    loadError.value = error.response?.data?.message || 'We could not load your orders right now.';
  } finally {
    isLoading.value = false;
  }
};

const refreshPayment = async (orderId) => {
  refreshingOrderId.value = orderId;

  try {
    const response = await api.getOrderPaymentStatus(orderId);
    const payload = response.data?.data || {};
    mergeOrder(payload.order, payload.payment);
  } catch (error) {
    window.alert(error.response?.data?.message || 'Unable to refresh the payment status right now.');
  } finally {
    refreshingOrderId.value = null;
  }
};

const cancelOrder = async (order) => {
  if (!order?.can_cancel) {
    return;
  }

  const confirmed = window.confirm(`Cancel Order #${order.id}? This is only allowed while the order is still pending and unpaid.`);
  if (!confirmed) {
    return;
  }

  cancellingOrderId.value = order.id;

  try {
    const response = await api.cancelOrder(order.id);
    mergeOrder(response.data?.data?.order);
  } catch (error) {
    window.alert(error.response?.data?.message || 'Unable to cancel this order right now.');
  } finally {
    cancellingOrderId.value = null;
  }
};

const continuePayment = (order) => {
  const checkoutUrl = order?.checkout_url || order?.payment?.checkout_url;

  if (checkoutUrl) {
    window.location.href = checkoutUrl;
  }
};

const browseMenu = () => {
  push('/customer');
};

const openCart = () => {
  push('/customer/cart');
};

const statusClass = (flowStatus) => {
  if (flowStatus === 'completed') {
    return 'bg-[#ECF7E8] text-[#4E8A45]';
  }

  if (flowStatus === 'cancelled') {
    return 'bg-[#FDEEEA] text-[#B6654B]';
  }

  if (flowStatus === 'ready') {
    return 'bg-[#FFF3DE] text-[#A36A1F]';
  }

  if (flowStatus === 'preparing') {
    return 'bg-[#EEF4FF] text-[#476B9C]';
  }

  return 'bg-[#F7EFE7] text-[#8A6950]';
};

const paymentClass = (paymentStatus) => {
  if (paymentStatus === 'paid') {
    return 'bg-[#ECF7E8] text-[#4E8A45]';
  }

  if (paymentStatus === 'failed' || paymentStatus === 'expired') {
    return 'bg-[#FDEEEA] text-[#B6654B]';
  }

  return 'bg-[#FFF3DE] text-[#A36A1F]';
};

onMounted(async () => {
  await loadOrders();

  if (highlightedOrderId.value) {
    await refreshPayment(highlightedOrderId.value);
  }
});
</script>
