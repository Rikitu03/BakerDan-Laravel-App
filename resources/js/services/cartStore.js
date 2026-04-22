import { ref, watch } from 'vue';

const STORAGE_KEY = 'bakerdan-cart-items';
const LAST_ADDED_KEY = 'bakerdan-cart-last-added';

const defaultItems = [
  {
    id: 101,
    productKey: 'catalog-1',
    source: 'catalog',
    name: 'Korean Garlic Cream Cheese Bun',
    description: 'Soft enriched dough with a rich cream cheese center and a glossy garlic finish.',
    price: 499,
    image: '/images/bakerdan/Creme_Cheese_Garlic.png',
    quantity: 1,
    size: 'Medium',
    flavor: '',
    tag: 'Best Seller',
  },
  {
    id: 102,
    productKey: 'catalog-2',
    source: 'catalog',
    name: 'Classic Cream Puffs',
    description: 'Airy pastry shells filled with silky vanilla cream and a light sugar dusting.',
    price: 299,
    image: '/images/bakerdan/Creme_Puffs.png',
    quantity: 1,
    size: 'Box of 6',
    flavor: 'Vanilla',
    tag: 'Fresh Batch',
  },
];

const readStorage = (key, fallback) => {
  if (typeof window === 'undefined') {
    return fallback;
  }

  try {
    const value = window.localStorage.getItem(key);
    return value ? JSON.parse(value) : fallback;
  } catch {
    return fallback;
  }
};

const initialItems = readStorage(STORAGE_KEY, defaultItems);
const cartItems = ref(initialItems);
const lastAddedId = ref(readStorage(LAST_ADDED_KEY, initialItems[0]?.id || null));

let cartSequence = cartItems.value.reduce((maxId, item) => {
  const currentId = Number(item.id) || 0;
  return currentId > maxId ? currentId : maxId;
}, 100);

const nextId = () => {
  cartSequence += 1;
  return cartSequence;
};

const persistCart = () => {
  if (typeof window === 'undefined') {
    return;
  }

  window.localStorage.setItem(STORAGE_KEY, JSON.stringify(cartItems.value));
  window.localStorage.setItem(LAST_ADDED_KEY, JSON.stringify(lastAddedId.value));
};

watch(cartItems, persistCart, { deep: true });
watch(lastAddedId, persistCart);

const normalizeCartItem = (item) => ({
  id: Number(item.id) || nextId(),
  productKey: item.productKey || `item-${Date.now()}`,
  source: item.source || 'catalog',
  name: item.name || 'Bakerdan Item',
  description: item.description || '',
  price: Number(item.price) || 0,
  image: item.image || '/images/bakerdan/Bread.png',
  quantity: Math.max(1, Number(item.quantity) || 1),
  size: item.size || 'Medium',
  flavor: item.flavor || '',
  tag: item.tag || 'Bakerdan',
  designDescription: item.designDescription || '',
  dedicationMessage: item.dedicationMessage || '',
});

const addCartItem = (item) => {
  const normalizedItem = normalizeCartItem(item);
  const existingIndex = cartItems.value.findIndex(
    (cartItem) => cartItem.productKey === normalizedItem.productKey && cartItem.source === normalizedItem.source,
  );

  if (existingIndex > -1 && normalizedItem.source !== 'custom') {
    cartItems.value[existingIndex] = {
      ...cartItems.value[existingIndex],
      quantity: cartItems.value[existingIndex].quantity + normalizedItem.quantity,
    };
    lastAddedId.value = cartItems.value[existingIndex].id;
    return cartItems.value[existingIndex].id;
  }

  cartItems.value = [normalizedItem, ...cartItems.value];
  lastAddedId.value = normalizedItem.id;
  return normalizedItem.id;
};

const updateCartItemQuantity = (itemId, quantity) => {
  cartItems.value = cartItems.value.map((item) =>
    item.id === itemId
      ? {
          ...item,
          quantity: Math.max(1, Number(quantity) || 1),
        }
      : item,
  );
};

const removeCartItem = (itemId) => {
  cartItems.value = cartItems.value.filter((item) => item.id !== itemId);

  if (lastAddedId.value === itemId) {
    lastAddedId.value = cartItems.value[0]?.id || null;
  }
};

const clearCart = () => {
  cartItems.value = [];
  lastAddedId.value = null;
};

const setLastAddedId = (itemId) => {
  lastAddedId.value = itemId;
};

export const useCartStore = () => ({
  cartItems,
  lastAddedId,
  addCartItem,
  updateCartItemQuantity,
  removeCartItem,
  clearCart,
  setLastAddedId,
});
