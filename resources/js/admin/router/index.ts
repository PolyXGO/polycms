import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router';
import AdminLayout from '../layout/AdminLayout.vue';
import Dashboard from '../views/Dashboard.vue';
import Login from '../views/Login.vue';
import PostList from '../views/posts/PostList.vue';
import PostEditor from '../views/posts/PostEditor.vue';
import ProductList from '../views/products/ProductList.vue';
import ProductEditor from '../views/products/ProductEditor.vue';
import CategoryList from '../views/categories/CategoryList.vue';
import TagList from '../views/tags/TagList.vue';
import MediaLibrary from '../views/media/MediaLibrary.vue';
import WidgetManager from '../views/widgets/WidgetManager.vue';
import { useAuthStore } from '../stores/auth';

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
                path: 'tags',
                name: 'admin.tags.index',
                component: TagList,
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
