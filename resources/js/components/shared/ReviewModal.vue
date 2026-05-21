<template>
  <div v-if="show" class="fixed inset-0 z-[100] overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
      <div
        class="fixed inset-0 bg-[#443A34]/40 backdrop-blur-sm transition-opacity"
        @click="close"
      ></div>

      <div class="relative transform overflow-hidden rounded-[32px] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
        <div class="bg-white px-6 py-7 sm:px-8 sm:py-8">
          <div class="flex items-center justify-between mb-6">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B47A52]">Review Product</p>
              <h3 class="text-2xl font-black text-[#443A34] mt-1" style="font-family: 'Urbanist', sans-serif;">
                How was your order?
              </h3>
            </div>
            <button @click="close" class="text-[#A89E96] hover:text-[#7C746E] transition-colors">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div v-if="item" class="flex items-center gap-4 mb-8 p-4 rounded-2xl bg-[#FCFAF8] border border-[#F0E6DE]">
            <img :src="item.image" :alt="item.name" class="h-16 w-16 rounded-xl object-cover border border-[#EFE5DC]" />
            <div class="min-w-0 flex-1">
              <p class="font-bold text-[#4B413B] truncate">{{ item.name }}</p>
              <p class="text-sm text-[#8A7E76]">Order #{{ orderId }}</p>
            </div>
          </div>

          <form @submit.prevent="submitReview">
            <div class="mb-6">
              <label class="block text-sm font-bold text-[#4B413B] mb-3">Rating</label>
              <div class="flex gap-2">
                <button
                  v-for="star in 5"
                  :key="star"
                  type="button"
                  @click="rating = star"
                  class="text-3xl transition-transform hover:scale-110"
                  :class="star <= rating ? 'text-[#F59E0B]' : 'text-[#E5E7EB]'"
                >
                  ★
                </button>
              </div>
            </div>

            <div class="mb-8">
              <label for="comment" class="block text-sm font-bold text-[#4B413B] mb-3">Your Feedback</label>
              <textarea
                id="comment"
                v-model="comment"
                rows="4"
                class="w-full rounded-2xl border-[#E8DDD3] bg-[#FCFAF8] px-4 py-3 text-[#4B413B] placeholder-[#A89E96] focus:border-[#C9876C] focus:ring-[#C9876C] transition-all"
                placeholder="Tell us what you liked or how we can improve..."
              ></textarea>
            </div>

            <div v-if="error" class="mb-6 p-4 rounded-xl bg-[#FFF4F1] text-[#B85D4A] text-sm font-medium border border-[#E7C7C0]">
              {{ error }}
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
              <button
                type="submit"
                :disabled="submitting || rating === 0"
                class="flex-1 rounded-full bg-[#C9876C] py-3.5 px-6 text-sm font-bold text-white shadow-lg shadow-[#C9876C]/20 transition-all hover:bg-[#B8765B] disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ submitting ? 'Submitting...' : 'Submit Review' }}
              </button>
              <button
                type="button"
                @click="close"
                class="flex-1 rounded-full border border-[#E8DDD3] bg-white py-3.5 px-6 text-sm font-bold text-[#7C746E] transition-all hover:bg-[#FCFAF8]"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import api from '../../services/api';

const props = defineProps({
  show: Boolean,
  orderId: [String, Number],
  item: Object
});

const emit = defineEmits(['close', 'submitted']);

const rating = ref(0);
const comment = ref('');
const submitting = ref(false);
const error = ref('');

watch(() => props.show, (newVal) => {
  if (newVal) {
    rating.value = 0;
    comment.value = '';
    error.value = '';
  }
});

const close = () => {
  if (submitting.value) return;
  emit('close');
};

const submitReview = async () => {
  if (rating.value === 0) {
    error.value = 'Please select a rating.';
    return;
  }

  submitting.value = true;
  error.value = '';

  try {
    await api.submitReview({
      order_id: props.orderId,
      product_id: props.item.product_id,
      rating: rating.value,
      comment: comment.value
    });

    emit('submitted', {
      orderId: props.orderId,
      productId: props.item.product_id
    });
    submitting.value = false;
    close();
  } catch (err) {
    console.error('Failed to submit review:', err);
    error.value = err.response?.data?.message || 'Failed to submit review. Please try again.';
  } finally {
    submitting.value = false;
  }
};
</script>
