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
    if (error.response?.status === 401) {
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default {
  // Products
  getProducts(params = {}) {
    return api.get('/products', { params })
  },
  
  getProduct(id) {
    return api.get(`/products/${id}`)
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

  getOrders() {
    return api.get('/orders')
  },

  cancelOrder(orderId) {
    return api.patch(`/orders/${orderId}/cancel`)
  },

  getOrderPaymentStatus(orderId) {
    return api.get(`/orders/${orderId}/payment-status`)
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

  // Categories
  getCategories() {
    return api.get('/categories')
  },
}
