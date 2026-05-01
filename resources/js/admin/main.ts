import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';
// CSS is loaded via Vite in Blade template
import axios from 'axios';
import { t } from './composables/useTranslation';
import * as EditorRegistry from './editor';

// ============================================================
// Plugin SDK — expose shared libs for independently-built modules
// ============================================================
import * as Vue from 'vue';
import * as VueRouter from 'vue-router';
import * as Pinia from 'pinia';

// Composables
import { useDialog } from './composables/useDialog';
import { useTranslation, loadTranslations } from './composables/useTranslation';
import { useTableSort } from './composables/useTableSort';
import { usePagination } from './composables/usePagination';
import { useValidation } from './composables/useValidation';
import { useSortable } from './composables/useSortable';
import { useGlobalSaveHotkey } from './composables/useGlobalSaveHotkey';

// Shared Components
import MediaPicker from './components/MediaPicker';
import HelpGuide from './components/HelpGuide.vue';
import MenuStructure from './views/menus/MenuStructure.vue';
import FormIconPicker from './components/forms/FormIconPicker.vue';
import FormField from './components/forms/FormField.vue';
import FormInput from './components/forms/FormInput.vue';
import FormToggle from './components/forms/FormToggle.vue';
import FormCountrySelect from './components/forms/FormCountrySelect.vue';
import Modal from './components/dialogs/Modal.vue';
import SortableHeader from './components/table/SortableHeader.vue';
import DataPagination from './components/table/DataPagination.vue';

// Stores
import { useDialogStore } from './stores/dialog';
import { useAuthStore } from './stores/auth';
import { useThemeStore } from './stores/theme';

// Utils
import { Storage } from './utils/storage';

// Chart.js (exposed for modules like Accounting & RemoteManager)
import * as ChartJS from 'chart.js';
import * as VueChartJS from 'vue-chartjs';

// Module loader
import { loadModules, createRouteRegistrar } from './moduleLoader';

// ============================================================
// Setup axios defaults
// ============================================================
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

// ============================================================
// Expose SDK on window BEFORE creating the app
// ============================================================
const registerRoutes = createRouteRegistrar(router);

(window as any).__POLYCMS_SDK__ = {
    // --- Core libs (used as Vite externals in module builds) ---
    Vue,
    VueRouter,
    Pinia,
    axios,
    ChartJS,
    VueChartJS,

    // --- Composables ---
    useDialog,
    useTranslation,
    loadTranslations,
    t,
    useTableSort,
    usePagination,
    useValidation,
    useSortable,
    useGlobalSaveHotkey,

    // --- Shared Components ---
    MediaPicker,
    HelpGuide,
    MenuStructure,
    FormIconPicker,
    FormField,
    FormInput,
    FormToggle,
    FormCountrySelect,
    Modal,
    SortableHeader,
    DataPagination,

    // --- Stores ---
    useDialogStore,
    useAuthStore,
    useThemeStore,

    // --- Utils ---
    Storage,

    // --- Router helpers ---
    router,
    registerRoutes,
};

// ============================================================
// Bootstrap Vue app
// ============================================================
const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

// Add global translation function
app.config.globalProperties.$t = t;

// Global Components
app.component('HelpGuide', HelpGuide);

// Check auth on mount
const authStore = useAuthStore();
authStore.checkAuth();

// Initialize theme store
const themeStore = useThemeStore();
themeStore.init();

(window as any).PolyCMS = {
    ...(window as any).PolyCMS,
    editor: EditorRegistry,
};

// Register default landing blocks globally
import { registerDefaultLandingBlocks } from './editor/registerDefaultLandingBlocks';
registerDefaultLandingBlocks();

// ============================================================
// Load independently-built modules, THEN mount the app
// ============================================================
(async () => {
    await loadModules(router);
    app.mount('#polycms-admin-app');
})();
