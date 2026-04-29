<template>
    <div class="settings-hub">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('Settings Hub') }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ t('Configure your site and e-commerce features') }}</p>
        </div>

        <div v-for="category in categories" :key="category.name" class="mb-10">
            <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                {{ t(category.name) }}
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div 
                    v-for="item in category.items" 
                    :key="item.key"
                    class="group relative flex items-start p-5 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-500 transition-all hover:shadow-md cursor-pointer"
                    @click="handleItemClick(item)"
                >
                    <!-- Pin Icon -->
                    <button 
                        @click.stop="togglePin(item)"
                        class="absolute top-3 right-3 text-gray-400 hover:text-indigo-500 transition-colors z-10"
                        :class="{ 'opacity-100 text-indigo-500': isPinned(item.key), 'opacity-0 group-hover:opacity-100': !isPinned(item.key) }"
                        :title="isPinned(item.key) ? t('Unpin from sidebar') : t('Pin to sidebar')"
                    >
                        <component :is="isPinned(item.key) ? BookmarkIconSolid : BookmarkIcon" class="w-5 h-5" />
                    </button>

                    <div class="flex-shrink-0 p-3 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/50 transition-colors">
                        <component :is="item.icon" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <div class="ml-4 pr-6">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400">
                            {{ t(item.label) }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                            {{ t(item.description) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { BookmarkIcon } from '@heroicons/vue/24/outline';
import { BookmarkIcon as BookmarkIconSolid } from '@heroicons/vue/24/solid';
import { 
    Cog6ToothIcon, 
    EnvelopeIcon, 
    LinkIcon, 
    PhotoIcon, 
    CommandLineIcon,
    LanguageIcon,
    GlobeAltIcon,
    ShoppingBagIcon,
    CreditCardIcon,
    ReceiptPercentIcon,
    DocumentTextIcon,
    QueueListIcon,
    BanknotesIcon,
    BookOpenIcon,
    QuestionMarkCircleIcon,
    DocumentDuplicateIcon,
    MagnifyingGlassCircleIcon,
    ShieldCheckIcon,
    PaintBrushIcon,
    ArrowPathIcon,
    InformationCircleIcon
} from '@heroicons/vue/24/outline';
import { useTranslation } from '../../composables/useTranslation';
import { useDialog } from '../../composables/useDialog';

const { t } = useTranslation();
const dialog = useDialog();
const router = useRouter();

const activeModules = (window as any).polycmsActiveModules || [];

const pinnedSettings = ref<any[]>([]);

const handleItemClick = async (item: any) => {
    if (item.action) {
        await item.action(router);
    } else if (item.route) {
        router.push(item.route);
    }
};

const loadPinnedSettings = async () => {
    try {
        const response = await axios.get('/api/v1/settings?group=admin_ui');
        const rawValue = response.data?.data?.admin_ui?.admin_pinned_settings?.value;
        if (typeof rawValue === 'string') {
            pinnedSettings.value = JSON.parse(rawValue);
        } else {
            pinnedSettings.value = Array.isArray(rawValue) ? rawValue : [];
        }
    } catch (error) {
        console.error('Failed to load pinned settings:', error);
        pinnedSettings.value = [];
    }
};

const isPinned = (key: string) => {
    return Array.isArray(pinnedSettings.value) ? pinnedSettings.value.some(p => p.key === key) : false;
};

const togglePin = async (item: any) => {
    const isCurrentlyPinned = isPinned(item.key);
    
    // Optimistic update
    if (isCurrentlyPinned) {
        pinnedSettings.value = pinnedSettings.value.filter(p => p.key !== item.key);
    } else {
        pinnedSettings.value.push({
            key: item.key,
            label: item.label,
            route: item.route,
            module: item.module // Include module requirement for backend filtering
        });
    }

    try {
        await axios.put('/api/v1/settings/group/admin_ui', {
            settings: {
                admin_pinned_settings: {
                    value: pinnedSettings.value,
                    type: 'array',
                    group: 'admin_ui'
                }
            }
        });
        
        dialog.success(t(isCurrentlyPinned ? 'Settings unpinned from sidebar' : 'Settings pinned to sidebar'));
        window.dispatchEvent(new Event('admin-menu-changed'));
    } catch (error) {
        console.error('Failed to toggle pin:', error);
        dialog.error(t('Failed to update pinned settings'));
        // Revert on failure
        await loadPinnedSettings();
    }
};

onMounted(() => {
    loadPinnedSettings();
});

const categories = computed(() => {
    const rawCategories = [
    {
        name: 'Common',
        items: [
            {
                key: 'general',
                label: 'General',
                description: 'View and update your general settings and site information',
                icon: Cog6ToothIcon,
                route: { name: 'admin.settings.group', params: { group: 'general' } }
            },
            {
                key: 'auth_appearance',
                label: 'Login Appearance',
                description: 'Customize the login page layout, backgrounds, and brand aesthetics',
                icon: PaintBrushIcon,
                route: { name: 'admin.settings.group', params: { group: 'auth_appearance' } }
            },
            {
                key: 'permalinks',
                label: 'Permalink',
                description: 'Configure URL structures for posts, pages and products',
                icon: LinkIcon,
                route: { name: 'admin.settings.group', params: { group: 'permalinks' } }
            },
            {
                key: 'reading',
                label: 'Reading',
                description: 'Configure your homepage and content display preferences',
                icon: BookOpenIcon,
                route: { name: 'admin.settings.group', params: { group: 'reading' } }
            },
            {
                key: 'email',
                label: 'Email',
                description: 'Configure your mail server and sender information',
                icon: EnvelopeIcon,
                route: { name: 'admin.settings.group', params: { group: 'email' } }
            },
            {
                key: 'email_templates',
                label: 'Email templates',
                description: 'Customize notification templates using variables',
                icon: DocumentTextIcon,
                route: { name: 'admin.settings.email-templates' }
            },
            {
                key: 'media',
                label: 'Media',
                description: 'Manage media upload sizes and library settings',
                icon: PhotoIcon,
                route: { name: 'admin.settings.group', params: { group: 'media' } }
            },
            {
                key: 'api',
                label: 'API Settings',
                description: 'Configure external API access and keys',
                icon: CommandLineIcon,
                route: { name: 'admin.settings.group', params: { group: 'api' } }
            },
            {
                key: 'template_defaults',
                label: 'Template Defaults',
                description: 'Set default themes and templates for pages, posts, and products.',
                icon: DocumentDuplicateIcon,
                route: { name: 'admin.settings.group', params: { group: 'template_defaults' } }
            },
            {
                key: 'mtoptimize',
                label: 'MTOptimize',
                description: 'Configure SEO templates, schema defaults, robots and canonical policies.',
                icon: MagnifyingGlassCircleIcon,
                route: { name: 'admin.settings.group', params: { group: 'mtoptimize' } },
                module: 'Polyx.MTOptimize'
            },
            {
                key: 'cookie_consent',
                label: 'Cookie Consent',
                description: 'Manage GDPR/CCPA cookie consent banners and behaviors.',
                icon: ShieldCheckIcon,
                route: { name: 'admin.settings.group', params: { group: 'cookie_consent' } },
                module: 'Polyx.CookieConsent'
            },
            {
                key: 'webhooks',
                label: 'Webhooks',
                description: 'Configure incoming and outgoing webhook endpoints.',
                icon: GlobeAltIcon,
                route: { name: 'admin.webhooks.index' }
            }
        ]
    },
    {
        name: 'Localization',
        items: [
            {
                key: 'languages',
                label: 'Languages',
                description: 'Manage site languages and default locale',
                icon: GlobeAltIcon,
                route: { name: 'admin.settings.languages' }
            },
            {
                key: 'translations',
                label: 'Translations',
                description: 'Edit theme and module translation strings',
                icon: LanguageIcon,
                action: async (r: any) => {
                    try {
                        const response = await axios.get('/api/v1/languages');
                        const languages = response.data?.data || [];
                        const defaultLang = languages.find((l: any) => l.is_default) || languages[0];
                        if (defaultLang) {
                            r.push({ name: 'admin.settings.languages.translations', params: { id: defaultLang.id }});
                        } else {
                            r.push({ name: 'admin.settings.languages' });
                        }
                    } catch (e) {
                         r.push({ name: 'admin.settings.languages' });
                    }
                }
            }
        ]
    },
    {
        name: 'Ecommerce',
        items: [
            {
                key: 'ecommerce_general',
                label: 'General',
                description: 'Basic store configuration and contact info',
                icon: ShoppingBagIcon,
                route: { name: 'admin.settings.group', params: { group: 'ecommerce' } }
            },
            {
                key: 'currencies',
                label: 'Currencies',
                description: 'Manage store currencies, formatting and exchange rates',
                icon: BanknotesIcon,
                route: { name: 'admin.settings.ecommerce.currencies' }
            },
            {
                key: 'payment_gateways',
                label: 'Payment Gateways',
                description: 'Configure PayPal, Stripe, Bank Transfer, etc.',
                icon: CreditCardIcon,
                route: { name: 'admin.settings.gateways' }
            },
            {
                key: 'coupons',
                label: 'Checkout & Coupons',
                description: 'Global checkout behavior and discount rules',
                icon: ReceiptPercentIcon,
                route: { name: 'admin.settings.group', params: { group: 'checkout' } }
            },
            {
                key: 'invoices',
                label: 'Invoices',
                description: 'Manage invoice numbering and company details',
                icon: QueueListIcon,
                route: { name: 'admin.settings.group', params: { group: 'invoices' } }
            },
            {
                key: 'refund_policy',
                label: 'Refund Policy',
                description: 'Configure default refund request window and fallback policy note',
                icon: DocumentTextIcon,
                route: { name: 'admin.settings.group', params: { group: 'refund_policy' } }
            },
            {
                key: 'global_faqs',
                label: "Global FAQ's",
                description: 'Manage reusable FAQ content for product detail pages',
                icon: QuestionMarkCircleIcon,
                route: { name: 'admin.settings.group', params: { group: 'global_faqs' } }
            },
            {
                key: 'global_tabs',
                label: 'Global Tabs',
                description: 'Manage reusable custom tabs for product detail pages',
                icon: QueueListIcon,
                route: { name: 'admin.settings.group', params: { group: 'global_tabs' } }
            }
        ]
    },
    {
        name: 'System',
        items: [
            {
                key: 'system_update',
                label: 'System Update',
                description: 'Update PolyCMS core from an official package',
                icon: ArrowPathIcon,
                route: { name: 'admin.settings.system-update' }
            },
            {
                key: 'system_info',
                label: 'System Info',
                description: 'View system version, PHP info, and server environment',
                icon: InformationCircleIcon,
                route: { name: 'admin.settings.system-info' }
            }
        ]
    }
    ];

    // Filter categories based on active modules
    return rawCategories.map(category => {
        return {
            ...category,
            items: category.items.filter(item => {
                if ((item as any).module) {
                    return activeModules.includes((item as any).module);
                }
                return true;
            })
        };
    }).filter(c => c.items.length > 0);
});
</script>

<style scoped>
.settings-hub {
    max-width: 100%;
}
</style>
