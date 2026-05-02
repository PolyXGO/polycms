<template>
    <form class="space-y-8" @submit.prevent="$emit('save')">
        <section class="rounded-xl border border-slate-200 bg-slate-50/70 p-5 dark:border-slate-700 dark:bg-slate-900/30">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-200">
                {{ $t('MTOptimize Engine') }}
            </h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                {{ $t('Control global SEO engine behavior, cache policy, and sitemap runtime profile.') }}
            </p>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <label class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-900">
                    <input
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                        :checked="asBool('mtoptimize_enabled', true)"
                        @change="setBool('mtoptimize_enabled', $event)"
                    />
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ getLabel('mtoptimize_enabled', 'Enable MTOptimize') }}</span>
                </label>

                <label class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-900">
                    <input
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                        :checked="asBool('mtoptimize_enable_keywords', false)"
                        @change="setBool('mtoptimize_enable_keywords', $event)"
                    />
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ getLabel('mtoptimize_enable_keywords', 'Enable Meta Keywords') }}</span>
                </label>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ getLabel('mtoptimize_cache_ttl_seconds', 'Metadata Cache TTL (seconds)') }}</label>
                    <input
                        type="number"
                        min="60"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                        :value="asNumber('mtoptimize_cache_ttl_seconds', 900)"
                        @input="setNumber('mtoptimize_cache_ttl_seconds', $event)"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ getLabel('mtoptimize_sitemap_chunk_size', 'Sitemap Chunk Size') }}</label>
                    <input
                        type="number"
                        min="10"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                        :value="asNumber('mtoptimize_sitemap_chunk_size', 500)"
                        @input="setNumber('mtoptimize_sitemap_chunk_size', $event)"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ getLabel('mtoptimize_sitemap_cache_ttl_seconds', 'Sitemap Cache TTL (seconds)') }}</label>
                    <input
                        type="number"
                        min="300"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                        :value="asNumber('mtoptimize_sitemap_cache_ttl_seconds', 3600)"
                        @input="setNumber('mtoptimize_sitemap_cache_ttl_seconds', $event)"
                    />
                </div>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900/50">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-200">{{ $t('Templates') }}</h3>
                <div class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ getLabel('mtoptimize_default_title_template', 'Default Title Template') }}</label>
                        <input
                            type="text"
                            class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                            :value="asText('mtoptimize_default_title_template', '{title} | {siteName}')"
                            @input="setText('mtoptimize_default_title_template', $event)"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ getLabel('mtoptimize_home_title_template', 'Homepage Title Template') }}</label>
                        <input
                            type="text"
                            class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                            :value="asText('mtoptimize_home_title_template', '{siteName} | {siteTagline}')"
                            @input="setText('mtoptimize_home_title_template', $event)"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ getLabel('mtoptimize_default_description_template', 'Default Description Template') }}</label>
                        <input
                            type="text"
                            class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                            :value="asText('mtoptimize_default_description_template', '{excerpt}')"
                            @input="setText('mtoptimize_default_description_template', $event)"
                        />
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900/50">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-200">{{ $t('Social & Organization') }}</h3>
                <div class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ getLabel('mtoptimize_default_og_image', 'Default OG Image URL') }}</label>
                        <input
                            type="url"
                            class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                            :value="asText('mtoptimize_default_og_image', '')"
                            @input="setText('mtoptimize_default_og_image', $event)"
                            placeholder="https://example.com/social-default.jpg"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ getLabel('mtoptimize_twitter_site', 'Twitter @account') }}</label>
                        <input
                            type="text"
                            class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                            :value="asText('mtoptimize_twitter_site', '')"
                            @input="setText('mtoptimize_twitter_site', $event)"
                            placeholder="@polycms"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ getLabel('mtoptimize_organization_name', 'Organization Name') }}</label>
                        <input
                            type="text"
                            class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                            :value="asText('mtoptimize_organization_name', '')"
                            @input="setText('mtoptimize_organization_name', $event)"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ getLabel('mtoptimize_organization_logo', 'Organization Logo URL') }}</label>
                        <input
                            type="url"
                            class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                            :value="asText('mtoptimize_organization_logo', '')"
                            @input="setText('mtoptimize_organization_logo', $event)"
                            placeholder="https://example.com/logo.png"
                        />
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900/50">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-200">{{ $t('Robots & Canonical Policy') }}</h3>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ getLabel('mtoptimize_default_robots', 'Default Robots') }}</label>
                    <input
                        type="text"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                        :value="asText('mtoptimize_default_robots', 'index,follow')"
                        @input="setText('mtoptimize_default_robots', $event)"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ getLabel('mtoptimize_advanced_robots', 'Advanced Robots Directives') }}</label>
                    <textarea
                        rows="3"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                        :value="asListText('mtoptimize_advanced_robots')"
                        @input="setListFromText('mtoptimize_advanced_robots', $event)"
                        placeholder="noarchive\nmax-snippet:160"
                    />
                </div>
            </div>

            <div class="mt-4 grid gap-3 md:grid-cols-2 lg:grid-cols-4">
                <label class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    <input type="checkbox" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" :checked="asBool('mtoptimize_noindex_search', true)" @change="setBool('mtoptimize_noindex_search', $event)" />
                    {{ getLabel('mtoptimize_noindex_search', 'Noindex Search Pages') }}
                </label>
                <label class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    <input type="checkbox" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" :checked="asBool('mtoptimize_noindex_system', true)" @change="setBool('mtoptimize_noindex_system', $event)" />
                    {{ getLabel('mtoptimize_noindex_system', 'Noindex System Pages') }}
                </label>
                <label class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    <input type="checkbox" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" :checked="asBool('mtoptimize_noindex_preview', true)" @change="setBool('mtoptimize_noindex_preview', $event)" />
                    {{ getLabel('mtoptimize_noindex_preview', 'Noindex Preview Pages') }}
                </label>
                <label class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    <input type="checkbox" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" :checked="asBool('mtoptimize_suppress_pagination_links_on_noindex', true)" @change="setBool('mtoptimize_suppress_pagination_links_on_noindex', $event)" />
                    {{ getLabel('mtoptimize_suppress_pagination_links_on_noindex', 'Suppress prev/next on noindex') }}
                </label>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ getLabel('mtoptimize_canonical_drop_params', 'Canonical Drop Params') }}</label>
                    <textarea
                        rows="3"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                        :value="asListText('mtoptimize_canonical_drop_params')"
                        @input="setListFromText('mtoptimize_canonical_drop_params', $event)"
                        placeholder="utm_source\nutm_medium\ngclid"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ getLabel('mtoptimize_canonical_keep_params', 'Canonical Keep Params') }}</label>
                    <textarea
                        rows="3"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                        :value="asListText('mtoptimize_canonical_keep_params')"
                        @input="setListFromText('mtoptimize_canonical_keep_params', $event)"
                        placeholder="page"
                    />
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900/50">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-200">{{ $t('Robots.txt Rules') }}</h3>
                <a
                    :href="robotsTxtUrl"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 transition hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50"
                >
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                    {{ $t('View robots.txt') }}
                </a>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ getLabel('mtoptimize_robots_extra_allow', 'Robots allow paths') }}</label>
                    <textarea
                        rows="3"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                        :value="asListText('mtoptimize_robots_extra_allow')"
                        @input="setListFromText('mtoptimize_robots_extra_allow', $event)"
                        placeholder="/"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ getLabel('mtoptimize_robots_extra_disallow', 'Robots disallow paths') }}</label>
                    <textarea
                        rows="3"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                        :value="asListText('mtoptimize_robots_extra_disallow')"
                        @input="setListFromText('mtoptimize_robots_extra_disallow', $event)"
                        placeholder="/admin\n/api"
                    />
                </div>
            </div>

            <!-- Allow crawl on non-production with description -->
            <div class="mt-4 rounded-lg border border-orange-200 bg-orange-50 p-4 dark:border-orange-800 dark:bg-orange-900/20">
                <label class="flex items-center gap-3">
                    <input
                        type="checkbox"
                        class="h-4 w-4 rounded border-orange-300 text-orange-600 focus:ring-orange-500"
                        :checked="asBool('mtoptimize_robots_allow_non_production', false)"
                        @change="setBool('mtoptimize_robots_allow_non_production', $event)"
                    />
                    <span class="text-sm font-medium text-orange-900 dark:text-orange-200">
                        {{ getLabel('mtoptimize_robots_allow_non_production', 'Allow crawl on non-production') }}
                    </span>
                </label>
                <p class="mt-2 ml-7 text-xs leading-relaxed text-orange-800 dark:text-orange-300/80">
                    {{ $t('When disabled (default), robots.txt will block all crawlers with "Disallow: /" on staging, development, or local environments (APP_ENV ≠ production). This prevents search engines from accidentally indexing your test site. Enable this only if you want bots to crawl your non-production environment.') }}
                </p>
            </div>

            <!-- Physical robots.txt warning -->
            <div v-if="hasPhysicalRobots" class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800/50 dark:bg-red-900/20">
                <div class="flex items-start">
                    <div class="flex-shrink-0 mt-0.5 text-red-600 dark:text-red-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-200">{{ $t('Physical robots.txt file detected') }}</h3>
                        <p class="mt-1 text-xs text-red-700 dark:text-red-300">
                            {{ $t('A physical robots.txt file exists in your public directory. This file is overriding the dynamic SEO rules generated by MTOptimize. You must delete it for these settings to take effect.') }}
                        </p>
                        <div class="mt-3">
                            <button
                                type="button"
                                @click="deletePhysicalRobots"
                                :disabled="deletingPhysicalRobots"
                                class="inline-flex items-center gap-1.5 rounded-md bg-red-100 px-3 py-1.5 text-xs font-medium text-red-800 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 dark:bg-red-900/40 dark:text-red-200 dark:hover:bg-red-900/60 dark:focus:ring-offset-slate-900"
                            >
                                <svg v-if="deletingPhysicalRobots" class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <svg v-else class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                {{ deletingPhysicalRobots ? $t('Deleting...') : $t('Delete Physical File') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Robots.txt Preview -->
            <div class="mt-4">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $t('Robots.txt Preview') }}</h4>
                    <button
                        type="button"
                        class="inline-flex items-center gap-1 rounded px-2 py-1 text-xs font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                        @click="refreshRobotsTxtPreview"
                    >
                        <svg class="h-3.5 w-3.5" :class="{ 'animate-spin': loadingRobotsPreview }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" /></svg>
                        {{ $t('Refresh') }}
                    </button>
                </div>
                <pre class="mt-2 max-h-80 overflow-auto rounded-lg border border-slate-200 bg-slate-950 px-4 py-3 font-mono text-xs leading-relaxed text-green-400 dark:border-slate-700">{{ robotsTxtPreview || $t('Click "Refresh" to load preview...') }}</pre>
            </div>
        </section>
    </form>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, getCurrentInstance } from 'vue';
import { useTranslation } from '../../../composables/useTranslation';

interface SettingItem {
    key: string;
    value: any;
    type: string;
    label: string;
    description: string;
}

const props = defineProps<{
    settings: Record<string, SettingItem>;
    saving: boolean;
}>();

const emit = defineEmits<{
    (e: 'update', group: string, key: string, value: any): void;
    (e: 'save'): void;
}>();

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;

/* ── robots.txt preview ── */
const robotsTxtUrl = computed(() => {
    return window.location.origin + '/robots.txt';
});

const robotsTxtPreview = ref<string>('');
const loadingRobotsPreview = ref(false);

const refreshRobotsTxtPreview = async () => {
    loadingRobotsPreview.value = true;
    try {
        const response = await fetch(robotsTxtUrl.value, { cache: 'no-store' });
        robotsTxtPreview.value = await response.text();
    } catch (e) {
        robotsTxtPreview.value = 'Error: Failed to fetch robots.txt';
    } finally {
        loadingRobotsPreview.value = false;
    }
};

const hasPhysicalRobots = ref(false);
const deletingPhysicalRobots = ref(false);

const checkPhysicalRobots = async () => {
    try {
        const response = await fetch('/api/v1/admin/seo-tools/physical-robots', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        const data = await response.json();
        hasPhysicalRobots.value = data.exists || false;
    } catch (e) {
        // ignore
    }
};

const deletePhysicalRobots = async () => {
    if (!confirm($t('Are you sure you want to delete the physical robots.txt file?'))) return;
    
    deletingPhysicalRobots.value = true;
    try {
        await fetch('/api/v1/admin/seo-tools/physical-robots', {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        hasPhysicalRobots.value = false;
        refreshRobotsTxtPreview();
    } catch (e) {
        alert($t('Failed to delete physical robots.txt file.'));
    } finally {
        deletingPhysicalRobots.value = false;
    }
};

onMounted(() => {
    refreshRobotsTxtPreview();
    checkPhysicalRobots();
});

const getSetting = (key: string): SettingItem | null => props.settings?.[key] ?? null;

const getLabel = (key: string, fallback: string): string => {
    return getSetting(key)?.label || fallback;
};

const getValue = (key: string, fallback: any = ''): any => {
    const setting = getSetting(key);
    if (!setting) {
        return fallback;
    }

    const value = setting.value;
    return value === null || value === undefined ? fallback : value;
};

const setValue = (key: string, value: any): void => {
    emit('update', 'mtoptimize', key, value);
};

const asText = (key: string, fallback = ''): string => {
    return String(getValue(key, fallback));
};

const asNumber = (key: string, fallback = 0): number => {
    const value = Number(getValue(key, fallback));
    return Number.isFinite(value) ? value : fallback;
};

const asBool = (key: string, fallback = false): boolean => {
    const value = getValue(key, fallback);

    if (typeof value === 'boolean') {
        return value;
    }

    if (typeof value === 'number') {
        return value === 1;
    }

    if (typeof value === 'string') {
        return ['1', 'true', 'yes', 'on'].includes(value.toLowerCase());
    }

    return Boolean(value);
};

const asListText = (key: string): string => {
    let value = getValue(key, []);

    // Parse JSON string into array if needed
    if (typeof value === 'string') {
        try {
            const parsed = JSON.parse(value);
            if (Array.isArray(parsed)) {
                value = parsed;
            } else {
                return value;
            }
        } catch {
            return value;
        }
    }

    if (Array.isArray(value)) {
        return value.map((item) => String(item)).filter(Boolean).join('\n');
    }

    return '';
};

const setText = (key: string, event: Event): void => {
    const target = event.target as HTMLInputElement;
    setValue(key, target.value || null);
};

const setNumber = (key: string, event: Event): void => {
    const target = event.target as HTMLInputElement;
    const parsed = Number(target.value);
    setValue(key, Number.isFinite(parsed) ? parsed : null);
};

const setBool = (key: string, event: Event): void => {
    const target = event.target as HTMLInputElement;
    setValue(key, target.checked);
};

const setListFromText = (key: string, event: Event): void => {
    const target = event.target as HTMLTextAreaElement;

    const list = target.value
        .split(/[,\n]/)
        .map((item) => item.trim())
        .filter((item, index, arr) => item !== '' && arr.indexOf(item) === index);

    setValue(key, list);
};
</script>
