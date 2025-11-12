import './bootstrap';
import { createApp } from 'vue';
import Chat from './components/Chat.vue';
import axios from 'axios';

console.log('Vue app initializing...');

// Make axios available in components
const app = createApp({});
app.config.globalProperties.$axios = axios;

console.log('Chat component:', Chat); // Debug log

// Register components
app.component('chat', Chat);

// Mount the app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('app')) {
        app.mount('#app');
    }
});
