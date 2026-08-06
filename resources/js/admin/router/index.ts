import { createRouter, createWebHistory } from 'vue-router';
import type { RouteLocationNormalized, RouteRecordRaw } from 'vue-router';
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
import CapabilityPresetList from '../views/products/capability-presets/CapabilityPresetList.vue';
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
import ShowcaseManager from '../views/flexiempty/ShowcaseManager.vue';
import LayoutAssetList from '../views/appearance/LayoutAssetList.vue';
import LayoutAssetEditor from '../views/appearance/LayoutAssetEditor.vue';
import CorePresetList from '../views/appearance/presets/CorePresetList.vue';
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

const routePermissionMap: Record<string, string | string[]> = {
    'admin.dashboard': 'access admin',
    'admin.posts.index': 'view-any post',
    'admin.posts.create': 'create post',
    'admin.posts.edit': 'update post',
    'admin.pages.index': 'view-any post',
    'admin.pages.create': 'create post',
    'admin.pages.edit': 'update post',
    'admin.products.index': 'view-any product',
    'admin.products.create': 'create product',
    'admin.products.edit': 'update product',
    'admin.products.attributes': 'update product',
    'admin.products.attribute-groups': 'update product',
    'admin.categories.index': 'view-any category',
    'admin.categories.create': 'create category',
    'admin.categories.edit': 'update category',
    'admin.product-categories.index': 'view-any category',
    'admin.product-categories.create': 'create category',
    'admin.product-categories.edit': 'update category',
    'admin.product-brands.index': 'view-any category',
    'admin.product-brands.create': 'create category',
    'admin.product-brands.edit': 'update category',
    'admin.tags.index': 'view-any tag',
    'admin.post-tags.index': 'view-any tag',
    'admin.post-tags.create': 'create tag',
    'admin.post-tags.edit': 'update tag',
    'admin.product-tags.index': 'view-any tag',
    'admin.product-tags.create': 'create tag',
    'admin.product-tags.edit': 'update tag',
    'admin.media.index': 'view-any media',
    'admin.widgets.index': 'view widgets',
    'admin.modules.index': 'view modules',
    'admin.users.index': 'view users',
    'admin.users.create': 'create users',
    'admin.users.edit': 'update users',
    'admin.roles.index': 'manage roles',
    'admin.roles.create': 'manage roles',
    'admin.roles.edit': 'manage roles',
    'admin.settings.index': 'view settings',
    'admin.settings.group': 'view settings',
    'admin.settings.custom-icons': 'manage settings',
    'admin.settings.languages': 'manage settings',
    'admin.settings.languages.translations': 'manage settings',
    'admin.settings.email-templates': 'manage settings',
    'admin.settings.email-templates.edit': 'manage settings',
    'admin.webhooks.index': 'manage settings',
    'admin.webhooks.create': 'manage settings',
    'admin.webhooks.edit': 'manage settings',
    'admin.settings.gateways': 'manage settings',
    'admin.settings.system-update': 'manage settings',
    'admin.settings.system-info': 'view settings',
    'admin.settings.cache': 'manage settings',
    'admin.settings.ecommerce.currencies': 'manage settings',
    'admin.themes.index': 'view themes',
    'admin.themes.options': 'manage theme options',
    'admin.flexiempty.showcases.index': 'manage theme options',
    'admin.appearance.parts.index': 'view layout assets',
    'admin.appearance.parts.create': 'create layout assets',
    'admin.appearance.parts.edit': 'update layout assets',
    'admin.appearance.templates.index': 'view layout assets',
    'admin.appearance.templates.create': 'create layout assets',
    'admin.appearance.templates.edit': 'update layout assets',
    'admin.appearance.presets': 'manage theme options',
    'admin.appearance.categories.index': 'view-any category',
    'admin.appearance.categories.create': 'create category',
    'admin.appearance.categories.edit': 'update category',
    'admin.menus.index': 'manage theme options',
    'admin.orders.index': 'view-any order',
    'admin.orders.show': 'view order',
    'admin.invoices.index': 'view-any order',
    'admin.coupons.index': 'view-any coupon',
    'admin.coupons.create': 'create coupon',
    'admin.coupons.edit': 'update coupon',
    'admin.subscriptions.index': 'view-any subscription',
    'admin.licenses.index': 'view-any license',
    'admin.transactions.index': 'view-any order',
    'admin.payment-logs.index': 'view-any order',
    'admin.payment-methods.index': 'manage settings',
    'admin.ecommerce.shipping-zones.index': 'manage settings',
    'admin.ecommerce.shipping-zones.create': 'manage settings',
    'admin.ecommerce.shipping-zones.edit': 'manage settings',
    'admin.ecommerce.tax-rates.index': 'manage settings',
    'admin.ecommerce.tax-rates.create': 'manage settings',
    'admin.ecommerce.tax-rates.edit': 'manage settings',
    'admin.reports.index': 'access admin',
    'admin.reports.article-feedback': 'access admin',
    'admin.reports.show': 'access admin',
    'admin.reviews.index': 'update product',
    'admin.contacts.dashboard': 'access admin',
    'admin.contacts.submissions': 'access admin',
    'admin.contacts.forms': 'access admin',
    'admin.contacts.forms.create': 'access admin',
    'admin.contacts.forms.edit': 'access admin',
};

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
                path: 'capability-presets',
                name: 'admin.capability-presets.index',
                component: CapabilityPresetList,
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
                path: 'contacts',
                name: 'admin.contacts.dashboard',
                component: () => import('../views/contacts/Dashboard.vue'),
                meta: { title: 'Contact Dashboard' },
            },
            {
                path: 'contacts/submissions',
                name: 'admin.contacts.submissions',
                component: () => import('../views/contacts/SubmissionList.vue'),
                meta: { title: 'Contacts Submissions' },
            },
            {
                path: 'contacts/forms',
                name: 'admin.contacts.forms',
                component: () => import('../views/contacts/FormList.vue'),
                meta: { title: 'Contacts Forms' },
            },
            {
                path: 'contacts/forms/create',
                name: 'admin.contacts.forms.create',
                component: () => import('../views/contacts/FormBuilder.vue'),
                meta: { title: 'Create Contact Form' },
            },
            {
                path: 'contacts/forms/:id/edit',
                name: 'admin.contacts.forms.edit',
                component: () => import('../views/contacts/FormBuilder.vue'),
                props: true,
                meta: { title: 'Edit Contact Form' },
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
                path: 'settings/custom-icons',
                name: 'admin.settings.custom-icons',
                component: () => import('../views/settings/CustomIconsManager.vue'),
                meta: { title: 'Custom Icons' },
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
                alias: ['settings/email_templates'],
                name: 'admin.settings.email-templates',
                component: EmailTemplateList,
            },
            {
                path: 'settings/email-templates/:id',
                alias: ['settings/email_templates/:id'],
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
                path: 'settings/cache',
                name: 'admin.settings.cache',
                component: () => import('../views/settings/CacheManagement.vue'),
                meta: { title: 'Cache Management' },
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
                path: 'market-integration',
                name: 'admin.market-integration.index',
                component: () => import('../views/settings/tabs/MarketSettings.vue'),
                meta: { title: 'Market Integration' },
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
                path: 'flexiempty/showcases',
                name: 'admin.flexiempty.showcases.index',
                component: ShowcaseManager,
                meta: { title: 'FlexiEmpty ShipCode Pages' },
            },
            {
                path: 'flexiempty/showcases/create',
                name: 'admin.flexiempty.showcases.create',
                component: ShowcaseManager,
                meta: { title: 'Upload ShipCode Page' },
            },
            {
                path: 'flexiempty/showcases/:id/edit',
                name: 'admin.flexiempty.showcases.edit',
                component: ShowcaseManager,
                meta: { title: 'Edit ShipCode Page' },
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
                path: 'appearance/presets',
                name: 'admin.appearance.presets',
                component: CorePresetList,
                meta: { title: 'Presets' },
            },
            {
                path: 'appearance/categories',
                name: 'admin.appearance.categories.index',
                component: CategoryList,
                meta: { title: 'Template Categories', type: 'layout_asset' },
            },
            {
                path: 'appearance/categories/create',
                name: 'admin.appearance.categories.create',
                component: CategoryEditor,
                meta: { title: 'New Category', type: 'layout_asset' },
            },
            {
                path: 'appearance/categories/:id/edit',
                name: 'admin.appearance.categories.edit',
                component: CategoryEditor,
                props: true,
                meta: { title: 'Edit Category', type: 'layout_asset' },
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
    } else if (to.meta.requiresAuth && !canAccessRoute(to, authStore)) {
        next({ name: 'admin.dashboard' });
    } else {
        next();
    }
});

function canAccessRoute(to: RouteLocationNormalized, authStore: ReturnType<typeof useAuthStore>): boolean {
    const metaPermission = to.meta?.permission ?? to.meta?.permissions;
    const mappedPermission = typeof to.name === 'string' ? routePermissionMap[to.name] : undefined;
    const permission = metaPermission ?? mappedPermission;

    if (!permission) {
        return true;
    }

    return authStore.can(permission, to.meta?.permissionMode === 'all' || to.meta?.permission_mode === 'all');
}

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
