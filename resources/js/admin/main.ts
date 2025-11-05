import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';
// CSS is loaded via Vite in Blade template
import axios from 'axios';
import { t } from './composables/useTranslation';

// Setup axios defaults
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.headers.common['Content-Type'] = 'application/json';

// Add request interceptor to handle FormData
axios.interceptors.request.use(
    (config) => {
        // If data is FormData, remove Content-Type header so axios can set it with boundary
        if (config.data instanceof FormData) {
            // Delete from common headers
            if (config.headers) {
                delete config.headers['Content-Type'];
                delete config.headers.common?.['Content-Type'];
            }
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Get auth token from localStorage
const token = localStorage.getItem('auth_token');
if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

// Add global translation function
app.config.globalProperties.$t = t;

// Check auth on mount
import { useAuthStore } from './stores/auth';
const authStore = useAuthStore();
authStore.checkAuth();

// Initialize theme store
import { useThemeStore } from './stores/theme';
const themeStore = useThemeStore();
themeStore.init();

app.mount('#polycms-admin-app');
