import './bootstrap';
import './admin-dashboard.js';
import { createApp } from 'vue';
import App from './App.vue';

window.addEventListener('pageshow', (event) => {
  if (event.persisted && window.location.pathname.startsWith('/customer')) {
    window.location.reload();
  }
});

const app = createApp(App);
app.mount('#app');
