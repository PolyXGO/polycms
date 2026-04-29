import { defineStore } from 'pinia';
import axios from 'axios';

interface CartItem {
    id?: number; // cart_item_id from server
    product_id: number;
    variant_id?: number | null;
    name: string;
    price: number;
    quantity: number;
    image_url?: string;
    slug?: string;
    permalink?: string;
    sku?: string;
    variant_label?: string;
    // service related
    service_id?: number | null;
    service_name?: string;
    billing_cycle?: string;
    // product type
    product_type?: string;
    // stock context
    stock_error?: string;
}

interface CartTotals {
    subtotal: number;
    discount: number;
    tax: number;
    total: number;
    discount_code?: string;
    applied_coupons?: Array<{
        code: string;
        discount: number;
        title?: string;
        description?: string;
        is_exclusive?: boolean;
    }>;
    coupon_error?: string;
}

const CART_STORAGE_KEY = 'polycms_cart';

// Fallback: Load cart from localStorage (for guest offline caching)
const loadCartFromStorage = (): { items: CartItem[]; couponCodes: string[] } => {
    try {
        const stored = localStorage.getItem(CART_STORAGE_KEY);
        if (stored) {
            const parsed = JSON.parse(stored);
            let codes: string[] = [];
            if (Array.isArray(parsed.couponCodes)) {
                codes = parsed.couponCodes;
            } else if (parsed.couponCode && typeof parsed.couponCode === 'string') {
                codes = [parsed.couponCode];
            }

            return {
                items: parsed.items || [],
                couponCodes: codes,
            };
        }
    } catch (e) {
        console.error('Error loading cart from storage', e);
    }
    return { items: [], couponCodes: [] };
};

// Save cart to localStorage
const saveCartToStorage = (items: CartItem[], couponCodes: string[]) => {
    try {
        localStorage.setItem(CART_STORAGE_KEY, JSON.stringify({ items, couponCodes }));
    } catch (e) {
        console.error('Error saving cart to storage', e);
    }
};

export const useCartStore = defineStore('cart', {
    state: () => {
        const stored = loadCartFromStorage();
        return {
            items: stored.items as CartItem[],
            couponCodes: stored.couponCodes as string[],
            totals: {
                subtotal: 0,
                discount: 0,
                tax: 0,
                total: 0,
                applied_coupons: []
            } as CartTotals,
            couponError: null as string | null,
            loading: false,
            synced: false, // Whether initial sync with server has happened
        };
    },

    getters: {
        itemCount: (state) => state.items.reduce((sum, item) => sum + item.quantity, 0),
        isEmpty: (state) => state.items.length === 0,
    },

    actions: {
        /**
         * Sync cart with server on page load
         * Server is the source of truth for pricing
         */
        async syncWithServer() {
            if (this.synced) return;
            try {
                const { data } = await axios.get('/api/v1/cart');
                if (data.items && data.items.length > 0) {
                    // Server has items — use server state
                    this.items = data.items.map((item: any) => ({
                        id: item.id,
                        product_id: item.product_id,
                        variant_id: item.variant_id,
                        name: item.product?.name || item.name || '',
                        price: parseFloat(item.unit_price || item.price || 0),
                        quantity: item.quantity,
                        image_url: item.product?.media?.[0]?.url || item.image_url,
                        slug: item.product?.slug || item.slug,
                        sku: item.sku,
                        variant_label: item.variant?.display_name || item.variant_label,
                        service_id: item.service_id,
                        product_type: item.product?.type || item.product_type,
                    }));
                    this.totals.subtotal = parseFloat(data.subtotal || 0);
                }
                saveCartToStorage(this.items, this.couponCodes);
            } catch (e) {
                // Offline or no session — use localStorage fallback
                console.warn('Cart sync failed, using local storage', e);
            }
            this.synced = true;
        },

        /**
         * Add item to cart — calls server API first, falls back to local
         */
        async addItem(item: CartItem) {
            this.loading = true;
            try {
                const { data } = await axios.post('/api/v1/cart/items', {
                    product_id: item.product_id,
                    variant_id: item.variant_id || null,
                    quantity: item.quantity || 1,
                    service_id: item.service_id || null,
                });

                // Replace local state with server response
                await this.syncCartFromResponse(data);
            } catch (error: any) {
                // Stock error from server
                if (error.response?.status === 422) {
                    const errData = error.response.data;
                    throw new Error(errData.message || 'Could not add item to cart');
                }

                // Fallback: add locally
                const existingIndex = this.items.findIndex(
                    (i) => i.product_id === item.product_id
                        && i.variant_id === item.variant_id
                        && i.service_id === item.service_id
                );

                if (existingIndex !== -1) {
                    this.items[existingIndex].quantity += item.quantity;
                    this.items[existingIndex].price = item.price;
                } else {
                    this.items.push(item);
                }
                saveCartToStorage(this.items, this.couponCodes);
            } finally {
                this.loading = false;
            }
            await this.calculateTotals();
        },

        async removeItem(indexOrId: number) {
            const item = this.items[indexOrId];
            if (item?.id) {
                try {
                    await axios.delete(`/api/v1/cart/items/${item.id}`);
                } catch (e) { /* silent */ }
            }
            this.items.splice(indexOrId, 1);
            saveCartToStorage(this.items, this.couponCodes);
            this.calculateTotals();
        },

        async updateQuantity(index: number, quantity: number) {
            if (quantity <= 0) {
                this.removeItem(index);
                return;
            }

            const item = this.items[index];
            if (item?.id) {
                try {
                    await axios.put(`/api/v1/cart/items/${item.id}`, { quantity });
                } catch (error: any) {
                    if (error.response?.status === 422) {
                        throw new Error(error.response.data.message || 'Stock limit reached');
                    }
                }
            }
            this.items[index].quantity = quantity;
            saveCartToStorage(this.items, this.couponCodes);
            this.calculateTotals();
        },

        async applyCoupon(code: string) {
            if (this.couponCodes.includes(code)) return;

            this.couponCodes.push(code);
            await this.calculateTotals();

            const isValid = this.totals.applied_coupons?.some(c => c.code === code);

            if (!isValid) {
                this.couponCodes = this.couponCodes.filter(c => c !== code);
                const errorMsg = this.couponError || 'Invalid coupon code';
                await this.calculateTotals();
                throw new Error(errorMsg);
            }

            saveCartToStorage(this.items, this.couponCodes);
        },

        async removeCoupon(code?: string) {
            if (code) {
                this.couponCodes = this.couponCodes.filter(c => c !== code);
            } else {
                this.couponCodes = [];
            }
            saveCartToStorage(this.items, this.couponCodes);
            await this.calculateTotals();
        },

        hasCoupon(code: string): boolean {
            return this.couponCodes.includes(code);
        },

        async calculateTotals() {
            if (this.items.length === 0) {
                this.couponCodes = [];
                this.totals = { subtotal: 0, discount: 0, tax: 0, total: 0 };
                saveCartToStorage(this.items, this.couponCodes);
                return;
            }

            this.loading = true;
            this.couponError = null;

            try {
                const response = await axios.post('/api/v1/checkout/calculate', {
                    items: this.items,
                    coupon_codes: this.couponCodes,
                });

                this.totals = response.data;

                if (response.data.items) {
                    this.items = this.items.map((item, index) => {
                        const updated = response.data.items[index];
                        if (updated) {
                            return {
                                ...item,
                                slug: updated.slug || item.slug,
                                permalink: updated.permalink || item.permalink,
                            };
                        }
                        return item;
                    });
                    saveCartToStorage(this.items, this.couponCodes);
                }

                if (response.data.coupon_error) {
                    this.couponError = response.data.coupon_error;
                }
            } catch (error) {
                console.error('Error calculating totals', error);
            } finally {
                this.loading = false;
            }
        },

        async syncCartFromResponse(data: any) {
            if (data.cart) {
                // Full cart response from add/update
                const cartData = data.cart;
                if (cartData.items?.length > 0) {
                    this.items = cartData.items.map((item: any) => ({
                        id: item.id,
                        product_id: item.product_id,
                        variant_id: item.variant_id,
                        name: item.product?.name || '',
                        price: parseFloat(item.unit_price || 0),
                        quantity: item.quantity,
                        image_url: item.product?.media?.[0]?.url,
                        slug: item.product?.slug,
                        variant_label: item.variant?.display_name,
                    }));
                }
            }
            saveCartToStorage(this.items, this.couponCodes);
        },

        async clear() {
            try {
                await axios.delete('/api/v1/cart');
            } catch (e) { /* silent */ }
            this.items = [];
            this.couponCodes = [];
            this.totals = { subtotal: 0, discount: 0, tax: 0, total: 0 };
            localStorage.removeItem(CART_STORAGE_KEY);
        }
    },
});
