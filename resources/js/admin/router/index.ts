import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router';
import AdminLayout from '../layout/AdminLayout.vue';
import Dashboard from '../views/Dashboard.vue';
import Login from '../views/Login.vue';
import PostList from '../views/posts/PostList.vue';
import PostEditor from '../views/posts/PostEditor.vue';
import PageList from '../views/pages/PageList.vue';
import ProductList from '../views/products/ProductList.vue';
import ProductEditor from '../views/products/ProductEditor.vue';
import CategoryList from '../views/categories/CategoryList.vue';
import CategoryEditor from '../views/categories/CategoryEditor.vue';
import ProductCategoryList from '../views/products/categories/ProductCategoryList.vue';
import ProductCategoryEditor from '../views/products/categories/ProductCategoryEditor.vue';
import ProductBrandList from '../views/products/brands/ProductBrandList.vue';
import ProductBrandEditor from '../views/products/brands/ProductBrandEditor.vue';
import TagList from '../views/tags/TagList.vue';
import PostTagList from '../views/tags/post/PostTagList.vue';
import PostTagEditor from '../views/tags/post/PostTagEditor.vue';
import ProductTagList from '../views/tags/product/ProductTagList.vue';
import ProductTagEditor from '../views/tags/product/ProductTagEditor.vue';
import MediaLibrary from '../views/media/MediaLibrary.vue';
import WidgetManager from '../views/widgets/WidgetManager.vue';
import ModuleList from '../views/modules/ModuleList.vue';
import UserList from '../views/users/UserList.vue';
import UserEditor from '../views/users/UserEditor.vue';
import RoleList from '../views/roles/RoleList.vue';
import RoleEditor from '../views/roles/RoleEditor.vue';
import Settings from '../views/settings/Settings.vue';
import SettingsHub from '../views/settings/SettingsHub.vue';
import LanguageSettings from '../views/settings/LanguageSettings.vue';
import TranslationEditor from '../views/settings/TranslationEditor.vue';
import EmailTemplateList from '../views/settings/EmailTemplateList.vue';
import EmailTemplateEditor from '../views/settings/EmailTemplateEditor.vue';
import CurrencySettings from '../views/settings/CurrencySettings.vue';
import ThemeList from '../views/themes/ThemeList.vue';
import ThemeOptions from '../views/themes/ThemeOptions.vue';
import LayoutAssetList from '../views/appearance/LayoutAssetList.vue';
import LayoutAssetEditor from '../views/appearance/LayoutAssetEditor.vue';
import Menus from '../views/menus/Menus.vue';
import Profile from '../views/profile/Profile.vue';
import OrderList from '../views/orders/OrderList.vue';
import OrderDetail from '../views/orders/OrderDetail.vue';
import InvoiceList from '../views/ecommerce/InvoiceList.vue';
import CouponList from '../views/coupons/CouponList.vue';
import CouponEditor from '../views/coupons/CouponEditor.vue';
import SubscriptionList from '../views/subscriptions/SubscriptionList.vue';
import LicenseList from '../views/licenses/LicenseList.vue';
import AccountOrderList from '../views/account/OrderList.vue';
import AccountSubscriptionList from '../views/account/SubscriptionList.vue';
import AccountLicenseList from '../views/account/LicenseList.vue';
import AccountProfile from '../views/account/Profile.vue';
// Payments section
import TransactionList from '../views/payments/TransactionList.vue';
import PaymentLogs from '../views/payments/PaymentLogs.vue';
import PaymentMethodList from '../views/payments/PaymentMethodList.vue';
// Reports section
import ReportsHub from '../views/reports/ReportsHub.vue';
import ArticleFeedbackReport from '../views/reports/feedback/ArticleFeedbackReport.vue';
import { useAuthStore } from '../stores/auth';

// E-commerce Shipping & Taxes
import ShippingZoneList from '../views/ecommerce/ShippingZoneList.vue';
import ShippingZoneEditor from '../views/ecommerce/ShippingZoneEditor.vue';
import TaxRateList from '../views/ecommerce/TaxRateList.vue';
import TaxRateEditor from '../views/ecommerce/TaxRateEditor.vue';

const moduleRouteModules = import.meta.glob('../../../../modules/*/*/resources/admin/routes.ts', {
    eager: true,
    import: 'default',
});

const moduleChildRoutes: RouteRecordRaw[] = [];

Object.values(moduleRouteModules).forEach((moduleRoutes: unknown) => {
    if (Array.isArray(moduleRoutes)) {
        moduleChildRoutes.push(...(moduleRoutes as RouteRecordRaw[]));
    }
});

const routes: Array<RouteRecordRaw> = [
    {
        path: '/admin/login',
        name: 'admin.login',
        component: Login,
        meta: { guestOnly: true },
    },
    {
        path: '/admin',
        name: 'admin',
        component: AdminLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'admin.dashboard',
                component: Dashboard,
                meta: { title: 'Dashboard' },
            },
            {
                path: 'posts',
                name: 'admin.posts.index',
                component: PostList,
            },
            {
                path: 'posts/create',
                name: 'admin.posts.create',
                component: PostEditor,
            },
            {
                path: 'posts/:id/edit',
                name: 'admin.posts.edit',
                component: PostEditor,
                props: true,
            },
            {
                path: 'pages',
                name: 'admin.pages.index',
                component: PageList,
            },
            {
                path: 'pages/create',
                name: 'admin.pages.create',
                component: PostEditor,
            },
            {
                path: 'pages/:id/edit',
                name: 'admin.pages.edit',
                component: PostEditor,
                props: true,
            },
            {
                path: 'products',
                name: 'admin.products.index',
                component: ProductList,
                meta: { title: 'Products' },
            },
            {
                path: 'products/attributes',
                name: 'admin.products.attributes',
                component: () => import('../views/products/ProductAttributeList.vue'),
                meta: { title: 'Product Attributes' },
            },
            {
                path: 'products/attribute-groups',
                name: 'admin.products.attribute-groups',
                component: () => import('../views/products/ProductAttributeGroupList.vue'),
                meta: { title: 'Attribute Groups' },
            },
            {
                path: 'products/create',
                name: 'admin.products.create',
                component: ProductEditor,
            },
            {
                path: 'products/:id/edit',
                name: 'admin.products.edit',
                component: ProductEditor,
                props: true,
            },
            {
                path: 'categories',
                name: 'admin.categories.index',
                component: CategoryList,
            },
            {
                path: 'categories/create',
                name: 'admin.categories.create',
                component: CategoryEditor,
            },
            {
                path: 'categories/:id/edit',
                name: 'admin.categories.edit',
                component: CategoryEditor,
                props: true,
            },
            {
                path: 'product-categories',
                name: 'admin.product-categories.index',
                component: ProductCategoryList,
            },
            {
                path: 'product-categories/create',
                name: 'admin.product-categories.create',
                component: ProductCategoryEditor,
            },
            {
                path: 'product-categories/:id/edit',
                name: 'admin.product-categories.edit',
                component: ProductCategoryEditor,
                props: true,
            },
            {
                path: 'product-brands',
                name: 'admin.product-brands.index',
                component: ProductBrandList,
            },
            {
                path: 'product-brands/create',
                name: 'admin.product-brands.create',
                component: ProductBrandEditor,
            },
            {
                path: 'product-brands/:id/edit',
                name: 'admin.product-brands.edit',
                component: ProductBrandEditor,
                props: true,
            },
            {
                path: 'tags',
                name: 'admin.tags.index',
                component: TagList,
            },
            {
                path: 'post-tags',
                name: 'admin.post-tags.index',
                component: PostTagList,
            },
            {
                path: 'post-tags/create',
                name: 'admin.post-tags.create',
                component: PostTagEditor,
            },
            {
                path: 'post-tags/:id/edit',
                name: 'admin.post-tags.edit',
                component: PostTagEditor,
                props: true,
            },
            {
                path: 'product-tags',
                name: 'admin.product-tags.index',
                component: ProductTagList,
            },
            {
                path: 'product-tags/create',
                name: 'admin.product-tags.create',
                component: ProductTagEditor,
            },
            {
                path: 'product-tags/:id/edit',
                name: 'admin.product-tags.edit',
                component: ProductTagEditor,
                props: true,
            },
            {
                path: 'media',
                name: 'admin.media.index',
                component: MediaLibrary,
                meta: { title: 'Media Library' },
            },
            {
                path: 'widgets',
                name: 'admin.widgets.index',
                component: WidgetManager,
                meta: { title: 'Widgets' },
            },
            {
                path: 'modules',
                name: 'admin.modules.index',
                component: ModuleList,
                meta: { title: 'Modules' },
            },
            {
                path: 'users',
                name: 'admin.users.index',
                component: UserList,
                meta: { title: 'Users' },
            },
            {
                path: 'users/create',
                name: 'admin.users.create',
                component: UserEditor,
            },
            {
                path: 'users/:id/edit',
                name: 'admin.users.edit',
                component: UserEditor,
                props: true,
            },
            {
                path: 'roles',
                name: 'admin.roles.index',
                component: RoleList,
                meta: { title: 'Roles' },
            },
            {
                path: 'roles/create',
                name: 'admin.roles.create',
                component: RoleEditor,
            },
            {
                path: 'roles/:id/edit',
                name: 'admin.roles.edit',
                component: RoleEditor,
                props: true,
            },
            {
                path: 'options-general',
                name: 'admin.options-general',
                redirect: { name: 'admin.settings.group', params: { group: 'general' } }
            },
            {
                path: 'settings',
                name: 'admin.settings.index',
                component: SettingsHub,
                meta: { title: 'Settings' },
            },
            {
                path: 'settings/languages',
                name: 'admin.settings.languages',
                component: LanguageSettings,
            },
            {
                path: 'settings/languages/:id/translations',
                name: 'admin.settings.languages.translations',
                component: TranslationEditor,
                props: true,
            },
            {
                path: 'settings/email-templates',
                name: 'admin.settings.email-templates',
                component: EmailTemplateList,
            },
            {
                path: 'settings/email-templates/:id',
                name: 'admin.settings.email-templates.edit',
                component: EmailTemplateEditor,
            },
            {
                path: 'settings/webhooks',
                name: 'admin.webhooks.index',
                component: () => import('../views/webhooks/WebhookList.vue'),
                meta: { title: 'Webhooks' },
            },
            {
                path: 'settings/webhooks/create',
                name: 'admin.webhooks.create',
                component: () => import('../views/webhooks/WebhookEditor.vue'),
                meta: { title: 'Create Webhook' },
            },
            {
                path: 'settings/webhooks/:id/edit',
                name: 'admin.webhooks.edit',
                component: () => import('../views/webhooks/WebhookEditor.vue'),
                meta: { title: 'Edit Webhook' },
            },
            {
                path: 'settings/gateways',
                name: 'admin.settings.gateways',
                component: PaymentMethodList,
            },
            {
                path: 'settings/gateways/:id',
                name: 'admin.settings.gateways.edit',
                redirect: { name: 'admin.settings.gateways' },
            },
            {
                path: 'settings/system-update',
                name: 'admin.settings.system-update',
                component: () => import('../views/settings/SystemUpdate.vue'),
                meta: { title: 'System Update' },
            },
            {
                path: 'settings/system-info',
                name: 'admin.settings.system-info',
                component: () => import('../views/settings/SystemInfo.vue'),
                meta: { title: 'System Info' },
            },
            {
                path: 'settings/:group',
                name: 'admin.settings.group',
                component: Settings,
                props: true,
            },
            {
                path: 'settings/ecommerce/currencies',
                name: 'admin.settings.ecommerce.currencies',
                component: CurrencySettings,
                meta: { title: 'Currencies' },
            },
            {
                path: 'themes',
                name: 'admin.themes.index',
                component: ThemeList,
                meta: { title: 'Themes' },
            },
            {
                path: 'themes/options',
                name: 'admin.themes.options',
                component: ThemeOptions,
            },
            {
                path: 'appearance/template-parts',
                name: 'admin.appearance.parts.index',
                component: LayoutAssetList,
                props: { kind: 'part' },
                meta: { title: 'Template Parts' },
            },
            {
                path: 'appearance/template-parts/create',
                name: 'admin.appearance.parts.create',
                component: LayoutAssetEditor,
                props: { kind: 'part' },
                meta: { title: 'New Template Part' },
            },
            {
                path: 'appearance/template-parts/:id/edit',
                name: 'admin.appearance.parts.edit',
                component: LayoutAssetEditor,
                props: { kind: 'part' },
                meta: { title: 'Edit Template Part' },
            },
            {
                path: 'appearance/templates',
                name: 'admin.appearance.templates.index',
                component: LayoutAssetList,
                props: { kind: 'template' },
                meta: { title: 'Templates' },
            },
            {
                path: 'appearance/templates/create',
                name: 'admin.appearance.templates.create',
                component: LayoutAssetEditor,
                props: { kind: 'template' },
                meta: { title: 'New Template' },
            },
            {
                path: 'appearance/templates/:id/edit',
                name: 'admin.appearance.templates.edit',
                component: LayoutAssetEditor,
                props: { kind: 'template' },
                meta: { title: 'Edit Template' },
            },
            {
                path: 'menus',
                name: 'admin.menus.index',
                component: Menus,
            },
            {
                path: 'profile',
                name: 'admin.profile',
                component: Profile,
            },
            {
                path: 'orders',
                name: 'admin.orders.index',
                component: OrderList,
                meta: { title: 'Orders' },
            },
            {
                path: 'orders/:id',
                name: 'admin.orders.show',
                component: OrderDetail,
            },
            {
                path: 'invoices',
                name: 'admin.invoices.index',
                component: InvoiceList,
                meta: { title: 'Invoices' },
            },
            {
                path: 'coupons',
                name: 'admin.coupons.index',
                component: CouponList,
                meta: { title: 'Coupons' },
            },
            {
                path: 'coupons/create',
                name: 'admin.coupons.create',
                component: CouponEditor,
            },
            {
                path: 'coupons/:id/edit',
                name: 'admin.coupons.edit',
                component: CouponEditor,
            },
            {
                path: 'subscriptions',
                name: 'admin.subscriptions.index',
                component: SubscriptionList,
            },
            {
                path: 'licenses',
                name: 'admin.licenses.index',
                component: LicenseList,
            },
            // Payments section
            {
                path: 'transactions',
                name: 'admin.transactions.index',
                component: TransactionList,
            },
            {
                path: 'payment-logs',
                name: 'admin.payment-logs.index',
                component: PaymentLogs,
            },
            {
                path: 'payment-methods',
                name: 'admin.payment-methods.index',
                component: PaymentMethodList,
            },
            // Shipping
            {
                path: 'ecommerce/shipping-zones',
                name: 'admin.ecommerce.shipping-zones.index',
                component: ShippingZoneList,
                meta: { title: 'Shipping Zones' },
            },
            {
                path: 'ecommerce/shipping-zones/create',
                name: 'admin.ecommerce.shipping-zones.create',
                component: ShippingZoneEditor,
            },
            {
                path: 'ecommerce/shipping-zones/:id/edit',
                name: 'admin.ecommerce.shipping-zones.edit',
                component: ShippingZoneEditor,
            },
            // Taxes
            {
                path: 'ecommerce/tax-rates',
                name: 'admin.ecommerce.tax-rates.index',
                component: TaxRateList,
                meta: { title: 'Tax Rates' },
            },
            {
                path: 'ecommerce/tax-rates/create',
                name: 'admin.ecommerce.tax-rates.create',
                component: TaxRateEditor,
            },
            {
                path: 'ecommerce/tax-rates/:id/edit',
                name: 'admin.ecommerce.tax-rates.edit',
                component: TaxRateEditor,
            },
            // Reports section
            {
                path: 'reports',
                name: 'admin.reports.index',
                component: ReportsHub,
                meta: { title: 'Reports' },
            },
            {
                path: 'reports/article-feedback',
                name: 'admin.reports.article-feedback',
                component: ArticleFeedbackReport,
                meta: { title: 'Article Feedback' },
            },
            {
                path: 'reports/:slug',
                name: 'admin.reports.show',
                component: ReportsHub,
                props: true,
                meta: { title: 'Report' },
            },
            // Reviews (Moderation)
            {
                path: 'reviews',
                name: 'admin.reviews.index',
                component: () => import('../views/reviews/ReviewList.vue'),
                meta: { title: 'Reviews' },
            },
            ...moduleChildRoutes,
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();

    // Check if there's a token in localStorage but auth state hasn't been initialized
    const token = localStorage.getItem('auth_token');
    if (token && !authStore.isAuthenticated && !authStore.user) {
        // Wait for auth check to complete
        await authStore.checkAuth();
    }

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next({ name: 'admin.login' });
    } else if (to.meta.guestOnly && authStore.isAuthenticated) {
        next({ name: 'admin.dashboard' });
    } else {
        next();
    }
});

const DEFAULT_TITLE = 'PolyCMS Admin';

// Simple plural to singular mapping for titles
const pluralToSingular: Record<string, string> = {
    'posts': 'Post',
    'pages': 'Page',
    'products': 'Product',
    'categories': 'Category',
    'product-categories': 'Product Category',
    'product-brands': 'Product Brand',
    'tags': 'Tag',
    'post-tags': 'Post Tag',
    'product-tags': 'Product Tag',
    'users': 'User',
    'roles': 'Role',
    'coupons': 'Coupon',
    'subscriptions': 'Subscription',
    'licenses': 'License',
    'orders': 'Order',
    'invoices': 'Invoice',
};

function formatRouteName(name: string): string {
    if (!name) return '';
    
    // e.g., 'admin.posts.create' -> ['posts', 'create']
    const cleanName = name.replace('admin.', '');
    const parts = cleanName.split('.');
    
    if (parts.length === 1) {
        // e.g., 'profile'
        const entity = parts[0];
        return entity.split('-').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
    }
    
    const entityKey = parts[0];
    const action = parts[1];
    
    const entityPlural = entityKey.split('-').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
    const entitySingular = pluralToSingular[entityKey] || entityPlural;
    
    if (action === 'index') return entityPlural;
    if (action === 'create') return `Add New ${entitySingular}`;
    if (action === 'edit') return `Edit ${entitySingular}`;
    if (action === 'show' || action === 'detail') return `View ${entitySingular}`;
    
    // Fallback for custom module routes like 'admin.accounting.coa' or 'admin.settings.gateways'
    const lastPart = parts[parts.length - 1];
    const customMapping: Record<string, string> = {
        'coa': 'Chart of Accounts',
    };
    
    return customMapping[lastPart] || lastPart.split('-').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
}

router.afterEach((to) => {
    let title = to.meta.title as string;
    
    if (!title && to.name) {
        title = formatRouteName(to.name as string);
    }
    
    if (title) {
        document.title = `${title} • ${DEFAULT_TITLE}`;
    } else {
        document.title = DEFAULT_TITLE;
    }
});


export default router;
