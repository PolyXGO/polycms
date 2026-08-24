import { defineStore } from 'pinia';
import axios from 'axios';

interface CartItem {
    id?: number; // cart_item_id from server
    product_id: number;
    variant_id?: number | null;
    name: string;
    price: number;
    original_price?: number;
    offer_discount?: number;
    offer_label?: string;
    offer_type?: string;
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
    max_per_order?: number | null;
    // stock context
    stock_error?: string;
    metadata?: Record<string, any>;
}

interface CartTotals {
    subtotal: number;
    discount: number;
    total_volume_discount?: number;
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
                        price: parseFloat(item.price || item.unit_price || 0),
                        original_price: item.original_price ? parseFloat(item.original_price) : (item.metadata?.original_price ? parseFloat(item.metadata.original_price) : undefined),
                        offer_discount: item.offer_discount ? parseFloat(item.offer_discount) : (item.metadata?.total_offer_discount ? parseFloat(item.metadata.total_offer_discount) : undefined),
                        offer_label: item.offer_label || item.metadata?.offer_label,
                        offer_type: item.offer_type || item.metadata?.offer_type,
                        quantity: item.quantity,
                        max_per_order: item.product?.max_per_order || item.max_per_order || null,
                        image_url: item.product?.media?.[0]?.url || item.image_url,
                        slug: item.product?.slug || item.slug,
                        sku: item.sku,
                        variant_label: item.variant?.display_name || item.variant_label,
                        service_id: item.service_id,
                        product_type: item.product?.type || item.product_type,
                        metadata: item.metadata,
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
                    if (this.items[index]) {
                        this.items[index].stock_error = '';
                    }
                } catch (error: any) {
                    if (error.response?.status === 422) {
                        const errMsg = error.response.data.message || 'Stock limit reached';
                        if (this.items[index]) {
                            this.items[index].stock_error = errMsg;
                        }
                        throw new Error(errMsg);
                    }
                }
            } else if (item) {
                if (item.max_per_order && quantity > item.max_per_order) {
                    const errMsg = `You can only purchase a maximum of ${item.max_per_order} units per order.`;
                    this.items[index].stock_error = errMsg;
                    throw new Error(errMsg);
                } else {
                    this.items[index].stock_error = '';
                }
            }

            if (this.items[index]) {
                this.items[index].quantity = quantity;
            }
            saveCartToStorage(this.items, this.couponCodes);
            this.calculateTotals();
        },

        async applyCoupon(code: string) {
            if (this.couponCodes.includes(code)) return;

            this.couponCodes.push(code);
            await this.calculateTotals();

            const isValid = this.totals.applied_coupons?.some(c => c.code === code);

            if (!isValid) {
                const errorMsg = this.couponError || 'Invalid coupon code';
                this.couponCodes = this.couponCodes.filter(c => c !== code);
                await this.calculateTotals();
                this.couponError = errorMsg;
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
            return (this.totals.applied_coupons || []).some((c: any) => c.code?.toUpperCase() === code?.toUpperCase());
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
                                price: parseFloat(updated.price !== undefined ? updated.price : item.price),
                                original_price: updated.original_price !== undefined && updated.original_price !== null ? parseFloat(updated.original_price) : undefined,
                                offer_discount: updated.offer_discount ? parseFloat(updated.offer_discount) : 0,
                                offer_label: updated.offer_label || null,
                                offer_type: updated.offer_type || null,
                                slug: updated.slug || item.slug,
                                permalink: updated.permalink || item.permalink,
                                metadata: updated.metadata || item.metadata,
                            };
                        }
                        return item;
                    });
                    saveCartToStorage(this.items, this.couponCodes);
                }

                if (response.data.applied_coupons && Array.isArray(response.data.applied_coupons)) {
                    this.couponCodes = response.data.applied_coupons.map((c: any) => c.code);
                } else if (response.data.coupon_error) {
                    this.couponCodes = [];
                }
                saveCartToStorage(this.items, this.couponCodes);

                if (response.data.coupon_error) {
                    this.couponError = response.data.coupon_error;
                }
            } catch (error) {
                console.error('Error calculating totals', error);
            } finally {
                this.loading = false;
            }
        },

        async updateServicePackage(index: number, serviceId: number) {
            const item = this.items[index];
            if (!item) return;

            if (item.id) {
                this.loading = true;
                try {
                    const { data } = await axios.put(`/api/v1/cart/items/${item.id}`, {
                        quantity: item.quantity,
                        service_id: serviceId
                    });
                    await this.syncCartFromResponse(data);
                } catch (error: any) {
                    console.error('Failed to update service package on server', error);
                } finally {
                    this.loading = false;
                }
            } else {
                // Offline fallback
                item.service_id = serviceId;
                saveCartToStorage(this.items, this.couponCodes);
            }
            await this.calculateTotals();
        },

        async syncCartFromResponse(data: any) {
            // The API returns items directly at the root along with subtotal
            if (data.items && Array.isArray(data.items)) {
                this.items = data.items.map((item: any) => ({
                    id: item.id,
                    product_id: item.product_id,
                    variant_id: item.variant_id,
                    name: item.name || item.product?.name || '',
                    price: parseFloat(item.price || item.unit_price || 0),
                    original_price: item.original_price ? parseFloat(item.original_price) : (item.metadata?.original_price ? parseFloat(item.metadata.original_price) : undefined),
                    offer_discount: item.offer_discount ? parseFloat(item.offer_discount) : (item.metadata?.total_offer_discount ? parseFloat(item.metadata.total_offer_discount) : undefined),
                    offer_label: item.offer_label || item.metadata?.offer_label,
                    offer_type: item.offer_type || item.metadata?.offer_type,
                    quantity: item.quantity,
                    image_url: item.image_url || item.product?.media?.[0]?.url,
                    slug: item.slug || item.product?.slug,
                    variant_label: item.variant_name || item.variant?.display_name,
                    permalink: item.frontend_url || item.permalink,
                    sku: item.sku,
                    service_id: item.service_id,
                    service_name: item.service_name,
                    metadata: item.metadata,
                }));
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
