import type { RouteRecordRaw } from 'vue-router';
import Banners from './views/Banners.vue';
import BannerForm from './views/BannerForm.vue';
import Settings from './views/Settings.vue';

const routes: RouteRecordRaw[] = [
    {
        path: 'banner-slider/banners',
        name: 'admin.banner-slider.banners',
        component: Banners,
    },
    {
        path: 'banner-slider/banners/create',
        name: 'admin.banner-slider.banners.create',
        component: BannerForm,
    },
    {
        path: 'banner-slider/banners/:id/edit',
        name: 'admin.banner-slider.banners.edit',
        component: BannerForm,
        props: true,
    },
    {
        path: 'banner-slider/settings',
        name: 'admin.banner-slider.settings',
        component: Settings,
    },
];

export default routes;
