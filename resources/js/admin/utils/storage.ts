/**
 * PolyCMS Storage Utility
 * Provides a standardized way to interact with localStorage
 */

const PREFIX = 'polycms_';

export class Storage {
    /**
     * Get item from storage
     */
    static get<T>(key: string): T | null;
    static get<T>(key: string, defaultValue: T): T;
    static get<T>(key: string, defaultValue: T | null = null): T | null {
        try {
            const item = localStorage.getItem(PREFIX + key);
            if (item === null) return defaultValue;
            
            // Try to parse as JSON, if it fails return as string
            try {
                return JSON.parse(item) as T;
            } catch {
                return item as unknown as T;
            }
        } catch (e) {
            console.error('Error getting from storage:', e);
            return defaultValue;
        }
    }

    /**
     * Set item in storage
     */
    static set(key: string, value: any): void {
        try {
            const val = typeof value === 'string' ? value : JSON.stringify(value);
            localStorage.setItem(PREFIX + key, val);
        } catch (e) {
            console.error('Error setting in storage:', e);
        }
    }

    /**
     * Remove item from storage
     */
    static remove(key: string): void {
        try {
            localStorage.removeItem(PREFIX + key);
        } catch (e) {
            console.error('Error removing from storage:', e);
        }
    }

    /**
     * Clear all PolyCMS related items
     */
    static clear(): void {
        try {
            Object.keys(localStorage).forEach(key => {
                if (key.startsWith(PREFIX)) {
                    localStorage.removeItem(key);
                }
            });
        } catch (e) {
            console.error('Error clearing storage:', e);
        }
    }
}
