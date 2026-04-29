import { ref } from 'vue';
import axios from 'axios';
import { useSlugify } from './useSlugify';

interface PermalinkStructure {
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
    tags: {
        post: string;
        product: string;
    };
    users: {
        single: string;
    };
}

const defaults: PermalinkStructure = {
    posts: {
        archive: 'posts',
        single: 'posts',
    },
    pages: {
        single: '',
    },
    products: {
        archive: 'products',
        single: 'products',
    },
    categories: {
        single: 'categories',
    },
    tags: {
        post: 'tags',
        product: 'product-tags',
    },
    users: {
        single: 'author',
    },
};

const structure = ref<PermalinkStructure>({ ...defaults });
let isLoaded = false;
let loadingPromise: Promise<void> | null = null;

const { slugify } = useSlugify();

const sanitizeSegment = (value: string | null | undefined, allowEmpty = false): string => {
    const trimmed = (value ?? '').trim().replace(/^\/+|\/+$/g, '');

    if (!trimmed) {
        return allowEmpty ? '' : '';
    }

    const segments = trimmed
        .split('/')
        .filter(Boolean)
        .map((segment) => slugify(segment));

    const sanitized = segments.filter(Boolean).join('/');

    if (!sanitized) {
        return allowEmpty ? '' : '';
    }

    return sanitized;
};

const resolveSegment = (
    group: Record<string, any>,
    key: string,
    fallback: string,
    allowEmpty = false
): string => {
    const definition = group[key] ?? {};
    const value = sanitizeSegment(definition.value ?? definition.default ?? fallback, allowEmpty);

    if (value === '' && !allowEmpty) {
        return fallback;
    }

    return value;
};

const parseResponse = (data: Record<string, any>): PermalinkStructure => {
    return {
        posts: {
            archive: resolveSegment(data, 'permalink_posts_archive', defaults.posts.archive),
            single: resolveSegment(data, 'permalink_posts_single', defaults.posts.single),
        },
        pages: {
            single: resolveSegment(data, 'permalink_pages_single', defaults.pages.single, true),
        },
        products: {
            archive: resolveSegment(data, 'permalink_products_archive', defaults.products.archive),
            single: resolveSegment(data, 'permalink_products_single', defaults.products.single),
        },
        categories: {
            single: resolveSegment(data, 'permalink_category_base', defaults.categories.single),
        },
        tags: {
            post: resolveSegment(data, 'permalink_post_tag_base', defaults.tags.post),
            product: resolveSegment(data, 'permalink_product_tag_base', defaults.tags.product),
        },
        users: {
            single: resolveSegment(data, 'permalink_user_base', defaults.users.single),
        },
    };
};

const ensureStructureLoaded = async (force = false): Promise<void> => {
    if (isLoaded && !force) {
        return;
    }

    if (loadingPromise) {
        return loadingPromise;
    }

    loadingPromise = axios
        .get('/api/v1/settings/group/permalinks')
        .then((response) => {
            const payload = response.data?.data ?? {};
            structure.value = parseResponse(payload);
            isLoaded = true;
        })
        .catch(() => {
            structure.value = { ...defaults };
        })
        .finally(() => {
            loadingPromise = null;
        });

    return loadingPromise;
};

const buildUrl = (type: 'post' | 'page' | 'product', slug: string): string => {
    if (!slug) {
        return '';
    }

    const baseUrl = window.location.origin;
    const current = structure.value;

    switch (type) {
        case 'post': {
            const prefix = current.posts.single || defaults.posts.single;
            return `${baseUrl}/${[prefix, slug].filter(Boolean).join('/')}`;
        }
        case 'page': {
            const prefix = current.pages.single;
            return prefix ? `${baseUrl}/${prefix}/${slug}` : `${baseUrl}/${slug}`;
        }
        case 'product': {
            const prefix = current.products.single || defaults.products.single;
            return `${baseUrl}/${[prefix, slug].filter(Boolean).join('/')}`;
        }
        default:
            return `${baseUrl}/${slug}`;
    }
};

export function usePermalinkSettings() {
    return {
        structure,
        defaults,
        ensureStructureLoaded,
        buildUrl,
    };
}

