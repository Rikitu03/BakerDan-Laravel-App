<template>
  <div class="fixed bottom-6 right-6 z-50 font-sans">
    <!-- Chat Button -->
    <button 
      @click="toggleChat" 
      class="bg-[#8b5a2b] hover:bg-[#6b4423] text-white rounded-full p-4 shadow-lg transition-transform hover:scale-105 focus:outline-none flex items-center justify-center"
      :class="{ 'hidden': isOpen }"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
      </svg>
    </button>

    <!-- Chat Window -->
    <div 
      v-show="isOpen" 
      class="bg-white rounded-xl shadow-2xl w-80 sm:w-96 overflow-hidden flex flex-col border border-gray-100"
      style="height: 500px; max-height: 80vh;"
    >
      <!-- Header -->
      <div class="bg-[#8b5a2b] text-white p-4 flex justify-between items-center">
        <div class="flex items-center space-x-2">
          <div class="bg-white p-1 rounded-full text-[#8b5a2b]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
            </svg>
          </div>
          <span class="font-bold">BakerDan Assistant</span>
        </div>
        <button @click="toggleChat" class="text-white hover:text-gray-200 focus:outline-none">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Messages Area -->
      <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50" ref="messagesContainer">
        <!-- Initial Message -->
        <div class="flex items-start">
          <div class="bg-[#f0e6d2] text-[#5c3a21] rounded-2xl rounded-tl-none px-4 py-2 text-sm shadow-sm max-w-[85%]">
            Hello! I'm your BakerDan Assistant. How can I help you today?
          </div>
        </div>

        <!-- Chat History -->
        <div 
          v-for="(msg, index) in messages" 
          :key="index"
          class="flex"
          :class="msg.role === 'user' ? 'justify-end' : 'justify-start'"
        >
          <div 
            class="rounded-2xl px-4 py-2 text-sm shadow-sm max-w-[85%]"
            :class="msg.role === 'user' 
              ? 'bg-[#8b5a2b] text-white rounded-tr-none' 
              : 'bg-[#f0e6d2] text-[#5c3a21] rounded-tl-none'"
          >
            {{ msg.text }}
          </div>
        </div>

        <!-- Loading Indicator -->
        <div v-if="isLoading" class="flex items-start">
          <div class="bg-[#f0e6d2] text-[#5c3a21] rounded-2xl rounded-tl-none px-4 py-3 text-sm shadow-sm flex items-center space-x-1">
            <div class="w-2 h-2 bg-[#8b5a2b] rounded-full animate-bounce" style="animation-delay: 0s;"></div>
            <div class="w-2 h-2 bg-[#8b5a2b] rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
            <div class="w-2 h-2 bg-[#8b5a2b] rounded-full animate-bounce" style="animation-delay: 0.4s;"></div>
          </div>
        </div>
      </div>

      <!-- Input Area -->
      <div class="p-3 bg-white border-t border-gray-100">
        <form @submit.prevent="sendMessage" class="flex space-x-2">
          <input 
            v-model="newMessage" 
            type="text" 
            placeholder="Type your message..." 
            class="flex-1 border border-gray-300 rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#8b5a2b] focus:border-transparent text-sm"
            :disabled="isLoading"
          />
          <button 
            type="submit" 
            class="bg-[#8b5a2b] hover:bg-[#6b4423] text-white rounded-full p-2 focus:outline-none disabled:opacity-50 transition-colors"
            :disabled="!newMessage.trim() || isLoading"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform rotate-90" viewBox="0 0 20 20" fill="currentColor">
              <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
            </svg>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick } from 'vue';

const isOpen = ref(false);
const messages = ref([]);
const newMessage = ref('');
const isLoading = ref(false);
const messagesContainer = ref(null);

const toggleChat = () => {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    scrollToBottom();
  }
};

const scrollToBottom = async () => {
  await nextTick();
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
};

const sendMessage = async () => {
  const text = newMessage.value.trim();
  if (!text) return;

  // Add user message
  messages.value.push({ role: 'user', text });
  newMessage.value = '';
  isLoading.value = true;
  scrollToBottom();

  try {
    // In a real app, this might use axios instance with authorization header
    // Currently, this depends on how auth is handled in BakerDan SPA
    const token = localStorage.getItem('token'); // or similar auth state
    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    };
    
    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }

    const response = await fetch('/api/chat', {
      method: 'POST',
      headers,
      body: JSON.stringify({
        message: text,
        history: messages.value.slice(0, -1) // Exclude the current message we just added
      })
    });

    const data = await response.json();

    if (data.success) {
      messages.value.push({ role: 'model', text: data.reply });
    } else {
      messages.value.push({ role: 'model', text: 'Sorry, I encountered an error. Please try again.' });
    }
  } catch (error) {
    console.error('Chat error:', error);
    messages.value.push({ role: 'model', text: 'Sorry, I could not connect to the server.' });
  } finally {
    isLoading.value = false;
    scrollToBottom();
  }
};
</script>
