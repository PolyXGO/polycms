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
    projects: {
        archive: string;
        single: string;
        category: string;
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
    projects: {
        archive: 'projects',
        single: 'projects',
        category: 'project-categories',
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
        projects: {
            archive: resolveSegment(data, 'permalink_projects_archive', defaults.projects.archive),
            single: resolveSegment(data, 'permalink_projects_single', defaults.projects.single),
            category: resolveSegment(data, 'permalink_project_category_base', defaults.projects.category),
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

const buildUrl = (type: 'post' | 'page' | 'product' | 'project' | 'project_category', slug: string, locale?: string): string => {
    if (!slug) {
        return '';
    }

    const baseUrl = window.location.origin;
    const current = structure.value;

    let path = '';
    switch (type) {
        case 'post': {
            const prefix = current.posts.single || defaults.posts.single;
            path = [prefix, slug].filter(Boolean).join('/');
            break;
        }
        case 'page': {
            const prefix = current.pages.single;
            path = prefix ? `${prefix}/${slug}` : slug;
            break;
        }
        case 'product': {
            const prefix = current.products.single || defaults.products.single;
            path = [prefix, slug].filter(Boolean).join('/');
            break;
        }
        case 'project': {
            const prefix = current.projects.single || defaults.projects.single;
            path = [prefix, slug].filter(Boolean).join('/');
            break;
        }
        case 'project_category': {
            const prefix = current.projects.category || defaults.projects.category;
            path = [prefix, slug].filter(Boolean).join('/');
            break;
        }
        default:
            path = slug;
    }

    if (locale && locale !== 'en') {
        return `${baseUrl}/${locale}/${path}`;
    }

    return `${baseUrl}/${path}`;
};

export function usePermalinkSettings() {
    return {
        structure,
        defaults,
        ensureStructureLoaded,
        buildUrl,
    };
}
