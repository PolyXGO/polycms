<template>
    <div class="space-y-8">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-50">PolyFengshui API Tokens</h1>
            <p class="text-sm text-gray-500 dark:text-gray-300">
                Manage the tokens required to access the PolyFengshui REST and GraphQL endpoints.
            </p>
        </div>

        <div class="bg-white dark:bg-gray-900 shadow rounded-lg p-6 space-y-6">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
                <div>
                    <h2 class="text-lg font-medium text-gray-800 dark:text-gray-100">API protection</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-300">
                        Require a token for public endpoints exposed by this module.
                    </p>
                </div>
                <label class="inline-flex items-center space-x-2">
                    <input
                        type="checkbox"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
                        v-model="active"
                        @change="saveSettings"
                    />
                    <span class="text-sm text-gray-700 dark:text-gray-200">Enable token protection</span>
                </label>
            </div>

            <div>
                <h2 class="text-lg font-medium text-gray-800 dark:text-gray-100 mb-3">Create new token</h2>
                <form class="grid gap-4 md:grid-cols-2" @submit.prevent="createToken">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Display name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Example: Partner ABC"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Allowed domain</label>
                        <input
                            v-model="form.domain"
                            type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="example.com"
                        />
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            Leave empty to allow the token to be used on multiple domains.
                        </p>
                    </div>
                    <div class="md:col-span-2 flex items-center space-x-3">
                        <button
                            type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                            :disabled="creating"
                        >
                            <svg
                                v-if="creating"
                                class="animate-spin h-4 w-4 mr-2 text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                                ></path>
                            </svg>
                            Create token
                        </button>
                        <span v-if="message" class="text-sm text-green-600">{{ message }}</span>
                        <span v-if="error" class="text-sm text-red-600">{{ error }}</span>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 shadow rounded-lg">
            <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-medium text-gray-800 dark:text-gray-100">Token list</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-300">Copy tokens carefully and keep them in a secure location.</p>
                </div>
            </div>

            <div v-if="loading" class="p-6">
                <p class="text-sm text-gray-500 dark:text-gray-300">Loading...</p>
            </div>

            <div v-else>
                <div v-if="tokens.length === 0" class="p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-300">No tokens yet. Create one above.</p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-800/70">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Domain
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Token
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Created at
                                </th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                            <tr v-for="token in tokens" :key="token.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ token.name || 'Unnamed' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ token.domain || 'Any domain' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    <div class="flex items-center space-x-3">
                                        <code class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 px-2 py-1 rounded">
                                            {{ tokenPreview(token.token) }}
                                        </code>
                                        <button
                                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-xs font-medium"
                                            @click="copyToken(token.token)"
                                        >
                                            Copy
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatDate(token.created_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button
                                        class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                        @click="removeToken(token.id)"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import axios from 'axios';

interface Token {
    id: string;
    name: string | null;
    token: string;
    domain: string | null;
    created_at: string;
}

const tokens = ref<Token[]>([]);
const loading = ref(false);
const creating = ref(false);
const active = ref(false);
const message = ref('');
const error = ref('');

const form = ref({
    name: '',
    domain: '',
});

const fetchTokens = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/admin-api/polyfengshui/tokens');
        tokens.value = response.data.tokens ?? [];
        active.value = response.data.active ?? false;
    } catch (err) {
        console.error(err);
        error.value = 'Unable to load tokens.';
    } finally {
        loading.value = false;
    }
};

const tokenPreview = (value: string) => {
    if (!value) {
        return '';
    }

    if (value.length <= 8) {
        return value;
    }

    return `${value.slice(0, 4)}••••${value.slice(-4)}`;
};

const createToken = async () => {
    if (creating.value) return;
    message.value = '';
    error.value = '';

    creating.value = true;
    try {
        const response = await axios.post('/admin-api/polyfengshui/tokens', {
            name: form.value.name,
            domain: form.value.domain,
        });

        tokens.value = response.data.tokens ?? tokens.value;
        message.value = 'Token created successfully.';
        form.value.name = '';
        form.value.domain = '';
    } catch (err) {
        console.error(err);
        error.value = 'Unable to create token.';
    } finally {
        creating.value = false;
    }
};

const removeToken = async (tokenId: string) => {
    if (!confirm('Are you sure you want to delete this token?')) {
        return;
    }

    message.value = '';
    error.value = '';

    try {
        const response = await axios.delete(`/admin-api/polyfengshui/tokens/${tokenId}`);
        tokens.value = response.data.tokens ?? tokens.value;
        message.value = 'Token deleted.';
    } catch (err) {
        console.error(err);
        error.value = 'Unable to delete token. Please try again later.';
    }
};

const saveSettings = async () => {
    message.value = '';
    error.value = '';

    try {
        await axios.post('/admin-api/polyfengshui/tokens/settings', { active: active.value });
        message.value = 'Token protection status updated.';
    } catch (err) {
        console.error(err);
        error.value = 'Unable to update settings.';
    }
};

const copyToken = async (value: string) => {
    try {
        await navigator.clipboard.writeText(value);
        message.value = 'Token copied to clipboard.';
    } catch (err) {
        console.error(err);
        error.value = 'Unable to copy token.';
    }
};

const formatDate = (value: string) => {
    if (!value) return '';
    const date = new Date(value);
    return date.toLocaleString();
};

onMounted(() => {
    fetchTokens();
});
</script>

