<template>
  <div class="p-8 max-w-4xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-4xl font-bold text-gray-800 mb-3" style="font-family: 'Urbanist', sans-serif;">
        Custom order
      </h1>
      <p class="text-gray-600">
        Order products tailored to want want! Just fill the details and upload a photo reference.
      </p>
    </div>

    <!-- Product Details Section -->
    <div class="bg-white rounded-2xl p-8 shadow-md mb-6">
      <h2 class="text-2xl font-bold text-gray-800 mb-6">Product Details</h2>

      <div class="flex gap-6">
        <!-- Image Upload -->
        <div class="w-1/2">
          <div 
            @click="triggerFileUpload"
            @dragover.prevent
            @drop.prevent="handleDrop"
            class="border-2 border-dashed border-gray-300 rounded-xl p-8 flex flex-col items-center justify-center cursor-pointer hover:border-[#C9876C] transition-colors h-64"
            :class="{ 'border-[#C9876C] bg-[#C9876C]/5': isDragging }"
          >
            <div v-if="!imagePreview" class="text-center">
              <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
              </svg>
              <p class="text-gray-600 font-medium mb-1">UPLOAD IMAGE</p>
              <p class="text-gray-600">REFERENCE</p>
              <p class="text-sm text-gray-500 mt-2">Drag & drop or click to upload</p>
            </div>
            <div v-else class="relative w-full h-full">
              <img :src="imagePreview" alt="Preview" class="w-full h-full object-cover rounded-lg" />
              <button
                @click.stop="removeImage"
                class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 hover:bg-red-600 transition-colors"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <input
              ref="fileInput"
              type="file"
              accept="image/*"
              @change="handleFileSelect"
              class="hidden"
            />
          </div>
        </div>

        <!-- Product Options -->
        <div class="w-1/2 space-y-4">
          <!-- Size -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Size</label>
            <select 
              v-model="formData.size"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#C9876C] focus:border-transparent outline-none"
            >
              <option>Small</option>
              <option>Medium</option>
              <option>Large</option>
            </select>
          </div>

          <!-- Quantity -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
            <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
              <button
                @click="decrementQuantity"
                class="px-4 py-3 hover:bg-gray-100 transition-colors"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                </svg>
              </button>
              <input
                v-model.number="formData.quantity"
                type="number"
                min="1"
                class="flex-1 text-center border-x border-gray-300 py-3 outline-none"
              />
              <button
                @click="incrementQuantity"
                class="px-4 py-3 hover:bg-gray-100 transition-colors"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Flavor -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Flavor</label>
            <select 
              v-model="formData.flavor"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#C9876C] focus:border-transparent outline-none"
            >
              <option>Chocolate</option>
              <option>Vanilla</option>
              <option>Strawberry</option>
              <option>Mocha</option>
              <option>Ube</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Design Description -->
    <div class="bg-white rounded-2xl p-8 shadow-md mb-6">
      <h2 class="text-2xl font-bold text-gray-800 mb-4">Design Description</h2>
      <textarea
        v-model="formData.designDescription"
        placeholder="Describe how you like it to be"
        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#C9876C] focus:border-transparent outline-none resize-none"
        rows="5"
        maxlength="300"
      ></textarea>
      <div class="text-right text-sm text-gray-500 mt-2">
        {{ formData.designDescription.length }}/300
      </div>
    </div>

    <!-- Dedication Message -->
    <div class="bg-white rounded-2xl p-8 shadow-md mb-8">
      <h2 class="text-2xl font-bold text-gray-800 mb-4">Dedication Message</h2>
      <textarea
        v-model="formData.dedicationMessage"
        placeholder="Write your message here"
        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#C9876C] focus:border-transparent outline-none resize-none"
        rows="5"
        maxlength="300"
      ></textarea>
      <div class="text-right text-sm text-gray-500 mt-2">
        {{ formData.dedicationMessage.length }}/300
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-4">
      <button
        @click="directCheckout"
        class="flex-1 bg-[#C9876C] hover:bg-[#B8765B] text-white font-semibold py-4 rounded-lg transition-colors shadow-md"
      >
        Direct Checkout
      </button>
      <button
        @click="addToCart"
        class="flex-1 bg-white hover:bg-gray-50 text-gray-700 font-semibold py-4 rounded-lg border-2 border-gray-300 transition-colors"
      >
        Add to Cart
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const formData = ref({
  size: 'Medium',
  quantity: 5,
  flavor: 'Chocolate',
  designDescription: '',
  dedicationMessage: '',
  imageFile: null
})

const imagePreview = ref(null)
const isDragging = ref(false)
const fileInput = ref(null)

const triggerFileUpload = () => {
  fileInput.value?.click()
}

const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file) {
    processFile(file)
  }
}

const handleDrop = (event) => {
  isDragging.value = false
  const file = event.dataTransfer.files[0]
  if (file && file.type.startsWith('image/')) {
    processFile(file)
  }
}

const processFile = (file) => {
  formData.value.imageFile = file
  const reader = new FileReader()
  reader.onload = (e) => {
    imagePreview.value = e.target.result
  }
  reader.readAsDataURL(file)
}

const removeImage = () => {
  imagePreview.value = null
  formData.value.imageFile = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const incrementQuantity = () => {
  formData.value.quantity++
}

const decrementQuantity = () => {
  if (formData.value.quantity > 1) {
    formData.value.quantity--
  }
}

const directCheckout = () => {
  console.log('Direct checkout:', formData.value)
  // Handle direct checkout
}

const addToCart = () => {
  console.log('Add to cart:', formData.value)
  router.push('/customer/cart')
}
</script>
