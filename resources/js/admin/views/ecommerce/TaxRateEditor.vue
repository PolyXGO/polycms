<template>
    <div class="w-full">
        <div class="flex items-center mb-6">
            <router-link :to="{ name: 'admin.ecommerce.tax-rates.index' }" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </router-link>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ isEditing ? $t('Edit Tax Rate') : $t('New Tax Rate') }}
            </h1>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <form @submit.prevent="saveTaxRate" novalidate class="space-y-6">
                <!-- Group 1: Basics -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Tax Name') }} *</label>
                        <input v-model="form.name" type="text" required :class="{'border-red-500': errors.name}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="e.g. VAT, State Tax" />
                        <p v-if="errors.name" class="mt-1 text-sm text-red-500">{{ errors.name[0] }}</p>
                    </div>

                    <!-- Location -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Location (Country / State)') }} *</label>
                        <CountryRegionSelect v-model="location" :multiple="false" :error="!!errors.country" />
                        <p v-if="errors.country" class="mt-1 text-sm text-red-500">{{ errors.country[0] }}</p>
                    </div>

                    <!-- Rate -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Tax Rate') }} (%) *</label>
                        <input v-model="displayRate" @input="updateRate" type="number" step="0.01" min="0" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="e.g. 10" />
                        <p class="text-xs text-gray-500 mt-1">{{ $t('Enter percentage (e.g. 10 for 10%)') }}</p>
                    </div>

                    <!-- Priority -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Priority') }}</label>
                        <input v-model.number="form.priority" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                        <p class="text-xs text-gray-500 mt-1">{{ $t('Lower number gets calculated first') }}</p>
                    </div>
                </div>

                <!-- Switches -->
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input id="is_compound" v-model="form.is_compound" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                        <label for="is_compound" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                            {{ $t('Compound Tax') }}
                            <span class="text-xs text-gray-500 block">{{ $t('Applied on top of other taxes.') }}</span>
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input id="is_active" v-model="form.is_active" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                        <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                            {{ $t('Active') }}
                        </label>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="submit" :disabled="saving" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                        {{ saving ? $t('Saving...') : $t('Save Tax Rate') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useTranslation } from '../../composables/useTranslation';
import CountryRegionSelect from '../../components/Form/CountryRegionSelect.vue';

const { t } = useTranslation();
const route = useRoute();
const router = useRouter();

const isEditing = computed(() => !!route.params.id);
const loading = ref(isEditing.value);
const saving = ref(false);
const errors = ref<Record<string, string[]>>({});
const displayRate = ref<number | string>('');

const form = ref({
    name: '',
    country: '',
    state: '',
    rate: 0,
    is_compound: false,
    is_active: true,
    priority: 0,
});

const updateRate = (e: any) => {
    const val = parseFloat(e.target.value);
    if (!isNaN(val)) {
        form.value.rate = val / 100;
    } else {
        form.value.rate = 0;
    }
};

const location = computed({
    get: () => ({ country: form.value.country, state: form.value.state }),
    set: (val: any) => {
        form.value.country = val?.country || '';
        form.value.state = val?.state || '';
    }
});

const loadTaxRate = async () => {
    try {
        const response = await axios.get(`/api/v1/tax-rates/${route.params.id}`);
        const data = response.data.data;
        form.value = {
            name: data.name,
            country: data.country,
            state: data.state || '',
            rate: parseFloat(data.rate),
            is_compound: data.is_compound,
            is_active: data.is_active,
            priority: data.priority,
        };
        displayRate.value = (form.value.rate * 100).toFixed(2);
    } catch (e) {
        console.error(e);
        alert(t('Failed to load tax rate'));
        router.push({ name: 'admin.ecommerce.tax-rates.index' });
    }
};

const saveTaxRate = async () => {
    errors.value = {};
    if (!form.value.country) {
        errors.value.country = [t('Please select a country.')];
        return;
    }
    if (!form.value.name.trim()) {
        errors.value.name = [t('Tax Name is required.')];
        return;
    }
    saving.value = true;
    try {

        if (isEditing.value) {
            await axios.put(`/api/v1/tax-rates/${route.params.id}`, form.value);
            alert(t('Tax rate updated successfully'));
        } else {
            const res = await axios.post('/api/v1/tax-rates', form.value);
            alert(t('Tax rate created successfully'));
            router.push({ name: 'admin.ecommerce.tax-rates.edit', params: { id: res.data.data.id } });
        }
    } catch (e: any) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors;
            alert(t('Please fix validation errors'));
        } else {
            console.error(e);
            alert(e.response?.data?.message || t('Failed to save tax rate'));
        }
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    if (isEditing.value) {
        loadTaxRate();
    }
});
</script>
