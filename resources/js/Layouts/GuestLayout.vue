<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';

const page = usePage();
const brandLogo = computed(() => (page.props.settings as any)?.brand_logo);
const brandName = computed(() => (page.props.settings as any)?.brand_name || 'PolyCMS');

const authAppearance = computed(() => (page.props.settings as any)?.auth_appearance || {});
const authShowLogo = computed(() => {
    const val = authAppearance.value.auth_show_logo?.value ?? authAppearance.value.auth_show_logo;
    return val !== undefined ? val : true;
});
const authLayoutPosition = computed(() => authAppearance.value.auth_layout_position?.value || 'center');
const authBgOverlay = computed(() => authAppearance.value.auth_bg_overlay?.value || 50);
const authCardGlassmorphism = computed(() => authAppearance.value.auth_card_glassmorphism?.value || 10);
const authShowVersion = computed(() => {
    const val = authAppearance.value.auth_show_version;
    if (val !== undefined) return val?.value ?? val;
    return true;
});
const authLoginText = computed(() => authAppearance.value.auth_login_text?.value || "PolyXGO with love\nCopyright 2026 © PolyXGO.");
const appVersion = computed(() => (page.props.settings as any)?.version || '1.0.0');
const laravelVersion = computed(() => (page.props.settings as any)?.laravel_version || '11.x');

const authBgMode = computed(() => authAppearance.value.auth_bg_mode?.value || 'random');
const currentSlideIndex = ref(0);
let slideInterval: any = null;

const parsedAuthBgImages = computed(() => {
    const rawImages = authAppearance.value.auth_bg_images?.value;
    if (!rawImages) return [];
    try {
        const images = typeof rawImages === 'string' ? JSON.parse(rawImages) : rawImages;
        return images.map((img: any) => img.url || img);
    } catch {
        return [];
    }
});

const authBgImage = computed(() => {
    const mode = authBgMode.value;
    if (mode === 'fixed' && authAppearance.value.auth_bg_fixed_image?.value) {
        return authAppearance.value.auth_bg_fixed_image.value;
    } else if (mode === 'random' && parsedAuthBgImages.value.length > 0) {
        return parsedAuthBgImages.value[Math.floor(Math.random() * parsedAuthBgImages.value.length)];
    }
    return null;
});

onMounted(() => {
    if (authBgMode.value === 'slideshow' && parsedAuthBgImages.value.length > 0) {
        slideInterval = setInterval(() => {
            currentSlideIndex.value = (currentSlideIndex.value + 1) % parsedAuthBgImages.value.length;
        }, 5000);
    }
});

onUnmounted(() => {
    if (slideInterval) clearInterval(slideInterval);
});
</script>

<template>
    <div 
        class="min-h-screen flex transition-all relative overflow-hidden" 
        :class="{
            'items-center justify-center p-4 sm:p-6 lg:p-8': authLayoutPosition === 'center',
            'items-stretch justify-start': authLayoutPosition === 'left',
            'items-stretch justify-end': authLayoutPosition === 'right'
        }"
    >
        <!-- Background Image Pane -->
        <div v-if="authBgMode === 'slideshow' && parsedAuthBgImages.length > 0" class="absolute inset-0 z-0 bg-gray-900">
            <template v-for="(img, idx) in parsedAuthBgImages" :key="idx">
                <img :src="img" 
                     class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000 ease-in-out" 
                     :class="currentSlideIndex === idx ? 'opacity-100' : 'opacity-0'" />
            </template>
            <div class="absolute inset-0 bg-black" :style="{ opacity: authBgOverlay / 100 }"></div>
        </div>
        <div v-else-if="authBgImage" class="absolute inset-0 z-0">
            <img :src="authBgImage" class="absolute inset-0 w-full h-full object-cover" />
            <div class="absolute inset-0 bg-black" :style="{ opacity: authBgOverlay / 100 }"></div>
        </div>
        <!-- Default fallback background -->
        <div v-else class="absolute inset-0 z-0 bg-gray-50 dark:bg-gray-900"></div>

        <!-- Footer Text -->
        <div v-if="authLoginText" 
             class="absolute bottom-6 z-20 select-none pointer-events-none hidden md:block"
             :class="authLayoutPosition === 'right' ? 'left-8 text-left' : 'right-8 text-right'">
            <div class="text-white dark:text-gray-300 drop-shadow-md">
                <span class="block text-sm font-semibold whitespace-pre-line pointer-events-auto">{{ authLoginText }}</span>
                <span v-if="authShowVersion" class="block text-xs opacity-75 mt-1 pointer-events-auto">
                    <a href="https://polycms.org" target="_blank" rel="nofollow" class="hover:underline font-medium hover:text-white transition-colors">PolyCMS</a> v{{ appVersion }} based on <a href="https://laravel.com" target="_blank" rel="nofollow" class="hover:underline font-medium hover:text-white transition-colors">Laravel</a> <span v-if="laravelVersion">v{{ laravelVersion }}</span>
                </span>
            </div>
        </div>

        <!-- Form Wrapper Pane (Logo + Card) -->
        <div 
            class="relative z-10 flex flex-col transition-all overflow-y-auto"
            :class="[
                authLayoutPosition === 'center' ? 'w-full max-w-md justify-center' : 'w-full lg:w-[480px] xl:w-[500px] min-h-screen flex-col justify-center px-8 sm:px-12 py-12 shadow-2xl auth-card',
                authLayoutPosition !== 'center' && authBgImage ? 'backdrop-blur-md border-white/10 dark:border-gray-700/30' : (authLayoutPosition !== 'center' ? 'bg-white dark:bg-gray-900 border-gray-100 dark:border-gray-800' : '')
            ]"
            :style="authLayoutPosition !== 'center' ? { 
                '--glass-opacity': (100 - authCardGlassmorphism) / 100, 
                'border-right-width': authLayoutPosition === 'left' ? '1px' : '0', 
                'border-left-width': authLayoutPosition === 'right' ? '1px' : '0' 
            } : {}"
        >
            
            <!-- Logo -->
            <div v-if="authShowLogo" class="mb-8 text-center w-full min-h-[48px] flex items-center justify-center">
                <a href="/" class="transition-opacity hover:opacity-80">
                    <img v-if="brandLogo" :src="brandLogo" :alt="brandName" class="h-20 w-auto object-contain mx-auto" />
                    <h1 v-else class="text-3xl font-extrabold text-blue-600 dark:text-blue-400 uppercase tracking-tight">{{ brandName }}</h1>
                </a>
            </div>

            <!-- The Card -->
            <div
                class="w-full space-y-6 transition-all"
                :class="[
                    authLayoutPosition === 'center' ? 'auth-card overflow-hidden px-8 py-10 shadow-2xl border sm:rounded-2xl' : '',
                    authLayoutPosition === 'center' && authBgImage ? 'backdrop-blur-md border-white/20 dark:border-gray-700/50' : (authLayoutPosition === 'center' ? 'border-gray-100 dark:border-gray-800' : '')
                ]"
                :style="authLayoutPosition === 'center' ? { '--glass-opacity': (100 - authCardGlassmorphism) / 100 } : {}"
            >
                <slot />
            </div>
        </div>
    </div>
</template>

<style scoped>
.auth-card {
    background-color: rgba(255, 255, 255, flex);
    /* Fallback default */
    --glass-opacity: 1;
}
:root .auth-card {
    background-color: rgba(255, 255, 255, var(--glass-opacity));
}
.dark .auth-card {
    background-color: rgba(31, 41, 55, var(--glass-opacity));
}
</style>
