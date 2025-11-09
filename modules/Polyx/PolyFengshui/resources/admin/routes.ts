import type { RouteRecordRaw } from 'vue-router';
import TokenManager from './views/TokenManager.vue';
import Docs from './views/Docs.vue';

const routes: RouteRecordRaw[] = [
    {
        path: 'polyfengshui/tokens',
        name: 'admin.polyfengshui.tokens',
        component: TokenManager,
    },
    {
        path: 'polyfengshui/docs',
        name: 'admin.polyfengshui.docs',
        component: Docs,
    },
];

export default routes;
