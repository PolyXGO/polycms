import { registerRoutes } from '@polycms';

registerRoutes([
    {
        path: 'sample-module/dashboard',
        name: 'admin.sample-module.dashboard',
        component: () => import('./views/Dashboard.vue'),
        meta: { title: 'Sample Module — Dashboard' },
    },
    {
        path: 'sample-module/notes',
        name: 'admin.sample-module.notes',
        component: () => import('./views/notes/Index.vue'),
        meta: { title: 'Sample Module — Notes' },
    },
    {
        path: 'sample-module/notes/create',
        name: 'admin.sample-module.notes.create',
        component: () => import('./views/notes/Form.vue'),
        meta: { title: 'Create Note' },
    },
    {
        path: 'sample-module/notes/:id/edit',
        name: 'admin.sample-module.notes.edit',
        component: () => import('./views/notes/Form.vue'),
        meta: { title: 'Edit Note' },
    },
    {
        path: 'sample-module/settings',
        name: 'admin.sample-module.settings',
        component: () => import('./views/Settings.vue'),
        meta: { title: 'Sample Module — Settings' },
    },
]);
