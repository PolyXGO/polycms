import type { RouteRecordRaw } from 'vue-router';
import SampleModuleSettings from './views/Settings.vue';

const routes: RouteRecordRaw[] = [
    {
        path: 'sample-module/settings',
        name: 'admin.sample-module.settings',
        component: SampleModuleSettings,
    },
];

export default routes;
