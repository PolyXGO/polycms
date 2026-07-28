<script setup lang="ts">
import AccountLayout from '@/Layouts/AccountLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { useTranslation } from '@/admin/composables/useTranslation';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { ref } from 'vue';
import AddressFormModal from './AddressFormModal.vue';

const { t } = useTranslation();

const props = defineProps<{
    addresses: any[];
}>();

const showModal = ref(false);
const editingAddress = ref<any>(null);

const openCreateModal = () => {
    editingAddress.value = null;
    showModal.value = true;
};

const openEditModal = (address: any) => {
    editingAddress.value = address;
    showModal.value = true;
};

const deleteAddress = (id: number) => {
    if (confirm(t('Are you sure you want to delete this address?'))) {
        router.delete(route('account.addresses.destroy', id), {
            preserveScroll: true,
        });
    }
};

const setAsDefault = (id: number) => {
    router.patch(route('account.addresses.default', id), {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="t('Addresses')" />

    <AccountLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <span>{{ t('Addresses') }}</span>
                <PrimaryButton @click="openCreateModal">
                    {{ t('Add New Address') }}
                </PrimaryButton>
            </div>
        </template>

        <div class="bg-white dark:bg-[#111] p-6 shadow-sm sm:rounded-xl border border-gray-200 dark:border-zinc-800 transition-colors">
            
            <div v-if="addresses.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ t('No addresses') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ t('Get started by adding a new address.') }}</p>
                <div class="mt-6">
                    <PrimaryButton @click="openCreateModal">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ t('New Address') }}
                    </PrimaryButton>
                </div>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div 
                    v-for="address in addresses" 
                    :key="address.id"
                    class="border rounded-lg p-5 relative transition-all duration-200"
                    :class="[
                        address.is_default 
                            ? 'border-gray-900 ring-1 ring-gray-900 dark:border-gray-100 dark:ring-gray-100 bg-gray-50/50 dark:bg-zinc-800/30' 
                            : 'border-gray-200 dark:border-zinc-800 hover:border-gray-300 dark:hover:border-zinc-600'
                    ]"
                >
                    <div v-if="address.is_default" class="absolute top-0 right-0 -mt-3 -mr-3 flex h-6 items-center justify-center rounded-full bg-gray-900 dark:bg-gray-100 px-3 text-xs font-semibold text-white dark:text-gray-900 shadow-sm">
                        {{ t('Default') }}
                    </div>

                    <div class="font-medium text-gray-900 dark:text-white mb-2 text-lg">
                        {{ address.full_name }}
                    </div>
                    
                    <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1 mb-4">
                        <p class="flex items-center">
                            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            {{ address.phone }}
                        </p>
                        <p class="flex items-start mt-2">
                            <svg class="h-4 w-4 mr-2 text-gray-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>
                                {{ address.address_line }}<br>
                                {{ address.city }}<template v-if="address.postal_code">, {{ address.postal_code }}</template><br>
                                {{ address.country }}
                            </span>
                        </p>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-zinc-800/80 flex justify-between items-center">
                        <div class="flex space-x-3">
                            <button @click="openEditModal(address)" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                                {{ t('Edit') }}
                            </button>
                            <button @click="deleteAddress(address.id)" class="text-sm font-medium text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                                {{ t('Delete') }}
                            </button>
                        </div>
                        
                        <button v-if="!address.is_default" @click="setAsDefault(address.id)" class="text-xs font-medium text-gray-500 hover:text-gray-900 dark:hover:text-white bg-gray-50 dark:bg-zinc-800/50 px-2 py-1 rounded border border-gray-200 dark:border-zinc-700 hover:border-gray-400 dark:hover:border-gray-500 transition-colors">
                            {{ t('Set Default') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <AddressFormModal 
            :show="showModal" 
            :address="editingAddress"
            @close="showModal = false" 
        />
    </AccountLayout>
</template>
