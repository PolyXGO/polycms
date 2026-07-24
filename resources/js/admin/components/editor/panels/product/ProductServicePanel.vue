<template>
  <div class="space-y-4">
    <!-- Preset Action Buttons -->
    <div class="flex items-center justify-between">
      <p class="text-xs font-semibold text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Quick Add Package Presets') }}</p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
      <button type="button" class="px-3 py-2 text-xs font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors" @click="applyPreset('daily')">{{ $t('Daily Package') }}</button>
      <button type="button" class="px-3 py-2 text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition-colors" @click="applyPreset('weekly')">{{ $t('Weekly Package') }}</button>
      <button type="button" class="px-3 py-2 text-xs font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors" @click="applyPreset('monthly')">{{ $t('Monthly Package') }}</button>
      <button type="button" class="px-3 py-2 text-xs font-semibold text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/50 transition-colors" @click="applyPreset('yearly')">{{ $t('Yearly Package') }}</button>
      <button type="button" class="px-3 py-2 text-xs font-semibold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-lg hover:bg-amber-100 dark:hover:bg-amber-900/50 transition-colors col-span-2 sm:col-span-1" @click="applyPreset('lifetime')">{{ $t('Lifetime Package') }}</button>
    </div>

    <!-- Package Cards List -->
    <div class="space-y-4">
      <div v-for="(pkg, index) in packages" :key="index" class="bg-admin-theme-surface border border-admin-theme-border rounded-xl shadow-sm overflow-hidden">
        <!-- Package Header -->
        <div class="p-4 bg-admin-theme-base/60 border-b border-admin-theme-border flex items-center justify-between cursor-pointer select-none" @click="togglePackageCollapse(index)">
          <div class="flex items-center gap-2">
            <span class="text-xs font-mono font-bold text-admin-theme-primary uppercase">PACKAGE #{{ index + 1 }}: {{ pkg.name || 'UNNAMED' }}</span>
            <span v-if="pkg.access_type === 'permanent'" class="px-2 py-0.5 text-[10px] font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 rounded border border-amber-200 dark:border-amber-800">PERMANENT</span>
            <span v-else class="px-2 py-0.5 text-[10px] font-bold bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 rounded border border-blue-200 dark:border-blue-800">SUBSCRIPTION</span>
          </div>
          <div class="flex items-center gap-3">
            <button v-if="packages.length > 1" type="button" class="text-xs text-red-600 hover:text-red-700 font-semibold" @click.stop="removePackage(index)">{{ $t('Remove Package') }}</button>
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
            <label class="block text-sm font-semibold text-admin-theme-text-secondary">{{ $t('Capabilities (Features)') }}</label>
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
import { inject, ref, watch, onMounted, getCurrentInstance } from 'vue';
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t;
import { EditorContextKey } from '../../../../editor/context';
import { useTranslation } from '@/admin/composables/useTranslation';
const { t } = useTranslation();

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

const packages = ref<PackageConfig[]>([createDefaultPackage()]);
const collapsedPackages = ref<Record<number, boolean>>({});

const togglePackageCollapse = (index: number) => {
  collapsedPackages.value[index] = !collapsedPackages.value[index];
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
  let p = packages.value[0];
  if (!p) {
    p = createDefaultPackage();
    packages.value.push(p);
  }

  switch (presetType) {
    case 'daily':
      p.name = 'Daily Package';
      p.code = 'daily';
      p.access_type = 'subscription';
      p.duration_value = 1;
      p.duration_unit = 'day';
      p.is_recurring = false;
      p.license_policy = 'site';
      p.max_activations = 1;
      break;
    case 'weekly':
      p.name = 'Weekly Package';
      p.code = 'weekly';
      p.access_type = 'subscription';
      p.duration_value = 1;
      p.duration_unit = 'week';
      p.is_recurring = false;
      p.license_policy = 'site';
      p.max_activations = 1;
      break;
    case 'monthly':
      p.name = 'Monthly Package';
      p.code = 'monthly';
      p.access_type = 'subscription';
      p.duration_value = 1;
      p.duration_unit = 'month';
      p.is_recurring = true;
      p.license_policy = 'site';
      p.max_activations = 5;
      break;
    case 'yearly':
      p.name = 'Yearly Package';
      p.code = 'yearly';
      p.access_type = 'subscription';
      p.duration_value = 1;
      p.duration_unit = 'year';
      p.is_recurring = true;
      p.license_policy = 'site';
      p.max_activations = 5;
      break;
    case 'lifetime':
      p.name = 'Lifetime Package';
      p.code = 'lifetime';
      p.access_type = 'permanent';
      p.duration_value = 1;
      p.duration_unit = 'year';
      p.is_recurring = false;
      p.license_policy = 'site';
      p.max_activations = 5;
      break;
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

// Load initial data
onMounted(() => {
  watch(() => context.form.value.services, (newVal) => {
    if (newVal && Array.isArray(newVal) && newVal.length > 0) {
      packages.value = newVal.map(item => ({
        ...item,
        price: item.price !== undefined && item.price !== null && item.price !== '' ? Number(item.price) : undefined,
        license_policy: getLicensePolicyType(item.license_policy),
        max_activations: item.max_activations !== undefined ? Number(item.max_activations) : getMaxActivations(item.license_policy, 5),
        capabilities: item.capabilities || {},
      }));
    } else if (context.form.value.service_config && Array.isArray(context.form.value.service_config)) {
      packages.value = context.form.value.service_config.map(item => ({
        ...item,
        price: item.price !== undefined && item.price !== null && item.price !== '' ? Number(item.price) : undefined,
        license_policy: getLicensePolicyType(item.license_policy),
        max_activations: item.max_activations !== undefined ? Number(item.max_activations) : getMaxActivations(item.license_policy, 5),
        capabilities: item.capabilities || {},
      }));
    }
  }, { immediate: true });
});
</script>
