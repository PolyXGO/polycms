import { defineStore } from 'pinia';
import axios from 'axios';

interface WishlistItem {
    id: number;
    product_id: number;
    variant_id?: number | null;
    product?: {
        id: number;
        name: string;
        slug: string;
        price: number;
        sale_price?: number;
        media?: Array<{ url: string }>;
    };
    created_at: string;
}

export const useWishlistStore = defineStore('wishlist', {
    state: () => ({
        items: [] as WishlistItem[],
        loading: false,
        checkedProducts: new Map<number, boolean>(),
    }),

    getters: {
        count: (state) => state.items.length,
        isWishlisted: (state) => (productId: number): boolean => {
            return state.checkedProducts.get(productId) ?? false;
        },
    },

    actions: {
        async loadWishlist() {
            this.loading = true;
            try {
                const { data } = await axios.get('/api/v1/wishlist');
                this.items = data.data || [];
                // Update checked products map
                this.items.forEach(item => {
                    this.checkedProducts.set(item.product_id, true);
                });
            } catch (e) {
                // Not logged in or network error
            } finally {
                this.loading = false;
            }
        },

        async toggle(productId: number, variantId?: number | null) {
            try {
                const { data } = await axios.post('/api/v1/wishlist/toggle', {
                    product_id: productId,
                    variant_id: variantId || null,
                });

                this.checkedProducts.set(productId, data.wishlisted);

                if (data.wishlisted) {
                    // Optimistic: add placeholder
                    if (!this.items.find(i => i.product_id === productId)) {
                        this.items.push({
                            id: 0,
                            product_id: productId,
                            variant_id: variantId,
                            created_at: new Date().toISOString(),
                        });
                    }
                } else {
                    this.items = this.items.filter(i => i.product_id !== productId);
                }

                return data.wishlisted;
            } catch (e: any) {
                if (e.response?.status === 401) {
                    // Redirect to login
                    window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);
                    return false;
                }
                throw e;
            }
        },

        async checkProduct(productId: number) {
            if (this.checkedProducts.has(productId)) return;
            try {
                const { data } = await axios.get(`/api/v1/wishlist/check/${productId}`);
                this.checkedProducts.set(productId, data.wishlisted);
            } catch (e) {
                this.checkedProducts.set(productId, false);
            }
        },
    },
});
