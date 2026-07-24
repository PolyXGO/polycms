import { defineStore } from 'pinia';
import { Storage } from '../utils';

type ThemeMode = 'light' | 'dark' | 'system';

interface ThemeState {
    mode: ThemeMode;
    isDark: boolean;
}

export const useThemeStore = defineStore('theme', {
    state: (): ThemeState => ({
        mode: (Storage.get<ThemeMode>('theme_mode') || 'system'),
        isDark: false,
    }),

    actions: {
        /**
         * Initialize theme from localStorage and system preference
         */
        init() {
            this.updateTheme();
            
            // Listen for system theme changes
            if (window.matchMedia) {
                const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
                mediaQuery.addEventListener('change', () => {
                    if (this.mode === 'system') {
                        this.updateTheme();
                    }
                });
            }
        },

        /**
         * Set theme mode
         */
        setMode(mode: ThemeMode) {
            this.mode = mode;
            Storage.set('theme_mode', mode);
            this.updateTheme();
        },

        /**
         * Toggle between light and dark
         */
        toggle() {
            if (this.mode === 'dark') {
                this.setMode('light');
            } else {
                this.setMode('dark');
            }
        },

        /**
         * Update theme based on current mode
         */
        updateTheme() {
            let shouldBeDark = false;

            if (this.mode === 'system') {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    shouldBeDark = true;
                }
            } else {
                shouldBeDark = this.mode === 'dark';
            }

            this.isDark = shouldBeDark;

            // Apply class to HTML element
            if (shouldBeDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        },
    },
});
