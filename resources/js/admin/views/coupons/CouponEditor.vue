<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ isEditing ? 'Edit Coupon' : 'New Coupon' }}</h1>
            <button
                @click="save"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                :disabled="saving"
            >
                {{ saving ? 'Saving...' : 'Save Coupon' }}
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Info -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">General Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Coupon Code</label>
                            <input v-model="form.code" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white uppercase" placeholder="e.g. SUMMER2024" />
                        </div>
                        
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                            <input v-model="form.title" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="Promotion Name" />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Discount Type</label>
                            <select v-model="form.type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="percent">Percentage (%)</option>
                                <option value="fixed_amount">Fixed Amount ($)</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Value</label>
                            <input v-model="form.value" type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                        </div>
                    </div>
                </div>
                
                <!-- Usage Limits -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Usage Limits</h3>
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Total Limit</label>
                            <input v-model="form.usage_limit" type="number" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="Leave empty for unlimited" />
                        </div>
                         
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Limit Per User</label>
                            <input v-model="form.usage_limit_per_user" type="number" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                        </div>
                        
                        <div>
                             <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Min Order Value</label>
                            <input v-model="form.min_order_value" type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Discount Value</label>
                            <input v-model="form.max_discount_value" type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="No limit" />
                        </div>


                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Restricted to Users</label>
                            <UserSearch v-model="form.restricted_emails" />
                            <p class="text-xs text-gray-500 mt-1">If set, only these users can use the coupon.</p>
                        </div>
                     </div>
                </div>
            </div>
            
            <div class="space-y-6">
                <!-- Status & Schedule -->
                 <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Publishing</h3>
                    
                    <div class="space-y-3 mb-4">
                        <div class="flex items-center">
                            <input v-model="form.is_active" type="checkbox" id="is_active" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                            <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-white">Active</label>
                        </div>

                        <div class="flex items-center">
                            <input v-model="form.is_public" type="checkbox" id="is_public" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                            <label for="is_public" class="ml-2 block text-sm text-gray-900 dark:text-white">Publicly Available</label>
                            <span class="ml-2 text-xs text-gray-500">(Shown in "Available Coupons" list)</span>
                        </div>

                        <div class="flex items-center">
                            <input v-model="form.is_exclusive" type="checkbox" id="is_exclusive" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                            <label for="is_exclusive" class="ml-2 block text-sm text-gray-900 dark:text-white">Exclusive Discount</label>
                            <span class="ml-2 text-xs text-gray-500">(Cannot be combined with other coupons)</span>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                            <input v-model="form.starts_at" type="datetime-local" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expiration Date</label>
                            <input v-model="form.expires_at" type="datetime-local" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                        </div>
                    </div>
                 </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useDialog } from '../../composables/useDialog';
import UserSearch from '../../components/UserSearch.vue';

const route = useRoute();
const router = useRouter();
const dialog = useDialog();
const isEditing = computed(() => !!route.params.id);
const saving = ref(false);

const form = ref({
    code: '',
    title: '',
    type: 'percent',
    value: 0,
    min_order_value: 0,
    max_discount_value: null,
    usage_limit: null,
    usage_limit_per_user: 1,
    starts_at: '',
    expires_at: '',
    restricted_emails: [] as string[],
    is_active: true,
    is_public: false,
    is_exclusive: false,
});

// Removed manual textarea logic since UserSearch handles it

// Sync array to textarea (removed as UserSearch handles array directly) -> logic removed

const loadCoupon = async () => {
    if (!isEditing.value) return;
    try {
        const { data } = await axios.get(`/api/v1/coupons/${route.params.id}`);
        form.value = data;
    } catch (e) {
        console.error(e);
    }
};

const save = async () => {
    saving.value = true;
    try {
        let response;
        if (isEditing.value) {
            response = await axios.put(`/api/v1/coupons/${route.params.id}`, form.value);
            dialog.success('Coupon updated successfully.');
            // Just reload the data to be sure
            await loadCoupon();
        } else {
            response = await axios.post('/api/v1/coupons', form.value);
            dialog.success('Coupon created successfully.');
            
            // Redirect to the edit page of the newly created coupon
            const newId = response.data.id || response.data.data?.id;
            if (newId) {
                router.push({ name: 'admin.coupons.edit', params: { id: newId } });
            }
        }
    } catch (e: any) {
        dialog.error(e.response?.data?.message || 'Error saving coupon');
    } finally {
        saving.value = false;
    }
};

onMounted(loadCoupon);
</script>
