<template>
  <div class="px-6 py-8 md:px-8 xl:px-10">
    <div class="mx-auto max-w-6xl">
      <div class="mb-8">
        <h1 class="text-4xl font-black tracking-tight text-[#44413E]" style="font-family: 'Urbanist', sans-serif;">
          Custom order
        </h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-[#766C65]">
          Order products tailored to what you want. Upload a photo reference, choose your options, and tell us the details.
        </p>
      </div>

      <div class="mb-8 border-t border-[#E4D8CD]"></div>

      <section class="rounded-[32px] border border-[#E8DDD3] bg-white p-6 shadow-[0_18px_40px_-34px_rgba(118,79,49,0.35)] md:p-8">
        <h2 class="text-2xl font-bold text-[#4B4743]">Product Details</h2>

        <div class="mt-6 grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
          <div
            @click="triggerFileUpload"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
            class="flex min-h-[320px] cursor-pointer items-center justify-center rounded-[30px] border-2 border-dashed border-[#D8C7B8] bg-[#FCFAF8] p-6 transition-colors"
            :class="{ 'border-[#C9876C] bg-[#FBF1E8]': isDragging }"
          >
            <div v-if="!imagePreview" class="text-center">
              <svg class="mx-auto h-16 w-16 text-[#7A6A5F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
              </svg>
              <p class="mt-6 text-xl font-semibold tracking-wide text-[#4E4742]">UPLOAD IMAGE</p>
              <p class="text-xl font-semibold tracking-wide text-[#4E4742]">REFERENCE</p>
              <p class="mt-3 text-sm text-[#8B817A]">Drag & drop or click to upload</p>
            </div>

            <div v-else class="relative h-full w-full">
              <img :src="imagePreview" alt="Reference preview" class="h-full w-full rounded-[24px] object-cover" />
              <button
                type="button"
                @click.stop="removeImage"
                class="absolute top-4 right-4 rounded-full bg-red-500 p-2 text-white shadow-md transition hover:bg-red-600"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <input
              ref="fileInput"
              type="file"
              accept="image/*"
              class="hidden"
              @change="handleFileSelect"
            />
          </div>

          <div class="rounded-[30px] border border-[#E7D9CB] bg-[#FCFAF8] p-6">
            <div class="space-y-5">
              <div class="grid gap-2">
                <label class="text-sm font-medium text-[#66605B]">Size</label>
                <select
                  v-model="formData.size"
                  class="h-12 rounded-full border border-[#D8CCC2] bg-white px-5 outline-none focus:border-transparent focus:ring-2 focus:ring-[#C9876C]"
                >
                  <option>Small</option>
                  <option>Medium</option>
                  <option>Large</option>
                </select>
              </div>

              <div class="grid gap-2">
                <label class="text-sm font-medium text-[#66605B]">Quantity</label>
                <div class="flex items-center overflow-hidden rounded-full border border-[#D8CCC2] bg-white">
                  <button
                    type="button"
                    @click="decrementQuantity"
                    class="px-5 py-3 transition-colors hover:bg-[#F3ECE5]"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                  </button>
                  <input
                    v-model.number="formData.quantity"
                    type="number"
                    min="1"
                    class="flex-1 bg-transparent py-3 text-center outline-none"
                  />
                  <button
                    type="button"
                    @click="incrementQuantity"
                    class="px-5 py-3 transition-colors hover:bg-[#F3ECE5]"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                  </button>
                </div>
              </div>

              <div class="grid gap-2">
                <label class="text-sm font-medium text-[#66605B]">Flavor</label>
                <select
                  v-model="formData.flavor"
                  class="h-12 rounded-full border border-[#D8CCC2] bg-white px-5 outline-none focus:border-transparent focus:ring-2 focus:ring-[#C9876C]"
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
      </section>

      <section class="mt-7 rounded-[32px] border border-[#E8DDD3] bg-white p-6 shadow-[0_18px_40px_-34px_rgba(118,79,49,0.25)] md:p-8">
        <h2 class="text-2xl font-bold text-[#4B4743]">Design Description</h2>
        <textarea
          v-model="formData.designDescription"
          placeholder="Describe how you like it to be"
          class="mt-5 min-h-[190px] w-full rounded-[26px] border border-[#DDD1C7] bg-[#FCFAF8] px-5 py-4 outline-none transition focus:border-transparent focus:ring-2 focus:ring-[#C9876C]"
          maxlength="300"
        ></textarea>
        <div class="mt-2 text-right text-sm text-gray-500">
          {{ formData.designDescription.length }}/300
        </div>
      </section>

      <section class="mt-7 rounded-[32px] border border-[#E8DDD3] bg-white p-6 shadow-[0_18px_40px_-34px_rgba(118,79,49,0.25)] md:p-8">
        <h2 class="text-2xl font-bold text-[#4B4743]">Dedication Message</h2>
        <textarea
          v-model="formData.dedicationMessage"
          placeholder="Write your message here"
          class="mt-5 min-h-[190px] w-full rounded-[26px] border border-[#DDD1C7] bg-[#FCFAF8] px-5 py-4 outline-none transition focus:border-transparent focus:ring-2 focus:ring-[#C9876C]"
          maxlength="300"
        ></textarea>
        <div class="mt-2 text-right text-sm text-gray-500">
          {{ formData.dedicationMessage.length }}/300
        </div>
      </section>

      <div class="mt-8 flex flex-wrap gap-4">
        <button
          type="button"
          @click="directCheckout"
          class="rounded-full bg-[#C9876C] px-8 py-3.5 font-semibold text-white shadow-md transition-colors hover:bg-[#B8765B]"
        >
          Direct Checkout
        </button>
        <button
          type="button"
          @click="addToCart"
          class="rounded-full border border-[#CFC5BC] bg-white px-8 py-3.5 font-semibold text-[#4E4742] transition-colors hover:bg-[#FAF6F1]"
        >
          Add to Cart
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useSpaRouter } from '../../router';
import { useCartStore } from '../../services/cartStore';

const { push } = useSpaRouter();
const { addCartItem } = useCartStore();

const formData = ref({
  size: 'Medium',
  quantity: 5,
  flavor: 'Chocolate',
  designDescription: '',
  dedicationMessage: '',
  imageFile: null,
});

const imagePreview = ref(null);
const isDragging = ref(false);
const fileInput = ref(null);

const triggerFileUpload = () => {
  fileInput.value?.click();
};

const handleFileSelect = (event) => {
  const file = event.target.files?.[0];

  if (file) {
    processFile(file);
  }
};

const handleDrop = (event) => {
  isDragging.value = false;
  const file = event.dataTransfer.files?.[0];

  if (file && file.type.startsWith('image/')) {
    processFile(file);
  }
};

const processFile = (file) => {
  formData.value.imageFile = file;
  const reader = new FileReader();

  reader.onload = (loadEvent) => {
    imagePreview.value = loadEvent.target?.result || null;
  };

  reader.readAsDataURL(file);
};

const removeImage = () => {
  imagePreview.value = null;
  formData.value.imageFile = null;

  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

const incrementQuantity = () => {
  formData.value.quantity += 1;
};

const decrementQuantity = () => {
  if (formData.value.quantity > 1) {
    formData.value.quantity -= 1;
  }
};

const directCheckout = () => {
  console.log('Direct checkout:', formData.value);
};

const addToCart = () => {
  const addedId = addCartItem({
    id: undefined,
    productKey: `custom-${Date.now()}`,
    source: 'custom',
    name: 'Custom Celebration Order',
    description: `A ${formData.value.flavor.toLowerCase()} custom bake with ${formData.value.size.toLowerCase()} sizing and personalized finishing touches.`,
    price: 899,
    image: imagePreview.value || '/images/bakerdan/Customized_Cookies.png',
    quantity: formData.value.quantity,
    size: formData.value.size,
    flavor: formData.value.flavor,
    tag: 'Custom Order',
    designDescription: formData.value.designDescription,
    dedicationMessage: formData.value.dedicationMessage,
  });

  push(`/customer/cart?added=${addedId}`);
};
</script>
