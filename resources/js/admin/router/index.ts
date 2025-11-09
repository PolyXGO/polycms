import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router';
import AdminLayout from '../layout/AdminLayout.vue';
import Dashboard from '../views/Dashboard.vue';
import Login from '../views/Login.vue';
import PostList from '../views/posts/PostList.vue';
import PostEditor from '../views/posts/PostEditor.vue';
import ProductList from '../views/products/ProductList.vue';
import ProductEditor from '../views/products/ProductEditor.vue';
import CategoryList from '../views/categories/CategoryList.vue';
import CategoryEditor from '../views/categories/CategoryEditor.vue';
import TagList from '../views/tags/TagList.vue';
import PostTagList from '../views/tags/post/PostTagList.vue';
import PostTagEditor from '../views/tags/post/PostTagEditor.vue';
import ProductTagList from '../views/tags/product/ProductTagList.vue';
import ProductTagEditor from '../views/tags/product/ProductTagEditor.vue';
import MediaLibrary from '../views/media/MediaLibrary.vue';
import WidgetManager from '../views/widgets/WidgetManager.vue';
import ModuleList from '../views/modules/ModuleList.vue';
import Settings from '../views/settings/Settings.vue';
import ThemeList from '../views/themes/ThemeList.vue';
import { useAuthStore } from '../stores/auth';

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
        component: AdminLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'admin.dashboard',
                component: Dashboard,
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
                path: 'products',
                name: 'admin.products.index',
                component: ProductList,
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
            },
            {
                path: 'widgets',
                name: 'admin.widgets.index',
                component: WidgetManager,
            },
            {
                path: 'modules',
                name: 'admin.modules.index',
                component: ModuleList,
            },
            {
                path: 'options-general',
                name: 'admin.options-general',
                component: Settings,
            },
            {
                path: 'themes',
                name: 'admin.themes.index',
                component: ThemeList,
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

export default router;
