import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';
import '../css/app.css';
import axios from 'axios';

// Setup axios defaults
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.headers.common['Content-Type'] = 'application/json';

// Get auth token from localStorage
const token = localStorage.getItem('auth_token');
if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

// Check auth on mount
import { useAuthStore } from './stores/auth';
const authStore = useAuthStore();
authStore.checkAuth();

app.mount('#polycms-admin-app');
