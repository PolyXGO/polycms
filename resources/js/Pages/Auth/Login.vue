<script setup lang="ts">
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};

const publicDemoAccounts = ref<any[]>([]);

const selectDemoAccount = (acc: any) => {
    form.email = acc.username;
    form.password = acc.password_plain;
};

onMounted(async () => {
    try {
        const res = await axios.get('/api/v1/admin/demo-builder/public-accounts?role=customer');
        if (res.data?.success && res.data?.data) {
            publicDemoAccounts.value = res.data.data;
        }
    } catch(err) {
        // Silently ignore if demo builder is not installed or endpoint missing
    }
});
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="mb-8 text-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Sign in to your account</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Enter your credentials to access your dashboard</p>
        </div>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/30 p-3 rounded-lg border border-green-200 dark:border-green-800">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email address *" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="Email address"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password *" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="Password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400"
                        >Remember me</span
                    >
                </label>
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="rounded-md text-sm text-gray-600 dark:text-gray-400 underline hover:text-gray-900 dark:hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Forgot your password?
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Log in
                </PrimaryButton>
            </div>

            <!-- Select Demo Account Block (Dynamic from Demo Builder) -->
            <div v-if="publicDemoAccounts.length > 0" class="mt-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white text-center mb-4">Select Demo Account</h3>
                <div class="grid grid-cols-2 gap-2 mb-3">
                    <button v-for="acc in publicDemoAccounts" :key="acc.id" @click.prevent="selectDemoAccount(acc)" 
                        type="button"
                        :class="[
                            'flex items-center justify-between gap-1 px-2 py-1.5 border rounded-lg transition-all focus:outline-none overflow-hidden cursor-pointer',
                            form.email === acc.username
                                ? 'border-indigo-500 ring-1 ring-indigo-500 bg-indigo-50 dark:bg-indigo-900/30'
                                : 'border-gray-200 dark:border-gray-600 hover:border-indigo-400 dark:hover:border-indigo-500 bg-gray-50 dark:bg-gray-900'
                        ]">
                        <div class="flex items-center gap-1.5 truncate">
                            <svg class="w-3.5 h-3.5 text-gray-700 dark:text-gray-300 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
                            <span class="text-[11px] font-medium text-gray-900 dark:text-white truncate" :title="acc.role_name">{{ acc.role_name }}</span>
                        </div>
                        <span class="text-[9px] px-1.5 py-0.5 rounded flex-shrink-0 bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300">Customer</span>
                    </button>
                </div>
                <p class="text-[10px] text-center text-gray-500 dark:text-gray-400 mt-2 italic leading-tight">
                    Customer accounts for testing front-end features and checkout flow.
                </p>
            </div>

        </form>
    </GuestLayout>
</template>
