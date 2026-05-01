import axios from 'axios';
import type { Router, RouteRecordRaw } from 'vue-router';

/**
 * Module manifest returned from backend API
 */
interface ModuleManifest {
    key: string;
    name: string;
    admin_entry?: string;
}

/**
 * Load a single script tag dynamically
 */
function loadScript(src: string): Promise<void> {
    return new Promise((resolve) => {
        const script = document.createElement('script');
        script.src = src;
        script.onload = () => resolve();
        script.onerror = () => {
            console.warn(`[PolyCMS] Failed to load module script: ${src}`);
            resolve(); // Don't block app for failed modules
        };
        document.head.appendChild(script);
    });
}

/**
 * Query the backend for active modules with frontend entry points,
 * then inject their built JS bundles into the page.
 *
 * This must be called AFTER the SDK is exposed on `window` and
 * BEFORE the Vue app is mounted, so that module routes are available
 * during the initial navigation.
 */
export async function loadModules(router: Router): Promise<void> {
    try {
        const token = localStorage.getItem('auth_token');
        const headers: Record<string, string> = { Accept: 'application/json' };
        if (token) {
            headers.Authorization = `Bearer ${token}`;
        }

        const response = await axios.get('/api/v1/modules/active-frontend', { headers });
        const modules: ModuleManifest[] = response.data?.data || [];

        if (modules.length === 0) return;

        const promises = modules.map((mod) => {
            if (!mod.admin_entry) return Promise.resolve();
            // key = "Polyx.BannerSlider" → path = "/modules/Polyx/BannerSlider/dist/admin.js"
            const src = `/modules/${mod.key.replace('.', '/')}/${mod.admin_entry}`;
            return loadScript(src);
        });

        await Promise.allSettled(promises);
    } catch (e) {
        // Silently fail — modules are optional enhancements
        console.warn('[PolyCMS] Module loader: could not fetch active modules', e);
    }
}

/**
 * Create the registerRoutes helper that modules call to inject their
 * routes into the admin layout.
 */
export function createRouteRegistrar(router: Router) {
    return (routes: RouteRecordRaw[]) => {
        routes.forEach((route) => {
            // All module routes are children of the '/admin' layout route
            router.addRoute('admin', route);
        });
    };
}
