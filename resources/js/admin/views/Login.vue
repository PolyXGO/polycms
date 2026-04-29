<template>
    <div 
        class="min-h-screen flex transition-all duration-700 relative overflow-hidden" 
        :class="{
            'opacity-0': !settingsLoaded,
            'opacity-100': settingsLoaded,
            'items-center justify-center p-4 sm:p-6 lg:p-8': authLayoutPosition === 'center',
            'items-stretch justify-start': authLayoutPosition === 'left',
            'items-stretch justify-end': authLayoutPosition === 'right'
        }"
    >
        <!-- Background Image Pane -->
        <div v-if="authBgMode === 'slideshow' && authBgImages.length > 0" class="absolute inset-0 z-0 bg-gray-900">
            <template v-for="(img, idx) in authBgImages" :key="idx">
                <img :src="img" 
                     class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000 ease-in-out" 
                     :class="currentSlideIndex === idx ? 'opacity-100' : 'opacity-0'" />
            </template>
            <div class="absolute inset-0 bg-black" :style="{ opacity: (authBgOverlay || 50) / 100 }"></div>
        </div>
        <div v-else-if="authBgImage" class="absolute inset-0 z-0">
            <img :src="authBgImage" class="absolute inset-0 w-full h-full object-cover" />
            <div class="absolute inset-0 bg-black" :style="{ opacity: (authBgOverlay || 50) / 100 }"></div>
        </div>
        <!-- Default fallback background -->
        <div v-else class="absolute inset-0 z-0 bg-gray-50 dark:bg-gray-900"></div>

        <!-- Footer Text -->
        <div v-if="authLoginText" 
             class="absolute bottom-6 z-20 select-none pointer-events-none hidden md:block"
             :class="authLayoutPosition === 'right' ? 'left-8 text-left' : 'right-8 text-right'">
            <div class="text-white dark:text-gray-300 drop-shadow-md">
                <span class="block text-sm font-semibold whitespace-pre-line pointer-events-auto">{{ authLoginText }}</span>
                <span v-if="authShowVersion" class="block text-xs opacity-75 mt-1 pointer-events-auto">
                    <a href="https://polycms.org" target="_blank" rel="nofollow" class="hover:underline font-medium hover:text-white transition-colors">PolyCMS</a> v{{ appVersion }} based on <a href="https://laravel.com" target="_blank" rel="nofollow" class="hover:underline font-medium hover:text-white transition-colors">Laravel</a> <span v-if="laravelVersion">v{{ laravelVersion }}</span>
                </span>
            </div>
        </div>

        <!-- Form Wrapper Pane (Logo + Card) -->
        <div 
            class="relative z-10 flex flex-col transition-all overflow-y-auto"
            :class="[
                authLayoutPosition === 'center' ? 'w-full max-w-md justify-center' : 'w-full lg:w-[480px] xl:w-[500px] min-h-screen flex-col justify-center px-8 sm:px-12 py-12 shadow-2xl auth-card',
                authLayoutPosition !== 'center' && authBgImage ? 'backdrop-blur-md border-white/10 dark:border-gray-700/30' : (authLayoutPosition !== 'center' ? 'bg-white dark:bg-gray-900 border-gray-100 dark:border-gray-800' : '')
            ]"
            :style="authLayoutPosition !== 'center' ? { 
                '--glass-opacity': (100 - (authCardGlassmorphism || 10)) / 100, 
                'border-right-width': authLayoutPosition === 'left' ? '1px' : '0', 
                'border-left-width': authLayoutPosition === 'right' ? '1px' : '0' 
            } : {}"
        >
            
            <!-- Brand Logo / Name above the card -->
            <div v-if="authShowLogo" class="mb-8 text-center w-full min-h-[48px] flex items-center justify-center">
                <template v-if="!brandLoading">
                    <a href="/" class="transition-opacity hover:opacity-80">
                        <img v-if="brandLogo" :src="brandLogo" :alt="brandName" class="h-20 w-auto object-contain mx-auto border-none shadow-none" />
                        <h2 v-else class="text-center text-3xl font-extrabold text-blue-600 dark:text-blue-400 uppercase tracking-tight">
                            {{ brandName }}
                        </h2>
                    </a>
                </template>
            </div>

            <!-- The Card -->
            <div 
                class="w-full space-y-8 transition-all"
                :class="[
                    authLayoutPosition === 'center' ? 'auth-card overflow-hidden px-8 py-10 shadow-2xl border sm:rounded-2xl' : '',
                    authLayoutPosition === 'center' && authBgImage ? 'backdrop-blur-md border-white/20 dark:border-gray-700/50' : (authLayoutPosition === 'center' ? 'border-gray-100 dark:border-gray-800' : '')
                ]"
                :style="authLayoutPosition === 'center' ? { '--glass-opacity': (100 - (authCardGlassmorphism || 10)) / 100 } : {}"
            >
                <div class="text-center">
                    <h2 class="text-center text-2xl font-bold text-gray-900 dark:text-white">
                        Sign in to your account
                    </h2>
                    <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                        Enter your credentials to access the admin panel
                    </p>
                </div>
            <form v-if="!show2FA" class="mt-8 space-y-6" @submit.prevent="handleLogin">
                <div v-if="error" class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ error }}</span>
                </div>



                <div class="space-y-4">
                    <FormField
                        name="email"
                        label="Email address"
                        :required="true"
                        :error="validationErrors.email"
                    >
                        <FormInput
                            v-model="form.email"
                            name="email"
                            type="email"
                            :rules="['required', 'email']"
                            validate-on="blur"
                            autocomplete="email"
                            placeholder="Email address"
                            class="rounded-t-md"
                        />
                    </FormField>
                    <FormField
                        name="password"
                        label="Password"
                        :required="true"
                        :error="validationErrors.password"
                    >
                        <FormInput
                            v-model="form.password"
                            name="password"
                            type="password"
                            :rules="['required']"
                            validate-on="blur"
                            autocomplete="current-password"
                            placeholder="Password"
                            class="rounded-b-md"
                        />
                    </FormField>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input
                            id="remember-me"
                            v-model="form.remember"
                            name="remember-me"
                            type="checkbox"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        />
                        <label for="remember-me" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                            Remember me
                        </label>
                    </div>
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="loading">Signing in...</span>
                        <span v-else>Sign in</span>
                    </button>
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
                            <span class="text-[9px] px-1.5 py-0.5 rounded flex-shrink-0 bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300">Admin</span>
                        </button>
                    </div>
                    <p class="text-[10px] text-center text-gray-500 dark:text-gray-400 mt-2 italic leading-tight">
                        Full administrator access for testing and demonstration purposes. Can manage all system features except Demo Builder settings.
                    </p>
                </div>
            </form>

            <form v-else class="mt-8 space-y-6" @submit.prevent="handle2FAVerify">
                <div>
                    <h3 class="text-center text-xl font-bold text-gray-900 dark:text-white">
                        Two-Factor Authentication
                    </h3>
                    <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                        Please enter the 6-digit code from your authenticator app.
                    </p>
                </div>

                <div v-if="error" class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ error }}</span>
                </div>

                <div class="space-y-4">
                    <FormField
                        name="two_factor_code"
                        label="Verification Code"
                        :required="true"
                        :error="twoFactorError"
                    >
                        <FormInput
                            v-model="twoFactorCode"
                            name="two_factor_code"
                            type="text"
                            placeholder="000000"
                            class="text-center text-2xl tracking-widest"
                            maxlength="10"
                            autocomplete="one-time-code"
                        />
                    </FormField>
                </div>

                <div class="flex flex-col space-y-3">
                    <button
                        type="submit"
                        :disabled="loading || (twoFactorCode.length !== 6 && twoFactorCode.length !== 10)"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                    >
                        <span v-if="loading">Verifying...</span>
                        <span v-else>Verify & Sign in</span>
                    </button>
                    
                    <button
                        type="button"
                        @click="show2FA = false"
                        class="text-sm text-indigo-600 hover:text-indigo-500 font-medium"
                    >
                        Back to Login
                    </button>
                </div>
            </form>
        </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';
import { useValidation } from '../composables/useValidation';
import FormField from '../components/forms/FormField.vue';
import FormInput from '../components/forms/FormInput.vue';

const router = useRouter();
const authStore = useAuthStore();

// Validation
const validation = useValidation({
    showToast: false,
});

const validationErrors = computed(() => validation.errors.value);

const loading = ref(false);
const error = ref('');
const show2FA = ref(false);
const twoFactorCode = ref('');
const twoFactorError = ref('');
const userId = ref<number | null>(null);

const form = reactive({
    email: '', 
    password: '', 
    remember: false,
});

const brandLogo = ref<string | null>(null);
const brandName = ref<string>('POLYCMS');
const brandLoading = ref(true);
const settingsLoaded = ref(false);

const authShowLogo = ref(true);
const authLayoutPosition = ref('center');
const authBgOverlay = ref(50);
const authCardGlassmorphism = ref(10);
const authShowVersion = ref(true);
const authBgMode = ref<string>('random');
const authBgImages = ref<string[]>([]);
const currentSlideIndex = ref(0);
let slideInterval: any = null;
const authBgImage = ref<string | null>(null);
const authLoginText = ref<string>('');
const appVersion = ref<string>('1.0.0');
const laravelVersion = ref<string>('');

const loadBrandSettings = async () => {
    try {
        const response = await axios.get('/api/v1/public/settings');
        const generalSettings = response.data.data?.general || {};
        brandLogo.value = generalSettings.brand_logo?.value || generalSettings.brand_logo || null;
        brandName.value = generalSettings.brand_name?.value || generalSettings.brand_name || 'POLYCMS';
        appVersion.value = response.data.data?.version || '1.0.0';
        laravelVersion.value = response.data.data?.laravel_version || '';

        const authAppearance = response.data.data?.auth_appearance || {};
        authShowLogo.value = authAppearance.auth_show_logo !== undefined ? (authAppearance.auth_show_logo?.value ?? authAppearance.auth_show_logo) : true;
        authShowVersion.value = authAppearance.auth_show_version !== undefined ? (authAppearance.auth_show_version?.value ?? authAppearance.auth_show_version) : true;
        authLayoutPosition.value = authAppearance.auth_layout_position?.value || 'center';
        authBgOverlay.value = authAppearance.auth_bg_overlay?.value || 50;
        authCardGlassmorphism.value = authAppearance.auth_card_glassmorphism?.value || 10;
        authLoginText.value = authAppearance.auth_login_text?.value || "PolyXGO with love\nCopyright 2026 © PolyXGO.";
        
        const mode = authAppearance.auth_bg_mode?.value || 'random';
        authBgMode.value = mode;

        if (mode === 'fixed' && authAppearance.auth_bg_fixed_image?.value) {
            authBgImage.value = authAppearance.auth_bg_fixed_image.value;
        } else if (authAppearance.auth_bg_images?.value) {
            const rawImages = authAppearance.auth_bg_images.value;
            const images = typeof rawImages === 'string' ? JSON.parse(rawImages) : rawImages;
            if (images && images.length > 0) {
                const urls = images.map((img: any) => img.url || img);
                authBgImages.value = urls;

                if (mode === 'slideshow') {
                    if (slideInterval) clearInterval(slideInterval);
                    slideInterval = setInterval(() => {
                        currentSlideIndex.value = (currentSlideIndex.value + 1) % urls.length;
                    }, 5000);
                } else if (mode === 'random') {
                    authBgImage.value = urls[Math.floor(Math.random() * urls.length)];
                }
            }
        }
        
        settingsLoaded.value = true;
    } catch (error) {
        console.error('Failed to load settings', error);
        settingsLoaded.value = true;
    } finally {
        brandLoading.value = false;
    }
};

const publicDemoAccounts = ref<any[]>([]);

const selectDemoAccount = (acc: any) => {
    form.email = acc.username;
    form.password = acc.password_plain;
};

const handleLogin = async () => {
    // Validate form
    const validationRules: Record<string, any[]> = {
        email: ['required', 'email'],
        password: ['required'],
    };

    const results = await validation.validateForm(form, validationRules);
    const hasValidationErrors = results.some(r => !r.valid);

    if (hasValidationErrors) {
        return;
    }

    loading.value = true;
    error.value = '';
    validation.clearAllErrors();

    try {
        await authStore.login({
            email: form.email,
            password: form.password,
        });

        // Redirect to dashboard after successful login
        router.push({ name: 'admin.dashboard' });
    } catch (err: any) {
        if (err.response?.status === 403 && err.response?.data?.two_factor_required) {
            show2FA.value = true;
            userId.value = err.response.data.user_id;
            return;
        }
        
        error.value = err.response?.data?.message || 'Invalid email or password. Please try again.';
        // Set validation errors if provided
        if (err.response?.data?.errors) {
            validation.setErrors(err.response.data.errors);
        }
    } finally {
        loading.value = false;
    }
};

const handle2FAVerify = async () => {
    if (!userId.value || (twoFactorCode.value.length !== 6 && twoFactorCode.value.length !== 10)) return;

    loading.value = true;
    error.value = '';
    twoFactorError.value = '';

    try {
        await authStore.verify2FA({
            user_id: userId.value,
            one_time_password: twoFactorCode.value,
        });
        
        // Redirect to dashboard after successful verification
        router.push({ name: 'admin.dashboard' });
    } catch (err: any) {
        error.value = err.response?.data?.message || 'Invalid verification code. Please try again.';
    } finally {
        loading.value = false;
    }
};

onMounted(async () => {
    // If already logged in, redirect to dashboard
    if (authStore.isAuthenticated) {
        router.push({ name: 'admin.dashboard' });
    }

    loadBrandSettings();

    try {
        const res = await axios.get('/api/v1/admin/demo-builder/public-accounts');
        if (res.data?.success && res.data?.data) {
            publicDemoAccounts.value = res.data.data;
        }
    } catch(err) {
        // Silently ignore if demo builder is not installed or endpoint missing
    }



});

onUnmounted(() => {
    if (slideInterval) clearInterval(slideInterval);
});

</script>

<style scoped>
.auth-card {
    background-color: rgba(255, 255, 255, flex);
    /* Fallback default */
    --glass-opacity: 1;
}
:root .auth-card {
    background-color: rgba(255, 255, 255, var(--glass-opacity));
}
.dark .auth-card {
    background-color: rgba(31, 41, 55, var(--glass-opacity));
}
</style>


