<div id="bakerdan-chat-widget" class="font-sans" style="position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999;">
    <!-- Chat Button -->
    <button 
      id="bd-chat-toggle"
      class="hover:scale-105 focus:outline-none flex items-center justify-center"
      style="background-color: #8b5a2b; color: white; border-radius: 9999px; padding: 1rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); transition: transform 0.2s;"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
      </svg>
    </button>

    <!-- Chat Window -->
    <div 
      id="bd-chat-window"
      class="bg-white rounded-xl shadow-2xl w-80 sm:w-96 overflow-hidden flex flex-col border border-gray-100 hidden absolute bottom-0 right-0"
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
        <button id="bd-chat-close" class="text-white hover:text-gray-200 focus:outline-none">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Messages Area -->
      <div id="bd-chat-messages" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50 flex flex-col">
        <!-- Initial Message -->
        <div class="flex items-start">
          <div class="bg-[#f0e6d2] text-[#5c3a21] rounded-2xl rounded-tl-none px-4 py-2 text-sm shadow-sm max-w-[85%]">
            Hello! I'm your BakerDan Assistant. How can I help you today?
          </div>
        </div>
        
        <!-- Human Support Option -->
        <div class="flex flex-col space-y-2 mt-4 items-start">
          <p class="text-[10px] uppercase tracking-wider text-gray-500 font-bold ml-1">Need human help?</p>
          <a 
            href="/customer/messages" 
            class="bg-white border border-[#8b5a2b] text-[#8b5a2b] rounded-full px-4 py-1.5 text-xs font-semibold hover:bg-[#8b5a2b] hover:text-white transition-colors shadow-sm inline-flex items-center gap-2"
          >
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
            Chat with Admin
          </a>
        </div>
      </div>

      <!-- Loading Indicator -->
      <div id="bd-chat-loading" class="hidden px-4 pb-4 bg-gray-50 flex items-start">
        <div class="bg-[#f0e6d2] text-[#5c3a21] rounded-2xl rounded-tl-none px-4 py-3 text-sm shadow-sm flex items-center space-x-1">
          <div class="w-2 h-2 bg-[#8b5a2b] rounded-full animate-bounce" style="animation-delay: 0s;"></div>
          <div class="w-2 h-2 bg-[#8b5a2b] rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
          <div class="w-2 h-2 bg-[#8b5a2b] rounded-full animate-bounce" style="animation-delay: 0.4s;"></div>
        </div>
      </div>

      <!-- Input Area -->
      <div class="p-3 bg-white border-t border-gray-100">
        <form id="bd-chat-form" class="flex space-x-2">
          <input 
            id="bd-chat-input"
            type="text" 
            placeholder="Type your message..." 
            class="flex-1 border border-gray-300 rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#8b5a2b] focus:border-transparent text-sm"
          />
          <button 
            id="bd-chat-submit"
            type="submit" 
            class="bg-[#8b5a2b] hover:bg-[#6b4423] text-white rounded-full p-2 focus:outline-none disabled:opacity-50 transition-colors"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform rotate-90" viewBox="0 0 20 20" fill="currentColor">
              <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
            </svg>
          </button>
        </form>
      </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', initChat);
if (document.readyState === 'interactive' || document.readyState === 'complete') {
    initChat();
}

function initChat() {
    const toggleBtn = document.getElementById('bd-chat-toggle');
    const closeBtn = document.getElementById('bd-chat-close');
    const chatWindow = document.getElementById('bd-chat-window');
    const form = document.getElementById('bd-chat-form');
    const input = document.getElementById('bd-chat-input');
    const submitBtn = document.getElementById('bd-chat-submit');
    const messagesContainer = document.getElementById('bd-chat-messages');
    const loadingIndicator = document.getElementById('bd-chat-loading');

    let history = [];
    let isLoading = false;

    // Wait until elements are actually in DOM
    if (!toggleBtn || !chatWindow) return;

    function toggleChat() {
        const isHidden = chatWindow.classList.contains('hidden');
        if (isHidden) {
            chatWindow.classList.remove('hidden');
            toggleBtn.classList.add('hidden');
            input.focus();
            scrollToBottom();
        } else {
            chatWindow.classList.add('hidden');
            toggleBtn.classList.remove('hidden');
        }
    }

    toggleBtn.addEventListener('click', toggleChat);
    closeBtn.addEventListener('click', toggleChat);

    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function formatAssistantMessage(text) {
      const bulletCount = (text.match(/\s\*\s+/g) || []).length;

      if (bulletCount < 2) {
        return text;
      }

      const parts = text.split(/\s*\*\s*/).map((part) => part.trim()).filter(Boolean);

      if (parts.length < 2) {
        return text;
      }

      const intro = parts.shift();

      return [intro, ...parts.map((part) => `- ${part}`)].join('\n');
    }

    function addMessage(text, role) {
        const wrapper = document.createElement('div');
        wrapper.className = 'flex ' + (role === 'user' ? 'justify-end' : 'justify-start');
        wrapper.style.marginTop = '1rem';
        
        const bubble = document.createElement('div');
        bubble.className = 'rounded-2xl px-4 py-2 text-sm shadow-sm max-w-[85%] break-words ';
        
        if (role === 'user') {
            bubble.className += 'bg-[#8b5a2b] text-white rounded-tr-none';
        } else {
          bubble.className += 'bg-[#f0e6d2] text-[#5c3a21] rounded-tl-none whitespace-pre-line';
        }
        
        bubble.textContent = role === 'user' ? text : formatAssistantMessage(text);
        wrapper.appendChild(bubble);
        messagesContainer.appendChild(wrapper);
        scrollToBottom();
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const text = input.value.trim();
        if (!text || isLoading) return;

        // Display user message
        addMessage(text, 'user');
        
        input.value = '';
        input.disabled = true;
        submitBtn.disabled = true;
        
        isLoading = true;
        loadingIndicator.classList.remove('hidden');
        loadingIndicator.classList.add('flex');
        scrollToBottom();

        try {
            const response = await fetch('/api/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    message: text,
                    history: history
                })
            });

            const data = await response.json();

            // Add user message to history
            history.push({ role: 'user', text: text });

            if (data.success) {
                addMessage(data.reply, 'model');
                history.push({ role: 'model', text: data.reply });
            } else {
                addMessage(data.message || 'Sorry, I encountered an error. Please try again.', 'model');
            }
        } catch (error) {
            console.error(error);
            addMessage('Sorry, I could not connect to the server.', 'model');
        } finally {
            isLoading = false;
            input.disabled = false;
            submitBtn.disabled = false;
            loadingIndicator.classList.add('hidden');
            loadingIndicator.classList.remove('flex');
            input.focus();
            scrollToBottom();
        }
    });
}
</script>
