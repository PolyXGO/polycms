<template>
    <div class="payment-gateways">
        <div class="mb-6 flex items-center gap-4">
            <router-link :to="{ name: 'admin.settings.index' }" class="text-indigo-600 hover:text-indigo-700 font-medium flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ t('Back to Hub') }}
            </router-link>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('Payment Gateways') }}</h1>
        </div>

        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div 
                v-for="gateway in gateways" 
                :key="gateway.code"
                class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-100 dark:border-gray-700"
            >
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center mr-4">
                            <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ gateway.code.substring(0, 2).toUpperCase() }}</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white">{{ gateway.name }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ gateway.code }}</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <button 
                            @click="toggleGateway(gateway)"
                            :class="[
                                'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2',
                                gateway.is_active ? 'bg-green-500' : 'bg-gray-200'
                            ]"
                        >
                            <span 
                                :class="[
                                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                    gateway.is_active ? 'translate-x-5' : 'translate-x-0'
                                ]"
                            ></span>
                        </button>
                    </div>
                </div>

                <div class="border-t border-gray-100 dark:border-gray-700 pt-4 flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ gateway.is_active ? t('Active') : t('Inactive') }}
                    </span>
                    <router-link 
                        :to="{ name: 'admin.settings.gateways.edit', params: { id: gateway.code } }"
                        class="text-indigo-600 hover:text-indigo-700 text-sm font-medium"
                    >
                        {{ t('Configure') }}
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useTranslation } from '../../composables/useTranslation';
import { useDialog } from '../../composables/useDialog';

const { t } = useTranslation();
const dialog = useDialog();
const loading = ref(true);
const gateways = ref<any[]>([]);

const loadGateways = async () => {
    try {
        const response = await axios.get('/api/v1/payment-gateways');
        gateways.value = response.data.data;
    } catch (error) {
        console.error('Error loading gateways:', error);
    } finally {
        loading.value = false;
    }
};

const toggleGateway = async (gateway: any) => {
    try {
        await axios.put(`/api/v1/payment-gateways/${gateway.code}`, {
            is_active: !gateway.is_active
        });
        gateway.is_active = !gateway.is_active;
        dialog.success(t('Gateway status updated'));
    } catch (error) {
        dialog.error(t('Failed to update status'));
    }
};

onMounted(loadGateways);
</script>
