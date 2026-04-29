<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('Tax Rates') }}</h1>
            <router-link
                :to="{ name: 'admin.ecommerce.tax-rates.create' }"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors cursor-pointer"
            >
                + {{ $t('New Tax Rate') }}
            </router-link>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ $t('Name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ $t('Location') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ $t('Rate') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ $t('Compound') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ $t('Priority') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ $t('Status') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ $t('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="tax in taxes" :key="tax.id">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                            {{ tax.name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ tax.country }} <span v-if="tax.state">({{ tax.state }})</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                            {{ (parseFloat(tax.rate) * 100).toFixed(2) }}%
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <span v-if="tax.is_compound" class="text-green-500">Yes</span>
                            <span v-else class="text-gray-400">No</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ tax.priority }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="tax.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                {{ tax.is_active ? $t('Active') : $t('Inactive') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <router-link :to="{ name: 'admin.ecommerce.tax-rates.edit', params: { id: tax.id } }" class="text-indigo-600 hover:text-indigo-900 dark:hover:text-indigo-400 mr-3">{{ $t('Edit') }}</router-link>
                            <button @click="deleteTax(tax.id)" class="text-red-600 hover:text-red-900 dark:hover:text-red-400" :disabled="deleting === tax.id">
                                {{ deleting === tax.id ? $t('Deleting...') : $t('Delete') }}
                            </button>
                        </td>
                    </tr>
                    <tr v-if="taxes.length === 0">
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">{{ $t('No tax rates found.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useTranslation } from '../../composables/useTranslation';

const { t } = useTranslation();

const taxes = ref<any[]>([]);
const deleting = ref<number | null>(null);

const loadTaxes = async () => {
    try {
        const response = await axios.get('/api/v1/tax-rates');
        taxes.value = response.data.data;
    } catch (e) {
        console.error(e);
    }
};

const deleteTax = async (id: number) => {
    if (!confirm(t('Are you sure you want to delete this tax rate?'))) return;
    deleting.value = id;
    try {
        await axios.delete(`/api/v1/tax-rates/${id}`);
        await loadTaxes();
    } catch (e) {
        console.error(e);
        alert(t('Failed to delete tax rate'));
    } finally {
        deleting.value = null;
    }
};

onMounted(loadTaxes);
</script>
