import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true
})

// Request interceptor for CSRF token
api.interceptors.request.use(
  config => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (token) {
      config.headers['X-CSRF-TOKEN'] = token
    }
    return config
  },
  error => Promise.reject(error)
)

// Response interceptor for error handling
api.interceptors.response.use(
  response => response,
  error => {
    // Don't auto-redirect for API errors - let the callers handle them
    // This allows guests to gracefully handle 401 errors for cart operations
    return Promise.reject(error)
  }
)

export default {
  // Products
  getProducts(params = {}, config = {}) {
    return api.get('/products', { ...config, params })
  },

  getProduct(id) {
    return api.get(`/products/${id}`)
  },

  chat(data) {
    return api.post('/chat', data)
  },

  // Cart
  getCart() {
    return api.get('/cart')
  },

  addToCart(productId, data) {
    return api.post(`/cart/add/${productId}`, data)
  },

  addCustomToCart(data) {
    const formData = new FormData()

    Object.entries(data || {}).forEach(([key, value]) => {
      if (value === undefined || value === null || value === '') {
        return
      }

      formData.append(key, value)
    })

    return api.post('/cart/custom', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
  },

  updateCartItem(itemId, data) {
    return api.put(`/cart/items/${itemId}`, data)
  },

  removeFromCart(itemId) {
    return api.delete(`/cart/items/${itemId}`)
  },

  clearCart() {
    return api.delete('/cart/clear')
  },

  checkout(data) {
    return api.post('/checkout', data)
  },

  validatePromo(data) {
    return api.post('/promos/validate', data)
  },

  getActivePromos() {
    return api.get('/promos/active')
  },

  getOrders(params = {}, config = {}) {
    return api.get('/orders', { ...config, params })
  },

  getPurchases() {
    return api.get('/purchases')
  },

  cancelOrder(orderId) {
    return api.patch(`/orders/${orderId}/cancel`)
  },

  cancelPendingOrder(pendingOrderId) {
    return api.patch(`/pending-orders/${encodeURIComponent(pendingOrderId)}/cancel`)
  },

  getOrderPaymentStatus(orderId) {
    return api.get(`/orders/${orderId}/payment-status`)
  },

  getPendingOrderPaymentStatus(pendingOrderId) {
    return api.get(`/pending-orders/${encodeURIComponent(pendingOrderId)}/payment-status`)
  },

  // Custom Orders
  createCustomOrder(data) {
    const formData = new FormData()

    Object.entries(data || {}).forEach(([key, value]) => {
      if (value === undefined || value === null || value === '') {
        return
      }

      formData.append(key, value)
    })

    return api.post('/custom-orders', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
  },

  // User Profile
  getProfile() {
    return api.get('/profile')
  },

  updateProfile(data) {
    return api.put('/profile', data)
  },

  updatePassword(data) {
    return api.put('/profile/password', data)
  },

  verifyCurrentPassword(data) {
    return api.post('/profile/verify-password', data)
  },

  // Notifications
  getNotifications() {
    return api.get('/notifications')
  },

  markNotificationRead(notificationId) {
    return api.post(`/notifications/${notificationId}/read`)
  },

  markAllNotificationsRead() {
    return api.post('/notifications/read-all')
  },

  // Categories
  getCategories() {
    return api.get('/categories')
  },

  // Reviews
  getProductReviews(productId) {
    return api.get(`/products/${productId}/reviews`)
  },

  submitReview(data) {
    return api.post('/reviews', data)
  },

  getCustomerReviews() {
    return api.get('/reviews/customer')
  },
}
