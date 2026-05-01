import { ref, reactive } from 'vue';
import axios from 'axios';

interface Translations {
    [key: string]: string;
}

// Global translations state
const translations = reactive<Translations>({});
let loading = false;
let loaded = false;

/**
 * Load translations from API
 */
export async function loadTranslations(): Promise<void> {
    if (loaded || loading) {
        return;
    }
    
    loading = true;
    try {
        const response = await axios.get('/api/v1/translations');
        if (response.data.success && response.data.data?.translations) {
            Object.assign(translations, response.data.data.translations);
            loaded = true;
        }
    } catch (error) {
        console.error('Error loading translations:', error);
    } finally {
        loading = false;
    }
}

/**
 * Translate function for Vue components
 * @param key Translation key
 * @param fallback Fallback text if translation not found
 */
export function t(key: string, fallback?: string): string {
    return translations[key] || fallback || key;
}

/**
 * Composable for translations
 */
export function useTranslation() {
    // Load translations on first use
    if (!loaded && !loading) {
        loadTranslations();
    }
    
    return {
        t,
        translations,
        loading: ref(loading),
        loadTranslations,
    };
}

// Pre-load translations
loadTranslations();

