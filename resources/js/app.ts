import '../css/app.css';
import './bootstrap';

import { createPinia } from 'pinia';
import { useCurrency } from './Composables/useCurrency';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, DefineComponent, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue)
            .mount(el);

        // Expose cart helper to window for Blade components
        (window as any).addToCart = async (item: any) => {
            const module = await import('./Stores/cartStore');
            const cart = module.useCartStore(pinia);
            cart.addItem(item);
            // Redirect to checkout
            window.location.href = '/checkout';
        };

        // Expose currency helper globally
        (window as any).PolyCMS = (window as any).PolyCMS || {};
        (window as any).PolyCMS.formatCurrency = (amount: any, targetCode?: string) => {
            const { formatCurrency } = useCurrency();
            return formatCurrency(amount, targetCode);
        };
    },
    progress: {
        color: '#4B5563',
    },
});
