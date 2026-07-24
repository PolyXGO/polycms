<template>
  <div class="space-y-6">
    <!-- Google Card -->
    <div class="bg-admin-theme-surface border border-admin-theme-border shadow rounded-xl p-6 transition-all duration-200">
      <div class="flex justify-between items-center pb-4 border-b border-admin-theme-border mb-5">
        <div class="flex items-center gap-3">
          <div class="p-2.5 bg-red-500/10 rounded-lg text-red-500">
            <!-- Google Icon -->
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12.24 10.285V13.4h6.887c-.275 1.565-1.88 4.604-6.887 4.604-4.33 0-7.866-3.577-7.866-8s3.536-8 7.866-8c2.46 0 4.105 1.025 5.047 1.926l2.427-2.334C17.955 2.192 15.34 1 12.24 1 6.033 1 1 6.033 1 12.24s5.033 11.24 11.24 11.24c6.478 0 10.793-4.537 10.793-10.986 0-.746-.08-1.32-.176-1.886H12.24z"/>
            </svg>
          </div>
          <div>
            <h3 class="text-base font-bold text-admin-theme-text flex items-center gap-1.5">
              {{ t('Google Authentication') }}
              <button 
                type="button"
                @click="openHelp('google')"
                class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-admin-theme-primary/10 text-admin-theme-primary hover:bg-admin-theme-primary/20 transition-colors cursor-pointer"
                title="Google Setup Guide"
              >
                <span class="text-xs font-bold font-mono">?</span>
              </button>
            </h3>
            <p class="text-xs text-admin-theme-text-muted mt-0.5">
              {{ t('Allow users to login and register with Google Accounts.') }}
            </p>
          </div>
        </div>
        <FormToggle
          name="external_auth_google_enabled"
          :modelValue="isGoogleEnabled"
          @update:modelValue="updateGoogleToggle"
        />
      </div>

      <div class="space-y-4" :class="{'opacity-50 pointer-events-none': !isGoogleEnabled}">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-admin-theme-text-secondary mb-1.5">
            {{ t('Google Client ID') }}
          </label>
          <input
            type="text"
            v-model="localSettings.external_auth_google_client_id"
            placeholder="e.g. 123456789-abc123xyz.apps.googleusercontent.com"
            class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-2 focus:ring-admin-theme-primary focus:border-admin-theme-primary text-sm font-medium"
          />
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-admin-theme-text-secondary mb-1.5">
            {{ t('Google Client Secret') }}
          </label>
          <input
            type="password"
            v-model="localSettings.external_auth_google_client_secret"
            placeholder="••••••••••••••••••••••••"
            class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-2 focus:ring-admin-theme-primary focus:border-admin-theme-primary text-sm font-medium"
          />
        </div>
      </div>
    </div>

    <!-- Facebook Card -->
    <div class="bg-admin-theme-surface border border-admin-theme-border shadow rounded-xl p-6 transition-all duration-200">
      <div class="flex justify-between items-center pb-4 border-b border-admin-theme-border mb-5">
        <div class="flex items-center gap-3">
          <div class="p-2.5 bg-blue-600/10 rounded-lg text-blue-600">
            <!-- Facebook Icon -->
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
              <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
          </div>
          <div>
            <h3 class="text-base font-bold text-admin-theme-text flex items-center gap-1.5">
              {{ t('Facebook Authentication') }}
              <button 
                type="button"
                @click="openHelp('facebook')"
                class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-admin-theme-primary/10 text-admin-theme-primary hover:bg-admin-theme-primary/20 transition-colors cursor-pointer"
                title="Facebook Setup Guide"
              >
                <span class="text-xs font-bold font-mono">?</span>
              </button>
            </h3>
            <p class="text-xs text-admin-theme-text-muted mt-0.5">
              {{ t('Allow users to login and register with Facebook Accounts.') }}
            </p>
          </div>
        </div>
        <FormToggle
          name="external_auth_facebook_enabled"
          :modelValue="isFacebookEnabled"
          @update:modelValue="updateFacebookToggle"
        />
      </div>

      <div class="space-y-4" :class="{'opacity-50 pointer-events-none': !isFacebookEnabled}">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-admin-theme-text-secondary mb-1.5">
            {{ t('Facebook App ID') }}
          </label>
          <input
            type="text"
            v-model="localSettings.external_auth_facebook_client_id"
            placeholder="e.g. 1098765432109876"
            class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-2 focus:ring-admin-theme-primary focus:border-admin-theme-primary text-sm font-medium"
          />
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-admin-theme-text-secondary mb-1.5">
            {{ t('Facebook App Secret') }}
          </label>
          <input
            type="password"
            v-model="localSettings.external_auth_facebook_client_secret"
            placeholder="••••••••••••••••••••••••"
            class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-2 focus:ring-admin-theme-primary focus:border-admin-theme-primary text-sm font-medium"
          />
        </div>
      </div>
    </div>

    <!-- GitHub Card -->
    <div class="bg-admin-theme-surface border border-admin-theme-border shadow rounded-xl p-6 transition-all duration-200">
      <div class="flex justify-between items-center pb-4 border-b border-admin-theme-border mb-5">
        <div class="flex items-center gap-3">
          <div class="p-2.5 bg-gray-900/10 dark:bg-gray-800 rounded-lg text-gray-800 dark:text-gray-200">
            <!-- GitHub Icon -->
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
              <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.477 2 12c0 4.42 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.464-1.11-1.464-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.831.092-.646.35-1.086.636-1.336-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.579.688.481C19.138 20.162 22 16.418 22 12c0-5.523-4.477-10-10-10z"/>
            </svg>
          </div>
          <div>
            <h3 class="text-base font-bold text-admin-theme-text flex items-center gap-1.5">
              {{ t('GitHub Authentication') }}
              <button 
                type="button"
                @click="openHelp('github')"
                class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-admin-theme-primary/10 text-admin-theme-primary hover:bg-admin-theme-primary/20 transition-colors cursor-pointer"
                title="GitHub Setup Guide"
              >
                <span class="text-xs font-bold font-mono">?</span>
              </button>
            </h3>
            <p class="text-xs text-admin-theme-text-muted mt-0.5">
              {{ t('Allow users to login and register with GitHub Accounts.') }}
            </p>
          </div>
        </div>
        <FormToggle
          name="external_auth_github_enabled"
          :modelValue="isGithubEnabled"
          @update:modelValue="updateGithubToggle"
        />
      </div>

      <div class="space-y-4" :class="{'opacity-50 pointer-events-none': !isGithubEnabled}">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-admin-theme-text-secondary mb-1.5">
            {{ t('GitHub Client ID') }}
          </label>
          <input
            type="text"
            v-model="localSettings.external_auth_github_client_id"
            placeholder="e.g. Ov23gY..."
            class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-2 focus:ring-admin-theme-primary focus:border-admin-theme-primary text-sm font-medium"
          />
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-admin-theme-text-secondary mb-1.5">
            {{ t('GitHub Client Secret') }}
          </label>
          <input
            type="password"
            v-model="localSettings.external_auth_github_client_secret"
            placeholder="••••••••••••••••••••••••"
            class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-2 focus:ring-admin-theme-primary focus:border-admin-theme-primary text-sm font-medium"
          />
        </div>
      </div>
    </div>

    <!-- Guideline / Help Modal -->
    <div v-if="helpProvider" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity duration-300">
      <div class="relative w-full max-w-2xl bg-admin-theme-surface border border-admin-theme-border shadow-2xl rounded-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <!-- Header -->
        <div class="flex justify-between items-center p-6 border-b border-admin-theme-border bg-admin-theme-base/20">
          <h3 class="text-lg font-extrabold text-admin-theme-text flex items-center gap-2">
            <span class="capitalize">{{ helpProvider }}</span> - Hướng dẫn cấu hình API
          </h3>
          <button 
            type="button" 
            @click="closeHelp"
            class="text-admin-theme-text-muted hover:text-admin-theme-text p-1.5 hover:bg-admin-theme-border/50 rounded-lg transition-all cursor-pointer"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Body -->
        <div class="p-6 overflow-y-auto max-h-[60vh] space-y-5 text-sm text-admin-theme-text-secondary leading-relaxed">
          <!-- Common Step: Callback URL -->
          <div class="p-4 bg-admin-theme-primary/5 rounded-xl border border-admin-theme-primary/10">
            <h4 class="font-bold text-admin-theme-primary mb-1.5">Đường dẫn phản hồi xác thực (Redirect URI)</h4>
            <p class="text-xs text-admin-theme-text-muted mb-3">Copy đường dẫn này để cấu hình ứng dụng phía bên thứ ba:</p>
            <div class="flex items-center gap-2">
              <input 
                type="text" 
                readonly 
                :value="getRedirectUri(helpProvider)"
                class="flex-1 px-3 py-1.5 text-xs font-mono border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text select-all"
              />
              <button 
                type="button"
                @click="copyText(getRedirectUri(helpProvider))"
                class="px-3.5 py-1.5 bg-admin-theme-primary text-admin-theme-primary-content text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-admin-theme-primary-hover transition-colors cursor-pointer"
              >
                {{ copySuccess ? 'Copied!' : 'Copy' }}
              </button>
            </div>
          </div>

          <!-- Provider Specific Guides -->
          <div v-if="helpProvider === 'google'" class="space-y-3">
            <h4 class="font-extrabold text-admin-theme-text">Các bước thiết lập trên Google Cloud Console:</h4>
            <ol class="list-decimal pl-5 space-y-2 text-xs">
              <li>Truy cập <a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener" class="text-admin-theme-primary hover:underline font-bold">Google Cloud Console</a>.</li>
              <li>Chọn hoặc tạo mới một Dự án (Project) của bạn.</li>
              <li>Đi đến mục <strong>OAuth consent screen</strong> để thiết lập màn hình xin quyền (chọn User Type là External, nhập Tên App và Email hỗ trợ).</li>
              <li>Đi đến mục <strong>Credentials</strong> -> Click <strong>Create Credentials</strong> -> Chọn <strong>OAuth client ID</strong>.</li>
              <li>Chọn Application type là <strong>Web application</strong>.</li>
              <li>Tại mục <strong>Authorized redirect URIs</strong> -> Thêm đường dẫn Redirect URI đã copy ở trên.</li>
              <li>Nhấn <strong>Create</strong> để tạo. Lưu lại Client ID và Client Secret vào các ô cấu hình.</li>
            </ol>
          </div>

          <div v-if="helpProvider === 'facebook'" class="space-y-3">
            <h4 class="font-extrabold text-admin-theme-text">Các bước thiết lập trên Facebook Developers:</h4>
            <ol class="list-decimal pl-5 space-y-2 text-xs">
              <li>Truy cập <a href="https://developers.facebook.com" target="_blank" rel="noopener" class="text-admin-theme-primary hover:underline font-bold">Facebook Developers</a> và Đăng nhập.</li>
              <li>Click <strong>My Apps</strong> -> Click <strong>Create App</strong> (chọn loại ứng dụng phù hợp).</li>
              <li>Trong trang quản lý App, nhấp vào <strong>Set up</strong> tại ô sản phẩm <strong>Facebook Login</strong>.</li>
              <li>Tại thanh bên trái, chọn <strong>Facebook Login</strong> -> <strong>Settings</strong>.</li>
              <li>Tại mục <strong>Valid OAuth Redirect URIs</strong> -> Điền đường dẫn Redirect URI đã copy ở trên.</li>
              <li>Đi đến <strong>Settings</strong> -> <strong>Basic</strong> ở thanh bên trái để lấy App ID và App Secret dán vào ô cấu hình.</li>
            </ol>
          </div>

          <div v-if="helpProvider === 'github'" class="space-y-3">
            <h4 class="font-extrabold text-admin-theme-text">Các bước thiết lập trên GitHub:</h4>
            <ol class="list-decimal pl-5 space-y-2 text-xs">
              <li>Truy cập mục cài đặt nhà phát triển <a href="https://github.com/settings/developers" target="_blank" rel="noopener" class="text-admin-theme-primary hover:underline font-bold">GitHub Developer Settings</a>.</li>
              <li>Nhấp chọn <strong>OAuth Apps</strong> -> Click <strong>Register a new application</strong>.</li>
              <li>Nhập Application name, Homepage URL (URL trang chủ của bạn).</li>
              <li>Tại mục <strong>Authorization callback URL</strong> -> Điền đường dẫn Redirect URI đã copy ở trên.</li>
              <li>Click <strong>Register application</strong>.</li>
              <li>Copy Client ID. Click <strong>Generate a new client secret</strong> để tạo Client Secret mới và dán vào cấu hình.</li>
            </ol>
          </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end p-6 border-t border-admin-theme-border bg-admin-theme-base/10">
          <button 
            type="button" 
            @click="closeHelp"
            class="px-5 py-2 border border-admin-theme-border text-admin-theme-text rounded-lg hover:bg-admin-theme-border/50 text-xs font-bold uppercase tracking-wider transition-colors cursor-pointer"
          >
            Đóng hướng dẫn
          </button>
        </div>
      </div>
    </div>

    <!-- Footer Action -->
    <div class="flex justify-end pt-4 border-t border-admin-theme-border">
      <button
        type="button"
        @click="$emit('save')"
        :disabled="saving"
        class="px-6 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer"
      >
        <span v-if="saving" class="flex items-center">
          <div class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
          {{ t('Saving...') }}
        </span>
        <span v-else>{{ t('Save Settings') }}</span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { useTranslation } from '../../../composables/useTranslation';
import FormToggle from '../../../components/forms/FormToggle.vue';

interface Setting {
  key: string;
  value: any;
  type: string;
  label: string;
  description: string;
}

interface Props {
  settings: Record<string, Setting>;
  saving: boolean;
  group: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
  (e: 'update', group: string, key: string, value: any): void;
  (e: 'save'): void;
}>();

const { t } = useTranslation();

// Helper Modal ref state
const helpProvider = ref<string | null>(null);
const copySuccess = ref(false);

const openHelp = (provider: string) => {
  helpProvider.value = provider;
  copySuccess.value = false;
};

const closeHelp = () => {
  helpProvider.value = null;
};

const getRedirectUri = (provider: string | null) => {
  if (!provider) return '';
  return window.location.origin + '/external-auth/callback/' + provider;
};

const copyText = async (text: string) => {
  try {
    await navigator.clipboard.writeText(text);
    copySuccess.value = true;
    setTimeout(() => {
      copySuccess.value = false;
    }, 2000);
  } catch (err) {
    console.error('Failed to copy to clipboard', err);
  }
};

// Local Settings reactive store
const localSettings = ref<Record<string, any>>({
  external_auth_google_enabled: '0',
  external_auth_google_client_id: '',
  external_auth_google_client_secret: '',
  external_auth_facebook_enabled: '0',
  external_auth_facebook_client_id: '',
  external_auth_facebook_client_secret: '',
  external_auth_github_enabled: '0',
  external_auth_github_client_id: '',
  external_auth_github_client_secret: '',
});

// Initialize local settings from props
watch(() => props.settings, (newSettings) => {
  if (newSettings) {
    Object.keys(newSettings).forEach(key => {
      if (newSettings[key] !== undefined) {
        localSettings.value[key] = newSettings[key].value;
      }
    });
  }
}, { immediate: true, deep: true });

// Propagate updates to parent
watch(localSettings, (newVal) => {
  Object.keys(newVal).forEach(key => {
    emit('update', props.group, key, newVal[key]);
  });
}, { deep: true });

const isGoogleEnabled = computed(() => ['true', '1', true, 1].includes(localSettings.value.external_auth_google_enabled));
const isFacebookEnabled = computed(() => ['true', '1', true, 1].includes(localSettings.value.external_auth_facebook_enabled));
const isGithubEnabled = computed(() => ['true', '1', true, 1].includes(localSettings.value.external_auth_github_enabled));

const updateGoogleToggle = (val: boolean) => {
  localSettings.value.external_auth_google_enabled = val ? '1' : '0';
};

const updateFacebookToggle = (val: boolean) => {
  localSettings.value.external_auth_facebook_enabled = val ? '1' : '0';
};

const updateGithubToggle = (val: boolean) => {
  localSettings.value.external_auth_github_enabled = val ? '1' : '0';
};
</script>
