<template>
    <div class="relative">
        <label v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ label }}</label>
        
        <!-- Selected Items -->
        <div class="flex flex-wrap gap-2 mb-2" v-if="modelValue && modelValue.length > 0">
            <div v-for="email in modelValue" :key="email" class="bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 px-2 py-1 rounded-md text-sm flex items-center">
                <span>{{ email }}</span>
                <button type="button" @click="remove(email)" class="ml-1 text-indigo-500 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-200">
                    &times;
                </button>
            </div>
        </div>

        <!-- Search Input -->
        <input 
            type="text" 
            v-model="searchQuery" 
            @input="handleInput"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            placeholder="Search users by name or email..."
        />
        
        <!-- Dropdown -->
        <div v-if="showDropdown && (results.length > 0 || loading)" class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg max-h-60 overflow-y-auto">
            <div v-if="loading" class="p-3 text-sm text-gray-500 dark:text-gray-400 text-center">Loading...</div>
            <ul v-else>
                <li 
                    v-for="user in results" 
                    :key="user.id" 
                    @click="select(user)" 
                    class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm text-gray-900 dark:text-white"
                >
                    <div class="font-medium">{{ user.name }}</div>
                    <div class="text-gray-500 dark:text-gray-400 text-xs">{{ user.email }}</div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import axios from 'axios';
import { debounce } from 'lodash';

const props = defineProps({
    modelValue: {
        type: Array as () => string[], // Array of emails
        default: () => []
    },
    label: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['update:modelValue']);

const searchQuery = ref('');
const results = ref<any[]>([]);
const loading = ref(false);
const showDropdown = ref(false);

const handleInput = debounce(async () => {
    if (searchQuery.value.length < 2) {
        results.value = [];
        showDropdown.value = false;
        return;
    }
    
    loading.value = true;
    showDropdown.value = true;
    
    try {
        const response = await axios.get('/api/v1/admin/users/search', {
            params: { q: searchQuery.value }
        });
        results.value = response.data;
    } catch (error) {
        console.error('UserId search error', error);
        results.value = [];
    } finally {
        loading.value = false;
    }
}, 300);

const select = (user: any) => {
    if (!props.modelValue.includes(user.email)) {
        const newValue = [...props.modelValue, user.email];
        emit('update:modelValue', newValue);
    }
    searchQuery.value = '';
    showDropdown.value = false;
};

const remove = (email: string) => {
    const newValue = props.modelValue.filter(e => e !== email);
    emit('update:modelValue', newValue);
};

// Close dropdown on click outside logic could be added here
</script>
