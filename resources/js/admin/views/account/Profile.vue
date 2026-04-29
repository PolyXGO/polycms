<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('My Profile') }}</h1>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden p-6">
             <form @submit.prevent="updateProfile" class="space-y-6 max-w-xl">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Name') }}</label>
                    <input type="text" id="name" v-model="form.name" class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-white" required>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Email') }}</label>
                    <input type="email" id="email" v-model="form.email" class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-white" required>
                </div>

                 <hr class="border-gray-200 dark:border-gray-700 my-4" />
                 
                 <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ t('Change Password') }}</h3>
                    <div class="space-y-4">
                         <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Current Password') }}</label>
                            <input type="password" id="current_password" v-model="form.current_password" class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('New Password') }}</label>
                            <input type="password" id="password" v-model="form.password" class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Confirm New Password') }}</label>
                            <input type="password" id="password_confirmation" v-model="form.password_confirmation" class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ t('Save Changes') }}
                    </button>
                </div>
             </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useTranslation } from '@/admin/composables/useTranslation';
import { useDialog } from '@/admin/composables/useDialog';

const { t } = useTranslation();
const dialog = useDialog();

const form = ref({
    name: '',
    email: '',
    current_password: '',
    password: '',
    password_confirmation: ''
});

const loadProfile = async () => {
    try {
        const response = await axios.get('/api/v1/profile');
        form.value.name = response.data.name;
        form.value.email = response.data.email;
    } catch (error) {
        dialog.error('Failed to load profile');
    }
};

const updateProfile = async () => {
    try {
        await axios.put('/api/v1/profile', form.value);
        dialog.success('Profile updated successfully');
        form.value.current_password = '';
        form.value.password = '';
        form.value.password_confirmation = '';
    } catch (e: any) {
        dialog.error(e.response?.data?.message || 'Update failed');
    }
};

onMounted(loadProfile);
</script>
