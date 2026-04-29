<template>
    <div class="topbar-settings-dropdown relative" v-click-outside="closeDropdown">
        <button 
            @click="toggleDropdown" 
            class="topbar-button language-btn"
            :class="{ 'active': isOpen }"
            :title="$t('Switch Language')"
        >
            <span class="topbar-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <span class="ml-1 text-xs font-semibold uppercase">{{ currentCode }}</span>
        </button>

        <transition name="settings-fade">
            <div v-if="isOpen" class="settings-dropdown-content language-dropdown">
                <div class="settings-body py-1">
                    <button 
                        v-for="lang in languages" 
                        :key="lang.code"
                        @click="switchLanguage(lang.code)"
                        class="w-full text-left px-4 py-2 text-sm text-gray-300 hover:bg-white/5 hover:text-blue-400 flex items-center justify-between transition-colors"
                        :class="{'text-blue-400 bg-white/5': currentCode === lang.code}"
                    >
                        <span class="flex items-center gap-2">
                             <span v-if="lang.flag" class="text-base">{{ lang.flag }}</span>
                             <span>{{ lang.name }}</span>
                        </span>
                        <svg v-if="currentCode === lang.code" class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>
                    
                    <div v-if="languages.length === 0" class="px-4 py-2 text-sm text-gray-500">
                        {{ $t('No languages available') }}
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useTranslation } from '../composables/useTranslation';

const { t } = useTranslation();
const $t = (key: string) => t(key);

interface Language {
    id: number;
    code: string;
    name: string;
    native_name: string;
    direction: string;
    flag: string | null;
    is_active: boolean;
    is_default: boolean;
}

const isOpen = ref(false);
const languages = ref<Language[]>([]);
const currentCode = ref<string>('en'); // Default fallback

// Directive for clicking outside
const vClickOutside = {
    mounted(el: any, binding: any) {
        el._clickOutside = (event: Event) => {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value(event);
            }
        };
        document.addEventListener('click', el._clickOutside);
    },
    unmounted(el: any) {
        document.removeEventListener('click', el._clickOutside);
    },
};

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

const closeDropdown = () => {
    isOpen.value = false;
};

const loadLanguages = async () => {
    try {
        const response = await axios.get('/api/v1/languages');
        if (response.data && response.data.data) {
            // Only show active languages
            languages.value = response.data.data.filter((l: Language) => l.is_active);
        }
    } catch (error) {
        console.error('Failed to load languages:', error);
    }
};

const loadCurrentLanguage = async () => {
    try {
        const response = await axios.get('/api/v1/settings/site_language');
        if (response.data && response.data.data) {
            currentCode.value = response.data.data.value || 'en';
        }
    } catch (error) {
        console.error('Failed to load current language setting:', error);
        // Try fallback to active default language from list logic if needed
    }
};

const switchLanguage = async (code: string) => {
    if (code === currentCode.value) {
        closeDropdown();
        return;
    }

    try {
        // Update global setting
        await axios.put('/api/v1/settings', {
            settings: {
                site_language: code
            }
        });

        // Update local state immediately for UI feedback
        currentCode.value = code;
        closeDropdown();

        // Reload page to apply language changes globally (backend session/locale)
        window.location.reload();
    } catch (error) {
        console.error('Failed to switch language:', error);
        alert($t('Failed to switch language. Please try again.'));
    }
};

onMounted(() => {
    loadLanguages();
    loadCurrentLanguage();
});
</script>

<style scoped>
.topbar-settings-dropdown {
    position: relative;
    display: inline-flex;
    align-items: center;
    height: 32px;
}

.language-btn {
    padding: 0 8px !important;
    width: auto !important;
    justify-content: center !important;
    margin-left: 2px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    border-radius: 3px;
    transition: all 0.15s ease;
    color: #d1d5db;
}

.language-btn:hover, .language-btn.active {
    color: #60a5fa !important;
    background: rgba(255, 255, 255, 0.1);
}

.topbar-icon svg {
    width: 14px;
    height: 14px;
}

.settings-dropdown-content {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 4px;
    background: #1f2937;
    min-width: 200px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 6px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
    z-index: 100001;
    overflow: hidden;
}

.settings-header {
    padding: 8px 12px;
    font-weight: 600;
    color: #9ca3af;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    text-transform: uppercase;
    letter-spacing: 0.025em;
    font-size: 11px;
}

.settings-fade-enter-active,
.settings-fade-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.settings-fade-enter-from,
.settings-fade-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>
