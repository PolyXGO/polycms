<template>
    <div v-if="form" class="space-y-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="post-title-input">Title</label>
            <input
                id="post-title-input"
                v-model="form.title"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-indigo-500 focus:border-indigo-500 text-2xl font-bold"
                placeholder="Add title"
                required
                @input="helpers.generateSlug?.()"
            />
        </div>

        <div v-if="form?.slug">
            <div class="flex flex-wrap items-center gap-4 bg-gray-50 dark:bg-gray-800/50 p-3 rounded-xl border border-dashed border-gray-300 dark:border-gray-600">
                <div class="flex-1 flex items-center min-w-0 overflow-hidden">
                    <span class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ slugPrefix }}</span>
                    <input
                        ref="slugInputRef"
                        v-model="form.slug"
                        type="text"
                        class="flex-1 px-2 py-1 bg-transparent border-none text-sm font-semibold text-indigo-600 dark:text-indigo-400 focus:outline-none min-w-[50px]"
                        :readonly="!isEditingSlug"
                        required
                        @input="handleSlugInput"
                        @blur="handleSlugInput"
                    />
                </div>
                <div class="flex gap-2 shrink-0">
                    <button 
                        type="button" 
                        class="px-3 py-1.5 text-xs font-semibold bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-200 hover:border-indigo-500 hover:text-indigo-600 transition-colors" 
                        @click="toggleSlugEdit"
                    >
                        {{ isEditingSlug ? 'Done' : 'Edit Slug' }}
                    </button>
                    <button 
                        type="button" 
                        class="px-3 py-1.5 text-xs font-semibold bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-200 hover:border-indigo-500 hover:text-indigo-600 transition-colors" 
                        @click="copyPermalink"
                    >
                        Copy Link
                    </button>
                </div>
            </div>
            <div class="mt-2 text-[11px] text-gray-500 dark:text-gray-400 pl-3">
                You can adjust the permalink structure at <router-link to="/admin/settings/group/permalinks" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">Permalink Settings</router-link>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="post-content">Content</label>
            <TiptapEditor
                id="post-content"
                v-model="contentHtml"
                v-model:json="contentRaw"
                placeholder="Start typing your content..."
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, inject, isRef, ref, nextTick } from 'vue';
// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-ignore - Vue SFC typing provided via shim
import TiptapEditor from '../../../TiptapEditor.ts';
import { EditorContextKey } from '../../../../editor/context';

const context = inject(EditorContextKey);
if (!context) {
    throw new Error('PostPrimaryPanel must be used within editor context');
}

const form = context.form;
const helpers = context.helpers ?? {};

if (!form.value) {
    form.value = {
        title: '',
        slug: '',
        type: context.type ?? 'post',
        status: context.state?.postStatuses?.[0]?.value ?? 'draft',
        excerpt: '',
        meta_title: '',
        meta_description: '',
        meta_keywords: '',
        created_at: null,
        updated_at: null,
    } as any;
}

const rawContent = context.state?.contentHtml;
const rawRaw = context.state?.contentRaw;

const baseRef = isRef(rawContent) ? rawContent : ref(rawContent ?? '');
const rawRef = isRef(rawRaw) ? rawRaw : ref(rawRaw ?? null);

if (!isRef(rawContent) && context.state) {
    (context.state as Record<string, unknown>).contentHtml = baseRef;
}
if (!isRef(rawRaw) && context.state) {
    (context.state as Record<string, unknown>).contentRaw = rawRef;
}

const contentHtml = computed<string>({
    get: () => (baseRef.value ?? '') as string,
    set: (value) => {
        baseRef.value = value ?? '';
    },
});

const contentRaw = computed<any>({
    get: () => rawRef.value,
    set: (value) => {
        rawRef.value = value;
    },
});

const permalink = computed(() => helpers.getPermalink?.() ?? '');
const slugPrefix = computed(() => {
    const link = permalink.value;
    if (!link) {
        return `${window.location.origin}/`;
    }
    try {
        const url = new URL(link);
        const segments = url.pathname.split('/').filter(Boolean);
        segments.pop();
        const basePath = segments.length ? `/${segments.join('/')}/` : '/';
        return `${url.origin}${basePath}`;
    } catch (error) {
        const currentSlug = form.value.slug ?? '';
        if (!currentSlug) {
            return link.endsWith('/') ? link : `${link}/`;
        }
        const base = link.endsWith(`${currentSlug}`)
            ? link.slice(0, -currentSlug.length)
            : link;
        return base.endsWith('/') ? base : `${base}/`;
    }
});

const fullPermalink = computed(() => {
    if (!form.value.slug) {
        return permalink.value || slugPrefix.value;
    }
    const prefix = slugPrefix.value.endsWith('/') ? slugPrefix.value : `${slugPrefix.value}/`;
    return `${prefix.replace(/\/+/g, '/')}${form.value.slug}`;
});

const isEditingSlug = ref(false);
const slugInputRef = ref<HTMLInputElement | null>(null);

const toggleSlugEdit = async () => {
    isEditingSlug.value = !isEditingSlug.value;
    if (isEditingSlug.value) {
        await nextTick();
        slugInputRef.value?.focus();
        slugInputRef.value?.select();
    } else {
        // Save when "Done" is clicked
        helpers.save?.();
    }
};

const copyPermalink = async () => {
    const text = fullPermalink.value;
    if (!text) return;
    if (navigator.clipboard?.writeText) {
        try {
            await navigator.clipboard.writeText(text);
        } catch (error) {
            console.warn('Copy failed', error);
        }
    } else {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.left = '-1000px';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
        } catch (error) {
            console.warn('Copy failed', error);
        }
        document.body.removeChild(textarea);
    }
};

const handleSlugInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    helpers.onSlugInput?.(event);
    form.value.slug = target.value;
};
</script>

<style scoped>
/* Tiptap spacing */
:deep(.ProseMirror) {
    min-height: 200px;
}
</style>
