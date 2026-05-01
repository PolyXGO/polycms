<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $t('Profile') || 'Profile' }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $t('Update your account profile information and password.') }}
                </p>
            </div>
        </div>

        <div v-if="loading" class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $t('Loading profile...') }}</p>
        </div>

        <div v-else class="space-y-6">
            <!-- Profile Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ $t('Profile Information') || 'Profile Information' }}
                </h2>

                <form @submit.prevent="handleProfileSubmit" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField
                            name="name"
                            :label="$t('Name') || 'Name'"
                            :required="true"
                            :error="validationErrors.name"
                        >
                            <FormInput
                                v-model="profileForm.name"
                                name="name"
                                type="text"
                                :rules="nameRules"
                                validate-on="blur"
                            />
                        </FormField>

                        <FormField
                            name="email"
                            :label="$t('Email') || 'Email'"
                            :hint="$t('Email cannot be changed from this page.') || 'Email cannot be changed from this page.'"
                        >
                            <FormInput
                                v-model="profileForm.email"
                                name="email"
                                type="email"
                                disabled
                                readonly
                            />
                        </FormField>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            :disabled="saving"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            {{ saving ? ($t('Saving...') || 'Saving...') : ($t('Save') || 'Save') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ $t('Change Password') || 'Change Password' }}
                </h2>

                <form @submit.prevent="handlePasswordSubmit" class="space-y-4">
                    <FormField
                        name="current_password"
                        :label="$t('Current Password') || 'Current Password'"
                        :required="true"
                        :error="validationErrors.current_password"
                    >
                        <FormInput
                            v-model="passwordForm.current_password"
                            name="current_password"
                            type="password"
                            :rules="['required']"
                            validate-on="blur"
                        />
                    </FormField>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField
                            name="password"
                            :label="$t('New Password') || 'New Password'"
                            :required="true"
                            :error="validationErrors.password"
                            :hint="$t('Password must be at least 8 characters') || 'Password must be at least 8 characters'"
                        >
                            <FormInput
                                v-model="passwordForm.password"
                                name="password"
                                type="password"
                                :rules="passwordRules"
                                validate-on="blur"
                            />
                        </FormField>

                        <FormField
                            name="password_confirmation"
                            :label="$t('Confirm New Password') || 'Confirm New Password'"
                            :required="true"
                            :error="validationErrors.password_confirmation"
                        >
                            <FormInput
                                v-model="passwordForm.password_confirmation"
                                name="password_confirmation"
                                type="password"
                                :rules="passwordConfirmationRules"
                                validate-on="blur"
                            />
                        </FormField>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            :disabled="savingPassword"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            {{ savingPassword ? ($t('Saving...') || 'Saving...') : ($t('Update Password') || 'Update Password') }}
                        </button>
                    </div>
                </form>
            </div>
            <!-- Two-Factor Authentication -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ $t('Two-Factor Authentication') || 'Two-Factor Authentication' }}
                </h2>

                <div v-if="profileForm.google2fa_enabled" class="space-y-4">
                    <div class="p-4 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 rounded-lg flex items-start">
                        <svg class="h-5 w-5 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <div>
                            <p class="font-medium">{{ $t('Two-factor authentication is enabled.') }}</p>
                            <p class="text-sm mt-1 opacity-90">{{ $t('Your account is secured with an additional layer of authentication.') }}</p>
                        </div>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('Recovery Codes') }}</span>
                        </div>
                        <button
                            @click="fetchRecoveryCodes"
                            class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium"
                        >
                            {{ $t('View Codes') }}
                        </button>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button
                            @click="showDisable2FAModal = true"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                        >
                            {{ $t('Disable 2FA') }}
                        </button>
                    </div>
                </div>

                <div v-else class="space-y-4">
                    <div v-if="!show2FASetup" class="p-4 bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 rounded-lg flex items-start">
                        <svg class="h-5 w-5 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <div>
                            <p class="font-medium">{{ $t('Two-factor authentication is not enabled.') }}</p>
                            <p class="text-sm mt-1 opacity-90">{{ $t('Add additional security to your account by using two-factor authentication.') }}</p>
                        </div>
                    </div>

                    <div v-if="show2FASetup" class="space-y-6 p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <div class="text-center space-y-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $t('Scan the QR code below using your authenticator app (e.g., Google Authenticator, Authy).') }}
                            </p>
                            
                            <div v-if="qrCode" class="inline-block p-2 bg-white rounded shadow-sm" v-html="qrCode"></div>
                            
                            <div class="space-y-2">
                                <p class="text-xs uppercase font-bold text-gray-500">{{ $t('Secret Key') }}</p>
                                <code class="px-2 py-1 bg-gray-100 dark:bg-gray-900 rounded text-indigo-600 dark:text-indigo-400 font-mono">{{ twoFactorSecret }}</code>
                            </div>
                        </div>

                        <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <FormField
                                name="two_factor_code"
                                :label="$t('Verification Code')"
                                :required="true"
                                :error="twoFactorError"
                            >
                                <FormInput
                                    v-model="twoFactorCode"
                                    name="two_factor_code"
                                    type="text"
                                    placeholder="000000"
                                    class="text-center text-2xl tracking-widest"
                                    pattern="[0-9]*"
                                    maxlength="6"
                                />
                            </FormField>

                            <div class="flex justify-end space-x-3">
                                <button
                                    @click="cancel2FASetup"
                                    class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                                >
                                    {{ $t('Cancel') }}
                                </button>
                                <button
                                    @click="enable2FA"
                                    :disabled="enabling2FA || twoFactorCode.length !== 6"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition-colors"
                                >
                                    {{ enabling2FA ? $t('Enabling...') : $t('Verify & Enable') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-if="!show2FASetup" class="flex justify-end">
                        <button
                            @click="start2FASetup"
                            :disabled="generatingQR"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition-colors"
                        >
                            {{ generatingQR ? $t('Generating QR...') : $t('Enable 2FA') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Disable 2FA Modal -->
            <div v-if="showDisable2FAModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full p-6 space-y-4">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ $t('Disable Two-Factor Authentication') }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ $t('To disable two-factor authentication, please enter your password to confirm.') }}
                    </p>

                    <FormField
                        name="confirm_password"
                        :label="$t('Password')"
                        :required="true"
                        :error="disable2FAError"
                    >
                        <FormInput
                            v-model="disable2FAPassword"
                            name="confirm_password"
                            type="password"
                            @keyup.enter="disable2FA"
                        />
                    </FormField>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button
                            @click="closeDisableModal"
                            class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                        >
                            {{ $t('Cancel') }}
                        </button>
                        <button
                            @click="disable2FA"
                            :disabled="disabling2FA || !disable2FAPassword"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 transition-colors"
                        >
                            {{ disabling2FA ? $t('Disabling...') : $t('Disable') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recovery Codes Modal -->
            <div v-if="showRecoveryCodesModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-lg w-full p-6 space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ $t('Two-Factor Recovery Codes') }}
                        </h3>
                        <button @click="showRecoveryCodesModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg text-amber-800 dark:text-amber-200 text-sm">
                        <p class="font-bold flex items-center mb-1">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            {{ $t('Immediate Action Required!') }}
                        </p>
                        <p>{{ $t('Store these recovery codes in a secure password manager. They can be used to access your account if your two-factor authentication device is lost.') }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 font-mono text-sm">
                        <div v-for="code in recoveryCodes" :key="code" class="flex items-center text-gray-700 dark:text-gray-300">
                            <span class="mr-2 text-indigo-500">•</span>
                            {{ code }}
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button
                            @click="showRecoveryCodesModal = false"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium"
                        >
                            {{ $t('I have saved these codes') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, getCurrentInstance, computed } from 'vue';
import axios from 'axios';
import { useDialog } from '../../composables/useDialog';
import { useTranslation } from '../../composables/useTranslation';
import { useAuthStore } from '../../stores/auth';
import { useValidation } from '../../composables/useValidation';
import FormField from '../../components/forms/FormField.vue';
import FormInput from '../../components/forms/FormInput.vue';

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;
const dialog = useDialog();
const authStore = useAuthStore();

const loading = ref(false);
const saving = ref(false);
const savingPassword = ref(false);

// Validation for profile form
const profileValidation = useValidation({
    showToast: false, // Don't show toast for individual field validation
});

// Validation for password form
const passwordValidation = useValidation({
    showToast: false,
});

const validationErrors = computed(() => ({
    ...profileValidation.errors.value,
    ...passwordValidation.errors.value,
}));

const profileForm = ref({
    name: '',
    email: '',
    google2fa_enabled: false,
});

const passwordForm = ref({
    current_password: '',
    password: '',
    password_confirmation: '',
});

// 2FA State
const show2FASetup = ref(false);
const showDisable2FAModal = ref(false);
const generatingQR = ref(false);
const enabling2FA = ref(false);
const disabling2FA = ref(false);
const qrCode = ref('');
const twoFactorSecret = ref('');
const twoFactorCode = ref('');
const twoFactorError = ref('');
const disable2FAPassword = ref('');
const disable2FAError = ref('');
const showRecoveryCodesModal = ref(false);
const recoveryCodes = ref<string[]>([]);

const start2FASetup = async () => {
    generatingQR.value = true;
    try {
        const response = await axios.get('/api/v1/profile/google2fa/qr-code');
        qrCode.value = response.data.data.qr_code;
        twoFactorSecret.value = response.data.data.secret;
        show2FASetup.value = true;
    } catch (error) {
        dialog.error($t('Failed to generate 2FA QR code'));
    } finally {
        generatingQR.value = false;
    }
};

const cancel2FASetup = () => {
    show2FASetup.value = false;
    twoFactorCode.value = '';
    twoFactorError.value = '';
};

const enable2FA = async () => {
    if (twoFactorCode.value.length !== 6) return;
    
    enabling2FA.value = true;
    twoFactorError.value = '';
    try {
        const response = await axios.post('/api/v1/profile/google2fa/enable', {
            one_time_password: twoFactorCode.value
        });
        dialog.success($t('Two-factor authentication enabled successfully'));
        profileForm.value.google2fa_enabled = true;
        show2FASetup.value = false;
        twoFactorCode.value = '';
        
        // Show recovery codes
        if (response.data.data?.recovery_codes) {
            recoveryCodes.value = response.data.data.recovery_codes;
            showRecoveryCodesModal.value = true;
        }
    } catch (error: any) {
        twoFactorError.value = error.response?.data?.message || $t('Invalid verification code');
    } finally {
        enabling2FA.value = false;
    }
};

const closeDisableModal = () => {
    showDisable2FAModal.value = false;
    disable2FAPassword.value = '';
    disable2FAError.value = '';
};

const disable2FA = async () => {
    if (!disable2FAPassword.value) return;

    disabling2FA.value = true;
    disable2FAError.value = '';
    try {
        await axios.post('/api/v1/profile/google2fa/disable', {
            password: disable2FAPassword.value
        });
        dialog.success($t('Two-factor authentication disabled successfully'));
        profileForm.value.google2fa_enabled = false;
        closeDisableModal();
    } catch (error: any) {
        disable2FAError.value = error.response?.data?.message || $t('Invalid password');
    } finally {
        disabling2FA.value = false;
    }
};

const fetchRecoveryCodes = async () => {
    try {
        const response = await axios.get('/api/v1/profile/google2fa/recovery-codes');
        recoveryCodes.value = response.data.data.recovery_codes;
        showRecoveryCodesModal.value = true;
    } catch (error) {
        dialog.error($t('Failed to fetch recovery codes'));
    }
};

// Validation rules
const nameRules = ['required', { type: 'min' as const, value: 2 }];
const passwordRules = ['required', { type: 'min' as const, value: 8, message: 'Password must be at least 8 characters' }];
const passwordConfirmationRules = ['required', { type: 'match' as const, field: 'password', message: 'Passwords do not match' }];

const loadProfile = async () => {
    loading.value = true;
    profileValidation.clearAllErrors();
    passwordValidation.clearAllErrors();
    try {
        const response = await axios.get('/api/v1/profile');
        if (response.data?.data) {
            profileForm.value = {
                name: response.data.data.name || '',
                email: response.data.data.email || '',
                google2fa_enabled: !!response.data.data.google2fa_enabled,
            };
            // Update auth store
            if (authStore.user) {
                authStore.user.name = response.data.data.name;
                authStore.user.email = response.data.data.email;
            }
        }
    } catch (error: any) {
        console.error('Error loading profile:', error);
        dialog.error($t('Failed to load profile'));
    } finally {
        loading.value = false;
    }
};

const handleProfileSubmit = async () => {
    // Validate form
    const validationRules: Record<string, any[]> = {
        name: ['required', { type: 'min' as const, value: 2 }],
    };

    const results = await profileValidation.validateForm(profileForm.value, validationRules);
    const hasValidationErrors = results.some(r => !r.valid);

    if (hasValidationErrors) {
        return;
    }

    saving.value = true;
    profileValidation.clearAllErrors();

    try {
        const response = await axios.put('/api/v1/profile', {
            name: profileForm.value.name,
            // Email is not sent - it cannot be changed from profile page
        });

        dialog.success($t('Profile updated successfully'));

        // Update auth store
        if (response.data?.data && authStore.user) {
            authStore.user.name = response.data.data.name;
            authStore.user.email = response.data.data.email;
        }
    } catch (error: any) {
        console.error('Error updating profile:', error);
        if (error.response?.data?.errors) {
            // Set errors from API response
            profileValidation.setErrors(error.response.data.errors);
        } else {
            const message = error.response?.data?.message || $t('Failed to update profile');
            dialog.error(message);
        }
    } finally {
        saving.value = false;
    }
};

const handlePasswordSubmit = async () => {
    // Validate form
    const validationRules: Record<string, any[]> = {
        current_password: ['required'],
        password: ['required', { type: 'min' as const, value: 8, message: 'Password must be at least 8 characters' }],
        password_confirmation: ['required', { type: 'match' as const, field: 'password', message: 'Passwords do not match' }],
    };

    // Need to pass full form data for match validation
    const formData = {
        current_password: passwordForm.value.current_password,
        password: passwordForm.value.password,
        password_confirmation: passwordForm.value.password_confirmation,
    };

    const results = await passwordValidation.validateForm(formData, validationRules);
    const hasValidationErrors = results.some(r => !r.valid);

    if (hasValidationErrors) {
        return;
    }

    savingPassword.value = true;
    passwordValidation.clearAllErrors();

    try {
        await axios.put('/api/v1/profile', {
            current_password: passwordForm.value.current_password,
            password: passwordForm.value.password,
            password_confirmation: passwordForm.value.password_confirmation,
        });

        dialog.success($t('Password updated successfully'));

        // Reset password form
        passwordForm.value = {
            current_password: '',
            password: '',
            password_confirmation: '',
        };
    } catch (error: any) {
        console.error('Error updating password:', error);
        if (error.response?.data?.errors) {
            // Set errors from API response
            passwordValidation.setErrors(error.response.data.errors);
        } else {
            const message = error.response?.data?.message || $t('Failed to update password');
            dialog.error(message);
        }
    } finally {
        savingPassword.value = false;
    }
};

onMounted(() => {
    loadProfile();
});
</script>
