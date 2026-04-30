import { computed, ref } from 'vue';
import api from './api';

const cartItems = ref([]);
const subtotal = ref(0);
const tax = ref(0);
const total = ref(0);
const itemCount = ref(0);
const isLoading = ref(false);
const isSubmitting = ref(false);
const hasLoaded = ref(false);
const lastAddedId = ref(null);
const lastCheckoutOrderId = ref(null);

let loadPromise = null;

const syncCart = (cart = {}) => {
  const items = Array.isArray(cart.items) ? cart.items : [];

  cartItems.value = items;
  subtotal.value = Number(cart.subtotal || 0);
  tax.value = Number(cart.tax || 0);
  total.value = Number(cart.total || 0);
  itemCount.value = Number(cart.item_count || items.reduce((sum, item) => sum + Number(item.quantity || 0), 0));

  if (lastAddedId.value && !items.some((item) => item.id === lastAddedId.value)) {
    lastAddedId.value = null;
  }
};

const loadCart = async ({ force = false } = {}) => {
  if (hasLoaded.value && !force) {
    return cartItems.value;
  }

  if (loadPromise && !force) {
    return loadPromise;
  }

  isLoading.value = true;
  loadPromise = api.getCart()
    .then((response) => {
      syncCart(response.data?.data);
      hasLoaded.value = true;
      return cartItems.value;
    })
    .finally(() => {
      isLoading.value = false;
      loadPromise = null;
    });

  return loadPromise;
};

const addCatalogItem = async (productId, data = {}) => {
  isSubmitting.value = true;

  try {
    const response = await api.addToCart(productId, data);
    const payload = response.data?.data || {};
    syncCart(payload.cart);
    lastAddedId.value = payload.item?.id || null;
    hasLoaded.value = true;

    return payload.item || null;
  } finally {
    isSubmitting.value = false;
  }
};

const addCustomItem = async (data = {}) => {
  isSubmitting.value = true;

  try {
    const response = await api.addCustomToCart({
      size: data.size,
      quantity: data.quantity,
      flavor: data.flavor,
      design_description: data.designDescription,
      dedication_message: data.dedicationMessage,
      guide_section: data.guideSection,
      image: data.imageFile,
      image_url: data.imageUrl,
      product_name: data.productName,
      base_price: data.basePrice,
    });
    const payload = response.data?.data || {};
    syncCart(payload.cart);
    lastAddedId.value = payload.item?.id || null;
    hasLoaded.value = true;

    return payload.item || null;
  } finally {
    isSubmitting.value = false;
  }
};

const updateCartItemQuantity = async (itemId, quantity) => {
  isSubmitting.value = true;

  try {
    const response = await api.updateCartItem(itemId, { quantity });
    const payload = response.data?.data || {};
    syncCart(payload.cart);

    return payload.item || null;
  } finally {
    isSubmitting.value = false;
  }
};

const removeCartItem = async (itemId) => {
  isSubmitting.value = true;

  try {
    const response = await api.removeFromCart(itemId);
    syncCart(response.data?.data?.cart);
  } finally {
    isSubmitting.value = false;
  }
};

const clearCart = async () => {
  isSubmitting.value = true;

  try {
    const response = await api.clearCart();
    syncCart(response.data?.data?.cart);
  } finally {
    isSubmitting.value = false;
  }
};

const checkout = async (itemIds, data = {}) => {
  isSubmitting.value = true;

  try {
    const response = await api.checkout({
      item_ids: itemIds,
      payment_method: data.paymentMethod,
      shipping_address: data.shippingAddress,
    });
    const payload = response.data?.data || {};
    syncCart(payload.cart);
    lastCheckoutOrderId.value = payload.order?.id || null;
    hasLoaded.value = true;

    return payload.order || null;
  } finally {
    isSubmitting.value = false;
  }
};

const syncOrderPaymentStatus = async (orderId) => {
  const response = await api.getOrderPaymentStatus(orderId);

  return response.data?.data || null;
};

export const useCartStore = () => ({
  cartItems,
  subtotal,
  tax,
  total,
  itemCount: computed(() => itemCount.value),
  cartCount: computed(() => itemCount.value),
  isLoading,
  isSubmitting,
  hasLoaded,
  lastAddedId,
  lastCheckoutOrderId,
  loadCart,
  addCatalogItem,
  addCustomItem,
  updateCartItemQuantity,
  removeCartItem,
  clearCart,
  checkout,
  syncOrderPaymentStatus,
});
