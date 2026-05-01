<template>
    <div v-if="form" class="space-y-5">
        <section class="space-y-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900/40">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-200">SEO Metadata</h3>

            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Meta Title</label>
                <input
                    v-model="form.meta_title"
                    type="text"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    placeholder="Title shown in search results"
                />
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ titleLength }} / 60</p>
            </div>

            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Meta Description</label>
                <textarea
                    v-model="form.meta_description"
                    rows="3"
                    class="min-h-[110px] w-full resize-vertical rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    placeholder="Short description for SEO"
                />
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ descriptionLength }} / 160</p>
            </div>

            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Meta Keywords</label>
                <input
                    v-model="form.meta_keywords"
                    type="text"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    placeholder="Comma separated keywords"
                />
            </div>

            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Canonical URL</label>
                <input
                    v-model="canonicalUrl"
                    type="url"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    placeholder="https://example.com/preferred-url (leave empty for auto)"
                />
            </div>
        </section>

        <section class="space-y-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900/40">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-200">Content Optimization</h3>

            <div class="grid gap-3 md:grid-cols-2">
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Focus Keyword</label>
                    <input
                        v-model="focusKeyword"
                        type="text"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        placeholder="e.g. premium travel package"
                    />
                </div>

                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Primary Category</label>
                    <select
                        v-model="primaryCategoryId"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                        <option :value="null">Auto select</option>
                        <option v-for="option in selectedCategoryOptions" :key="option.id" :value="option.id">
                            {{ option.depth > 0 ? `${'— '.repeat(option.depth)}${option.name}` : option.name }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-3">
                <div class="space-y-1 md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Social Title</label>
                    <input
                        v-model="socialTitle"
                        type="text"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        placeholder="Optional override"
                    />
                </div>
                <div class="space-y-1 md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Social Description</label>
                    <input
                        v-model="socialDescription"
                        type="text"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        placeholder="Optional override"
                    />
                </div>
                <div class="space-y-1 md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Social Image URL</label>
                    <input
                        v-model="socialImage"
                        type="url"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        placeholder="https://..."
                    />
                </div>
            </div>
        </section>

        <section class="space-y-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900/40">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-200">SEO Checklist</h3>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-200">
                    Score {{ checklistScore }}/100
                </span>
            </div>

            <ul class="space-y-2">
                <li
                    v-for="item in checklistItems"
                    :key="item.key"
                    class="rounded-lg border px-3 py-2"
                    :class="statusClass(item.status)"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium">{{ item.label }}</p>
                            <p class="text-xs opacity-90">{{ item.message }}</p>
                            <p v-if="item.suggestion" class="mt-1 text-xs opacity-80">Suggestion: {{ item.suggestion }}</p>
                        </div>
                        <span class="text-xs font-semibold uppercase">{{ item.status }}</span>
                    </div>
                </li>
            </ul>
        </section>

        <section class="space-y-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900/40">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-200">Snippet Preview</h3>

            <div class="space-y-3">
                <div class="rounded-lg border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-900">
                    <p class="truncate text-xs text-emerald-700 dark:text-emerald-400">{{ previewUrl }}</p>
                    <p class="mt-1 text-base font-medium text-blue-700 dark:text-blue-400">{{ googleTitle }}</p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ googleDescription }}</p>
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                        <div class="h-32 bg-slate-100 dark:bg-slate-800">
                            <img v-if="resolvedSocialImage" :src="resolvedSocialImage" alt="Facebook preview" class="h-full w-full object-cover" />
                        </div>
                        <div class="space-y-1 p-3">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Facebook</p>
                            <p class="line-clamp-2 text-sm font-semibold text-slate-800 dark:text-slate-100">{{ resolvedSocialTitle }}</p>
                            <p class="line-clamp-2 text-xs text-slate-600 dark:text-slate-300">{{ resolvedSocialDescription }}</p>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                        <div class="h-32 bg-slate-100 dark:bg-slate-800">
                            <img v-if="resolvedSocialImage" :src="resolvedSocialImage" alt="Twitter preview" class="h-full w-full object-cover" />
                        </div>
                        <div class="space-y-1 p-3">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Twitter</p>
                            <p class="line-clamp-2 text-sm font-semibold text-slate-800 dark:text-slate-100">{{ resolvedSocialTitle }}</p>
                            <p class="line-clamp-2 text-xs text-slate-600 dark:text-slate-300">{{ resolvedSocialDescription }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup lang="ts">
import { inject, computed, ref, watch, onMounted } from 'vue';
import axios from 'axios';
import { EditorContextKey } from '../../../../editor/context';

interface CategoryOption {
    id: number;
    name: string;
    depth: number;
}

interface ChecklistItem {
    key: string;
    label: string;
    status: 'pass' | 'warn' | 'fail' | 'info';
    message: string;
    suggestion?: string;
}

const context = inject(EditorContextKey);
if (!context) {
    throw new Error('ProductSeoPanel must be used within editor context');
}

const form = context.form;
if (!form.value) {
    form.value = {
        name: '',
        slug: '',
        sku: '',
        type: context.type ?? 'product',
        status: 'draft',
        price: 0,
        sale_price: null,
        stock_quantity: 0,
        stock_status: 'in_stock',
        manage_stock: false,
        featured: false,
        meta_title: '',
        meta_description: '',
        meta_keywords: '',
        settings: {},
    } as any;
}

const selectedCategories = context.state?.productCategories;
const descriptionHtml = context.state?.descriptionHtml;
const featuredImage = context.state?.featuredImage;

const ensureSeoSettings = (): Record<string, any> => {
    if (!form.value.settings || typeof form.value.settings !== 'object' || Array.isArray(form.value.settings)) {
        form.value.settings = {};
    }

    if (!form.value.settings.seo || typeof form.value.settings.seo !== 'object' || Array.isArray(form.value.settings.seo)) {
        form.value.settings.seo = {
            focus_keyword: '',
            canonical_url: '',
            primary_category_id: null,
            social_title: '',
            social_description: '',
            social_image: '',
        };
    } else {
        // Just ensure keys exist without re-assigning the whole object to avoid triggering reactivity loops
        if (form.value.settings.seo.focus_keyword === undefined) form.value.settings.seo.focus_keyword = '';
        if (form.value.settings.seo.canonical_url === undefined) form.value.settings.seo.canonical_url = '';
        if (form.value.settings.seo.primary_category_id === undefined) form.value.settings.seo.primary_category_id = null;
        if (form.value.settings.seo.social_title === undefined) form.value.settings.seo.social_title = '';
        if (form.value.settings.seo.social_description === undefined) form.value.settings.seo.social_description = '';
        if (form.value.settings.seo.social_image === undefined) form.value.settings.seo.social_image = '';
    }

    return form.value.settings.seo;
};

ensureSeoSettings();

const setSeoField = (key: string, value: string | number | null): void => {
    const seo = ensureSeoSettings();
    seo[key] = value === '' ? null : value;
};

const canonicalUrl = computed({
    get: () => String(ensureSeoSettings().canonical_url || ''),
    set: (value: string) => setSeoField('canonical_url', value || null),
});

const focusKeyword = computed({
    get: () => String(ensureSeoSettings().focus_keyword || ''),
    set: (value: string) => setSeoField('focus_keyword', value || null),
});

const socialTitle = computed({
    get: () => String(ensureSeoSettings().social_title || ''),
    set: (value: string) => setSeoField('social_title', value || null),
});

const socialDescription = computed({
    get: () => String(ensureSeoSettings().social_description || ''),
    set: (value: string) => setSeoField('social_description', value || null),
});

const socialImage = computed({
    get: () => String(ensureSeoSettings().social_image || ''),
    set: (value: string) => setSeoField('social_image', value || null),
});

const primaryCategoryId = computed<number | null>({
    get: () => {
        const raw = Number(ensureSeoSettings().primary_category_id || 0);
        return Number.isFinite(raw) && raw > 0 ? raw : null;
    },
    set: (value) => {
        const normalized = Number(value);
        if (!Number.isFinite(normalized) || normalized <= 0) {
            setSeoField('primary_category_id', null);
            return;
        }

        if (selectedCategories?.value && !selectedCategories.value.includes(normalized)) {
            setSeoField('primary_category_id', null);
            return;
        }

        setSeoField('primary_category_id', normalized);
    },
});

const categoryOptions = ref<CategoryOption[]>([]);

const fetchCategories = async (): Promise<void> => {
    try {
        const response = await axios.get('/api/v1/product-categories', {
            params: { tree: true },
        });

        const tree = Array.isArray(response.data?.data) ? response.data.data : [];
        categoryOptions.value = flattenCategoryTree(tree);
    } catch {
        categoryOptions.value = [];
    }
};

const flattenCategoryTree = (nodes: any[], depth = 0): CategoryOption[] => {
    const output: CategoryOption[] = [];

    nodes.forEach((node) => {
        const id = Number(node?.id);
        if (!Number.isFinite(id)) {
            return;
        }

        output.push({
            id,
            name: String(node?.name || `Category #${id}`),
            depth,
        });

        const children = Array.isArray(node?.children) ? node.children : [];
        output.push(...flattenCategoryTree(children, depth + 1));
    });

    return output;
};

const selectedCategoryOptions = computed<CategoryOption[]>(() => {
    const ids = Array.isArray(selectedCategories?.value) ? selectedCategories?.value : [];
    if (!ids || ids.length === 0) {
        return [];
    }

    const idSet = new Set(ids);
    const matched = categoryOptions.value.filter((item) => idSet.has(item.id));
    const missing = ids.filter((id: number) => !matched.some((item) => item.id === id));

    return [
        ...matched,
        ...missing.map((id: number) => ({ id, name: `Category #${id}`, depth: 0 })),
    ];
});

watch(
    () => selectedCategories?.value,
    (ids) => {
        if (!Array.isArray(ids) || ids.length === 0) {
            primaryCategoryId.value = null;
            return;
        }

        if (primaryCategoryId.value && !ids.includes(primaryCategoryId.value)) {
            primaryCategoryId.value = null;
        }
    },
    { deep: true }
);

onMounted(() => {
    fetchCategories();
});

const seoTitle = computed(() => String(form.value.meta_title || form.value.name || '').trim());
const seoDescription = computed(() => String(form.value.meta_description || form.value.short_description || '').trim());

const titleLength = computed(() => seoTitle.value.length);
const descriptionLength = computed(() => seoDescription.value.length);
const normalizedKeyword = computed(() => focusKeyword.value.trim().toLowerCase());

const plainContent = computed(() => {
    const html = String(descriptionHtml?.value || '');
    return html.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
});

const linkStats = computed(() => {
    const html = String(descriptionHtml?.value || '');
    const matches = [...html.matchAll(/<a\s+[^>]*href=["']([^"']+)["']/gi)];

    let internal = 0;
    let external = 0;

    matches.forEach((match) => {
        const href = String(match[1] || '').trim();
        if (!href) {
            return;
        }

        if (href.startsWith('/') || href.startsWith(window.location.origin)) {
            internal += 1;
        } else if (/^https?:\/\//i.test(href)) {
            external += 1;
        }
    });

    return { internal, external };
});

const headingStats = computed(() => {
    const html = String(descriptionHtml?.value || '');
    return {
        h1: (html.match(/<h1\b/gi) || []).length,
        h2: (html.match(/<h2\b/gi) || []).length,
    };
});

const imageAltStats = computed(() => {
    const html = String(descriptionHtml?.value || '');
    const images = html.match(/<img\b[^>]*>/gi) || [];

    if (images.length === 0) {
        return { total: 0, missingAlt: 0 };
    }

    let missingAlt = 0;
    images.forEach((tag) => {
        const altMatch = tag.match(/\balt=["']([^"']*)["']/i);
        if (!altMatch || String(altMatch[1] || '').trim() === '') {
            missingAlt += 1;
        }
    });

    return { total: images.length, missingAlt };
});

const checklistItems = computed<ChecklistItem[]>(() => {
    const items: ChecklistItem[] = [];

    const titleLen = titleLength.value;
    if (titleLen >= 30 && titleLen <= 60) {
        items.push({ key: 'title_length', label: 'Title length', status: 'pass', message: `Title length is ${titleLen} characters.` });
    } else if (titleLen >= 20 && titleLen <= 70) {
        items.push({ key: 'title_length', label: 'Title length', status: 'warn', message: `Title length is ${titleLen} characters.`, suggestion: 'Keep title around 30-60 characters for stronger snippets.' });
    } else {
        items.push({ key: 'title_length', label: 'Title length', status: 'fail', message: `Title length is ${titleLen} characters.`, suggestion: 'Rewrite title to 30-60 characters.' });
    }

    const descLen = descriptionLength.value;
    if (descLen >= 120 && descLen <= 160) {
        items.push({ key: 'description_length', label: 'Description length', status: 'pass', message: `Description length is ${descLen} characters.` });
    } else if (descLen >= 80 && descLen <= 200) {
        items.push({ key: 'description_length', label: 'Description length', status: 'warn', message: `Description length is ${descLen} characters.`, suggestion: 'Aim for 120-160 characters.' });
    } else {
        items.push({ key: 'description_length', label: 'Description length', status: 'fail', message: `Description length is ${descLen} characters.`, suggestion: 'Add a clear summary around 120-160 characters.' });
    }

    if (normalizedKeyword.value === '') {
        items.push({ key: 'focus_keyword', label: 'Focus keyword', status: 'info', message: 'No focus keyword is set.', suggestion: 'Set one target keyword to improve optimization guidance.' });
    } else {
        const inTitle = seoTitle.value.toLowerCase().includes(normalizedKeyword.value);
        const inDescription = seoDescription.value.toLowerCase().includes(normalizedKeyword.value);

        if (inTitle && inDescription) {
            items.push({ key: 'focus_keyword', label: 'Focus keyword usage', status: 'pass', message: 'Focus keyword appears in title and description.' });
        } else if (inTitle || inDescription) {
            items.push({ key: 'focus_keyword', label: 'Focus keyword usage', status: 'warn', message: 'Focus keyword appears only in one metadata field.', suggestion: 'Include it in both title and description.' });
        } else {
            items.push({ key: 'focus_keyword', label: 'Focus keyword usage', status: 'fail', message: 'Focus keyword is missing in title and description.', suggestion: 'Use focus keyword naturally in metadata.' });
        }
    }

    const slug = String(form.value.slug || '').trim();
    const slugOk = /^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug);
    items.push(
        slugOk
            ? { key: 'slug', label: 'Slug quality', status: 'pass', message: 'Slug is SEO-friendly.' }
            : { key: 'slug', label: 'Slug quality', status: 'warn', message: 'Slug may not be SEO-friendly.', suggestion: 'Use lowercase words separated by hyphens.' }
    );

    const contentLen = plainContent.value.length;
    if (contentLen >= 300) {
        items.push({ key: 'content_length', label: 'Content length', status: 'pass', message: `Body content has ${contentLen} characters.` });
    } else if (contentLen >= 160) {
        items.push({ key: 'content_length', label: 'Content length', status: 'warn', message: `Body content has ${contentLen} characters.`, suggestion: 'Expand content depth to improve topical coverage.' });
    } else {
        items.push({ key: 'content_length', label: 'Content length', status: 'fail', message: `Body content has ${contentLen} characters.`, suggestion: 'Add more useful body content.' });
    }

    const headings = headingStats.value;
    if (headings.h1 <= 1 && headings.h2 >= 1) {
        items.push({ key: 'heading_structure', label: 'Heading structure', status: 'pass', message: `Heading structure looks valid (H1: ${headings.h1}, H2: ${headings.h2}).` });
    } else if (headings.h1 <= 1) {
        items.push({ key: 'heading_structure', label: 'Heading structure', status: 'warn', message: `Only ${headings.h2} H2 heading(s) found.`, suggestion: 'Add subheadings to improve readability.' });
    } else {
        items.push({ key: 'heading_structure', label: 'Heading structure', status: 'fail', message: `Detected ${headings.h1} H1 headings.`, suggestion: 'Keep only one H1 per page.' });
    }

    const links = linkStats.value;
    if (links.internal > 0 && links.external > 0) {
        items.push({ key: 'links', label: 'Internal/External links', status: 'pass', message: `Internal: ${links.internal}, External: ${links.external}.` });
    } else if (links.internal > 0 || links.external > 0) {
        items.push({ key: 'links', label: 'Internal/External links', status: 'warn', message: `Internal: ${links.internal}, External: ${links.external}.`, suggestion: 'Use both internal and trusted external links when relevant.' });
    } else {
        items.push({ key: 'links', label: 'Internal/External links', status: 'fail', message: 'No links found in content.', suggestion: 'Add at least one internal link and one external reference if applicable.' });
    }

    const images = imageAltStats.value;
    if (images.total === 0) {
        items.push({ key: 'image_alt', label: 'Image alt text', status: 'info', message: 'No images detected in content.' });
    } else if (images.missingAlt === 0) {
        items.push({ key: 'image_alt', label: 'Image alt text', status: 'pass', message: `All ${images.total} image(s) include alt text.` });
    } else {
        items.push({ key: 'image_alt', label: 'Image alt text', status: 'warn', message: `${images.missingAlt}/${images.total} image(s) missing alt text.`, suggestion: 'Add meaningful alt text for accessibility and image SEO.' });
    }

    const hasCategory = Array.isArray(selectedCategories?.value) && selectedCategories.value.length > 0;
    if (hasCategory) {
        items.push({ key: 'category', label: 'Category assignment', status: 'pass', message: `${selectedCategories?.value?.length || 0} categor(ies) selected.` });
    } else {
        items.push({ key: 'category', label: 'Category assignment', status: 'warn', message: 'No category selected.', suggestion: 'Assign at least one category for taxonomy SEO and breadcrumb quality.' });
    }

    return items;
});

const checklistScore = computed(() => {
    const scoredItems = checklistItems.value.filter((item) => item.status !== 'info');
    if (scoredItems.length === 0) {
        return 0;
    }

    const points = scoredItems.reduce((total, item) => {
        if (item.status === 'pass') return total + 1;
        if (item.status === 'warn') return total + 0.5;
        return total;
    }, 0);

    return Math.round((points / scoredItems.length) * 100);
});

const previewUrl = computed(() => {
    const helper = context.helpers?.getPermalink;

    if (typeof helper === 'function') {
        const value = String(helper() || '').trim();
        if (value !== '') {
            return value;
        }
    }

    const slug = String(form.value.slug || '').trim();
    const suffix = slug ? `/${slug}` : '';

    return `${window.location.origin}${suffix}`;
});

const googleTitle = computed(() => truncate(seoTitle.value || 'Untitled product', 60));
const googleDescription = computed(() => truncate(seoDescription.value || 'No description available for this product yet.', 160));

const resolvedSocialTitle = computed(() => socialTitle.value.trim() || seoTitle.value || 'Untitled product');
const resolvedSocialDescription = computed(() => socialDescription.value.trim() || seoDescription.value || 'No description available for this product yet.');
const resolvedSocialImage = computed(() => {
    const manual = socialImage.value.trim();
    if (manual !== '') {
        return manual;
    }

    const featured = featuredImage?.value?.url;
    return featured ? String(featured) : '';
});

const statusClass = (status: ChecklistItem['status']): string => {
    if (status === 'pass') return 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-100';
    if (status === 'warn') return 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-100';
    if (status === 'fail') return 'border-rose-200 bg-rose-50 text-rose-900 dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-100';
    return 'border-slate-200 bg-slate-50 text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200';
};

const truncate = (value: string, limit: number): string => {
    const normalized = value.trim();
    if (normalized.length <= limit) {
        return normalized;
    }

    return `${normalized.slice(0, Math.max(0, limit - 1)).trim()}…`;
};
</script>
