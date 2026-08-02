<template>
  <div class="space-y-4">
    <!-- Preset Action Buttons -->
    <div class="flex items-center justify-between">
      <p class="text-xs font-semibold text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Quick Add Package Presets') }}</p>
      <button 
        v-if="packages.length > 1" 
        type="button" 
        class="text-[11px] font-medium text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1 transition-colors" 
        @click="sortPackagesByDefault"
        title="Sort packages by duration (Daily → Lifetime)"
      >
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
        </svg>
        <span>{{ $t('Sort Default Order (Daily → Lifetime)') }}</span>
      </button>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
      <button
        v-for="btn in PRESET_BUTTONS"
        :key="btn.id"
        type="button"
        :class="[
          'px-3 py-2 text-xs font-semibold border rounded-lg transition-all flex items-center justify-center gap-1.5',
          btn.btnClass,
          isPresetAdded(btn.id) ? 'ring-2 ring-emerald-500/50 shadow-sm opacity-90' : ''
        ]"
        @click="applyPreset(btn.id)"
      >
        <svg v-if="isPresetAdded(btn.id)" class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
        </svg>
        <span>{{ $t(btn.label) }}</span>
      </button>
    </div>

    <!-- Package Cards List -->
    <div class="space-y-4">
      <div
        v-for="(pkg, index) in packages"
        :key="`${pkg.code || index}-${index}`"
        :id="'package-card-' + index"
        class="bg-admin-theme-surface border rounded-xl shadow-sm overflow-hidden transition-all duration-200"
        :class="[
          highlightIndex === index ? 'ring-2 ring-emerald-500 border-emerald-500' : 'border-admin-theme-border',
          draggingIndex === index ? 'opacity-40 border-dashed border-indigo-500 scale-[0.99]' : ''
        ]"
        draggable="true"
        @dragstart="onDragStart($event, index)"
        @dragover="onDragOver"
        @drop="onDrop($event, index)"
        @dragend="onDragEnd"
      >
        <!-- Package Header -->
        <div class="p-3.5 bg-admin-theme-base/60 border-b border-admin-theme-border flex items-center justify-between cursor-pointer select-none" @click="togglePackageCollapse(index)">
          <div class="flex items-center gap-2.5">
            <!-- Drag Handle Icon -->
            <div 
              class="cursor-grab active:cursor-grabbing p-1 text-admin-theme-text-muted hover:text-admin-theme-primary transition-colors"
              title="Drag to reorder"
              @click.stop
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
              </svg>
            </div>

            <span class="text-xs font-mono font-bold text-admin-theme-primary uppercase">PACKAGE #{{ index + 1 }}: {{ pkg.name || 'UNNAMED' }}</span>
            <span v-if="pkg.access_type === 'permanent'" class="px-2 py-0.5 text-[10px] font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 rounded border border-amber-200 dark:border-amber-800">PERMANENT</span>
            <span v-else class="px-2 py-0.5 text-[10px] font-bold bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 rounded border border-blue-200 dark:border-blue-800">SUBSCRIPTION</span>
          </div>

          <div class="flex items-center gap-2">
            <!-- Reorder Up/Down Buttons -->
            <div class="flex items-center gap-0.5 bg-admin-theme-base/80 p-0.5 rounded-lg border border-admin-theme-border shrink-0" @click.stop>
              <button
                type="button"
                class="p-1 text-admin-theme-text-muted hover:text-admin-theme-primary disabled:opacity-30 disabled:hover:text-admin-theme-text-muted transition-colors"
                :disabled="index === 0"
                title="Move Up"
                @click="movePackage(index, 'up')"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                </svg>
              </button>
              <button
                type="button"
                class="p-1 text-admin-theme-text-muted hover:text-admin-theme-primary disabled:opacity-30 disabled:hover:text-admin-theme-text-muted transition-colors"
                :disabled="index === packages.length - 1"
                title="Move Down"
                @click="movePackage(index, 'down')"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
            </div>

            <button v-if="packages.length > 1" type="button" class="text-xs text-red-600 hover:text-red-700 font-semibold px-1.5" @click.stop="removePackage(index)">{{ $t('Remove Package') }}</button>

            <svg class="w-4 h-4 text-admin-theme-text-muted transition-transform duration-200" :class="{ 'rotate-180': !collapsedPackages[index] }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>

        <!-- Package Body -->
        <div v-show="!collapsedPackages[index]" class="p-4 space-y-4">
          <!-- Helpful Explanation Banner -->
          <div class="bg-admin-theme-base/40 border border-admin-theme-border rounded-lg p-3 text-xs text-admin-theme-text-secondary leading-relaxed">
            <span class="font-bold text-admin-theme-text uppercase tracking-wider block mb-0.5">{{ $t('Package Description:') }}</span>
            {{ getPackageExplanation(pkg) }}
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1">
              <label class="block text-sm font-medium text-admin-theme-text-secondary">{{ $t('Service Name *') }}</label>
              <input v-model="pkg.name" type="text" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary" required placeholder="e.g. Monthly VIP, Lifetime Pro" />
            </div>

            <div class="space-y-1">
              <label class="block text-sm font-medium text-admin-theme-text-secondary">{{ $t('Service Code *') }}</label>
              <input v-model="pkg.code" type="text" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary" required placeholder="e.g. monthly_vip, lifetime_pro" />
            </div>
          </div>

          <div class="space-y-1">
            <label class="block text-sm font-medium text-admin-theme-text-secondary">{{ $t('Access Type') }}</label>
            <select v-model="pkg.access_type" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary">
              <option value="subscription">{{ $t('Subscription (Recurring / Limited Time)') }}</option>
              <option value="permanent">{{ $t('Permanent / Lifetime (One-time)') }}</option>
            </select>
          </div>

          <!-- Recurring duration fields -->
          <div v-if="pkg.access_type !== 'permanent'" class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-admin-theme-base/30 p-3 rounded-lg border border-admin-theme-border">
            <div class="space-y-1">
              <label class="block text-sm font-medium text-admin-theme-text-secondary">{{ $t('Duration Value') }}</label>
              <input v-model.number="pkg.duration_value" type="number" min="1" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary" />
            </div>
            <div class="space-y-1">
              <label class="block text-sm font-medium text-admin-theme-text-secondary">{{ $t('Duration Unit') }}</label>
              <select v-model="pkg.duration_unit" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary">
                <option value="day">{{ $t('Day(s)') }}</option>
                <option value="week">{{ $t('Week(s)') }}</option>
                <option value="month">{{ $t('Month(s)') }}</option>
                <option value="year">{{ $t('Year(s)') }}</option>
              </select>
            </div>
            <div class="flex items-center space-x-2 pt-6">
              <input :id="'recurring-' + index" v-model="pkg.is_recurring" type="checkbox" class="rounded border-admin-theme-border text-admin-theme-primary focus:ring-admin-theme-primary bg-admin-theme-input-bg" />
              <label :for="'recurring-' + index" class="text-sm text-admin-theme-text cursor-pointer">{{ $t('Auto-recurring Payment') }}</label>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div v-if="pkg.access_type !== 'permanent'" class="space-y-1">
              <label class="block text-sm font-medium text-admin-theme-text-secondary">{{ $t('Trial Period (Days)') }}</label>
              <input v-model.number="pkg.trial_period_days" type="number" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary" min="0" />
            </div>
            <div class="space-y-1" :class="{'sm:col-span-1': pkg.access_type !== 'permanent', 'sm:col-span-2': pkg.access_type === 'permanent' }">
              <label class="block text-sm font-medium text-admin-theme-text-secondary">{{ $t('Package Price *') }}</label>
              <input v-model.number="pkg.price" type="number" step="0.01" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary" required min="0" />
              <p v-if="pkg.price === undefined || pkg.price === null || pkg.price === ''" class="mt-1 text-xs text-red-500 font-semibold">{{ $t('Package price is required.') }}</p>
            </div>
          </div>

          <!-- License Policy & Max Activations Section -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <div class="flex items-center gap-1.5">
                <label class="block text-sm font-medium text-admin-theme-text-secondary">{{ $t('License Policy') }}</label>
                <span class="text-xs text-indigo-500 font-normal cursor-help" :title="$t('Determines how license keys are generated and bound upon order completion.')">
                  <i class="fas fa-question-circle"></i>
                </span>
              </div>
              <select v-model="pkg.license_policy" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary">
                <option value="">{{ $t('No License Key (Open / Unrestricted)') }}</option>
                <option value="per_seat">{{ $t('Per Seat (User Accounts Limit)') }}</option>
                <option value="site">{{ $t('Site License / Domain (Website Domain Activations)') }}</option>
              </select>
              <!-- Inline helper text explaining the active selection -->
              <div class="p-2.5 rounded-lg bg-admin-theme-base/50 border border-admin-theme-border text-xs text-admin-theme-text-secondary leading-relaxed">
                <span v-if="!pkg.license_policy" class="flex items-start gap-1.5">
                  <i class="fas fa-info-circle text-gray-400 mt-0.5 shrink-0"></i>
                  <span>{{ $t('No license key is generated. Customers get direct file download access without activation constraints.') }}</span>
                </span>
                <span v-else-if="pkg.license_policy === 'per_seat'" class="flex items-start gap-1.5">
                  <i class="fas fa-user-shield text-blue-500 mt-0.5 shrink-0"></i>
                  <span>{{ $t('Generates a license key tied to user seats. Limits the number of team member accounts or active users.') }}</span>
                </span>
                <span v-else-if="pkg.license_policy === 'site'" class="flex items-start gap-1.5">
                  <i class="fas fa-globe text-purple-500 mt-0.5 shrink-0"></i>
                  <span>{{ $t('Generates a license key bound to website domains. Limits live site activations (e.g., up to 5 active domains).') }}</span>
                </span>
              </div>
            </div>
            <div v-if="pkg.license_policy" class="space-y-1.5">
              <label class="block text-sm font-medium text-admin-theme-text-secondary">{{ $t('Max Activations (Seats)') }}</label>
              <input v-model.number="pkg.max_activations" type="number" min="1" placeholder="e.g. 5" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary" />
              <p class="text-[11px] text-admin-theme-text-secondary leading-snug">
                <i class="fas fa-sliders-h text-indigo-400 mr-1"></i>
                {{ $t('Maximum total active domains or user seats allowed for this package.') }}
              </p>
            </div>
          </div>

          <!-- Capabilities Section -->
          <div class="space-y-3">
            <div class="flex justify-between items-end">
              <label class="block text-sm font-semibold text-admin-theme-text-secondary">{{ $t('Capabilities (Features)') }}</label>
              
              <!-- Capability Presets Badges -->
              <div v-if="capabilityPresets.length > 0" class="flex flex-wrap gap-1.5 justify-end max-w-md">
                <button 
                  v-for="preset in capabilityPresets" 
                  :key="preset.id"
                  type="button"
                  @click="addPresetCapability(index, preset)"
                  class="px-2 py-0.5 text-[10px] rounded bg-indigo-50 text-indigo-600 border border-indigo-200 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-300 dark:border-indigo-800 transition-colors"
                  :title="preset.group"
                >
                  + {{ preset.name }}
                </button>
              </div>
            </div>
            <div class="space-y-2">
              <div v-for="(capValue, capKey) in pkg.capabilities" :key="capKey" class="flex gap-2 items-center">
                <input :value="capKey" @input="updateCapabilityKey(index, String(capKey), ($event.target as HTMLInputElement).value)" :placeholder="$t('Key')" class="flex-1 px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm" />
                <input :value="capValue" @input="updateCapabilityValue(index, String(capKey), ($event.target as HTMLInputElement).value)" :placeholder="$t('Value')" class="flex-1 px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm" />
                <button type="button" class="p-1.5 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg transition-colors" @click="removeCapability(index, String(capKey))">&times;</button>
              </div>
            </div>
            <button type="button" class="px-3 py-1.5 bg-admin-theme-primary/10/30 text-admin-theme-primary dark:text-admin-theme-primary border border-indigo-100 dark:border-indigo-800 rounded-lg text-xs font-semibold hover:bg-admin-theme-primary/15 dark:hover:bg-indigo-900/50 transition-colors" @click="addCapability(index)">+ {{ $t('Add Capability') }}</button>
          </div>
        </div>
      </div>
    </div>
    <button type="button" class="w-full p-4 bg-admin-theme-base/50 text-admin-theme-text-secondary border-2 border-dashed border-admin-theme-border rounded-xl font-bold hover:bg-black/10 dark:hover:bg-white/10 hover:text-admin-theme-text transition-all" @click="addPackage">+ {{ $t('Add Another Package') }}</button>
  </div>
</template>

<script setup lang="ts">
import { inject, ref, watch, onMounted, getCurrentInstance, nextTick } from 'vue';
import axios from 'axios';
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t;
import { EditorContextKey } from '../../../../editor/context';
import { useTranslation } from '@/admin/composables/useTranslation';
const { t } = useTranslation();

const capabilityPresets = ref<any[]>([]);

const context = inject(EditorContextKey);
if (!context) {
  throw new Error('ProductServicePanel must be used within EditorContext');
}

interface PackageConfig {
  name: string;
  code: string;
  price: number | null;
  access_type: string;
  duration_value: number;
  duration_unit: string;
  is_recurring: boolean;
  trial_period_days: number;
  license_policy: string;
  max_activations: number;
  capabilities: Record<string, any>;
}

const PRESET_BUTTONS = [
  { id: 'daily', label: 'Daily Package', btnClass: 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/40 border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100 dark:hover:bg-indigo-900/50' },
  { id: 'weekly', label: 'Weekly Package', btnClass: 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 dark:hover:bg-emerald-900/50' },
  { id: 'monthly', label: 'Monthly Package', btnClass: 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 border-blue-200 dark:border-blue-800 hover:bg-blue-100 dark:hover:bg-blue-900/50' },
  { id: 'yearly', label: 'Yearly Package', btnClass: 'text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-950/40 border-purple-200 dark:border-purple-800 hover:bg-purple-100 dark:hover:bg-purple-900/50' },
  { id: 'lifetime', label: 'Lifetime Package', btnClass: 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 border-amber-200 dark:border-amber-800 hover:bg-amber-100 dark:hover:bg-amber-900/50 col-span-2 sm:col-span-1' },
];

const PRESET_CONFIGS: Record<string, Omit<PackageConfig, 'price' | 'capabilities'>> = {
  daily: {
    name: 'Daily Package',
    code: 'daily',
    access_type: 'subscription',
    duration_value: 1,
    duration_unit: 'day',
    is_recurring: false,
    trial_period_days: 0,
    license_policy: 'site',
    max_activations: 1,
  },
  weekly: {
    name: 'Weekly Package',
    code: 'weekly',
    access_type: 'subscription',
    duration_value: 1,
    duration_unit: 'week',
    is_recurring: false,
    trial_period_days: 0,
    license_policy: 'site',
    max_activations: 1,
  },
  monthly: {
    name: 'Monthly Package',
    code: 'monthly',
    access_type: 'subscription',
    duration_value: 1,
    duration_unit: 'month',
    is_recurring: true,
    trial_period_days: 0,
    license_policy: 'site',
    max_activations: 5,
  },
  yearly: {
    name: 'Yearly Package',
    code: 'yearly',
    access_type: 'subscription',
    duration_value: 1,
    duration_unit: 'year',
    is_recurring: true,
    trial_period_days: 0,
    license_policy: 'site',
    max_activations: 5,
  },
  lifetime: {
    name: 'Lifetime Package',
    code: 'lifetime',
    access_type: 'permanent',
    duration_value: 1,
    duration_unit: 'year',
    is_recurring: false,
    trial_period_days: 0,
    license_policy: 'site',
    max_activations: 5,
  },
};

const createDefaultPackage = (): PackageConfig => ({
  name: '',
  code: '',
  price: null,
  access_type: 'subscription',
  duration_value: 1,
  duration_unit: 'month',
  is_recurring: false,
  trial_period_days: 0,
  license_policy: '',
  max_activations: 5,
  capabilities: {},
});

const PRESET_ORDER_WEIGHT: Record<string, number> = {
  daily: 10,
  weekly: 20,
  monthly: 30,
  yearly: 40,
  lifetime: 50,
};

const getPackageWeight = (pkg: PackageConfig): number => {
  if (!pkg) return 999;
  const code = (pkg.code || '').trim().toLowerCase();
  if (code in PRESET_ORDER_WEIGHT) {
    return PRESET_ORDER_WEIGHT[code];
  }
  if (pkg.access_type === 'permanent') {
    return 50;
  }
  const val = Number(pkg.duration_value) || 1;
  const unit = (pkg.duration_unit || 'month').toLowerCase();
  if (unit === 'day') return 10 * val;
  if (unit === 'week') return 20 * val;
  if (unit === 'month') return 30 * val;
  if (unit === 'year') return 40 * val;
  return 100;
};

const sortPackagesByDefault = () => {
  packages.value.sort((a, b) => getPackageWeight(a) - getPackageWeight(b));
};

const packages = ref<PackageConfig[]>([createDefaultPackage()]);
const collapsedPackages = ref<Record<number, boolean>>({});
const highlightIndex = ref<number | null>(null);
const draggingIndex = ref<number | null>(null);

const onDragStart = (e: DragEvent, index: number) => {
  draggingIndex.value = index;
  if (e.dataTransfer) {
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', String(index));
  }
};

const onDragOver = (e: DragEvent) => {
  e.preventDefault();
  if (e.dataTransfer) {
    e.dataTransfer.dropEffect = 'move';
  }
};

const onDrop = (e: DragEvent, targetIndex: number) => {
  e.preventDefault();
  if (draggingIndex.value === null || draggingIndex.value === targetIndex) {
    draggingIndex.value = null;
    return;
  }
  const itemToMove = packages.value.splice(draggingIndex.value, 1)[0];
  packages.value.splice(targetIndex, 0, itemToMove);
  draggingIndex.value = null;
};

const onDragEnd = () => {
  draggingIndex.value = null;
};

const movePackage = (index: number, direction: 'up' | 'down') => {
  if (direction === 'up' && index > 0) {
    const item = packages.value.splice(index, 1)[0];
    packages.value.splice(index - 1, 0, item);
  } else if (direction === 'down' && index < packages.value.length - 1) {
    const item = packages.value.splice(index, 1)[0];
    packages.value.splice(index + 1, 0, item);
  }
};

const togglePackageCollapse = (index: number) => {
  collapsedPackages.value[index] = !collapsedPackages.value[index];
};

const findPresetIndex = (presetType: string): number => {
  const targetCode = PRESET_CONFIGS[presetType]?.code || presetType;
  return packages.value.findIndex((pkg) => {
    if (!pkg) return false;
    const pkgCode = (pkg.code || '').trim().toLowerCase();
    if (pkgCode === targetCode.toLowerCase()) return true;
    if (presetType === 'lifetime' && pkg.access_type === 'permanent' && (!pkgCode || pkgCode === 'lifetime')) return true;
    return false;
  });
};

const isPresetAdded = (presetType: string): boolean => {
  return findPresetIndex(presetType) !== -1;
};

const isPkgEmpty = (pkg: PackageConfig): boolean => {
  if (!pkg) return true;
  const nameEmpty = !pkg.name || pkg.name.trim() === '';
  const codeEmpty = !pkg.code || pkg.code.trim() === '';
  const priceEmpty = pkg.price === null || pkg.price === undefined || pkg.price === 0;
  return nameEmpty && codeEmpty && priceEmpty;
};

const scrollToPackage = (index: number) => {
  if (index < 0 || index >= packages.value.length) return;
  collapsedPackages.value[index] = false;
  nextTick(() => {
    const el = document.getElementById(`package-card-${index}`);
    if (el) {
      el.scrollIntoView({ behavior: 'smooth', block: 'center' });
      highlightIndex.value = index;
      setTimeout(() => {
        if (highlightIndex.value === index) {
          highlightIndex.value = null;
        }
      }, 1800);
    }
  });
};

const getPackageExplanation = (pkg: any) => {
  if (!pkg) return '';
  const price = pkg.price !== null && pkg.price !== undefined ? `${Number(pkg.price).toFixed(2)}` : '0.00';
  
  if (pkg.access_type === 'permanent') {
    const prefix = t('Lifetime access');
    return `${prefix} (One-time payment of $${price}).`;
  }
  
  const unitMap: Record<string, string> = {
    day: t('day(s)'),
    week: t('week(s)'),
    month: t('month(s)'),
    year: t('year(s)'),
  };
  const unit = unitMap[pkg.duration_unit] || pkg.duration_unit;
  const recurringText = pkg.is_recurring ? t('Auto-renews until cancelled.') : t('One-time access for this duration.');
  const trialText = pkg.trial_period_days > 0 ? ` ${pkg.trial_period_days} ${t('days free trial included.')}` : '';
  
  return `$${price} every ${pkg.duration_value || 1} ${unit}.${trialText} ${recurringText}`;
};

const applyPreset = (presetType: string) => {
  const existingIndex = findPresetIndex(presetType);
  if (existingIndex !== -1) {
    scrollToPackage(existingIndex);
    return;
  }

  const presetData = PRESET_CONFIGS[presetType];
  if (!presetData) return;

  if (packages.value.length === 1 && isPkgEmpty(packages.value[0])) {
    packages.value[0] = {
      ...createDefaultPackage(),
      ...presetData,
    };
  } else {
    const newPkg: PackageConfig = {
      ...createDefaultPackage(),
      ...presetData,
    };
    packages.value.push(newPkg);
  }

  sortPackagesByDefault();

  const newIndex = findPresetIndex(presetType);
  if (newIndex !== -1) {
    scrollToPackage(newIndex);
  }
};

const addPackage = () => {
  packages.value.push(createDefaultPackage());
};

const removePackage = (index: number) => {
  if (packages.value.length > 1) {
    packages.value.splice(index, 1);
  }
};

const addCapability = (pkgIndex: number) => {
  const currentCaps = packages.value[pkgIndex].capabilities || {};
  let newKey = 'new_feature';
  let counter = 1;
  while (newKey in currentCaps) {
    newKey = `new_feature_${counter}`;
    counter++;
  }
  packages.value[pkgIndex].capabilities = {
    ...currentCaps,
    [newKey]: 'enabled'
  };
};

const removeCapability = (pkgIndex: number, key: string) => {
  const currentCaps = { ...packages.value[pkgIndex].capabilities };
  delete currentCaps[key];
  packages.value[pkgIndex].capabilities = currentCaps;
};

const updateCapabilityKey = (pkgIndex: number, oldKey: string, newKey: string) => {
  const currentCaps = { ...packages.value[pkgIndex].capabilities };
  const val = currentCaps[oldKey];
  delete currentCaps[oldKey];
  currentCaps[newKey] = val;
  packages.value[pkgIndex].capabilities = currentCaps;
};

const updateCapabilityValue = (pkgIndex: number, key: string, value: any) => {
  packages.value[pkgIndex].capabilities = {
    ...packages.value[pkgIndex].capabilities,
    [key]: value
  };
};

const getLicensePolicyType = (policy: any): string => {
  if (!policy) return '';
  if (typeof policy === 'object') {
    return policy.type || '';
  }
  return String(policy);
};

const getMaxActivations = (policy: any, defaultVal = 5): number => {
  if (policy && typeof policy === 'object' && policy.max_activations !== undefined) {
    return Number(policy.max_activations);
  }
  return defaultVal;
};

// Sync with main form
watch(packages, (newValue) => {
  context.form.value.service_config = newValue.map(pkg => ({
    ...pkg,
    license_policy: pkg.license_policy ? {
      type: pkg.license_policy,
      max_activations: pkg.max_activations || 5
    } : ''
  }));
}, { deep: true });

const addPresetCapability = (pkgIndex: number, preset: any) => {
  const currentLocale = window.Laravel?.locale || 'en';
  let val = preset.name;
  if (preset.translations && typeof preset.translations === 'object') {
    val = preset.translations[currentLocale] || preset.translations['en'] || preset.translations['vi'] || preset.name;
  }
  
  const currentCaps = packages.value[pkgIndex].capabilities || {};
  let newKey = 'feature';
  let counter = 1;
  while (newKey in currentCaps) {
    newKey = `feature_${counter}`;
    counter++;
  }
  
  packages.value[pkgIndex].capabilities = {
    ...currentCaps,
    [newKey]: val
  };
};

const fetchCapabilityPresets = async () => {
  try {
    const { data } = await axios.get('/api/v1/admin/capability-presets');
    capabilityPresets.value = data.data;
  } catch (error) {
    console.error('Failed to load capability presets', error);
  }
};

// Load initial data
onMounted(() => {
  fetchCapabilityPresets();
  
  watch(() => context.form.value.services, (newVal) => {
    if (newVal && Array.isArray(newVal) && newVal.length > 0) {
      packages.value = newVal.map(item => ({
        ...item,
        price: item.price !== undefined && item.price !== null && item.price !== '' ? Number(item.price) : undefined,
        license_policy: getLicensePolicyType(item.license_policy),
        max_activations: item.max_activations !== undefined ? Number(item.max_activations) : getMaxActivations(item.license_policy, 5),
        capabilities: item.capabilities || {},
      }));
      sortPackagesByDefault();
    } else if (context.form.value.service_config && Array.isArray(context.form.value.service_config)) {
      packages.value = context.form.value.service_config.map(item => ({
        ...item,
        price: item.price !== undefined && item.price !== null && item.price !== '' ? Number(item.price) : undefined,
        license_policy: getLicensePolicyType(item.license_policy),
        max_activations: item.max_activations !== undefined ? Number(item.max_activations) : getMaxActivations(item.license_policy, 5),
        capabilities: item.capabilities || {},
      }));
      sortPackagesByDefault();
    }
  }, { immediate: true });
});
</script>
