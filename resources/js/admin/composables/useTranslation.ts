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
        const response = await axios.get(`/api/v1/translations?t=${new Date().getTime()}`);
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
 * @param fallbackOrParams Fallback text OR parameters object
 * @param params Parameters object when fallback text is provided
 */
export function t(key: string, fallbackOrParams?: string | Record<string, any>, params?: Record<string, any>): string {
    let result = translations[key];
    let replacements: Record<string, any> | undefined;

    if (!result) {
        if (typeof fallbackOrParams === 'string') {
            result = fallbackOrParams;
            replacements = params;
        } else if (typeof fallbackOrParams === 'object' && fallbackOrParams !== null) {
            result = key;
            replacements = fallbackOrParams;
        } else {
            result = key;
        }
    } else {
        if (typeof fallbackOrParams === 'object' && fallbackOrParams !== null) {
            replacements = fallbackOrParams;
        } else {
            replacements = params;
        }
    }

    if (replacements && typeof result === 'string') {
        Object.keys(replacements).forEach((paramKey) => {
            const val = replacements[paramKey];
            const strVal = String(val !== undefined && val !== null ? val : '');
            result = (result as string).replace(new RegExp(`\\{${paramKey}\\}`, 'g'), strVal);
            result = (result as string).replace(new RegExp(`:${paramKey}`, 'g'), strVal);
        });
    }

    return typeof result === 'string' ? result : String(result || key);
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

