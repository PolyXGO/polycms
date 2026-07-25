<template>
 <div>
 <!-- Header -->
 <div class="mb-8">
 <router-link to="/admin/settings" class="inline-flex items-center text-sm font-medium text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary mb-3">
 <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
 {{ t('Back to Hub') }}
 </router-link>
 <h1 class="text-2xl font-bold text-admin-theme-text">{{ t('Cache Management') }}</h1>
 <p class="text-sm text-admin-theme-text-secondary mt-1">
 {{ t('View cache status and clear application, view, config, and OPcache caches.') }}
 </p>
 </div>

 <!-- Quick Actions Bar -->
 <div class="mb-6 flex items-center gap-4 flex-wrap">
 <button
 @click="clearAll"
 :disabled="clearingAll"
 class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
 >
 <ArrowPathIcon v-if="clearingAll" class="w-4 h-4 animate-spin" />
 <TrashIcon v-else class="w-4 h-4" />
 {{ clearingAll ? t('Clearing...') : t('Clear All Caches') }}
 </button>

 <button
 @click="fixPermissions"
 :disabled="fixingPermissions"
 class="inline-flex items-center gap-2 px-5 py-2.5 bg-admin-theme-primary hover:bg-admin-theme-primary/90 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
 >
 <ArrowPathIcon v-if="fixingPermissions" class="w-4 h-4 animate-spin" />
 <WrenchScrewdriverIcon v-else class="w-4 h-4" />
 {{ fixingPermissions ? t('Fixing...') : t('Fix Permissions') }}
 </button>

 <span v-if="lastCleared" class="text-xs text-admin-theme-text-muted">
 {{ t('Last cleared') }}: {{ lastCleared }}
 </span>
 </div>

 <!-- Cache Driver Info -->
 <div v-if="status" class="mb-6 px-4 py-3 bg-admin-theme-primary/10 rounded-lg border border-indigo-100 dark:border-indigo-800/40">
 <div class="flex items-center gap-2 text-sm text-admin-theme-primary">
 <InformationCircleIcon class="w-4 h-4 flex-shrink-0" />
 <span>{{ t('Cache Driver') }}: <strong class="font-semibold">{{ status.driver }}</strong>
 <span v-if="status.store !== status.driver" class="ml-1">({{ status.store }})</span>
 </span>
 </div>
 </div>

  <!-- Cache Optimization Form -->
  <div v-if="!loading && status" class="mb-8 p-6 bg-admin-theme-surface rounded-xl border border-admin-theme-border shadow-sm">
    <div class="flex items-center justify-between mb-4 pb-2 border-b border-admin-theme-border flex-wrap gap-3">
      <h2 class="text-base font-semibold text-admin-theme-text flex items-center gap-2">
        <Cog6ToothIcon class="w-5 h-5 text-admin-theme-text-muted" />
        {{ t('Optimization & Cache Settings') }}
      </h2>
      <button
        type="button"
        @click="showHelpGuide = true"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-admin-theme-primary/10 text-admin-theme-primary hover:bg-admin-theme-primary/20 text-xs font-semibold rounded-lg transition-all"
      >
        <InformationCircleIcon class="w-4 h-4" />
        {{ t('Cache & Acceleration Guide') }}
      </button>
    </div>

    <!-- Global Cache Switcher -->
    <div class="mb-6 p-4 bg-admin-theme-base/30 rounded-xl border border-admin-theme-border flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h3 class="text-sm font-semibold text-admin-theme-text">{{ t('System-wide Cache Status') }}</h3>
        <p class="text-xs text-admin-theme-text-muted">
          {{ t('Toggle persistent caching across the entire application. Disabling this will bypass all cache stores.') }}
        </p>
      </div>
      <div>
        <label class="relative inline-flex items-center cursor-pointer">
          <input type="checkbox" :checked="configForm.polycms_cache_enabled === 'yes'" @change="configForm.polycms_cache_enabled = ($event.target.checked ? 'yes' : 'no')" class="sr-only peer">
          <div class="w-11 h-6 bg-admin-theme-border peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-admin-theme-primary"></div>
          <span class="ms-3 text-sm font-medium text-admin-theme-text">
            {{ configForm.polycms_cache_enabled === 'yes' ? t('Enabled') : t('Disabled') }}
          </span>
        </label>
      </div>
    </div>

    <!-- Instant Page Loading & Performance Presets -->
    <div class="mb-6 p-5 bg-gradient-to-r from-indigo-900/10 via-purple-900/10 to-blue-900/10 rounded-2xl border border-indigo-500/20">
      <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
        <h3 class="text-sm font-bold text-admin-theme-text flex items-center gap-2">
          <BoltIcon class="w-4 h-4 text-amber-500" />
          {{ t('Instant Page Acceleration Presets') }}
        </h3>
        <span class="text-xs font-mono px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-semibold border border-emerald-500/20">
          Store Driver: {{ configForm.cache_store.toUpperCase() }} (100% Compatible)
        </span>
      </div>
      
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
        <!-- Preset 1: Ultra Instant -->
        <button
          type="button"
          @click="applyPreset('ultra_instant')"
          :class="configForm.cache_preset === 'ultra_instant' ? 'border-admin-theme-primary bg-admin-theme-primary/10 ring-2 ring-admin-theme-primary/30' : 'border-admin-theme-border bg-admin-theme-surface hover:border-admin-theme-primary/50'"
          class="p-3.5 rounded-xl border text-left transition-all relative overflow-hidden"
        >
          <div class="flex items-center justify-between mb-1">
            <span class="text-xs font-bold text-admin-theme-text flex items-center gap-1.5">
              <SparklesIcon class="w-4 h-4 text-amber-500 flex-shrink-0" />
              {{ t('Ultra Instant 0ms') }}
            </span>
            <span v-if="configForm.cache_preset === 'ultra_instant'" class="text-[10px] uppercase font-bold text-admin-theme-primary bg-admin-theme-primary/20 px-1.5 py-0.5 rounded">Active</span>
          </div>
          <p class="text-[11px] text-admin-theme-text-muted leading-tight">
            {{ t('Hover Prefetch + Speculation Rules + Server HTML Cache + HTTP Cache.') }}
          </p>
        </button>

        <!-- Preset 2: Balanced -->
        <button
          type="button"
          @click="applyPreset('balanced')"
          :class="configForm.cache_preset === 'balanced' ? 'border-admin-theme-primary bg-admin-theme-primary/10 ring-2 ring-admin-theme-primary/30' : 'border-admin-theme-border bg-admin-theme-surface hover:border-admin-theme-primary/50'"
          class="p-3.5 rounded-xl border text-left transition-all relative overflow-hidden"
        >
          <div class="flex items-center justify-between mb-1">
            <span class="text-xs font-bold text-admin-theme-text flex items-center gap-1.5">
              <ScaleIcon class="w-4 h-4 text-blue-500 flex-shrink-0" />
              {{ t('Balanced') }}
            </span>
            <span v-if="configForm.cache_preset === 'balanced'" class="text-[10px] uppercase font-bold text-admin-theme-primary bg-admin-theme-primary/20 px-1.5 py-0.5 rounded">Active</span>
          </div>
          <p class="text-[11px] text-admin-theme-text-muted leading-tight">
            {{ t('Hover Prefetch + Browser HTTP Cache. Lightweight & low server memory.') }}
          </p>
        </button>

        <!-- Preset 3: Custom -->
        <button
          type="button"
          @click="configForm.cache_preset = 'custom'"
          :class="configForm.cache_preset === 'custom' ? 'border-admin-theme-primary bg-admin-theme-primary/10 ring-2 ring-admin-theme-primary/30' : 'border-admin-theme-border bg-admin-theme-surface hover:border-admin-theme-primary/50'"
          class="p-3.5 rounded-xl border text-left transition-all relative overflow-hidden"
        >
          <div class="flex items-center justify-between mb-1">
            <span class="text-xs font-bold text-admin-theme-text flex items-center gap-1.5">
              <AdjustmentsHorizontalIcon class="w-4 h-4 text-purple-500 flex-shrink-0" />
              {{ t('Custom Control') }}
            </span>
            <span v-if="configForm.cache_preset === 'custom'" class="text-[10px] uppercase font-bold text-admin-theme-primary bg-admin-theme-primary/20 px-1.5 py-0.5 rounded">Active</span>
          </div>
          <p class="text-[11px] text-admin-theme-text-muted leading-tight">
            {{ t('Manually toggle individual prefetch and response caching components below.') }}
          </p>
        </button>
      </div>

      <!-- Individual Component Toggles -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 p-4 bg-admin-theme-surface/70 rounded-xl border border-admin-theme-border">
        <!-- Instant Prefetch on Hover -->
        <div>
          <div class="flex items-center justify-between mb-1">
            <span class="text-xs font-semibold text-admin-theme-text">{{ t('Hover Prefetch (Client)') }}</span>
            <input type="checkbox" :checked="configForm.instant_prefetch_enabled === 'yes'" @change="configForm.instant_prefetch_enabled = ($event.target.checked ? 'yes' : 'no'); configForm.cache_preset = 'custom'" class="rounded border-admin-theme-border text-admin-theme-primary focus:ring-admin-theme-primary">
          </div>
          <p class="text-[10px] text-admin-theme-text-muted">{{ t('Prefetches HTML when mouse hovers over link.') }}</p>
        </div>

        <!-- Speculation Rules API -->
        <div>
          <div class="flex items-center justify-between mb-1">
            <span class="text-xs font-semibold text-admin-theme-text">{{ t('Speculation Rules (Browser)') }}</span>
            <input type="checkbox" :checked="configForm.speculation_rules_enabled === 'yes'" @change="configForm.speculation_rules_enabled = ($event.target.checked ? 'yes' : 'no'); configForm.cache_preset = 'custom'" class="rounded border-admin-theme-border text-admin-theme-primary focus:ring-admin-theme-primary">
          </div>
          <p class="text-[10px] text-admin-theme-text-muted">{{ t('Background pre-render via Chrome API.') }}</p>
        </div>

        <!-- Server Response HTML Cache -->
        <div>
          <div class="flex items-center justify-between mb-1">
            <span class="text-xs font-semibold text-admin-theme-text">{{ t('Server HTML Cache') }}</span>
            <input type="checkbox" :checked="configForm.response_html_cache_enabled === 'yes'" @change="configForm.response_html_cache_enabled = ($event.target.checked ? 'yes' : 'no'); configForm.cache_preset = 'custom'" class="rounded border-admin-theme-border text-admin-theme-primary focus:ring-admin-theme-primary">
          </div>
          <p class="text-[10px] text-admin-theme-text-muted">{{ t('Caches compiled HTML responses on server.') }}</p>
        </div>

        <!-- Browser HTTP Asset Cache -->
        <div>
          <div class="flex items-center justify-between mb-1">
            <span class="text-xs font-semibold text-admin-theme-text">{{ t('Browser HTTP Cache') }}</span>
            <input type="checkbox" :checked="configForm.browser_http_cache_enabled === 'yes'" @change="configForm.browser_http_cache_enabled = ($event.target.checked ? 'yes' : 'no'); configForm.cache_preset = 'custom'" class="rounded border-admin-theme-border text-admin-theme-primary focus:ring-admin-theme-primary">
          </div>
          <p class="text-[10px] text-admin-theme-text-muted">{{ t('Stale-While-Revalidate HTTP Headers.') }}</p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Card 1: URL Query Canonicalization & Tracking Strategy -->
      <div class="md:col-span-3 p-4 bg-admin-theme-base/40 rounded-xl border border-admin-theme-border space-y-4">
        <h3 class="text-xs font-black uppercase tracking-widest text-admin-theme-text flex items-center justify-between">
          <span class="flex items-center gap-1.5">
            <AdjustmentsHorizontalIcon class="w-4 h-4 text-admin-theme-primary" />
            {{ t('URL Canonicalization & Query Parameter Strategy') }}
          </span>
          <button
            type="button"
            @click="openTopicGuide('canonicalization')"
            class="text-admin-theme-text-muted hover:text-admin-theme-primary transition-colors p-1 flex items-center gap-1 text-[11px] font-semibold bg-admin-theme-primary/10 rounded-md"
            title="Help & details on query canonicalization"
          >
            <QuestionMarkCircleIcon class="w-4 h-4 text-admin-theme-primary" />
            {{ t('Guide') }}
          </button>
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-admin-theme-text-secondary mb-1.5">{{ t('Query Parameter Strategy') }}</label>
            <select
              v-model="configForm.cache_query_param_strategy"
              class="w-full px-3 py-2 text-xs border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
            >
              <option value="ignore_known_tracking">Ignore Known Tracking (Recommended: Strips utm_*, fbclid, etc.)</option>
              <option value="strip_all_query">Strip All Query Parameters (Highest Cache Hit Ratio)</option>
              <option value="preserve_all_query">Preserve All Query Parameters (Strict Unique URL Key)</option>
            </select>
            <p class="text-[11px] text-admin-theme-text-muted mt-1">
              {{ t('Strips tracking query parameters while preserving content filters. Reordered parameters are auto-sorted (ksort).') }}
            </p>
          </div>
          <div>
            <label class="block text-xs font-bold text-admin-theme-text-secondary mb-1.5">{{ t('Ignored Tracking Parameters') }}</label>
            <input
              type="text"
              v-model="configForm.cache_ignored_query_params"
              class="w-full px-3 py-2 text-xs border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary font-mono"
              placeholder="utm_source, utm_medium, fbclid, gclid..."
            />
            <p class="text-[11px] text-admin-theme-text-muted mt-1">
              {{ t('Comma-separated parameters to strip automatically. Wildcards supported (e.g., utm_*, fb_*).') }}
            </p>
          </div>
        </div>
      </div>

      <!-- Card 2: Cache TTL & Stale-While-Revalidate Window -->
      <div class="md:col-span-3 p-4 bg-admin-theme-base/40 rounded-xl border border-admin-theme-border space-y-4">
        <h3 class="text-xs font-black uppercase tracking-widest text-admin-theme-text flex items-center justify-between">
          <span class="flex items-center gap-1.5">
            <ClockIcon class="w-4 h-4 text-amber-500" />
            {{ t('Cache Life Cycle & Stale-While-Revalidate TTL') }}
          </span>
          <button
            type="button"
            @click="openTopicGuide('ttl')"
            class="text-admin-theme-text-muted hover:text-amber-600 dark:hover:text-amber-400 transition-colors p-1 flex items-center gap-1 text-[11px] font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 rounded-md"
            title="Help & details on TTL and SWR"
          >
            <QuestionMarkCircleIcon class="w-4 h-4 text-amber-500" />
            {{ t('Guide') }}
          </button>
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-admin-theme-text-secondary mb-1.5">{{ t('Server Response Cache TTL (Seconds)') }}</label>
            <input
              type="number"
              v-model="configForm.response_cache_ttl"
              class="w-full px-3 py-2 text-xs border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
              placeholder="60"
            />
            <p class="text-[11px] text-admin-theme-text-muted mt-1">
              {{ t('Fresh lifetime in seconds for compiled HTML responses.') }}
            </p>
          </div>
          <div>
            <label class="block text-xs font-bold text-admin-theme-text-secondary mb-1.5">{{ t('Stale-While-Revalidate Window TTL (Seconds)') }}</label>
            <input
              type="number"
              v-model="configForm.page_cache_swr_ttl"
              class="w-full px-3 py-2 text-xs border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
              placeholder="600"
            />
            <p class="text-[11px] text-admin-theme-text-muted mt-1">
              {{ t('Window during which stale cached pages are instantly served while refreshing in background.') }}
            </p>
          </div>
        </div>
      </div>

      <!-- Card 3: Route Allowlist & Bypass Blocklist -->
      <div class="md:col-span-3 p-4 bg-admin-theme-base/40 rounded-xl border border-admin-theme-border space-y-4">
        <h3 class="text-xs font-black uppercase tracking-widest text-admin-theme-text flex items-center justify-between">
          <span class="flex items-center gap-1.5">
            <MapIcon class="w-4 h-4 text-emerald-500" />
            {{ t('Route Allowlist & Critical Flow Blocklist') }}
          </span>
          <button
            type="button"
            @click="openTopicGuide('routes')"
            class="text-admin-theme-text-muted hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors p-1 flex items-center gap-1 text-[11px] font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-md"
            title="Help & details on route allowlists and bypass blocklists"
          >
            <QuestionMarkCircleIcon class="w-4 h-4 text-emerald-500" />
            {{ t('Guide') }}
          </button>
        </h3>
        <div class="space-y-4">
          <!-- Preset Checkboxes by Data Type & Module -->
          <div class="p-3.5 bg-admin-theme-surface/80 rounded-xl border border-admin-theme-border space-y-3">
            <label class="block text-xs font-bold text-admin-theme-text flex items-center justify-between">
              <span>{{ t('Nhanh chọn loại dữ liệu được phép Cache (Route Presets Checklist)') }}</span>
              <span class="text-[11px] font-normal text-admin-theme-text-muted">PolyCMS Core & Integrated Modules</span>
            </label>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
              <div v-for="group in predefinedRouteGroups" :key="group.name" class="p-2.5 bg-admin-theme-base/40 rounded-lg border border-admin-theme-border/60">
                <h4 class="text-[11px] font-bold text-admin-theme-primary mb-2 border-b border-admin-theme-border/40 pb-1 flex items-center justify-between">
                  <span>{{ group.name }}</span>
                </h4>
                <div class="space-y-1.5">
                  <label v-for="rt in group.routes" :key="rt.key" class="flex items-center gap-2 text-xs text-admin-theme-text cursor-pointer hover:text-admin-theme-primary transition-colors">
                    <input
                      type="checkbox"
                      :checked="isRouteChecked(rt.key)"
                      @change="togglePredefinedRoute(rt.key, $event.target.checked)"
                      class="rounded border-admin-theme-border text-admin-theme-primary focus:ring-admin-theme-primary"
                    />
                    <span class="truncate" :title="rt.label">{{ rt.label }}</span>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Manual Custom Allowlist Text Field & Bypass Blocklist -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-admin-theme-text-secondary mb-1.5">
                {{ t('Cached Route Allowlist (Chuỗi Route Tùy Chỉnh / Nhập Tay)') }}
              </label>
              <input
                type="text"
                v-model="configForm.cache_allowlist_routes"
                class="w-full px-3 py-2 text-xs border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary font-mono"
                placeholder="home, page.show, post.show, category.show, product.show, projects.show..."
              />
              <p class="text-[11px] text-admin-theme-text-muted mt-1">
                {{ t('Tự động đồng bộ với Checkbox trên. Bạn có thể tự do gõ bổ sung các route tùy chỉnh của module cá nhân tại đây.') }}
              </p>
            </div>
            <div>
              <label class="block text-xs font-bold text-admin-theme-text-secondary mb-1.5">{{ t('Bypassed Path Blocklist') }}</label>
              <input
                type="text"
                v-model="configForm.cache_blacklisted_paths"
                class="w-full px-3 py-2 text-xs border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary font-mono"
                placeholder="admin, api, cart, checkout, account, logout..."
              />
              <p class="text-[11px] text-admin-theme-text-muted mt-1">
                {{ t('Comma-separated path prefixes that MUST ALWAYS bypass page cache.') }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Card 4: Capacities, Integrity & Decoupled View Counter -->
      <div class="md:col-span-3 p-4 bg-admin-theme-base/40 rounded-xl border border-admin-theme-border space-y-4">
        <h3 class="text-xs font-black uppercase tracking-widest text-admin-theme-text flex items-center justify-between">
          <span class="flex items-center gap-1.5">
            <ShieldCheckIcon class="w-4 h-4 text-purple-500" />
            {{ t('Capacity Limits, Data Integrity & Analytics Counter') }}
          </span>
          <button
            type="button"
            @click="openTopicGuide('integrity')"
            class="text-admin-theme-text-muted hover:text-purple-600 dark:hover:text-purple-400 transition-colors p-1 flex items-center gap-1 text-[11px] font-semibold bg-purple-500/10 text-purple-600 dark:text-purple-400 rounded-md"
            title="Help & details on DB limits, integrity & view counter"
          >
            <QuestionMarkCircleIcon class="w-4 h-4 text-purple-500" />
            {{ t('Guide') }}
          </button>
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-xs font-bold text-admin-theme-text-secondary mb-1.5">{{ t('DB Entry Max Size (KB)') }}</label>
            <input
              type="number"
              v-model="configForm.database_cache_max_entry_kb"
              class="w-full px-3 py-2 text-xs border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
              placeholder="512"
            />
            <p class="text-[11px] text-admin-theme-text-muted mt-1">{{ t('Max KB for single DB entry.') }}</p>
          </div>
          <div>
            <label class="block text-xs font-bold text-admin-theme-text-secondary mb-1.5">{{ t('DB Total Limit (MB)') }}</label>
            <input
              type="number"
              v-model="configForm.database_cache_max_total_mb"
              class="w-full px-3 py-2 text-xs border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
              placeholder="512"
            />
            <p class="text-[11px] text-admin-theme-text-muted mt-1">{{ t('Total DB capacity cap.') }}</p>
          </div>
          <div>
            <label class="block text-xs font-bold text-admin-theme-text-secondary mb-1.5">{{ t('Integrity Verification') }}</label>
            <select
              v-model="configForm.cache_integrity_mode"
              class="w-full px-3 py-2 text-xs border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
            >
              <option value="sha256">SHA-256 Checksum (Default)</option>
              <option value="hmac_sha256">HMAC-SHA256 Signed Signature</option>
              <option value="disabled">Disabled</option>
            </select>
            <p class="text-[11px] text-admin-theme-text-muted mt-1">{{ t('Payload verification mode.') }}</p>
          </div>
          <div>
            <label class="block text-xs font-bold text-admin-theme-text-secondary mb-1.5">{{ t('Decoupled View Counter') }}</label>
            <select
              v-model="configForm.durable_view_counter_enabled"
              class="w-full px-3 py-2 text-xs border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
            >
              <option value="yes">Enabled (Bucket Batching)</option>
              <option value="no">Disabled</option>
            </select>
            <p class="text-[11px] text-admin-theme-text-muted mt-1">{{ t('Prevent cache purge on view.') }}</p>
          </div>
        </div>
      </div>

      <!-- Store Driver Connections Header -->
      <h3 class="text-xs font-black uppercase tracking-widest text-admin-theme-text flex items-center justify-between md:col-span-3 pt-2 border-t border-admin-theme-border/40">
        <span class="flex items-center gap-1.5">
          <ServerStackIcon class="w-4 h-4 text-indigo-500" />
          {{ t('Store Driver Connections & Memory Storage') }}
        </span>
        <button
          type="button"
          @click="openTopicGuide('stores')"
          class="text-admin-theme-text-muted hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors p-1 flex items-center gap-1 text-[11px] font-semibold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-md"
          title="Help & details on Cache Drivers"
        >
          <QuestionMarkCircleIcon class="w-4 h-4 text-indigo-500" />
          {{ t('Guide') }}
        </button>
      </h3>

      <!-- Default Cache Store -->
      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-admin-theme-text-secondary mb-2">{{ t('Default Cache Store') }}</label>
        <select
          v-model="configForm.cache_store"
          class="w-full px-3 py-2 text-sm border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-2 focus:ring-admin-theme-primary/20 transition-all"
        >
          <option value="file">File (Default)</option>
          <option value="database">Database</option>
          <option value="redis">Redis (High Performance)</option>
        </select>
        <p class="text-[11px] text-admin-theme-text-muted mt-1 leading-normal">
          {{ t('Redis is recommended for high-traffic sites to avoid disk I/O bottlenecks.') }}
        </p>
      </div>

      <!-- Session Store Driver -->
      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-admin-theme-text-secondary mb-2">{{ t('Session Driver') }}</label>
        <select
          v-model="configForm.session_driver"
          class="w-full px-3 py-2 text-sm border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-2 focus:ring-admin-theme-primary/20 transition-all"
        >
          <option value="file">File (Default)</option>
          <option value="database">Database</option>
          <option value="redis">Redis (High Performance)</option>
        </select>
        <p class="text-[11px] text-admin-theme-text-muted mt-1 leading-normal">
          {{ t('Redis or Database avoids logout issues on multiple server nodes.') }}
        </p>
      </div>

      <!-- Queue Connection -->
      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-admin-theme-text-secondary mb-2">{{ t('Queue Connection') }}</label>
        <select
          v-model="configForm.queue_connection"
          class="w-full px-3 py-2 text-sm border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-2 focus:ring-admin-theme-primary/20 transition-all"
        >
          <option value="sync">Synchronous (sync)</option>
          <option value="database">Database (queue)</option>
          <option value="redis">Redis (High Performance)</option>
        </select>
        <p class="text-[11px] text-admin-theme-text-muted mt-1 leading-normal">
          {{ t('Sync processes tasks immediately. Database/Redis runs tasks in the background.') }}
        </p>
      </div>

      <!-- Redis Server Settings -->
      <div v-if="isRedisEnabled" class="md:col-span-3 p-4 bg-admin-theme-base/40 rounded-xl border border-admin-theme-border mt-2 space-y-4">
        <h3 class="text-xs font-black uppercase tracking-widest text-admin-theme-text flex items-center gap-1.5">
          <ServerStackIcon class="w-4 h-4 text-admin-theme-primary" />
          {{ t('Redis Server Configuration') }}
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-admin-theme-text-secondary mb-1.5">{{ t('Host') }}</label>
            <input
              type="text"
              v-model="configForm.redis_host"
              class="w-full px-3 py-1.5 text-xs border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
              placeholder="127.0.0.1"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-admin-theme-text-secondary mb-1.5">{{ t('Port') }}</label>
            <input
              type="number"
              v-model="configForm.redis_port"
              class="w-full px-3 py-1.5 text-xs border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
              placeholder="6379"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-admin-theme-text-secondary mb-1.5">{{ t('Password') }}</label>
            <input
              type="password"
              v-model="configForm.redis_password"
              class="w-full px-3 py-1.5 text-xs border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
              placeholder="Optional"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-admin-theme-text-secondary mb-1.5">{{ t('Cache Database Index') }}</label>
            <input
              type="number"
              v-model="configForm.redis_cache_db"
              class="w-full px-3 py-1.5 text-xs border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
              placeholder="1"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-admin-theme-text-secondary mb-1.5">{{ t('Cache Prefix') }}</label>
            <input
              type="text"
              v-model="configForm.redis_cache_prefix"
              class="w-full px-3 py-1.5 text-xs border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
              placeholder="polycms_cache_"
            />
          </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
          <button
            type="button"
            @click="testRedis"
            :disabled="testingRedis"
            class="inline-flex items-center gap-1.5 px-4 py-1.5 border border-admin-theme-primary text-admin-theme-primary hover:bg-admin-theme-primary/10 text-xs font-medium rounded-lg transition-all disabled:opacity-50"
          >
            <ArrowPathIcon v-if="testingRedis" class="w-3.5 h-3.5 animate-spin" />
            <BoltIcon v-else class="w-3.5 h-3.5" />
            {{ testingRedis ? t('Testing...') : t('Test Redis Connection') }}
          </button>
          
          <span v-if="redisTestResult !== null" :class="redisTestSuccess ? 'text-green-600 dark:text-green-400' : 'text-red-500'" class="text-xs font-semibold">
            {{ redisTestResult }}
          </span>
        </div>
      </div>

      <!-- Action Footer -->
      <div class="md:col-span-3 pt-4 border-t border-admin-theme-border/50 flex items-center justify-between">
        <span class="text-[11px] text-admin-theme-text-muted">
          * {{ t('Note: Saving cache optimization changes will flush all application configurations.') }}
        </span>
        <button
          type="button"
          @click="saveConfig"
          :disabled="savingConfig"
          class="inline-flex items-center gap-2 px-5 py-2.5 bg-admin-theme-primary hover:bg-admin-theme-primary/95 text-white text-sm font-medium rounded-lg transition-all disabled:opacity-50 shadow-sm"
        >
          <ArrowPathIcon v-if="savingConfig" class="w-4 h-4 animate-spin" />
          <CheckCircleIcon v-else class="w-4 h-4" />
          {{ savingConfig ? t('Saving...') : t('Save Configuration') }}
        </button>
      </div>
    </div>
  </div>

 <!-- Loading State -->
 <div v-if="loading" class="text-center py-12">
 <ArrowPathIcon class="w-6 h-6 animate-spin mx-auto text-admin-theme-text-muted" />
 <p class="text-sm text-admin-theme-text-muted mt-2">{{ t('Loading cache status...') }}</p>
 </div>

 <!-- Cache Type Groups -->
 <template v-else-if="status">
 <div v-for="group in groupedTypes" :key="group.key" class="mb-8">
 <h2 class="text-base font-semibold text-admin-theme-text-secondary mb-3 pb-2 border-b border-admin-theme-border flex items-center gap-2">
 <component :is="groupIcon(group.key)" class="w-5 h-5 text-admin-theme-text-muted" />
 {{ t(group.label) }}
 </h2>

 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
 <div
 v-for="cacheType in group.items"
 :key="cacheType.key"
 class="relative p-5 bg-admin-theme-surface rounded-xl border border-admin-theme-border shadow-sm transition-all hover:shadow-md"
 >
 <!-- Handler badge (module-managed) -->
 <div v-if="cacheType.handler" class="absolute top-3 right-3">
 <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 rounded-full">
 <PuzzlePieceIcon class="w-3 h-3" />
 {{ cacheType.handler }}
 </span>
 </div>

 <div class="flex items-start gap-4">
 <div class="flex-shrink-0 p-2.5 rounded-lg" :class="typeIconBg(cacheType)">
 <component :is="typeIcon(cacheType)" class="w-5 h-5" :class="typeIconColor(cacheType)" />
 </div>
 <div class="flex-1 min-w-0">
 <h3 class="text-sm font-bold text-admin-theme-text">{{ cacheType.label }}</h3>
 <p class="text-xs text-admin-theme-text-muted mt-0.5 leading-relaxed">{{ cacheType.description }}</p>

 <!-- Type-specific info badges -->
 <div class="flex flex-wrap gap-2 mt-2">
 <template v-if="cacheType.key ==='view' && cacheType.info?.compiled_count !== undefined">
 <span class="info-badge">
 {{ cacheType.info.compiled_count }} {{ t('compiled files') }}
 </span>
 </template>
 <template v-if="cacheType.key ==='config'">
 <span :class="cacheType.info?.cached ?'info-badge info-badge--active' :'info-badge'">
 {{ cacheType.info?.cached ? t('Cached') : t('Not cached') }}
 </span>
 </template>
 <template v-if="cacheType.key ==='route'">
 <span :class="cacheType.info?.cached ?'info-badge info-badge--active' :'info-badge'">
 {{ cacheType.info?.cached ? t('Cached') : t('Not cached') }}
 </span>
 </template>
 <template v-if="cacheType.key ==='event'">
 <span :class="cacheType.info?.cached ?'info-badge info-badge--active' :'info-badge'">
 {{ cacheType.info?.cached ? t('Cached') : t('Not cached') }}
 </span>
 </template>
 <template v-if="cacheType.key ==='application'">
 <span class="info-badge">
 {{ cacheType.info?.driver ||'—' }}
 </span>
 </template>
 <template v-if="cacheType.key ==='opcache'">
 <span :class="cacheType.info?.enabled ?'info-badge info-badge--active' :'info-badge info-badge--inactive'">
 {{ cacheType.info?.enabled ? t('Enabled') : t('Disabled') }}
 </span>
 <template v-if="cacheType.info?.enabled">
 <span class="info-badge">{{ cacheType.info?.cached_scripts }} {{ t('scripts') }}</span>
 <span class="info-badge">{{ cacheType.info?.hit_rate }}% {{ t('hit rate') }}</span>
 </template>
 </template>
 </div>
 </div>
 </div>

 <!-- Action buttons -->
 <div class="mt-4 pt-3 border-t border-admin-theme-border/50 flex items-center gap-2">
 <button
 @click.stop="openDetail(cacheType)"
 class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md transition-all
 text-admin-theme-primary dark:text-admin-theme-primary bg-admin-theme-primary/10 hover:bg-admin-theme-primary/15 hover:bg-admin-theme-primary/15"
 >
 <EyeIcon class="w-3.5 h-3.5" />
 {{ t('Details') }}
 </button>
 <button
 v-if="cacheType.clearable"
 @click="clearSingle(cacheType.key)"
 :disabled="clearing[cacheType.key]"
 class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md transition-all
 text-admin-theme-text-secondary bg-admin-theme-base/50 hover:bg-red-50 hover:text-red-600
 dark:hover:bg-red-900/20 dark:hover:text-red-400 disabled:opacity-50 disabled:cursor-not-allowed"
 >
 <ArrowPathIcon v-if="clearing[cacheType.key]" class="w-3.5 h-3.5 animate-spin" />
 <TrashIcon v-else class="w-3.5 h-3.5" />
 {{ clearing[cacheType.key] ? t('Clearing...') : t('Clear') }}
 </button>
 <span v-else class="text-xs text-admin-theme-text-muted italic">
 {{ t('Not available') }}
 </span>
 </div>

 <!-- Success flash -->
 <transition name="fade">
 <div v-if="cleared[cacheType.key]" class="absolute inset-0 flex items-center justify-center bg-green-50/90 dark:bg-green-900/40 rounded-xl z-10">
 <div class="flex items-center gap-2 text-green-600 dark:text-green-400 font-medium">
 <CheckCircleIcon class="w-5 h-5" />
 {{ t('Cleared!') }}
 </div>
 </div>
 </transition>
 </div>
 </div>
 </div>
 </template>

 <!-- Detail Modal -->
 <teleport to="body">
 <transition name="fade">
 <div v-if="detailModal.open" class="fixed inset-0 z-[9999] flex items-center justify-center p-4" @click.self="detailModal.open = false">
 <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
 <div class="relative bg-admin-theme-surface rounded-2xl shadow-2xl w-full max-w-3xl max-h-[85vh] flex flex-col z-10 border border-admin-theme-border">
 <!-- Modal Header -->
 <div class="flex items-center justify-between px-6 py-4 border-b border-admin-theme-border flex-shrink-0">
 <div class="flex items-center gap-3">
 <div class="p-2 rounded-lg bg-admin-theme-primary/10/30">
 <component :is="typeIcon(detailModal.type)" class="w-5 h-5 text-admin-theme-primary" />
 </div>
 <div>
 <h3 class="text-lg font-bold text-admin-theme-text">{{ detailModal.type?.label }}</h3>
 <p class="text-xs text-admin-theme-text-muted">{{ detailModal.type?.description }}</p>
 </div>
 </div>
 <button @click="detailModal.open = false" class="p-1.5 text-admin-theme-text-muted hover:text-admin-theme-text rounded-lg hover:bg-admin-theme-base">
 <XMarkIcon class="w-5 h-5" />
 </button>
 </div>

 <!-- Modal Body -->
 <div class="flex-1 overflow-y-auto px-6 py-4">
 <div v-if="detailModal.loading" class="flex justify-center py-12">
 <ArrowPathIcon class="w-6 h-6 animate-spin text-admin-theme-primary" />
 </div>
 <div v-else-if="detailModal.data?.detail" class="space-y-5">

 <!-- Key-Value pairs for simple detail -->
 <template v-if="detailModal.data.detail.path">
 <div class="detail-row">
 <span class="detail-label">{{ t('Path') }}</span>
 <code class="detail-value-code">{{ detailModal.data.detail.path }}</code>
 </div>
 </template>
 <template v-if="detailModal.data.detail.cached !== undefined">
 <div class="detail-row">
 <span class="detail-label">{{ t('Status') }}</span>
 <span :class="detailModal.data.detail.cached ?'text-green-600 dark:text-green-400' :'text-gray-500'">
 {{ detailModal.data.detail.cached ?'●' + t('Cached') :'○' + t('Not cached') }}
 </span>
 </div>
 </template>
 <template v-if="detailModal.data.detail.size">
 <div class="detail-row">
 <span class="detail-label">{{ t('File Size') }}</span>
 <span class="detail-value">{{ formatSize(detailModal.data.detail.size) }}</span>
 </div>
 </template>
 <template v-if="detailModal.data.detail.modified">
 <div class="detail-row">
 <span class="detail-label">{{ t('Last Modified') }}</span>
 <span class="detail-value">{{ detailModal.data.detail.modified }}</span>
 </div>
 </template>

 <!-- Driver/Config (application cache) -->
 <template v-if="detailModal.data.detail.driver">
 <div class="detail-row">
 <span class="detail-label">{{ t('Driver') }}</span>
 <span class="detail-value font-semibold">{{ detailModal.data.detail.driver }}</span>
 </div>
 </template>
 <template v-if="detailModal.data.detail.store_config">
 <div>
 <h4 class="text-sm font-semibold text-admin-theme-text-secondary mb-2">{{ t('Store Configuration') }}</h4>
 <div class="bg-admin-theme-base/50 rounded-lg p-3 text-xs font-mono overflow-x-auto">
 <pre class="text-admin-theme-text-secondary whitespace-pre-wrap">{{ JSON.stringify(detailModal.data.detail.store_config, null, 2) }}</pre>
 </div>
 </div>
 </template>
 <template v-if="detailModal.data.detail.files?.count !== undefined">
 <div class="detail-row">
 <span class="detail-label">{{ t('Cache Files') }}</span>
 <span class="detail-value">{{ detailModal.data.detail.files.count }} {{ t('files') }} ({{ formatSize(detailModal.data.detail.files.total_size) }})</span>
 </div>
 </template>

 <!-- View cache file list -->
 <template v-if="detailModal.data.detail.total_files !== undefined">
 <div class="detail-row">
 <span class="detail-label">{{ t('Compiled Views') }}</span>
 <span class="detail-value">{{ detailModal.data.detail.total_files }} {{ t('files') }} ({{ formatSize(detailModal.data.detail.total_size) }})</span>
 </div>
 </template>
 <template v-if="detailModal.data.detail.files?.length">
 <div>
 <h4 class="text-sm font-semibold text-admin-theme-text-secondary mb-2">{{ t('File List') }} <span class="text-xs font-normal text-gray-400">({{ t('max 100') }})</span></h4>
 <div class="bg-admin-theme-base/50 rounded-lg overflow-hidden">
 <div class="max-h-64 overflow-y-auto">
 <table class="w-full text-xs">
 <thead class="bg-admin-theme-base sticky top-0">
 <tr>
 <th class="px-3 py-2 text-left font-medium text-admin-theme-text-secondary">{{ t('File') }}</th>
 <th class="px-3 py-2 text-right font-medium text-admin-theme-text-secondary">{{ t('Size') }}</th>
 <th class="px-3 py-2 text-right font-medium text-admin-theme-text-secondary">{{ t('Modified') }}</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
 <tr v-for="f in detailModal.data.detail.files" :key="f.name" class="hover:bg-black/10 dark:hover:bg-white/10/50">
 <td class="px-3 py-1.5 font-mono text-admin-theme-text-secondary truncate max-w-[300px]" :title="f.name">{{ f.name }}</td>
 <td class="px-3 py-1.5 text-right text-admin-theme-text-muted whitespace-nowrap">{{ formatSize(f.size) }}</td>
 <td class="px-3 py-1.5 text-right text-admin-theme-text-muted whitespace-nowrap">{{ f.modified }}</td>
 </tr>
 </tbody>
 </table>
 </div>
 </div>
 </div>
 </template>

 <!-- PolyCMS cache keys -->
 <template v-if="detailModal.data.detail.keys?.length">
 <div>
 <h4 class="text-sm font-semibold text-admin-theme-text-secondary mb-2">{{ t('Cache Keys') }}</h4>
 <div class="space-y-1.5">
 <div v-for="k in detailModal.data.detail.keys" :key="k.key" class="flex items-center justify-between px-3 py-2 bg-admin-theme-base/50 rounded-lg">
 <code class="text-xs text-admin-theme-text-secondary">{{ k.key }}</code>
 <span :class="k.exists ?'text-green-600 dark:text-green-400' :'text-admin-theme-text-muted'" class="text-xs font-medium">
 {{ k.exists ?'●' + t('Active') :'○' + t('Empty') }}
 </span>
 </div>
 </div>
 </div>
 <div class="detail-row" v-if="detailModal.data.detail.cache_driver">
 <span class="detail-label">{{ t('Cache Driver') }}</span>
 <span class="detail-value">{{ detailModal.data.detail.cache_driver }}</span>
 </div>
 </template>

 <!-- OPcache detail -->
 <template v-if="detailModal.data.detail.memory">
 <div>
 <h4 class="text-sm font-semibold text-admin-theme-text-secondary mb-2">{{ t('Memory Usage') }}</h4>
 <div class="grid grid-cols-3 gap-3">
 <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 text-center">
 <div class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ detailModal.data.detail.memory.used_mb }} MB</div>
 <div class="text-xs text-blue-500/70">{{ t('Used') }}</div>
 </div>
 <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 text-center">
 <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ detailModal.data.detail.memory.free_mb }} MB</div>
 <div class="text-xs text-green-500/70">{{ t('Free') }}</div>
 </div>
 <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3 text-center">
 <div class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ detailModal.data.detail.memory.wasted_mb }} MB</div>
 <div class="text-xs text-amber-500/70">{{ t('Wasted') }} ({{ detailModal.data.detail.memory.wasted_pct }}%)</div>
 </div>
 </div>
 </div>
 </template>
 <template v-if="detailModal.data.detail.statistics">
 <div>
 <h4 class="text-sm font-semibold text-admin-theme-text-secondary mb-2">{{ t('Statistics') }}</h4>
 <div class="grid grid-cols-2 gap-2">
 <div class="detail-row"><span class="detail-label">{{ t('Cached Scripts') }}</span><span class="detail-value">{{ detailModal.data.detail.statistics.cached_scripts }}</span></div>
 <div class="detail-row"><span class="detail-label">{{ t('Cache Keys') }}</span><span class="detail-value">{{ detailModal.data.detail.statistics.cached_keys }} / {{ detailModal.data.detail.statistics.max_keys }}</span></div>
 <div class="detail-row"><span class="detail-label">{{ t('Hits / Misses') }}</span><span class="detail-value">{{ detailModal.data.detail.statistics.hits?.toLocaleString() }} / {{ detailModal.data.detail.statistics.misses?.toLocaleString() }}</span></div>
 <div class="detail-row"><span class="detail-label">{{ t('Hit Rate') }}</span><span class="detail-value font-bold text-green-600 dark:text-green-400">{{ detailModal.data.detail.statistics.hit_rate }}%</span></div>
 </div>
 </div>
 </template>
 <template v-if="detailModal.data.detail.top_scripts?.length">
 <div>
 <h4 class="text-sm font-semibold text-admin-theme-text-secondary mb-2">{{ t('Top Cached Scripts') }}</h4>
 <div class="bg-admin-theme-base/50 rounded-lg overflow-hidden">
 <div class="max-h-48 overflow-y-auto">
 <table class="w-full text-xs">
 <thead class="bg-admin-theme-base sticky top-0"><tr>
 <th class="px-3 py-2 text-left font-medium text-admin-theme-text-secondary">{{ t('Script') }}</th>
 <th class="px-3 py-2 text-right font-medium text-admin-theme-text-secondary">{{ t('Hits') }}</th>
 <th class="px-3 py-2 text-right font-medium text-admin-theme-text-secondary">{{ t('Memory') }}</th>
 </tr></thead>
 <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
 <tr v-for="s in detailModal.data.detail.top_scripts" :key="s.full_path" class="hover:bg-black/10 dark:hover:bg-white/10/50">
 <td class="px-3 py-1.5 font-mono text-admin-theme-text-secondary truncate max-w-[300px]" :title="s.full_path">{{ s.path }}</td>
 <td class="px-3 py-1.5 text-right text-gray-500">{{ s.hits?.toLocaleString() }}</td>
 <td class="px-3 py-1.5 text-right text-gray-500">{{ formatSize(s.memory) }}</td>
 </tr>
 </tbody>
 </table>
 </div>
 </div>
 </div>
 </template>

  <!-- Disabled OPcache -->
  <template v-if="detailModal.data.detail.reason">
    <div class="text-sm text-admin-theme-text-muted italic py-4">{{ detailModal.data.detail.reason }}</div>
  </template>
  </div>
  <div v-else class="text-center py-8 text-sm text-gray-500">{{ t('No detail data available') }}</div>
  </div>

  <!-- Modal Footer -->
  <div class="flex justify-end gap-3 px-6 py-3 border-t border-admin-theme-border flex-shrink-0">
    <button @click="detailModal.open = false" class="px-4 py-2 text-sm font-medium text-admin-theme-text-secondary bg-admin-theme-base hover:bg-admin-theme-base rounded-lg transition-colors">
      {{ t('Close') }}
    </button>
  </div>
  </div>
  </div>
  </transition>
  </teleport>

  <!-- Help Guide Modal -->
  <teleport to="body">
    <transition name="fade">
      <div v-if="showHelpGuide" class="fixed inset-0 z-[9999] flex items-center justify-center p-4" @click.self="showHelpGuide = false">
       <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
       <div class="relative bg-admin-theme-surface rounded-2xl shadow-2xl w-full max-w-3xl max-h-[85vh] flex flex-col z-10 border border-admin-theme-border">
         <!-- Modal Header -->
         <div class="flex items-center justify-between px-6 py-4 border-b border-admin-theme-border flex-shrink-0">
           <div class="flex items-center gap-3">
             <div class="p-2 rounded-lg bg-amber-500/10 text-amber-500">
               <BoltIcon class="w-5 h-5" />
             </div>
             <div>
               <h3 class="text-lg font-bold text-admin-theme-text">{{ t('Instant Acceleration & Cache Help Guide') }}</h3>
               <p class="text-xs text-admin-theme-text-muted">{{ t('Detailed guide on optimization presets, prefetching, and cache stores.') }}</p>
             </div>
           </div>
           <button @click="showHelpGuide = false" class="p-1.5 text-admin-theme-text-muted hover:text-admin-theme-text rounded-lg hover:bg-admin-theme-base">
             <XMarkIcon class="w-5 h-5" />
           </button>
         </div>

         <!-- Modal Body -->
          <div class="flex-1 overflow-y-auto px-6 py-5 space-y-6 text-sm text-admin-theme-text">
            <!-- Section 1: Presets -->
            <div id="guide-topic-presets" class="space-y-2 p-4 rounded-xl border border-admin-theme-border/60 bg-admin-theme-base/20">
              <h4 class="font-bold text-admin-theme-primary flex items-center gap-1.5 text-base">
                <SparklesIcon class="w-5 h-5 text-amber-500 flex-shrink-0" />
                {{ t('1. Optimization Presets & Instant Acceleration') }}
              </h4>
              <div class="space-y-3 text-xs leading-relaxed text-admin-theme-text-secondary">
                <p>
                  <strong class="text-admin-theme-text font-bold">Ultra Instant 0ms (Recommended):</strong> {{ t('Combines Client Hover Prefetch + Chrome Speculation Rules + Server HTML Cache + Browser Cache. When hovering over links, target HTML is preloaded in under 100ms before click, delivering instant 0ms visual transitions.') }}
                </p>
                <p>
                  <strong class="text-admin-theme-text font-bold">Balanced Mode:</strong> {{ t('Enables Hover Prefetch and Browser HTTP Cache headers while skipping server HTML storage. Ideal for environments with tight disk/RAM constraints.') }}
                </p>
                <p>
                  <strong class="text-admin-theme-text font-bold">Custom Control:</strong> {{ t('Grants full granular control to toggle individual prefetch and response caching layers below.') }}
                </p>
              </div>
            </div>

            <!-- Section 2: Canonicalization & Query Strategy -->
            <div id="guide-topic-canonicalization" class="space-y-2 p-4 rounded-xl border border-admin-theme-border/60 bg-admin-theme-base/20">
              <h4 class="font-bold text-admin-theme-primary flex items-center gap-1.5 text-base">
                <AdjustmentsHorizontalIcon class="w-5 h-5 text-admin-theme-primary flex-shrink-0" />
                {{ t('2. URL Canonicalization & Query Parameter Strategy') }}
              </h4>
              <div class="space-y-3 text-xs leading-relaxed text-admin-theme-text-secondary">
                <p>
                  <strong class="text-admin-theme-text font-bold">RFC 3986 Path Case Preservation:</strong> {{ t('PolyCMS strictly preserves URL path letter case (e.g. /Products/Laptop vs /products/laptop) to comply with RFC 3986 standards and prevent cache collisions.') }}
                </p>
                <p>
                  <strong class="text-admin-theme-text font-bold">Ignore Known Tracking (Default):</strong> {{ t('Automatically strips marketing parameters (utm_source, utm_medium, fbclid, gclid, _ga, ref, etc.) so ad clicks hit the main cached page while preserving real filter parameters.') }}
                </p>
                <p>
                  <strong class="text-admin-theme-text font-bold">Alphabetical Query Sorting (ksort):</strong> {{ t('Parameters like ?color=blue&size=M and ?size=M&color=blue are automatically sorted alphabetically to map to the exact same cache key.') }}
                </p>
              </div>
            </div>

            <!-- Section 3: TTL & SWR Window -->
            <div id="guide-topic-ttl" class="space-y-2 p-4 rounded-xl border border-admin-theme-border/60 bg-admin-theme-base/20">
              <h4 class="font-bold text-admin-theme-primary flex items-center gap-1.5 text-base">
                <ClockIcon class="w-5 h-5 text-amber-500 flex-shrink-0" />
                {{ t('3. Server Response TTL & Stale-While-Revalidate (SWR)') }}
              </h4>
              <div class="space-y-3 text-xs leading-relaxed text-admin-theme-text-secondary">
                <p>
                  <strong class="text-admin-theme-text font-bold">Fresh Response TTL:</strong> {{ t('Defines how long a compiled HTML response remains completely fresh before revalidation is required (Default: 60s).') }}
                </p>
                <p>
                  <strong class="text-admin-theme-text font-bold">Stale-While-Revalidate (SWR Window):</strong> {{ t('When an entry expires, PolyCMS serves the stale cache entry instantly (2-5ms) to incoming visitors while silently queueing an asynchronous background refresh to update the cache envelope.') }}
                </p>
              </div>
            </div>

            <!-- Section 4: Route Allowlist & Bypass Blocklist -->
            <div id="guide-topic-routes" class="space-y-2 p-4 rounded-xl border border-admin-theme-border/60 bg-admin-theme-base/20">
              <h4 class="font-bold text-admin-theme-primary flex items-center gap-1.5 text-base">
                <MapIcon class="w-5 h-5 text-emerald-500 flex-shrink-0" />
                {{ t('4. Route Allowlist & Critical Bypass Blocklist') }}
              </h4>
              <div class="space-y-3 text-xs leading-relaxed text-admin-theme-text-secondary">
                <p>
                  <strong class="text-admin-theme-text font-bold">Route Allowlist:</strong> {{ t('Only named public routes (e.g. home, page.show, post.show, category.show, product.show, projects.show) are eligible for server HTML caching.') }}
                </p>
                <p>
                  <strong class="text-admin-theme-text font-bold">Automatic Critical Flow Bypass:</strong> {{ t('Shopping cart (/cart), Checkout (/checkout), Account (/account), Authentication (/login, /logout), Admin panel (/admin), REST API (/api/v1/*), Inertia requests (X-Inertia), and requests with Set-Cookie or private headers are ALWAYS 100% BYPASSED for security.') }}
                </p>
              </div>
            </div>

            <!-- Section 5: Capacity, Integrity & View Counter -->
            <div id="guide-topic-integrity" class="space-y-2 p-4 rounded-xl border border-admin-theme-border/60 bg-admin-theme-base/20">
              <h4 class="font-bold text-admin-theme-primary flex items-center gap-1.5 text-base">
                <ShieldCheckIcon class="w-5 h-5 text-purple-500 flex-shrink-0" />
                {{ t('5. Capacity Limits, Payload Integrity & View Counter') }}
              </h4>
              <div class="space-y-3 text-xs leading-relaxed text-admin-theme-text-secondary">
                <p>
                  <strong class="text-admin-theme-text font-bold">Database Store Capacity Caps:</strong> {{ t('When using Database Cache Mode on SMB/shared hosting, single entries are capped at 512KB and total storage is capped at 512MB to prevent database bloat.') }}
                </p>
                <p>
                  <strong class="text-admin-theme-text font-bold">SHA-256 Checksum Verification:</strong> {{ t('Every cached HTML payload is checksum-verified upon retrieval to prevent corrupted cache responses from reaching users.') }}
                </p>
                <p>
                  <strong class="text-admin-theme-text font-bold">Decoupled View Counter:</strong> {{ t('Page views for products/posts are buffered into post_view_buckets without invalidating HTML page cache on every visit.') }}
                </p>
              </div>
            </div>

            <!-- Section 6: Cache Stores -->
            <div id="guide-topic-stores" class="space-y-2 p-4 rounded-xl border border-admin-theme-border/60 bg-admin-theme-base/20">
              <h4 class="font-bold text-admin-theme-primary flex items-center gap-1.5 text-base">
                <ServerStackIcon class="w-5 h-5 text-indigo-500 flex-shrink-0" />
                {{ t('6. Store Driver Compatibility (File, Database, Redis)') }}
              </h4>
              <div class="space-y-3 text-xs leading-relaxed text-admin-theme-text-secondary">
                <p><strong class="text-emerald-500 font-bold">File Store (Default):</strong> {{ t('Works out of the box on standard web hosting without external software requirements.') }}</p>
                <p><strong class="text-amber-500 font-bold">Database Store:</strong> {{ t('Stores cache entries in MySQL/PostgreSQL. Excellent for multi-server VPS setups sharing a central database.') }}</p>
                <p><strong class="text-purple-500 font-bold">Redis Store (High Performance):</strong> {{ t('In-memory cache with sub-millisecond response latency. Recommended for high-traffic sites with thousands of active visitors.') }}</p>
              </div>
            </div>

            <!-- Section 7: Redis Config -->
            <div id="guide-topic-redis" class="space-y-2 p-4 rounded-xl border border-admin-theme-border/60 bg-admin-theme-base/20">
              <h4 class="font-bold text-admin-theme-primary flex items-center gap-1.5 text-base">
                <ServerStackIcon class="w-5 h-5 text-admin-theme-primary flex-shrink-0" />
                {{ t('7. Redis Server Configuration & Testing') }}
              </h4>
              <div class="space-y-3 text-xs leading-relaxed text-admin-theme-text-secondary">
                <p>{{ t('Specify Host (127.0.0.1), Port (6379), Password (optional), and Database Index (default: 1). Use the "Test Redis Connection" button to verify server responsiveness before saving.') }}</p>
              </div>
            </div>

            <!-- Section 8: Live Server & PHP-FPM Process Pool Optimization -->
            <div id="guide-topic-server-tuning" class="space-y-2 p-4 rounded-xl border border-admin-theme-border/60 bg-admin-theme-base/20">
              <h4 class="font-bold text-admin-theme-primary flex items-center gap-1.5 text-base">
                <WrenchScrewdriverIcon class="w-5 h-5 text-emerald-500 flex-shrink-0" />
                {{ t('8. PHP-FPM Process Pool & Server Tuning Guide (High Concurrency)') }}
              </h4>
              <div class="space-y-3 text-xs leading-relaxed text-admin-theme-text-secondary">
                <p>
                  {{ t('When experiencing high concurrent traffic, queueing latency depends on the PHP-FPM Process Pool / Worker Limit on your server. To prevent worker bottlenecks, adjust the settings in your Web Control Panel:') }}
                </p>
                <div class="p-3 bg-admin-theme-surface rounded-lg border border-admin-theme-border space-y-2">
                  <p><strong class="text-admin-theme-text font-bold">{{ t('Example 1: CyberPanel / OpenLiteSpeed WebAdmin Panel:') }}</strong></p>
                  <ul class="list-disc list-inside space-y-1 pl-1 text-[11px]">
                    <li>{{ t('Access WebAdmin Panel (e.g. https://your-vps:7080).') }}</li>
                    <li>{{ t('Navigate to Server Configuration → External App → lsphp82.') }}</li>
                    <li>{{ t('Increase Max Connections from 10-20 up to 50 - 100.') }}</li>
                    <li>{{ t('Enable LSCache Module at the Web Server level to serve cached HTML directly from RAM.') }}</li>
                  </ul>
                </div>
                <div class="p-3 bg-admin-theme-surface rounded-lg border border-admin-theme-border space-y-2">
                  <p><strong class="text-admin-theme-text font-bold">{{ t('Example 2: Nginx / cPanel / DirectAdmin / Plesk (Standard PHP-FPM):') }}</strong></p>
                  <ul class="list-disc list-inside space-y-1 pl-1 text-[11px]">
                    <li>{{ t('Open pool config /etc/php-fpm.d/www.conf or your Panels PHP FPM Pool settings.') }}</li>
                    <li>{{ t('Increase pm.max_children from 10-15 up to 50 - 100 (depending on VPS RAM).') }}</li>
                    <li>{{ t('Set pm.max_requests to 1000 for automatic memory cleanup.') }}</li>
                  </ul>
                </div>
                <p class="text-[11px] italic text-admin-theme-text-muted">
                  * {{ t('NOTE: Setting names may vary depending on your Web Control Panel (CyberPanel, cPanel, DirectAdmin, Plesk, AAPanel, HestiaCP, etc.).') }}
                </p>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
         <div class="flex justify-end gap-3 px-6 py-3 border-t border-admin-theme-border flex-shrink-0">
           <button @click="showHelpGuide = false" class="px-4 py-2 text-sm font-medium text-white bg-admin-theme-primary hover:bg-admin-theme-primary/90 rounded-lg transition-colors">
             {{ t('Got it, thanks!') }}
           </button>
         </div>
       </div>
     </div>
   </transition>
 </teleport>
 </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from'vue';
import axios from 'axios';
import { useTranslation } from '../../composables/useTranslation';
import { isDemoRestrictionError } from '../../utils/demoRestriction';
import { useDialog } from'../../composables/useDialog';
import {
  ArrowPathIcon,
  TrashIcon,
  InformationCircleIcon,
  CheckCircleIcon,
  ServerStackIcon,
  CpuChipIcon,
  CircleStackIcon,
  PuzzlePieceIcon,
  Cog6ToothIcon,
  CloudIcon,
  EyeIcon,
  MapIcon,
  BoltIcon,
  SwatchIcon,
  CubeIcon,
  AdjustmentsHorizontalIcon,
  DocumentDuplicateIcon,
  XMarkIcon,
  WrenchScrewdriverIcon,
  SparklesIcon,
  ScaleIcon,
  GlobeAltIcon,
  RocketLaunchIcon,
  ClockIcon,
  ShieldCheckIcon,
  QuestionMarkCircleIcon,
} from '@heroicons/vue/24/outline';

const { t } = useTranslation();
const dialog = useDialog();

const loading = ref(true);
const status = ref<any>(null);
const clearing = reactive<Record<string, boolean>>({});
const cleared = reactive<Record<string, boolean>>({});
const clearingAll = ref(false);
const fixingPermissions = ref(false);
const lastCleared = ref('');
const detailModal = reactive<{ open: boolean; type: any; data: any; loading: boolean }>({
 open: false, type: null, data: null, loading: false,
});

// Cache Optimization Form State & Methods
const configForm = reactive({
  polycms_cache_enabled: 'yes',
  cache_store: 'file',
  session_driver: 'file',
  queue_connection: 'sync',
  redis_host: '127.0.0.1',
  redis_port: 6379,
  redis_password: '',
  redis_cache_db: 1,
  redis_cache_prefix: 'polycms_cache_',
  cache_preset: 'ultra_instant',
  instant_prefetch_enabled: 'yes',
  speculation_rules_enabled: 'yes',
  response_html_cache_enabled: 'yes',
  browser_http_cache_enabled: 'yes',
  response_cache_ttl: 60,
  page_cache_swr_ttl: 600,
  cache_query_param_strategy: 'ignore_known_tracking',
  cache_ignored_query_params: 'utm_source, utm_medium, utm_campaign, utm_term, utm_content, fbclid, gclid, msclkid, ttclid, ref, _ga, _gl',
  database_cache_max_entry_kb: 512,
  database_cache_max_total_mb: 512,
  cache_integrity_mode: 'sha256',
  durable_view_counter_enabled: 'yes',
  cache_allowlist_routes: 'home, posts.index, posts.archive, post.show, posts.show, page.show, pages.show, category.show, categories.show, products.index, products.archive, product.show, products.show, product-categories.show, projects.index, projects.archive, project.show, projects.show',
  cache_blacklisted_paths: 'admin, api, cart, checkout, account, logout, login, register, password, livewire',
});

const predefinedRouteGroups = [
  {
    name: 'Bài Viết & Blog (Posts Core)',
    routes: [
      { key: 'home', label: 'Trang chủ (home)' },
      { key: 'posts.index', label: 'Danh sách Blog (posts.index)' },
      { key: 'post.show', label: 'Chi tiết Bài viết (post.show)' },
      { key: 'category.show', label: 'Danh mục Bài viết (category.show)' },
      { key: 'tag.show', label: 'Thẻ Bài viết (tag.show)' },
    ]
  },
  {
    name: 'Trang Tĩnh (Pages Core)',
    routes: [
      { key: 'page.show', label: 'Trang đơn / Tĩnh (page.show)' }
    ]
  },
  {
    name: 'Sản Phẩm (Ecommerce)',
    routes: [
      { key: 'products.index', label: 'Trang Cửa hàng (products.index)' },
      { key: 'product.show', label: 'Chi tiết Sản phẩm (product.show)' },
      { key: 'product-categories.show', label: 'Danh mục Sản phẩm (product-categories.show)' },
      { key: 'product-brands.show', label: 'Thương hiệu (product-brands.show)' },
      { key: 'product-tags.show', label: 'Thẻ Sản phẩm (product-tags.show)' },
    ]
  },
  {
    name: 'Mô-đun Tích Hợp (ProjectHub, Docs...)',
    routes: [
      { key: 'projects.index', label: 'Danh sách Dự án (projects.index)' },
      { key: 'project.show', label: 'Chi tiết Dự án (project.show)' },
      { key: 'releases.show', label: 'Release Notes (releases.show)' },
      { key: 'docs.show', label: 'Tài liệu (docs.show)' },
    ]
  }
];

function isRouteChecked(routeName: string): boolean {
  if (!configForm.cache_allowlist_routes) return false;
  const currentRoutes = configForm.cache_allowlist_routes
    .split(',')
    .map(r => r.trim())
    .filter(Boolean);
  
  if (routeName === 'posts.index') {
    return currentRoutes.includes('posts.index') || currentRoutes.includes('posts.archive');
  }
  if (routeName === 'post.show') {
    return currentRoutes.includes('post.show') || currentRoutes.includes('posts.show');
  }
  if (routeName === 'category.show') {
    return currentRoutes.includes('category.show') || currentRoutes.includes('categories.show');
  }
  if (routeName === 'page.show') {
    return currentRoutes.includes('page.show') || currentRoutes.includes('pages.show');
  }
  if (routeName === 'products.index') {
    return currentRoutes.includes('products.index') || currentRoutes.includes('products.archive');
  }
  if (routeName === 'product.show') {
    return currentRoutes.includes('product.show') || currentRoutes.includes('products.show');
  }
  if (routeName === 'project.show') {
    return currentRoutes.includes('project.show') || currentRoutes.includes('projects.show');
  }
  return currentRoutes.includes(routeName);
}

function togglePredefinedRoute(routeName: string, checked: boolean) {
  let currentRoutes = (configForm.cache_allowlist_routes || '')
    .split(',')
    .map(r => r.trim())
    .filter(Boolean);

  const aliases: Record<string, string[]> = {
    'posts.index': ['posts.index', 'posts.archive'],
    'post.show': ['post.show', 'posts.show'],
    'category.show': ['category.show', 'categories.show'],
    'page.show': ['page.show', 'pages.show'],
    'products.index': ['products.index', 'products.archive'],
    'product.show': ['product.show', 'products.show'],
    'project.show': ['project.show', 'projects.show'],
  };

  const toModify = aliases[routeName] || [routeName];

  if (checked) {
    toModify.forEach(r => {
      if (!currentRoutes.includes(r)) {
        currentRoutes.push(r);
      }
    });
  } else {
    currentRoutes = currentRoutes.filter(r => !toModify.includes(r));
  }

  configForm.cache_allowlist_routes = currentRoutes.join(', ');
}

const showHelpGuide = ref(false);
const activeGuideTopic = ref<string | null>(null);
const savingConfig = ref(false);
const testingRedis = ref(false);
const redisTestResult = ref<string | null>(null);
const redisTestSuccess = ref(false);

function openTopicGuide(topicKey?: string) {
  activeGuideTopic.value = topicKey || null;
  showHelpGuide.value = true;
  if (topicKey) {
    setTimeout(() => {
      const el = document.getElementById('guide-topic-' + topicKey);
      if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }, 150);
  }
}

const isRedisEnabled = computed(() => {
  return configForm.cache_store === 'redis' || 
         configForm.session_driver === 'redis' || 
         configForm.queue_connection === 'redis';
});

function applyPreset(presetKey: string) {
  configForm.cache_preset = presetKey;
  if (presetKey === 'ultra_instant') {
    configForm.instant_prefetch_enabled = 'yes';
    configForm.speculation_rules_enabled = 'yes';
    configForm.response_html_cache_enabled = 'yes';
    configForm.browser_http_cache_enabled = 'yes';
  } else if (presetKey === 'balanced') {
    configForm.instant_prefetch_enabled = 'yes';
    configForm.speculation_rules_enabled = 'no';
    configForm.response_html_cache_enabled = 'no';
    configForm.browser_http_cache_enabled = 'yes';
  }
}

async function loadConfig() {
  try {
    const res = await axios.get('/api/v1/settings/group/cache_optimization');
    const settings = res.data?.data || {};
    
    configForm.polycms_cache_enabled = settings.polycms_cache_enabled?.value || 'yes';
    configForm.cache_store = settings.cache_store?.value || 'file';
    configForm.session_driver = settings.session_driver?.value || 'file';
    configForm.queue_connection = settings.queue_connection?.value || 'sync';
    configForm.redis_host = settings.redis_host?.value || '127.0.0.1';
    configForm.redis_port = settings.redis_port?.value !== undefined ? Number(settings.redis_port.value) : 6379;
    configForm.redis_password = settings.redis_password?.value || '';
    configForm.redis_cache_db = settings.redis_cache_db?.value !== undefined ? Number(settings.redis_cache_db.value) : 1;
    configForm.redis_cache_prefix = settings.redis_cache_prefix?.value || 'polycms_cache_';
    configForm.cache_preset = settings.cache_preset?.value || 'ultra_instant';
    configForm.instant_prefetch_enabled = settings.instant_prefetch_enabled?.value || 'yes';
    configForm.speculation_rules_enabled = settings.speculation_rules_enabled?.value || 'yes';
    configForm.response_html_cache_enabled = settings.response_html_cache_enabled?.value || 'yes';
    configForm.browser_http_cache_enabled = settings.browser_http_cache_enabled?.value || 'yes';
    configForm.response_cache_ttl = settings.response_cache_ttl?.value !== undefined ? Number(settings.response_cache_ttl.value) : 60;
    configForm.page_cache_swr_ttl = settings.page_cache_swr_ttl?.value !== undefined ? Number(settings.page_cache_swr_ttl.value) : 600;
    configForm.cache_query_param_strategy = settings.cache_query_param_strategy?.value || 'ignore_known_tracking';
    configForm.cache_ignored_query_params = settings.cache_ignored_query_params?.value || 'utm_source, utm_medium, utm_campaign, utm_term, utm_content, fbclid, gclid, msclkid, ttclid, ref, _ga, _gl';
    configForm.database_cache_max_entry_kb = settings.database_cache_max_entry_kb?.value !== undefined ? Number(settings.database_cache_max_entry_kb.value) : 512;
    configForm.database_cache_max_total_mb = settings.database_cache_max_total_mb?.value !== undefined ? Number(settings.database_cache_max_total_mb.value) : 512;
    configForm.cache_integrity_mode = settings.cache_integrity_mode?.value || 'sha256';
    configForm.durable_view_counter_enabled = settings.durable_view_counter_enabled?.value || 'yes';
    configForm.cache_allowlist_routes = settings.cache_allowlist_routes?.value || 'home, page.show, post.show, category.show, product.show, projects.show';
    configForm.cache_blacklisted_paths = settings.cache_blacklisted_paths?.value || 'admin, api, cart, checkout, account, logout, login, register, password, livewire';
  } catch (e) {
    console.error('Failed to load cache configuration settings:', e);
  }
}

async function saveConfig() {
  savingConfig.value = true;
  redisTestResult.value = null;
  try {
    await axios.put('/api/v1/settings/group/cache_optimization', {
      settings: {
        polycms_cache_enabled: { value: configForm.polycms_cache_enabled, type: 'string', group: 'cache_optimization' },
        cache_store: { value: configForm.cache_store, type: 'string', group: 'cache_optimization' },
        session_driver: { value: configForm.session_driver, type: 'string', group: 'cache_optimization' },
        queue_connection: { value: configForm.queue_connection, type: 'string', group: 'cache_optimization' },
        redis_host: { value: configForm.redis_host, type: 'string', group: 'cache_optimization' },
        redis_port: { value: configForm.redis_port, type: 'number', group: 'cache_optimization' },
        redis_password: { value: configForm.redis_password, type: 'string', group: 'cache_optimization' },
        redis_cache_db: { value: configForm.redis_cache_db, type: 'number', group: 'cache_optimization' },
        redis_cache_prefix: { value: configForm.redis_cache_prefix, type: 'string', group: 'cache_optimization' },
        cache_preset: { value: configForm.cache_preset, type: 'string', group: 'cache_optimization' },
        instant_prefetch_enabled: { value: configForm.instant_prefetch_enabled, type: 'string', group: 'cache_optimization' },
        speculation_rules_enabled: { value: configForm.speculation_rules_enabled, type: 'string', group: 'cache_optimization' },
        response_html_cache_enabled: { value: configForm.response_html_cache_enabled, type: 'string', group: 'cache_optimization' },
        browser_http_cache_enabled: { value: configForm.browser_http_cache_enabled, type: 'string', group: 'cache_optimization' },
        response_cache_ttl: { value: configForm.response_cache_ttl, type: 'number', group: 'cache_optimization' },
        page_cache_swr_ttl: { value: configForm.page_cache_swr_ttl, type: 'number', group: 'cache_optimization' },
        cache_query_param_strategy: { value: configForm.cache_query_param_strategy, type: 'string', group: 'cache_optimization' },
        cache_ignored_query_params: { value: configForm.cache_ignored_query_params, type: 'string', group: 'cache_optimization' },
        database_cache_max_entry_kb: { value: configForm.database_cache_max_entry_kb, type: 'number', group: 'cache_optimization' },
        database_cache_max_total_mb: { value: configForm.database_cache_max_total_mb, type: 'number', group: 'cache_optimization' },
        cache_integrity_mode: { value: configForm.cache_integrity_mode, type: 'string', group: 'cache_optimization' },
        durable_view_counter_enabled: { value: configForm.durable_view_counter_enabled, type: 'string', group: 'cache_optimization' },
        cache_allowlist_routes: { value: configForm.cache_allowlist_routes, type: 'string', group: 'cache_optimization' },
        cache_blacklisted_paths: { value: configForm.cache_blacklisted_paths, type: 'string', group: 'cache_optimization' },
      }
    });
    dialog.success(t('Cache configuration saved successfully!'));
    
    // Refresh status to show the new default driver
    await loadStatus();
  } catch (e) {
    if (isDemoRestrictionError(e)) return;
    console.error('Failed to save cache configuration settings:', e);
    dialog.error(t('Failed to save configuration settings'));
  } finally {
    savingConfig.value = false;
  }
}

async function testRedis() {
  testingRedis.value = true;
  redisTestResult.value = null;
  redisTestSuccess.value = false;
  try {
    const res = await axios.post('/api/v1/system/cache/test-redis', {
      host: configForm.redis_host || '127.0.0.1',
      port: Number(configForm.redis_port) || 6379,
      password: configForm.redis_password || null,
    });
    
    redisTestSuccess.value = res.data?.success;
    redisTestResult.value = res.data?.message || t('Connection test completed.');
  } catch (e: any) {
    if (isDemoRestrictionError(e)) return;
    redisTestSuccess.value = false;
    redisTestResult.value = e.response?.data?.message || t('Failed to connect to Redis server.');
  } finally {
    testingRedis.value = false;
  }
}

const groupLabels: Record<string, string> = {
 laravel:'Laravel Framework',
 polycms:'PolyCMS Internal',
 server:'Server',
 module:'Module Extensions',
};

const groupOrder = ['laravel','polycms','server','module'];

const groupedTypes = computed(() => {
 if (!status.value?.types) return [];

 const groups: Record<string, any> = {};

 for (const t of status.value.types) {
 const g = t.group ||'module';
 if (!groups[g]) {
 groups[g] = { key: g, label: groupLabels[g] || g, items: [] };
 }
 groups[g].items.push(t);
 }

 return groupOrder.map(k => groups[k]).filter(Boolean);
});

function groupIcon(key: string) {
 return ({
 laravel: Cog6ToothIcon,
 polycms: CircleStackIcon,
 server: ServerStackIcon,
 module: PuzzlePieceIcon,
 } as any)[key] || CircleStackIcon;
}

function typeIcon(cacheType: any) {
 return ({
 application: CircleStackIcon,
 view: EyeIcon,
 config: AdjustmentsHorizontalIcon,
 route: MapIcon,
 event: BoltIcon,
 theme: SwatchIcon,
 module: CubeIcon,
 settings: Cog6ToothIcon,
 template: DocumentDuplicateIcon,
 opcache: CpuChipIcon,
 } as any)[cacheType.key] || CloudIcon;
}

function typeIconBg(cacheType: any) {
 if (!cacheType.clearable) return'bg-admin-theme-base/50';
 return ({
 laravel:'bg-red-50 dark:bg-red-900/20',
 polycms:'bg-admin-theme-primary/10',
 server:'bg-amber-50 dark:bg-amber-900/20',
 module:'bg-emerald-50 dark:bg-emerald-900/20',
 } as any)[cacheType.group] ||'bg-admin-theme-base/50';
}

function typeIconColor(cacheType: any) {
 if (!cacheType.clearable) return'text-admin-theme-text-muted';
 return ({
 laravel:'text-red-500 dark:text-red-400',
 polycms:'text-admin-theme-primary dark:text-admin-theme-primary',
 server:'text-amber-500 dark:text-amber-400',
 module:'text-emerald-500 dark:text-emerald-400',
 } as any)[cacheType.group] ||'text-admin-theme-text-muted';
}

async function loadStatus() {
 loading.value = true;
 try {
 const res = await axios.get('/api/v1/system/cache/status');
 status.value = res.data?.data;
 } catch (e) {
 console.error('Failed to load cache status:', e);
 dialog.error(t('Failed to load cache status'));
 } finally {
 loading.value = false;
 }
}

async function clearSingle(type: string) {
 clearing[type] = true;
 try {
 const res = await axios.post('/api/v1/system/cache/clear', { types: [type] });
 if (res.data?.results?.[type] ==='success') {
 cleared[type] = true;
 setTimeout(() => { cleared[type] = false; }, 1500);
 dialog.success(t('Cache cleared successfully'));
 // Refresh status
 await loadStatus();
 } else {
 dialog.error(t('Failed to clear cache'));
 }
 } catch (e) {
 if (isDemoRestrictionError(e)) return;
 console.error(`Failed to clear ${type}:`, e);
 dialog.error(t('Failed to clear cache'));
 } finally {
 clearing[type] = false;
 }
}

async function clearAll() {
 clearingAll.value = true;
 try {
 const res = await axios.post('/api/v1/system/cache/clear', { types: ['all'] });
 if (res.data?.success) {
 lastCleared.value = new Date().toLocaleTimeString();
 dialog.success(t('All caches cleared successfully'));
 await loadStatus();
 } else {
 dialog.error(t('Some caches failed to clear'));
 }
 } catch (e) {
 if (isDemoRestrictionError(e)) return;
 console.error('Failed to clear all caches:', e);
 dialog.error(t('Failed to clear caches'));
 } finally {
 clearingAll.value = false;
 }
}

async function fixPermissions() {
  fixingPermissions.value = true;
  try {
    const res = await axios.post('/api/v1/system/cache/fix-permissions');
    if (res.data?.success) {
      dialog.success(t('Permissions fixed successfully'));
      await loadStatus();
    } else {
      const data = res.data?.data;
      const failedList = data?.failed_files?.map((f: any) => `${f.path} (Owner: ${f.owner})`).join('\n') || '';
      dialog.warning(
        t('Some paths could not be fixed. You may need to run commands via SSH:\n\nchown -R polyc3546:polyc3546 storage bootstrap/cache\nchmod -R 775 storage bootstrap/cache\n\nFailed paths:\n') + failedList
      );
      await loadStatus();
    }
  } catch (e) {
    if (isDemoRestrictionError(e)) return;
    console.error('Failed to fix permissions:', e);
    dialog.error(t('Failed to fix permissions'));
  } finally {
    fixingPermissions.value = false;
  }
}

async function openDetail(cacheType: any) {
 detailModal.open = true;
 detailModal.type = cacheType;
 detailModal.data = null;
 detailModal.loading = true;
 try {
 const res = await axios.get(`/api/v1/system/cache/detail/${cacheType.key}`);
 detailModal.data = res.data?.data;
 } catch (e) {
 console.error('Failed to load cache detail:', e);
 detailModal.data = null;
 } finally {
 detailModal.loading = false;
 }
}

function formatSize(bytes: number): string {
 if (!bytes || bytes === 0) return'0 B';
 const k = 1024;
 const sizes = ['B','KB','MB','GB'];
 const i = Math.floor(Math.log(bytes) / Math.log(k));
 return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) +'' + sizes[i];
}

onMounted(() => {
  loadStatus();
  loadConfig();
});
</script>

<style scoped>
.info-badge {
 @apply inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300;
}
.info-badge--active {
 @apply bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400;
}
.info-badge--inactive {
 @apply bg-gray-100 text-gray-400 dark:bg-gray-700/50 dark:text-gray-500;
}
.fade-enter-active, .fade-leave-active {
 transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
 opacity: 0;
}
.detail-row {
 @apply flex items-center justify-between py-2 px-3 bg-admin-theme-base/30 rounded-lg;
}
.detail-label {
 @apply text-sm text-admin-theme-text-muted;
}
.detail-value {
 @apply text-sm text-admin-theme-text;
}
.detail-value-code {
 @apply text-xs font-mono text-admin-theme-text-secondary bg-admin-theme-base/50 px-2 py-1 rounded max-w-[400px] truncate;
}
</style>
