/**
 * Sample Module — Vue Admin Routes
 *
 * These routes are auto-discovered by PolyCMS's dynamic route loading system.
 * Convention: modules/{Vendor}/{ModuleName}/resources/admin/routes.ts
 *
 * The router uses import.meta.glob to find all routes.ts files in modules/
 * and registers them as children of the /admin route.
 *
 * Route naming convention: admin.{module-slug}.{action}
 * Path convention: {module-slug}/{action}
 */
import type { RouteRecordRaw } from 'vue-router';

const routes: RouteRecordRaw[] = [
    {
        path: 'sample-module/dashboard',
        name: 'admin.sample-module.dashboard',
        component: () => import('./views/Dashboard.vue'),
        meta: {
            title: 'Sample Module — Dashboard',
        },
    },
    {
        path: 'sample-module/notes',
        name: 'admin.sample-module.notes',
        component: () => import('./views/notes/Index.vue'),
        meta: {
            title: 'Sample Module — Notes',
        },
    },
    {
        path: 'sample-module/notes/create',
        name: 'admin.sample-module.notes.create',
        component: () => import('./views/notes/Form.vue'),
        meta: {
            title: 'Create Note',
        },
    },
    {
        path: 'sample-module/notes/:id/edit',
        name: 'admin.sample-module.notes.edit',
        component: () => import('./views/notes/Form.vue'),
        meta: {
            title: 'Edit Note',
        },
    },
    {
        path: 'sample-module/settings',
        name: 'admin.sample-module.settings',
        component: () => import('./views/Settings.vue'),
        meta: {
            title: 'Sample Module — Settings',
        },
    },
];

export default routes;
