import { createPinia } from 'pinia';
import { useCartStore } from './Stores/cartStore';
import { createApp, h } from 'vue';
import MiniCart from './Components/MiniCart.vue';
import { useTranslation } from './admin/composables/useTranslation';
import { useCurrency } from './Composables/useCurrency';

// Initialize Pinia
const pinia = createPinia();

// Check if Pinia is already active (in case of double load), if not, create independent instance
// Note: In a hybrid app, this might create a separate store instance from the main Vue app if mapped to different roots.
// Since Cart page is a full reload/Inertia page, local storage persistence bridges the gap.

const cart = useCartStore(pinia);

// Expose addToCart to window
(window as any).addToCart = async (item: any) => {
    console.log('Adding to cart (Bridge):', item);
    try {
        await cart.addItem(item);
        
        // Check global config for redirection
        if ((window as any).AppConfig?.redirectCartAfterAdd !== false) {
            window.location.href = '/cart';
        } else {
            console.log('Item added successfully. Redirect disabled by settings.');
            // Re-mount mini cart to ensure it updates instantly with the new item
            mountMiniCart();
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        alert('Failed to add item to cart');
        throw error;
    }
};

// Helper for buttons with loading state
(window as any).buyNow = async (item: any, event: Event) => {
    event.preventDefault();
    const btn = event.currentTarget as HTMLButtonElement | HTMLAnchorElement;
    if (!btn) return;

    const originalText = btn.innerHTML;
    const originalPointerEvents = btn.style.pointerEvents;
    
    // Disable button
    btn.style.pointerEvents = 'none';
    btn.classList.add('opacity-75', 'cursor-not-allowed');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';

    try {
        await (window as any).addToCart(item);
        
        // If redirect was disabled, the page won't reload. We need to revert the button state
        // to show success, then back to normal.
        if ((window as any).AppConfig?.redirectCartAfterAdd === false) {
            btn.innerHTML = '<i class="fas fa-check mr-2"></i> Added!';
            btn.classList.add('bg-green-600', 'hover:bg-green-700'); 
            
            setTimeout(() => {
                btn.style.pointerEvents = originalPointerEvents;
                btn.classList.remove('opacity-75', 'cursor-not-allowed', 'bg-green-600', 'hover:bg-green-700');
                btn.innerHTML = originalText;
            }, 2000);
        }
    } catch (e) {
        // Reset button on error (addToCart throws)
        btn.style.pointerEvents = originalPointerEvents;
        btn.classList.remove('opacity-75', 'cursor-not-allowed');
        btn.innerHTML = originalText;
    }
};

// Mount MiniCart if root exists
const mountMiniCart = () => {
    const root = document.getElementById('mini-cart-root');
    if (root) {
        const app = createApp({
            render: () => h(MiniCart)
        });
        
        app.use(pinia);
        
        // Mock some required globals if MiniCart uses them
        // Note: useTranslation and useCurrency inside MiniCart should work if they use the same pinia/settings source
        
        app.mount(root);
        console.log('MiniCart mounted to #mini-cart-root');
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mountMiniCart);
} else {
    mountMiniCart();
}

console.log('E-commerce Bridge Loaded');
