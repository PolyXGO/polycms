/**
 * Permalink helper utilities for generating URLs based on permalink settings
 */

export interface PermalinkSettings {
    posts: {
        archive: string;
        single: string;
    };
    pages: {
        single: string;
    };
    products: {
        archive: string;
        single: string;
    };
    categories: {
        single: string;
    };
    product_categories: {
        single: string;
    };
    product_brands: {
        single: string;
    };
    tags: {
        post: string;
        product: string;
    };
    users: {
        single: string;
    };
}

/**
 * Get permalink settings from window object
 */
export function getPermalinkSettings(): PermalinkSettings {
    return (window as any).polycmsPermalinkSettings || {};
}

/**
 * Generate a URL based on permalink settings
 */
export function getPermalinkUrl(group: string, slug: string = '', context: string = 'single'): string {
    const settings = getPermalinkSettings();
    const segment = (settings as any)[group]?.[context] || '';
    const trimmedSlug = slug.trim().replace(/^\/+|\/+$/g, '');
    
    const parts = [segment, trimmedSlug].filter(part => part !== '');
    const path = parts.join('/');
    
    return `/${path}`;
}

/**
 * Get URL for a post category
 */
export function getCategoryUrl(slug: string): string {
    return getPermalinkUrl('categories', slug, 'single');
}

/**
 * Get URL for a product category
 */
export function getProductCategoryUrl(slug: string): string {
    return getPermalinkUrl('product_categories', slug, 'single');
}

/**
 * Get URL for a product brand
 */
export function getProductBrandUrl(slug: string): string {
    return getPermalinkUrl('product_brands', slug, 'single');
}

/**
 * Get URL for a post tag
 */
export function getPostTagUrl(slug: string): string {
    return getPermalinkUrl('tags', slug, 'post');
}

/**
 * Get URL for a product tag
 */
export function getProductTagUrl(slug: string): string {
    return getPermalinkUrl('tags', slug, 'product');
}

/**
 * Get URL for an author archive
 */
export function getAuthorUrl(slug: string): string {
    return getPermalinkUrl('users', slug, 'single');
}

/**
 * Get URL for a post
 */
export function getPostUrl(slug: string): string {
    return getPermalinkUrl('posts', slug, 'single');
}

/**
 * Get URL for a product
 */
export function getProductUrl(slug: string): string {
    return getPermalinkUrl('products', slug, 'single');
}

/**
 * Get URL for a page
 */
export function getPageUrl(slug: string): string {
    return getPermalinkUrl('pages', slug, 'single');
}
