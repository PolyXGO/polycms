/**
 * BannerSlider Module — Standalone Entry Point
 *
 * This file is the entry for the independent Vite build.
 * It self-registers routes into the PolyCMS admin router via the SDK.
 */
import { registerRoutes } from '@polycms';

// Import views (relative paths, within this module)
import Banners from './views/Banners.vue';
import BannerForm from './views/BannerForm.vue';
import Settings from './views/Settings.vue';

// Self-register routes into admin layout
registerRoutes([
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
]);
