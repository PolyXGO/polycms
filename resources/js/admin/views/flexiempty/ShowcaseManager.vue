<template>
 <div class="space-y-6">
  <!-- Edit/Create Single Page -->
  <div v-if="viewMode === 'form'" class="space-y-6">
   <form @submit.prevent="savePackage">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-admin-theme-border pb-4 mb-6">
     <div>
      <p class="text-xs font-black uppercase tracking-[0.2em] text-admin-theme-primary">Appearance</p>
      <h1 class="mt-1 text-2xl font-bold text-admin-theme-text">{{ editing ? 'Edit Landing Page' : 'Upload Landing Page' }}</h1>
      <p class="mt-2 text-sm text-admin-theme-text-secondary">ZIP packages should contain static HTML, CSS, JS and media files only.</p>
     </div>
     <div class="flex items-center gap-3 shrink-0">
      <a
       v-if="editing && editing.public_url"
       :href="editing.public_url"
       target="_blank"
       class="rounded-lg border border-admin-theme-border bg-admin-theme-surface px-4 py-2 text-sm font-semibold text-admin-theme-primary transition-colors hover:bg-admin-theme-base/5"
      >
       View Landing Page
      </a>
      <button
       type="button"
       class="rounded-lg border border-admin-theme-border bg-admin-theme-surface px-4 py-2 text-sm font-semibold text-admin-theme-text-secondary transition-colors hover:bg-admin-theme-base/5"
       @click="goBackToList"
      >
       Cancel
      </button>
      <button
       type="submit"
       :disabled="saving"
       class="rounded-lg bg-admin-theme-primary px-4 py-2 text-sm font-semibold text-admin-theme-primary-content hover:bg-admin-theme-primary-hover disabled:opacity-60 transition-colors"
      >
       {{ saving ? 'Saving...' : 'Save Changes' }}
      </button>
     </div>
    </header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
     <!-- Left Column (Main Form Content) -->
     <div class="space-y-6 lg:col-span-2">
      <!-- General Information -->
      <div class="rounded-xl border border-admin-theme-border bg-admin-theme-surface p-6 shadow-sm space-y-5">
       <h3 class="text-sm font-black uppercase tracking-wider text-admin-theme-text-secondary border-b border-admin-theme-border pb-2">General Information</h3>
       
       <div class="grid gap-5 md:grid-cols-2">
        <label class="space-y-2">
         <span class="text-sm font-semibold text-admin-theme-text-secondary">Title</span>
         <input v-model="form.title" class="field" type="text" placeholder="Landing page title" required />
        </label>
        <label class="space-y-2">
         <span class="text-sm font-semibold text-admin-theme-text-secondary">Slug</span>
         <input v-model="form.slug" class="field" type="text" placeholder="my-product" required />
        </label>
       </div>

       <label v-if="editing" class="block space-y-2">
        <span class="text-sm font-semibold text-admin-theme-text-secondary">Main Preview HTML</span>
        <select v-model="form.entry_file" class="field">
         <option v-if="htmlFiles.length === 0" value="">No HTML files found</option>
         <option v-for="file in htmlFiles" :key="file" :value="file">{{ file }}</option>
        </select>
        <span class="block text-xs text-admin-theme-text-muted">
         This file will be used as the main screen when opening Preview. Relative links inside the package still work.
        </span>
       </label>

       <label class="block space-y-2">
        <span class="text-sm font-semibold text-admin-theme-text-secondary">Summary</span>
        <textarea v-model="form.summary" class="field min-h-24" placeholder="Short description for this landing page package"></textarea>
       </label>

       <label class="block space-y-2">
        <span class="text-sm font-semibold text-admin-theme-text-secondary">GitHub URL</span>
        <input v-model="form.settings.github_url" class="field" type="url" placeholder="https://github.com/vendor/project" />
        <span class="block text-xs text-admin-theme-text-muted">
         Links inside the uploaded HTML with <code>data-link="github"</code> will be replaced with this URL.
        </span>
       </label>
      </div>

      <!-- SEO Configuration Section -->
      <div class="rounded-xl border border-admin-theme-border bg-admin-theme-surface p-6 shadow-sm space-y-5">
       <h3 class="text-sm font-black uppercase tracking-wider text-admin-theme-text-secondary border-b border-admin-theme-border pb-2">SEO Configuration</h3>
       <div class="space-y-4">
        <label class="block space-y-2">
         <span class="text-sm font-semibold text-admin-theme-text-secondary">SEO Title (Meta Title)</span>
         <input v-model="form.seo_meta.meta_title" class="field" type="text" placeholder="Meta title for search engines" />
        </label>
        <label class="block space-y-2">
         <span class="text-sm font-semibold text-admin-theme-text-secondary">SEO Description (Meta Description)</span>
         <textarea v-model="form.seo_meta.meta_description" class="field min-h-20" placeholder="Meta description for search engines"></textarea>
        </label>
        <label class="block space-y-2">
         <span class="text-sm font-semibold text-admin-theme-text-secondary">SEO Keywords (Meta Keywords)</span>
         <input v-model="form.seo_meta.meta_keywords" class="field" type="text" placeholder="keywords, separated, by, commas" />
        </label>
       </div>
      </div>
     </div>

     <!-- Right Column (Sidebar Settings) -->
     <div class="space-y-6">
      <!-- Publish Meta Box -->
      <div class="rounded-xl border border-admin-theme-border bg-admin-theme-surface p-6 shadow-sm space-y-4">
       <h3 class="text-sm font-black uppercase tracking-wider text-admin-theme-text-secondary border-b border-admin-theme-border pb-2">Status & Language</h3>
       <label class="block space-y-2">
        <span class="text-sm font-semibold text-admin-theme-text-secondary">Status</span>
        <select v-model="form.status" class="field">
         <option value="draft">Draft</option>
         <option value="published">Published</option>
         <option value="archived">Archived</option>
        </select>
       </label>
       <label class="block space-y-2">
        <span class="text-sm font-semibold text-admin-theme-text-secondary">Language Code</span>
        <input v-model="form.language_code" class="field" type="text" placeholder="en" />
       </label>
      </div>

      <!-- ZIP Package Box -->
      <div class="rounded-xl border border-admin-theme-border bg-admin-theme-surface p-6 shadow-sm space-y-4">
       <h3 class="text-sm font-black uppercase tracking-wider text-admin-theme-text-secondary border-b border-admin-theme-border pb-2">Package Files</h3>
       
       <label class="block space-y-2">
        <span class="text-sm font-semibold text-admin-theme-text-secondary">{{ editing ? 'Replace ZIP package (optional)' : 'ZIP package' }}</span>
        <input class="field file:mr-4 file:rounded-lg file:border-0 file:bg-admin-theme-primary file:px-3 file:py-2 file:text-sm file:font-semibold file:text-admin-theme-primary-content" type="file" accept=".zip" :required="!editing" @change="handleFileChange" />
        <span v-if="selectedFile" class="block text-xs text-admin-theme-text-muted">
         Selected: <strong class="text-admin-theme-text">{{ selectedFile.name }}</strong>
        </span>
       </label>

       <!-- Current package info -->
       <div v-if="editing" class="rounded-lg border border-admin-theme-border bg-admin-theme-base/50 p-4 space-y-3">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between border-b border-admin-theme-border pb-2">
         <span class="text-xs font-bold text-admin-theme-text uppercase">Active Package</span>
         <button
          type="button"
          :disabled="downloadLoading === editing.id"
          class="inline-flex items-center rounded-md border border-admin-theme-border bg-admin-theme-surface px-2.5 py-1 text-xs text-admin-theme-primary transition hover:bg-admin-theme-base hover:underline"
          @click="downloadSource(editing)"
         >
          {{ downloadLoading === editing.id ? 'Downloading...' : 'Download source' }}
         </button>
        </div>
        <div class="space-y-2 text-xs text-admin-theme-text-secondary">
         <div>
          <span class="block font-semibold uppercase tracking-wide text-admin-theme-text-muted text-[10px]">Filename</span>
          <span class="block break-all text-admin-theme-text font-mono mt-0.5">{{ packageFileName(editing) }}</span>
         </div>
         <div>
          <span class="block font-semibold uppercase tracking-wide text-admin-theme-text-muted text-[10px]">Package size</span>
          <span class="block text-admin-theme-text mt-0.5">{{ editing.file_count }} files / {{ formatBytes(editing.total_size) }}</span>
         </div>
         <div>
          <span class="block font-semibold uppercase tracking-wide text-admin-theme-text-muted text-[10px]">Public URL</span>
          <a :href="editing.public_url" target="_blank" class="block break-all font-semibold text-admin-theme-primary hover:underline mt-0.5">{{ editing.public_url }}</a>
         </div>
        </div>
       </div>
      </div>

      <!-- Iframe Display Settings Box -->
      <div class="rounded-xl border border-admin-theme-border bg-admin-theme-surface p-6 shadow-sm space-y-4">
       <h3 class="text-sm font-black uppercase tracking-wider text-admin-theme-text-secondary border-b border-admin-theme-border pb-2">Display & Security</h3>
       <label class="block space-y-2">
        <span class="text-sm font-semibold text-admin-theme-text-secondary">Iframe Height (px)</span>
        <input v-model.number="form.settings.iframe_height" class="field" type="number" min="320" step="20" />
       </label>
       <div class="space-y-2">
        <span class="text-sm font-semibold text-admin-theme-text-secondary">Iframe security mode</span>
        <select v-model="sandboxMode" class="field" @change="applySandboxPreset">
         <option value="recommended">Recommended for landing pages</option>
         <option value="strict">Strict static preview</option>
         <option value="custom">Advanced custom permissions</option>
        </select>
        <p class="text-xs leading-relaxed text-admin-theme-text-muted">
         Controls iframe isolation automatically. Use Recommended for interactive landing pages that need JS, forms, popups, downloads or browser storage.
        </p>
        <textarea v-if="sandboxMode === 'custom'" v-model="form.settings.sandbox" class="field min-h-20 font-mono text-xs"></textarea>
       </div>
      </div>
     </div>
    </div>
   </form>

   <!-- Floating actions for quick save -->
   <div v-if="viewMode === 'form'" class="editor-floating-actions" :style="floatingActionsStyle">
    <button 
     type="button" 
     class="editor-floating-actions__primary" 
     :disabled="saving" 
     @click="savePackage"
     :title="saving ? 'Saving...' : 'Save Changes'"
    >
     <svg v-if="!saving" class="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M19 21H5C4.44772 21 4 20.5523 4 20V4C4 3.44772 4.44772 3 5 3H16L20 7V20C20 20.5523 19.5523 21 19 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
      <path d="M17 21V15H7V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
      <path d="M7 3V8H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
     </svg>
     <svg v-else class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
     </svg>
    </button>
   </div>
  </div>

  <!-- Listing Page -->
  <div v-else class="space-y-6">
   <header class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
     <p class="text-xs font-black uppercase tracking-[0.2em] text-admin-theme-primary">Appearance</p>
     <h1 class="mt-1 text-2xl font-bold text-admin-theme-text">FlexiEmpty ShipCode Pages</h1>
     <p class="mt-2 max-w-3xl text-sm text-admin-theme-text-secondary">
      Upload static HTML/CSS/JS ZIP packages and publish them as isolated landing page previews.
      <a href="/admin/themes/options" class="font-semibold text-admin-theme-primary hover:underline">
       Configure friendly URL
      </a>
      in Theme Options.
     </p>
    </div>
    <button
     type="button"
     class="inline-flex items-center justify-center rounded-lg bg-admin-theme-primary px-4 py-2 text-sm font-semibold text-admin-theme-primary-content transition-colors hover:bg-admin-theme-primary-hover"
     @click="openCreatePage"
    >
     Upload Landing Page
    </button>
   </header>

   <section class="overflow-hidden rounded-lg border border-admin-theme-border bg-admin-theme-surface shadow">
    <div class="flex flex-col gap-4 border-b border-admin-theme-border p-5 lg:flex-row lg:items-center lg:justify-between">
     <label class="relative block flex-1">
      <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-admin-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.2-5.2M16 10.5a5.5 5.5 0 11-11 0 5.5 5.5 0 0111 0z" />
      </svg>
      <input
       v-model="filters.search"
       type="text"
       placeholder="Search by title, slug or summary"
       class="w-full rounded-lg border border-admin-theme-input-border bg-admin-theme-input-bg py-2.5 pl-11 pr-4 text-sm text-admin-theme-text placeholder-admin-theme-text-muted focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
       @input="loadPackages"
      />
     </label>
     <select
      v-model="filters.status"
      class="rounded-lg border border-admin-theme-input-border bg-admin-theme-input-bg px-3 py-2.5 text-sm text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
      @change="loadPackages"
     >
      <option value="">All statuses</option>
      <option value="published">Published</option>
      <option value="draft">Draft</option>
      <option value="archived">Archived</option>
     </select>
    </div>

    <div v-if="loading && packages.length === 0" class="flex flex-col items-center justify-center gap-3 p-12 text-center">
     <div class="h-8 w-8 animate-spin rounded-full border-2 border-admin-theme-primary border-t-transparent"></div>
     <p class="text-sm text-admin-theme-text-secondary">Loading landing pages...</p>
    </div>

    <div v-else-if="packages.length === 0" class="p-12 text-center">
     <div class="text-lg font-semibold text-admin-theme-text">No landing pages found</div>
     <p class="mt-2 text-sm text-admin-theme-text-muted">Upload a static ZIP package to publish the first ShipCode page.</p>
    </div>

    <div v-else class="overflow-x-auto">
     <table class="min-w-full divide-y divide-admin-theme-border">
      <thead class="bg-admin-theme-base">
       <tr>
        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-admin-theme-text-muted">Landing Page</th>
        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-admin-theme-text-muted">Entry</th>
        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-admin-theme-text-muted">Status</th>
        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-admin-theme-text-muted">Files</th>
        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-admin-theme-text-muted">Actions</th>
       </tr>
      </thead>
      <tbody class="divide-y divide-admin-theme-border bg-admin-theme-surface">
       <tr v-for="item in packages" :key="item.id" class="hover:bg-black/5 dark:hover:bg-white/5">
        <td class="px-6 py-4">
         <div class="font-semibold text-admin-theme-text">{{ item.title }}</div>
         <a :href="item.public_url" target="_blank" class="mt-1 inline-block break-all text-sm font-medium text-admin-theme-primary hover:underline">/{{ item.slug }}</a>
         <p v-if="item.summary" class="mt-1 max-w-xl text-sm text-admin-theme-text-secondary line-clamp-2">{{ item.summary }}</p>
        </td>
        <td class="px-6 py-4 text-sm text-admin-theme-text-secondary">{{ item.entry_file }}</td>
        <td class="px-6 py-4">
         <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClass(item.status)">{{ item.status }}</span>
        </td>
        <td class="px-6 py-4 text-sm text-admin-theme-text-secondary">
         {{ item.file_count }} files<br />{{ formatBytes(item.total_size) }}
        </td>
        <td class="px-6 py-4 text-right">
         <div class="inline-flex flex-wrap justify-end gap-3 text-sm font-semibold">
          <button
           type="button"
           :disabled="downloadLoading === item.id"
           class="text-admin-theme-primary hover:underline disabled:opacity-50"
           @click="downloadSource(item)"
          >
           {{ downloadLoading === item.id ? 'Downloading...' : 'Download source' }}
          </button>
          <a :href="item.public_url" target="_blank" class="text-admin-theme-primary hover:underline">Preview</a>
          <button type="button" class="text-admin-theme-primary hover:underline" @click="openEditPage(item)">Edit</button>
          <button type="button" class="text-red-500 hover:underline" @click="deletePackage(item)">Delete</button>
         </div>
        </td>
       </tr>
      </tbody>
     </table>
    </div>
   </section>
  </div>
 </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref, watch, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useDialog } from '@/admin/composables/useDialog';
import { useLandingStore } from '@/admin/stores/landingStore';

type ShowcasePackage = {
 id: number;
 title: string;
 slug: string;
 summary?: string | null;
 status: string;
 entry_file: string;
 html_files?: string[];
 source_zip_path?: string | null;
 storage_path?: string | null;
 file_count: number;
 total_size: number;
 public_url: string;
 settings?: Record<string, any>;
 language_code?: string | null;
 seo_meta?: Record<string, any> | null;
};

const route = useRoute();
const router = useRouter();
const dialog = useDialog();

const packages = ref<ShowcasePackage[]>([]);
const loading = ref(false);
const saving = ref(false);
const viewMode = ref<'list' | 'form'>('list');
const editing = ref<ShowcasePackage | null>(null);
const selectedFile = ref<File | null>(null);
const landingStore = useLandingStore();

const floatingActionsStyle = computed(() => {
 const baseOffset = 32; // Default right offset
 if (landingStore.activeBlock) {
  return {
   right: `${(landingStore.optionsWidth || 300) + baseOffset}px`,
  };
 }
 return {
  right: `${baseOffset}px`,
 };
});
const htmlFiles = ref<string[]>([]);
const downloadLoading = ref<number | null>(null);
const sandboxMode = ref<'recommended' | 'strict' | 'custom'>('recommended');

const sandboxPresets = {
 recommended: 'allow-scripts allow-same-origin allow-forms allow-popups allow-pointer-lock allow-downloads',
 strict: 'allow-scripts',
};

const filters = reactive({ search: '', status: '' });
const form = reactive({
 title: '',
 slug: '',
 summary: '',
 status: 'draft',
 language_code: '',
 entry_file: '',
 settings: {
  iframe_height: 900,
  sandbox: sandboxPresets.recommended,
  github_url: '',
 },
 seo_meta: {
  meta_title: '',
  meta_description: '',
  meta_keywords: '',
 },
});

const loadPackages = async () => {
 loading.value = true;
 try {
  const response = await axios.get('/api/v1/showcase-packages', { params: filters });
  packages.value = response.data.data?.data || response.data.data || [];
 } finally {
  loading.value = false;
 }
};

const handleRoute = async () => {
 if (route.name === 'admin.flexiempty.showcases.create') {
  viewMode.value = 'form';
  editing.value = null;
  selectedFile.value = null;
  Object.assign(form, {
   title: '',
   slug: '',
   summary: '',
   status: 'draft',
   language_code: '',
   entry_file: '',
   settings: {
    iframe_height: 900,
    sandbox: sandboxPresets.recommended,
    github_url: '',
   },
   seo_meta: {
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
   },
  });
  sandboxMode.value = 'recommended';
  htmlFiles.value = [];
 } else if (route.name === 'admin.flexiempty.showcases.edit' && route.params.id) {
  viewMode.value = 'form';
  editing.value = null;
  selectedFile.value = null;
  loading.value = true;
  try {
   const id = route.params.id;
   const response = await axios.get(`/api/v1/showcase-packages/${id}`);
   const item = response.data.data;
   editing.value = item;
   htmlFiles.value = item.html_files || [];
   Object.assign(form, {
    title: item.title,
    slug: item.slug,
    summary: item.summary || '',
    status: item.status,
    language_code: item.language_code || '',
    entry_file: item.entry_file || htmlFiles.value[0] || '',
    settings: {
     iframe_height: item.settings?.iframe_height || 900,
     sandbox: item.settings?.sandbox || sandboxPresets.recommended,
     github_url: item.settings?.github_url || '',
    },
    seo_meta: {
     meta_title: item.seo_meta?.meta_title || item.seo_meta?.title || '',
     meta_description: item.seo_meta?.meta_description || item.seo_meta?.description || '',
     meta_keywords: item.seo_meta?.meta_keywords || item.seo_meta?.keywords || '',
    },
   });
   sandboxMode.value = detectSandboxMode(form.settings.sandbox);
  } catch (err: any) {
   console.error(err);
   dialog.error(err.response?.data?.message || 'Failed to load landing page details');
   router.push({ name: 'admin.flexiempty.showcases.index' });
  } finally {
   loading.value = false;
  }
 } else {
  viewMode.value = 'list';
  editing.value = null;
  selectedFile.value = null;
  loadPackages();
 }
};

watch(() => route.path, handleRoute);

const openCreatePage = () => {
 router.push({ name: 'admin.flexiempty.showcases.create' });
};

const openEditPage = (item: ShowcasePackage) => {
 router.push({ name: 'admin.flexiempty.showcases.edit', params: { id: item.id } });
};

const goBackToList = () => {
 router.push({ name: 'admin.flexiempty.showcases.index' });
};

const handleFileChange = (event: Event) => {
 const input = event.target as HTMLInputElement;
 selectedFile.value = input.files?.[0] || null;
};

const detectSandboxMode = (sandbox: string): 'recommended' | 'strict' | 'custom' => {
 if (sandbox === sandboxPresets.recommended) return 'recommended';
 if (sandbox === sandboxPresets.strict) return 'strict';
 return 'custom';
};

const applySandboxPreset = () => {
 if (sandboxMode.value === 'recommended') form.settings.sandbox = sandboxPresets.recommended;
 if (sandboxMode.value === 'strict') form.settings.sandbox = sandboxPresets.strict;
};

const savePackage = async () => {
 saving.value = true;
 try {
  if (editing.value) {
   const response = await axios.put(`/api/v1/showcase-packages/${editing.value.id}`, form);
   let updatedItem = response.data.data;
   if (selectedFile.value) {
    const data = new FormData();
    data.append('package', selectedFile.value);
    const replaceResponse = await axios.post(`/api/v1/showcase-packages/${editing.value.id}/replace`, data);
    updatedItem = replaceResponse.data.data;
   }
   editing.value = updatedItem;
   htmlFiles.value = updatedItem.html_files || [];
   Object.assign(form, {
    title: updatedItem.title,
    slug: updatedItem.slug,
    summary: updatedItem.summary || '',
    status: updatedItem.status,
    language_code: updatedItem.language_code || '',
    entry_file: updatedItem.entry_file || htmlFiles.value[0] || '',
    settings: {
     iframe_height: updatedItem.settings?.iframe_height || 900,
     sandbox: updatedItem.settings?.sandbox || sandboxPresets.recommended,
     github_url: updatedItem.settings?.github_url || '',
    },
    seo_meta: {
     meta_title: updatedItem.seo_meta?.meta_title || updatedItem.seo_meta?.title || '',
     meta_description: updatedItem.seo_meta?.meta_description || updatedItem.seo_meta?.description || '',
     meta_keywords: updatedItem.seo_meta?.meta_keywords || updatedItem.seo_meta?.keywords || '',
    },
   });
   sandboxMode.value = detectSandboxMode(form.settings.sandbox);
   selectedFile.value = null;
   dialog.success('Landing page package updated successfully.');
  } else {
   const data = new FormData();
   data.append('title', form.title);
   data.append('slug', form.slug);
   data.append('summary', form.summary);
   data.append('status', form.status);
   data.append('language_code', form.language_code);
   data.append('settings[iframe_height]', String(form.settings.iframe_height));
   data.append('settings[sandbox]', form.settings.sandbox);
   data.append('settings[github_url]', form.settings.github_url || '');
   data.append('seo_meta[meta_title]', form.seo_meta.meta_title || '');
   data.append('seo_meta[meta_description]', form.seo_meta.meta_description || '');
   data.append('seo_meta[meta_keywords]', form.seo_meta.meta_keywords || '');
   if (selectedFile.value) data.append('package', selectedFile.value);
   
   const response = await axios.post('/api/v1/showcase-packages', data);
   dialog.success('Landing page package uploaded successfully.');
   const newPackage = response.data.data;
   if (newPackage && newPackage.id) {
    router.push({ name: 'admin.flexiempty.showcases.edit', params: { id: newPackage.id } });
   } else {
    goBackToList();
   }
  }
 } catch (err: any) {
  console.error(err);
  dialog.error(err.response?.data?.message || 'Failed to save landing page');
 } finally {
  saving.value = false;
 }
};

const deletePackage = async (item: ShowcasePackage) => {
 if (!confirm(`Delete landing page package "${item.title}"?`)) return;
 try {
  await axios.delete(`/api/v1/showcase-packages/${item.id}`);
  dialog.success('Landing page package deleted successfully.');
  await loadPackages();
 } catch (err: any) {
  console.error(err);
  dialog.error(err.response?.data?.message || 'Failed to delete landing page');
 }
};

const statusClass = (status: string) => {
 if (status === 'published') return 'bg-emerald-500/15 text-emerald-500';
 if (status === 'archived') return 'bg-gray-500/15 text-gray-400';
 return 'bg-amber-500/15 text-amber-500';
};

const formatBytes = (bytes: number) => {
 if (!bytes) return '0 B';
 const units = ['B', 'KB', 'MB', 'GB'];
 const index = Math.floor(Math.log(bytes) / Math.log(1024));
 return `${(bytes / Math.pow(1024, index)).toFixed(index ? 1 : 0)} ${units[index]}`;
};

const sourceZipName = (path?: string | null) => {
 if (!path) return 'No ZIP metadata';
 return path.split(/[\\/]/).pop() || path;
};

const packageFileName = (item: ShowcasePackage) => {
 return item.settings?.source_zip_original_name || sourceZipName(item.source_zip_path);
};

const downloadSource = async (item: ShowcasePackage) => {
 if (downloadLoading.value === item.id) return;

 downloadLoading.value = item.id;

 try {
  const response = await axios.get(`/api/v1/showcase-packages/${item.id}/download-source`, {
   responseType: 'blob',
  });

  const blob = new Blob([response.data], { type: 'application/zip' });
  let filename = packageFileName(item) || `${item.slug || 'landing-page'}.zip`;
  const headers = response.headers as Record<string, string | undefined>;
  const contentDisposition = headers['content-disposition'] || headers['Content-Disposition'];
  const match = contentDisposition ? /filename\*=UTF-8''([^;]+)|filename="?([^";]+)"?/i.exec(contentDisposition) : null;

  if (match?.[1] || match?.[2]) {
   filename = decodeURIComponent(match[1] || match[2]);
  }

  const url = window.URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.setAttribute('download', filename);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  window.URL.revokeObjectURL(url);
  const refreshed = packages.value.find((x) => x.id === item.id);
  if (refreshed) {
   refreshed.total_size = blob.size; // fallback update size
  }
 } catch (error: any) {
  console.error('Error downloading landing page source:', error);
  dialog.error(error.response?.data?.message || 'Failed to download source package');
 } finally {
  downloadLoading.value = null;
 }
};

onMounted(handleRoute);
</script>

<style scoped>
.field {
 @apply w-full rounded-lg border border-admin-theme-input-border bg-admin-theme-input-bg px-3 py-2 text-sm text-admin-theme-text placeholder-admin-theme-text-muted focus:outline-none focus:ring-1 focus:ring-admin-theme-primary;
}

.editor-floating-actions {
 position: fixed;
 display: none;
 bottom: 2rem;
 right: 2rem;
 flex-direction: column;
 gap: 0.75rem;
 z-index: 40;
 transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.editor-floating-actions__primary {
 width: 3.5rem;
 height: 3.5rem;
 display: flex;
 align-items: center;
 justify-content: center;
 border-radius: 9999px;
 background: rgb(var(--admin-theme-primary));
 color: rgb(var(--admin-theme-primary-content));
 border: none;
 box-shadow: 0 10px 25px -5px rgb(var(--admin-theme-primary) / 0.4), 0 8px 10px -6px rgb(var(--admin-theme-primary) / 0.4);
 cursor: pointer;
 transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.editor-floating-actions__primary:hover {
 background: rgb(var(--admin-theme-primary-hover));
 transform: translateY(-2px);
 box-shadow: 0 20px 25px -5px rgb(var(--admin-theme-primary) / 0.4), 0 10px 10px -5px rgb(var(--admin-theme-primary) / 0.4);
}

.editor-floating-actions__primary:active {
 transform: translateY(0);
}

.editor-floating-actions__primary:disabled {
 opacity: 0.7;
 cursor: not-allowed;
 transform: none;
}

@media (min-width: 1024px) {
 .editor-floating-actions {
  display: flex;
 }
}
</style>
