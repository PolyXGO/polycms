<template>
    <div v-if="context.form.value.type === 'service' || context.form.value.type === 'digital'" class="space-y-6">
        <div v-for="(pkg, index) in packages" :key="index" class="p-6 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100 dark:border-gray-700">
                <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">{{ $t('Package') }} #{{ index + 1 }}: {{ pkg.name || $t('Untitled') }}</h4>
                <button type="button" class="text-xs font-semibold text-red-500 hover:text-red-600 hover:underline transition-colors" @click="removePackage(index)" v-if="packages.length > 1">
                    {{ $t('Remove Package') }}
                </button>
            </div>
            
            <div class="space-y-4">
                 <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('Service Name *') }}</label>
                        <input v-model="pkg.name" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-indigo-500 focus:border-indigo-500" required :placeholder="$t('e.g. Basic Package')" />
                    </div>
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('Service Code *') }}</label>
                        <input v-model="pkg.code" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-indigo-500 focus:border-indigo-500" required :placeholder="$t('e.g. PKG-BASIC')" />
                    </div>
                </div>

                 <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('Access Type') }}</label>
                    <select v-model="pkg.access_type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="permanent">{{ $t('Permanent (One-time)') }}</option>
                        <option value="subscription">{{ $t('Subscription / Limited Time') }}</option>
                    </select>
                </div>

                <div v-if="pkg.access_type !== 'permanent'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                     <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('Duration Value') }}</label>
                        <input v-model.number="pkg.duration_value" type="number" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500" min="1" />
                    </div>
                     <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('Duration Unit') }}</label>
                        <select v-model="pkg.duration_unit" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="day">{{ $t('Days') }}</option>
                            <option value="week">{{ $t('Weeks') }}</option>
                            <option value="month">{{ $t('Months') }}</option>
                            <option value="year">{{ $t('Years') }}</option>
                        </select>
                    </div>
                </div>

                <div v-if="pkg.access_type === 'subscription'" class="flex items-center">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" v-model="pkg.is_recurring" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 transition-all duration-200" />
                        <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('Is Recurring?') }}</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                     <div v-if="pkg.access_type !== 'permanent'" class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('Trial Period (Days)') }}</label>
                        <input v-model.number="pkg.trial_period_days" type="number" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500" min="0" />
                    </div>
                     <div class="space-y-1" :class="{ 'sm:col-span-1': pkg.access_type !== 'permanent', 'sm:col-span-2': pkg.access_type === 'permanent' }">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('Package Price Override') }}</label>
                        <input v-model.number="pkg.price" type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500" />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $t('Leave empty to use product price.') }}</p>
                    </div>
                </div>
                
                 <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('License Policy') }}</label>
                    <select v-model="pkg.license_policy" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                         <option value="">{{ $t('No License Key') }}</option>
                        <option value="per_seat">{{ $t('Per Seat') }}</option>
                        <option value="site">{{ $t('Site License') }}</option>
                    </select>
                </div>

                <!-- Capabilities Section -->
                <div class="space-y-3">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $t('Capabilities (Features)') }}</label>
                    <div class="space-y-2">
                        <div v-for="(capValue, capKey) in pkg.capabilities" :key="capKey" class="flex gap-2 items-center">
                            <input :value="capKey" @input="updateCapabilityKey(index, String(capKey), ($event.target as HTMLInputElement).value)" :placeholder="$t('Key')" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm" />
                            <input :value="capValue" @input="updateCapabilityValue(index, String(capKey), ($event.target as HTMLInputElement).value)" :placeholder="$t('Value')" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm" />
                            <button type="button" class="p-1.5 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg transition-colors" @click="removeCapability(index, String(capKey))">&times;</button>
                        </div>
                    </div>
                    <button type="button" class="px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800 rounded-lg text-xs font-semibold hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors" @click="addCapability(index)">+ {{ $t('Add Capability') }}</button>
                </div>
            </div>
        </div>
        <button type="button" class="w-full p-4 bg-gray-50 dark:bg-gray-800/50 text-gray-600 dark:text-gray-400 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl font-bold hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white transition-all" @click="addPackage">+ {{ $t('Add Another Package') }}</button>
    </div>
    <div v-else class="p-8 text-center text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/30 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700">
        <p class="text-sm font-medium">{{ $t('This panel is only applicable for') }} <strong>{{ $t('Service') }}</strong> {{ $t('or') }} <strong>{{ $t('Digital') }}</strong> {{ $t('products.') }}</p>
    </div>
</template>

<script setup lang="ts">
import { inject, ref, watch, onMounted, getCurrentInstance } from 'vue';
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t;
import { EditorContextKey } from '../../../../editor/context';

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
    capabilities: {},
});

const packages = ref<PackageConfig[]>([createDefaultPackage()]);

const addPackage = () => {
    packages.value.push(createDefaultPackage());
};

const removePackage = (index: number) => {
    packages.value.splice(index, 1);
};

const addCapability = (pkgIndex: number) => {
    const pkg = packages.value[pkgIndex];
    if (!pkg.capabilities) pkg.capabilities = {};
    const key = `feature_${Object.keys(pkg.capabilities).length + 1}`;
    pkg.capabilities = { ...pkg.capabilities, [key]: '' };
};

const removeCapability = (pkgIndex: number, key: string) => {
    const caps = { ...packages.value[pkgIndex].capabilities };
    delete caps[key];
    packages.value[pkgIndex].capabilities = caps;
};

const updateCapabilityKey = (pkgIndex: number, oldKey: string, newKey: string) => {
    if (oldKey === newKey) return;
    const pkg = packages.value[pkgIndex];
    const value = pkg.capabilities[oldKey];
    const caps = { ...pkg.capabilities };
    delete caps[oldKey];
    caps[newKey] = value;
    pkg.capabilities = caps;
};

const updateCapabilityValue = (pkgIndex: number, key: string, value: any) => {
    packages.value[pkgIndex].capabilities = {
        ...packages.value[pkgIndex].capabilities,
        [key]: value
    };
};

// Sync with main form
watch(packages, (newValue) => {
    context.form.value.service_config = newValue;
}, { deep: true });

// Load initial data
onMounted(() => {
    watch(() => context.form.value.services, (newVal) => {
        if (newVal && Array.isArray(newVal) && newVal.length > 0) {
             packages.value = newVal.map(item => ({
                 ...item,
                 license_policy: item.license_policy || '',
                 capabilities: item.capabilities || {},
             }));
        } else if (context.form.value.service_config && Array.isArray(context.form.value.service_config)) {
             packages.value = context.form.value.service_config;
        }
    }, { immediate: true });
});
</script>
