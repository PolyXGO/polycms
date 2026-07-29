<template>
 <div v-if="form" class="space-y-4">
 <div class="space-y-1">
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ $t('Product Type *') }}</label>
 <select v-model="form.type" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary" required>
 <option value="product">{{ $t('Physical Product') }}</option>
 <option value="variable">{{ $t('Variable Product') }}</option>
 <option value="service">{{ $t('Service / License') }}</option>
 <option value="digital">{{ $t('Digital Product') }}</option>
 </select>
 </div>

  <div class="space-y-1">
  <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ $t('Price *') }}</label>
  <input v-model.number="form.price" type="number" min="0" step="0.01" :disabled="!isDefaultLanguage" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-admin-theme-primary focus:border-admin-theme-primary disabled:bg-gray-100 disabled:text-gray-400 dark:disabled:bg-gray-800" required />
  </div>

  <div class="space-y-1">
  <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ $t('Sale Price') }}</label>
  <input v-model.number="form.sale_price" type="number" min="0" step="0.01" :disabled="!isDefaultLanguage" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-admin-theme-primary focus:border-admin-theme-primary disabled:bg-gray-100 disabled:text-gray-400 dark:disabled:bg-gray-800" />
  </div>

  <!-- Warning alert for non-default locale -->
  <div v-if="!isDefaultLanguage" class="p-3 bg-blue-50 border border-blue-200 text-blue-800 dark:bg-blue-950/20 dark:border-blue-900 dark:text-blue-400 rounded-lg text-xs flex items-start gap-2">
    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <span>
      {{ $t('Prices and stock/inventory are managed in the primary language version and automatically converted or synchronized.') || 'Giá bán và kho hàng được quản lý ở phiên bản ngôn ngữ gốc và tự động chuyển đổi/đồng bộ.' }}
    </span>
  </div>

 <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
 <div class="space-y-1">
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ $t('Stock Quantity') }}</label>
 <input v-model.number="form.stock_quantity" type="number" min="0" :disabled="!isDefaultLanguage" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-admin-theme-primary focus:border-admin-theme-primary disabled:bg-gray-100 disabled:text-gray-400 dark:disabled:bg-gray-800" />
 </div>
 <div class="space-y-1">
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ $t('Max per order (Anti-scalping)') }}</label>
 <input v-model.number="form.max_per_order" type="number" min="1" :disabled="!isDefaultLanguage" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-admin-theme-primary focus:border-admin-theme-primary disabled:bg-gray-100 disabled:text-gray-400 dark:disabled:bg-gray-800" :placeholder="$t('Unlimited')" />
 </div>
 </div>

 <div class="space-y-1">
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ $t('Stock Status') }}</label>
 <select v-model="form.stock_status" :disabled="!isDefaultLanguage" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary disabled:bg-gray-100 disabled:text-gray-400 dark:disabled:bg-gray-800">
 <option value="hide">{{ $t('Default (Hide)') }}</option>
 <option value="in_stock">{{ $t('In Stock') }}</option>
 <option value="out_of_stock">{{ $t('Out of Stock') }}</option>
 <option value="disabled_add_to_cart">{{ $t('Sales Paused (Disabled Add to Cart)') }}</option>
 <option value="on_backorder">{{ $t('On Backorder') }}</option>
 </select>
 </div>

 <div class="flex flex-wrap gap-4 items-center mt-2">
 <label class="inline-flex items-center gap-2 text-sm text-admin-theme-text-secondary cursor-pointer">
 <input v-model="form.manage_stock" type="checkbox" :disabled="!isDefaultLanguage" class="w-4 h-4 text-admin-theme-primary rounded focus:ring-admin-theme-primary border-admin-theme-input-border transition-all duration-200" />
 <span>{{ $t('Enable stock management') }}</span>
 </label>
 <label class="inline-flex items-center gap-2 text-sm text-admin-theme-text-secondary cursor-pointer">
 <input v-model="form.featured" type="checkbox" class="w-4 h-4 text-admin-theme-primary rounded focus:ring-admin-theme-primary border-admin-theme-input-border transition-all duration-200" />
 <span>{{ $t('Mark as featured product') }}</span>
 </label>
 </div>

 <div class="pt-4 border-t border-admin-theme-border">
 <p class="text-sm font-semibold text-gray-800 mb-3">{{ $t('Refund Policy') }}</p>
 <label class="inline-flex items-center gap-2 text-sm text-admin-theme-text-secondary cursor-pointer">
 <input
 v-model="form.allow_refund"
 type="checkbox"
 class="w-4 h-4 text-admin-theme-primary rounded focus:ring-admin-theme-primary border-admin-theme-input-border transition-all duration-200"
 />
 <span>{{ $t('Allow customer refund requests') }}</span>
 </label>

 <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
 <div class="space-y-1">
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
 {{ $t('Refund window (days)') }}
 </label>
 <input
 v-model.number="form.refund_window_days"
 type="number"
 min="0"
 max="3650"
 :disabled="!form.allow_refund"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:text-gray-400 text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-admin-theme-primary focus:border-admin-theme-primary"
 :placeholder="$t('Leave empty to use global policy')"
 />
 </div>
 </div>

 <div class="space-y-1 mt-3">
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
 {{ $t('Refund policy note') }}
 </label>
 <textarea
 v-model="form.refund_policy_note"
 rows="3"
 :disabled="!form.allow_refund"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:text-gray-400 text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-admin-theme-primary focus:border-admin-theme-primary"
 :placeholder="$t('Shown to customers in account orders/subscriptions')"
 />
 <p class="text-xs text-admin-theme-text-muted">
 {{ $t('If refund is disabled, customers will see that this product does not support refunds.') }}
 </p>
 </div>
  </div>

  <!-- Purchase CTA Summary Section -->
  <div class="pt-4 border-t border-admin-theme-border space-y-2">
    <label class="block text-sm font-semibold text-admin-theme-text">{{ $t('Purchase CTA Summary') || 'Lời Kêu Gọi Mua Hàng (Summary CTA)' }}</label>
    <p class="text-xs text-admin-theme-text-muted mt-0.5">
      {{ $t('A brief call-to-action text displayed right after the price/title and before the purchase buttons to increase conversion rate.') || 'Mô tả ngắn gọn hoặc thông điệp kêu gọi hành động hiển thị ngay dưới giá/tiêu đề sản phẩm để kích thích mua hàng.' }}
    </p>
    <textarea
      v-model="form.settings.purchase_cta_summary"
      rows="3"
      class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-admin-theme-primary focus:border-admin-theme-primary text-sm"
      :placeholder="$t('e.g. Get lifetime updates, premium support and full source code access with a one-time purchase.') || 'Ví dụ: Sở hữu trọn đời sản phẩm, cập nhật miễn phí và hỗ trợ cao cấp chỉ với một lần thanh toán duy nhất.'"
    ></textarea>
  </div>

  <!-- Unified Purchase Buttons & Channels Section -->
  <div class="pt-4 border-t border-admin-theme-border">
    <div class="mb-3">
      <p class="text-sm font-semibold text-admin-theme-text">{{ $t('Purchase Buttons & Channels') || 'Kênh Mua Hàng & Nút Bấm' }}</p>
      <p class="text-xs text-admin-theme-text-muted mt-0.5">
        {{ $t('Manage purchase buttons displayed on the product page. You can sort them by reordering.') || 'Quản lý thứ tự hiển thị các nút mua hàng trên trang chi tiết sản phẩm. Bật/tắt hoặc sắp xếp vị trí tuỳ ý.' }}
      </p>
    </div>

    <!-- Empty state -->
    <div v-if="!form.settings?.purchase_options?.buttons || form.settings.purchase_options.buttons.length === 0" class="text-center py-6 border border-dashed border-admin-theme-border rounded-lg text-admin-theme-text-muted text-sm">
      {{ $t('No purchase buttons added yet.') || 'Chưa cấu hình nút mua hàng nào.' }}
    </div>

    <!-- Buttons Repeater List -->
    <div v-else class="space-y-4">
      <div
        v-for="(btn, index) in form.settings.purchase_options.buttons"
        :key="btn.id"
        class="p-4 rounded-xl border border-admin-theme-border bg-admin-theme-base/50 dark:bg-admin-theme-surface/30 relative space-y-3"
      >
        <!-- Header: Order & Reorder & Remove -->
        <div class="flex items-center justify-between gap-3 pb-2 border-b border-admin-theme-border/50">
          <div class="flex items-center gap-1.5">
            <span class="text-xs font-semibold text-admin-theme-text-secondary bg-admin-theme-border/50 px-2 py-0.5 rounded">
              #{{ index + 1 }}
            </span>
            <span
              class="text-xs font-semibold px-2 py-0.5 rounded"
              :class="btn.type === 'direct' ? 'bg-green-100 text-green-800 dark:bg-green-950/30 dark:text-green-400' : 'bg-blue-100 text-blue-800 dark:bg-blue-950/30 dark:text-blue-400'"
            >
              {{ btn.type === 'direct' ? ($t('Direct') || 'Mua tại site') : ($t('External') || 'Link sàn ngoài') }}
            </span>
            <span class="text-sm font-medium text-admin-theme-text ml-1">
              {{ getPlatformLabel(btn) }}
            </span>
          </div>

          <div class="flex items-center gap-1.5">
            <!-- Sort Up -->
            <button
              type="button"
              :disabled="index === 0"
              @click="moveButton(index, -1)"
              class="p-1 rounded text-admin-theme-text-secondary hover:bg-admin-theme-border disabled:opacity-30 disabled:hover:bg-transparent"
              title="Move Up"
            >
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
              </svg>
            </button>
            <!-- Sort Down -->
            <button
              type="button"
              :disabled="index === form.settings.purchase_options.buttons.length - 1"
              @click="moveButton(index, 1)"
              class="p-1 rounded text-admin-theme-text-secondary hover:bg-admin-theme-border disabled:opacity-30 disabled:hover:bg-transparent"
              title="Move Down"
            >
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <!-- Delete (Only allowed for external buttons) -->
            <button
              v-if="btn.type !== 'direct'"
              type="button"
              @click="removeButton(index)"
              class="p-1 rounded text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 ml-1"
              title="Remove"
            >
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Form fields layout -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <!-- Button Label Input -->
          <div class="space-y-1">
            <label class="block text-xs font-medium text-admin-theme-text-secondary">{{ $t('Button Label') || 'Nhãn nút bấm' }}</label>
            <input
              v-model="btn.label"
              type="text"
              class="w-full px-3 py-1.5 text-sm border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-admin-theme-primary focus:border-admin-theme-primary"
              :placeholder="btn.type === 'direct' ? (isVi ? 'Ví dụ: Thêm vào giỏ hàng' : 'e.g. Add to Cart') : (isVi ? 'Ví dụ: Mua ngay' : 'e.g. Buy Now')"
              required
            />
          </div>

          <!-- Price (For external type) or Helper Text (For direct type) -->
          <div v-if="btn.type === 'external'" class="space-y-1">
            <label class="block text-xs font-medium text-admin-theme-text-secondary">{{ $t('Price (optional)') || 'Giá bán (nếu có)' }}</label>
            <input
              v-model.number="btn.price"
              type="number"
              min="0"
              step="0.01"
              :disabled="!isDefaultLanguage"
              class="w-full px-3 py-1.5 text-sm border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-admin-theme-primary focus:border-admin-theme-primary disabled:bg-gray-100 disabled:text-gray-400 dark:disabled:bg-gray-800"
              :placeholder="$t('Optional') || 'Để trống nếu cùng giá'"
            />
          </div>
          <div v-else class="flex items-center text-xs text-admin-theme-text-muted bg-admin-theme-border/20 px-3 py-1.5 rounded-lg border border-admin-theme-border/40">
            <span>{{ $t('This button automatically initiates standard shopping cart checkout workflow.') || 'Nút này tự động kích hoạt giỏ hàng và thanh toán trực tiếp của website.' }}</span>
          </div>
        </div>

        <!-- URL Input (Only external type) -->
        <div v-if="btn.type === 'external'" class="space-y-1">
          <label class="block text-xs font-medium text-admin-theme-text-secondary">{{ $t('Product URL') || 'Đường dẫn sản phẩm (URL)' }}</label>
          <input
            v-model="btn.url"
            type="url"
            class="w-full px-3 py-1.5 text-sm border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-admin-theme-primary focus:border-admin-theme-primary"
            :placeholder="$t('https://...')"
            required
          />
        </div>

        <!-- Button Style Preset Selection -->
        <div class="space-y-1">
          <label class="block text-xs font-medium text-admin-theme-text-secondary">{{ $t('Button Style Preset') || 'Kiểu giao diện nút (Preset Style)' }}</label>
          <div class="flex items-center gap-2">
            <div class="flex-1 px-3 py-1.5 text-sm border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text flex items-center justify-between min-w-0 gap-3">
              <div class="flex items-center gap-2 min-w-0">
                <span class="truncate font-medium">
                  {{ getSelectedPresetName(btn.preset_uuid) }}
                </span>
                <!-- Mini Preview of the active style -->
                <span
                  v-if="btn.preset_uuid && presets.length > 0"
                  :style="getPresetStyle(btn.preset_uuid)"
                  class="ml-2 shrink-0 shadow-sm"
                >
                  {{ btn.label || 'Preview' }}
                </span>
              </div>
              <button
                v-if="btn.preset_uuid"
                type="button"
                @click="btn.preset_uuid = ''"
                class="text-xs text-red-500 hover:text-red-600 font-semibold ml-2 shrink-0"
                title="Reset to Default"
              >
                {{ $t('Use Default') || 'Dùng Mặc định' }}
              </button>
            </div>
            <button
              type="button"
              @click="openPresetModal(btn)"
              class="px-3 py-1.5 text-xs font-bold rounded-lg border border-admin-theme-primary text-admin-theme-primary hover:bg-admin-theme-primary/5 transition-colors whitespace-nowrap flex items-center gap-1 shrink-0"
            >
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              {{ $t('Select') || 'Chọn' }}
            </button>
          </div>
        </div>

        <!-- Toggle Switch / Checkbox is_active -->
        <div class="flex items-center pt-1">
          <label class="inline-flex items-center gap-2 text-xs text-admin-theme-text-secondary cursor-pointer">
            <input
              v-model="btn.is_active"
              type="checkbox"
              class="w-4 h-4 text-admin-theme-primary rounded focus:ring-admin-theme-primary border-admin-theme-input-border transition-all duration-200"
            />
            <span>{{ $t('Active / Display this button') || 'Kích hoạt / Hiển thị nút này' }}</span>
          </label>
        </div>
      </div>

      <!-- Add External Channel button at the bottom of the list -->
      <div class="flex justify-end pt-2">
        <button
          type="button"
          @click="addPurchaseButton('external')"
          class="inline-flex items-center px-4 py-2 text-xs font-semibold rounded-lg bg-admin-theme-primary text-admin-theme-primary-content hover:bg-admin-theme-primary-hover transition-colors shadow-sm"
        >
          + {{ $t('Add External Channel') || 'Thêm nút Link sàn/ngoài' }}
        </button>
      </div>
    </div>
  </div>
  </div>
</template>

<script setup lang="ts">
import { inject, getCurrentInstance, ref, onMounted, computed, watch } from 'vue';
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t;
import { EditorContextKey } from '../../../../editor/context';
import axios from 'axios';
import { useDialogStore } from '@/admin/stores/dialog';
import PresetLoadModal from '@/admin/components/editor/panels/shared/PresetLoadModal.vue';
import { useTranslation } from '@/admin/composables/useTranslation';

const { t } = useTranslation();

const dialogStore = useDialogStore();
const presets = ref<any[]>([]);

const getSelectedPresetName = (uuid: string) => {
  if (!uuid) return $t?.('Default Theme Style') || 'Mặc định theo Theme';
  const found = presets.value.find(p => p.uuid === uuid);
  return found ? found.name : $t?.('Unknown Preset') || 'Unknown Preset';
};

const getPresetStyle = (uuid: string) => {
  if (!uuid) return {};
  const found = presets.value.find(p => p.uuid === uuid);
  if (!found) return {};
  
  let payload = found.payload;
  if (typeof payload === 'string') {
    try {
      payload = JSON.parse(payload);
    } catch (e) {
      return {};
    }
  }
  
  return {
    backgroundColor: payload.bg_color || '#1e293b',
    color: payload.text_color || '#ffffff',
    borderColor: payload.border_color || payload.bg_color || '#1e293b',
    borderRadius: payload.border_radius || '6px',
    borderWidth: '1px',
    borderStyle: 'solid',
    fontSize: '11px',
    fontWeight: '600',
    padding: '3px 8px',
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    cursor: 'default',
    lineHeight: '1.2'
  };
};

const openPresetModal = (btn: any) => {
  dialogStore.showModal({
    title: $t?.('Select Button Preset') || 'Chọn kiểu giao diện nút',
    size: 'wide',
    closable: true,
    component: PresetLoadModal,
    props: {
      type: 'button_style',
      onLoad: (preset: any) => {
        btn.preset_uuid = preset.uuid;
        dialogStore.success($t?.('Preset selected successfully') || 'Đã áp dụng kiểu giao diện');
      }
    }
  });
};

const activeLanguages = ref<any[]>([]);
const isDefaultLanguage = computed(() => {
  if (activeLanguages.value.length === 0) {
    return !form.value.locale || form.value.locale === 'en';
  }
  const match = activeLanguages.value.find(l => l.code === (form.value.locale || 'en'));
  return match ? match.is_default : true;
});

onMounted(async () => {
  try {
    const res = await axios.get('/api/v1/presets', { params: { type: 'button_style' } });
    if (res.data?.status === 'success') {
      presets.value = res.data.data || [];
    }
  } catch (err) {
    console.error('Failed to load presets:', err);
  }

  try {
    const res = await axios.get('/api/v1/languages');
    if (res.data?.success) {
      activeLanguages.value = res.data.data.filter((l: any) => l.is_active);
    }
  } catch (err) {
    console.error('Failed to load languages:', err);
  }
});

const context = inject(EditorContextKey);
if (!context) {
  throw new Error('ProductPricingPanel must be used within editor context');
}

const form = context.form;

if (!form.value) {
  form.value = {
    name: '',
    slug: '',
    sku: '',
    type: context.type ?? 'product',
    status: 'draft',
    price: 0,
    sale_price: null,
    stock_quantity: 0,
    stock_status: 'hide',
    manage_stock: false,
    featured: false,
    allow_refund: true,
    refund_window_days: null,
    refund_policy_note: '',
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
  } as any;
}

// Safe initialization of settings & purchase_options
if (!form.value.settings) {
  form.value.settings = {};
}
const isVi = computed(() => form.value?.locale === 'vi');
const getDefaultDirectLabel = () => isVi.value ? 'Thêm vào giỏ hàng' : 'Add to Cart';
const getDefaultExternalLabel = () => isVi.value ? 'Mua ngay' : 'Buy Now';

if (!form.value.settings.purchase_options || typeof form.value.settings.purchase_options !== 'object' || Array.isArray(form.value.settings.purchase_options)) {
  form.value.settings.purchase_options = {};
}
if (!Array.isArray(form.value.settings.purchase_options.buttons) || form.value.settings.purchase_options.buttons.length === 0) {
  form.value.settings.purchase_options.buttons = [
    {
      id: 'direct-default',
      type: 'direct',
      platform: 'current_site',
      label: getDefaultDirectLabel(),
      url: '',
      price: null,
      is_active: true
    }
  ];
} else {
  // If first button is direct default and still has legacy Vietnamese text on non-VI product, normalize to Add to Cart
  const firstBtn = form.value.settings.purchase_options.buttons[0];
  if (firstBtn && (firstBtn.id === 'direct-default' || firstBtn.type === 'direct')) {
    if (!isVi.value && firstBtn.label === 'Thêm vào giỏ hàng') {
      firstBtn.label = 'Add to Cart';
    }
  }
}

watch(
  () => form.value?.locale,
  (newLocale, oldLocale) => {
    if (newLocale !== oldLocale && Array.isArray(form.value?.settings?.purchase_options?.buttons)) {
      form.value.settings.purchase_options.buttons.forEach((btn: any) => {
        if (btn.type === 'direct') {
          if (!btn.label || btn.label === 'Thêm vào giỏ hàng' || btn.label === 'Add to Cart') {
            btn.label = newLocale === 'vi' ? 'Thêm vào giỏ hàng' : 'Add to Cart';
          }
        } else if (btn.type === 'external') {
          if (!btn.label || btn.label === 'Mua ngay' || btn.label === 'Buy now' || btn.label === 'Buy Now') {
            btn.label = newLocale === 'vi' ? 'Mua ngay' : 'Buy Now';
          }
        }
      });
    }
  }
);

const addPurchaseButton = (type: 'direct' | 'external') => {
  const buttons = form.value.settings.purchase_options.buttons;
  if (type === 'direct') {
    buttons.push({
      id: `direct-${Date.now()}-${buttons.length + 1}`,
      type: 'direct',
      platform: 'current_site',
      label: getDefaultDirectLabel(),
      url: '',
      price: null,
      is_active: true
    });
  } else {
    buttons.push({
      id: `ext-${Date.now()}-${buttons.length + 1}`,
      type: 'external',
      platform: 'other',
      label: getDefaultExternalLabel(),
      url: '',
      price: null,
      is_active: true
    });
  }
};

const removeButton = (index: number) => {
  form.value.settings.purchase_options.buttons.splice(index, 1);
};

const moveButton = (index: number, direction: -1 | 1) => {
  const targetIndex = index + direction;
  const buttons = form.value.settings.purchase_options.buttons;
  if (targetIndex < 0 || targetIndex >= buttons.length) return;
  const temp = buttons[index];
  buttons[index] = buttons[targetIndex];
  buttons[targetIndex] = temp;
};

const getPlatformLabel = (button: any) => {
  if (button.type === 'direct') {
    return button.label || $t?.('Website') || 'Website';
  }
  return button.label || $t?.('External Link') || 'Link ngoài';
};
</script>
