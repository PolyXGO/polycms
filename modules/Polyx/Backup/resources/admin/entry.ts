import { registerRoutes } from '@polycms';

registerRoutes([
    {
        path: 'backup',
        children: [
            {
                path: '',
                name: 'admin.backup.index',
                component: () => import('./views/BackupIndex.vue'),
            },
            {
                path: 'settings',
                name: 'admin.backup.settings',
                component: () => import('./views/BackupSettings.vue'),
            },
        ],
    },
]);
