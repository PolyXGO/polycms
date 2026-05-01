<template>
    <div 
        class="relative group"
        @mouseenter="handleMouseEnter"
        @mouseleave="handleMouseLeave"
        ref="itemRef"
    >
        <!-- Menu Item with Children (Collapsible) -->
        <div v-if="item.children && item.children.length > 0">
            <button
                @click="!collapsed ? toggleMenu() : null"
                :class="[
                    'w-full flex items-center px-4 py-2.5 rounded transition duration-200',
                    isOpen && !collapsed ? 'bg-gray-900 dark:bg-gray-700 text-white' : 'text-gray-300 dark:text-gray-400 hover:bg-gray-700 dark:hover:bg-gray-700 hover:text-white',
                    collapsed ? 'justify-center relative' : 'justify-between'
                ]"
                :title="collapsed ? item.label : ''"
            >
                <div class="flex items-center">
                    <svg
                        v-if="item.icon"
                        class="w-5 h-5 flex-shrink-0"
                        :class="[collapsed ? '' : 'mr-3']"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            v-if="getIconPath(item.icon)"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            :d="getIconPath(item.icon) || undefined"
                        />
                        <circle v-else cx="12" cy="12" r="9" stroke-width="2" />
                    </svg>
                    <span v-if="!collapsed" class="font-medium truncate">{{ item.label }}</span>
                </div>
                <svg
                    v-if="!collapsed"
                    class="w-4 h-4 transition-transform duration-200 ml-2"
                    :class="{ 'transform rotate-180': isOpen }"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 9l-7 7-7-7"
                    />
                </svg>
            </button>
            
            <!-- Standard Dropdown (Expanded Mode) -->
            <div v-show="isOpen && !collapsed" class="ml-4 mt-1 space-y-1">
                <router-link
                    v-for="child in item.children"
                    :key="child.key"
                    :to="getChildRoute(child)"
                    :class="[
                        'block px-4 py-2 rounded transition duration-200 truncate',
                        isChildActive(child) ? 'bg-gray-900 dark:bg-gray-700 text-white' : 'text-gray-300 dark:text-gray-400 hover:bg-gray-700 dark:hover:bg-gray-700 hover:text-white'
                    ]"
                    @click="onNavClick"
                >
                    {{ child.label }}
                </router-link>
            </div>

            <!-- Floating Submenu (Collapsed Mode) - Teleported -->
            <Teleport to="body">
                <div 
                    v-if="collapsed && isHovered" 
                    class="fixed w-48 bg-gray-800 dark:bg-gray-800 rounded-md shadow-lg border border-gray-700 dark:border-gray-600 z-[9999] py-1"
                    :style="floatingStyle"
                    @mouseenter="handleMouseEnter"
                    @mouseleave="handleMouseLeave"
                >
                    <!-- Invisible bridge to prevent hover loss (handled by delay, but kept for safe zone) -->
                    <div class="absolute -left-4 top-0 w-4 h-full bg-transparent"></div>
                    
                    <div class="px-4 py-2 border-b border-gray-700 dark:border-gray-600 font-bold text-white text-sm bg-gray-900 dark:bg-gray-900 rounded-t-md">
                        {{ item.label }}
                    </div>
                    <router-link
                        v-for="child in item.children"
                        :key="child.key"
                        :to="getChildRoute(child)"
                        :class="[
                            'block px-4 py-2 text-sm transition duration-200',
                            isChildActive(child) ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                        ]"
                        @click="onNavClick"
                    >
                        {{ child.label }}
                    </router-link>
                </div>
            </Teleport>
        </div>

        <!-- Menu Item without Children (Simple Link) -->
        <router-link
            v-else
            :to="getItemRoute()"
            :class="[
                'flex items-center px-4 py-2.5 rounded transition duration-200',
                isItemActive() ? 'bg-gray-900 dark:bg-gray-700 text-white' : 'text-gray-300 dark:text-gray-400 hover:bg-gray-700 dark:hover:bg-gray-700 hover:text-white',
                collapsed ? 'justify-center' : ''
            ]"
            :title="collapsed ? item.label : ''"
            @click="onNavClick"
        >
            <svg
                v-if="item.icon"
                class="w-5 h-5 flex-shrink-0"
                :class="[collapsed ? '' : 'mr-3']"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    v-if="getIconPath(item.icon)"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    :d="getIconPath(item.icon) || undefined"
                />
                <circle v-else cx="12" cy="12" r="9" stroke-width="2" />
            </svg>
            <span v-if="!collapsed" class="truncate">{{ item.label }}</span>
            
            <!-- Tooltip for Simple Link (Collapsed Mode) - Teleported -->
            <Teleport to="body">
                <div 
                    v-if="collapsed && isHovered" 
                    class="fixed ml-2 w-auto whitespace-nowrap bg-gray-900 text-white text-xs px-2 py-1 rounded shadow-lg z-[9999]"
                    :style="{ top: tooltipTop + 'px', left: tooltipLeft + 'px' }"
                >
                    {{ item.label }}
                </div>
            </Teleport>
        </router-link>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute, RouteLocationRaw } from 'vue-router';

interface MenuChild {
    key: string;
    label: string;
    route?: string;
    routeParams?: Record<string, any>;
    urlParams?: Record<string, any>;
    icon?: string | null;
    order?: number;
}

interface MenuItemData {
    key: string;
    label: string;
    route?: string;
    routeParams?: Record<string, any>;
    urlParams?: Record<string, any>;
    icon?: string;
    order?: number;
    children?: MenuChild[];
}

interface Props {
    item: MenuItemData;
    route: any;
    collapsed?: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'nav-click'): void;
}>();

const onNavClick = () => {
    emit('nav-click');
};

const isOpen = ref(false);
const isHovered = ref(false);
const itemRef = ref<HTMLElement | null>(null);
const floatingStyle = ref({ top: '0px', left: '0px' });
const tooltipTop = ref(0);
const tooltipLeft = ref(0);
let hoverTimeout: any = null;

const handleMouseEnter = () => {
    if (!props.collapsed) return;
    
    if (hoverTimeout) {
        clearTimeout(hoverTimeout);
        hoverTimeout = null;
    }
    
    isHovered.value = true;
    
    if (itemRef.value) {
        const rect = itemRef.value.getBoundingClientRect();
        // Position submenu to the right of the item
        floatingStyle.value = {
            top: `${rect.top}px`,
            left: `${rect.right + 4}px` // +4px for gap
        };
        // Position tooltip centered vertically relative to item
        tooltipTop.value = rect.top + (rect.height / 2) - 10; // Approx center adjustment
        tooltipLeft.value = rect.right;
    }
};

const handleMouseLeave = () => {
    if (!props.collapsed) return;
    
    hoverTimeout = setTimeout(() => {
        isHovered.value = false;
    }, 100); // Small delay to allow moving to submenu
};

const ICON_MAP: Record<string, string> = {
    'sparkles': 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
    'dashboard': 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
};

const getIconPath = (icon: string | null | undefined): string | null => {
    if (!icon) return null;
    if (icon.startsWith('M') || icon.startsWith('m')) return icon;
    return ICON_MAP[icon] || null;
};

// Check if menu should be open based on route
const checkRouteMatch = () => {
    if (!props.item.children || props.item.children.length === 0) {
        return;
    }

    // Check if any child route matches current route
    const hasActiveChild = props.item.children.some(child => isChildActive(child));

    if (hasActiveChild) {
        isOpen.value = true;
    }
};

const toggleMenu = () => {
    isOpen.value = !isOpen.value;
};

const getItemRoute = (): RouteLocationRaw => {
    if (!props.item.route) {
        return { name: 'admin.dashboard' };
    }

    const routeConfig: RouteLocationRaw = { name: props.item.route };
    
    if (props.item.routeParams) {
        routeConfig.query = props.item.routeParams;
    }
    
    // Add support for true URL params for dynamic routes (e.g. /settings/:group)
    if (props.item.urlParams) {
        routeConfig.params = props.item.urlParams;
    }

    return routeConfig;
};

const getChildRoute = (child: MenuChild): RouteLocationRaw => {
    if (!child.route) {
        return { name: 'admin.dashboard' };
    }

    const routeConfig: RouteLocationRaw = { name: child.route };
    
    if (child.routeParams) {
        routeConfig.query = child.routeParams;
    }
    
    // Add support for true URL params
    if (child.urlParams) {
        routeConfig.params = child.urlParams;
    }

    return routeConfig;
};

const isItemActive = (): boolean => {
    if (!props.item.route) {
        return false;
    }

    const routeName = props.route.name;
    
    if (routeName === props.item.route) {
        if (props.item.urlParams) {
            for (const key in props.item.urlParams) {
                if (String(props.route.params?.[key]) !== String(props.item.urlParams[key])) {
                    return false;
                }
            }
        }

        // If item has routeParams, check them
        if (props.item.routeParams) {
            const routeType = props.route.query?.type;
            if (props.item.routeParams.type && routeType !== props.item.routeParams.type) {
                return false;
            }
        }
        return true;
    }

    return false;
};

const isChildActive = (child: MenuChild): boolean => {
    if (!child.route) {
        return false;
    }

    const routeName = props.route.name;
    const routeType = props.route.query?.type;

    const urlParamsMatch = () => {
        if (!child.urlParams) return true;
        for (const key in child.urlParams) {
            if (String(props.route.params?.[key]) !== String(child.urlParams[key])) {
                return false;
            }
        }
        return true;
    };

    // 1. Check exact route match
    if (child.route === routeName) {
        if (!urlParamsMatch()) return false;

        // Also check routeParams for categories
        if (child.routeParams?.type && routeType === child.routeParams.type) {
            return true;
        }
        if (!child.routeParams || Object.keys(child.routeParams).length === 0) {
            return true;
        }
        return false;
    }

    // 2. Check if route name starts with child route (for nested routes)
    if (typeof routeName === 'string' && routeName.toString().startsWith(child.route)) {
        if (!urlParamsMatch()) return false;

        if (child.routeParams?.type && routeType === child.routeParams.type) {
            return true;
        }
        if (!child.routeParams || Object.keys(child.routeParams).length === 0) {
            return true;
        }
        return false;
    }

    // 3. For index routes, check if current route belongs to same group
    // BUT only if no other sibling is an exact match
    if (child.route.endsWith('.index')) {
        const routePrefix = child.route.replace('.index', '');
        if (typeof routeName === 'string' && routeName.toString().startsWith(routePrefix)) {
            if (!urlParamsMatch()) return false;

            // Check specificity: is there another sibling that matches exactly?
            const hasBetterSiblingMatch = props.item.children?.some(sibling => {
                if (sibling.key === child.key || !sibling.route) return false;
                
                // If sibling matches exactly, it wins
                if (sibling.route === routeName) {
                    const siblingUrlParamsMatch = () => {
                        if (!sibling.urlParams) return true;
                        for (const key in sibling.urlParams) {
                            if (String(props.route.params?.[key]) !== String(sibling.urlParams[key])) {
                                return false;
                            }
                        }
                        return true;
                    };
                    if (!siblingUrlParamsMatch()) return false;

                    if (sibling.routeParams?.type && routeType === sibling.routeParams.type) return true;
                    if (!sibling.routeParams || Object.keys(sibling.routeParams).length === 0) return true;
                }
                return false;
            });

            if (!hasBetterSiblingMatch) {
                if (child.routeParams?.type && routeType === child.routeParams.type) {
                    return true;
                }
                if (!child.routeParams || Object.keys(child.routeParams).length === 0) {
                    return true;
                }
            }
        }
    }

    return false;
};

// Watch route changes
watch(() => [props.route.name, props.route.query], () => {
    checkRouteMatch();
}, { immediate: true });

onMounted(() => {
    checkRouteMatch();
});
</script>

<style scoped>
/* Touch-friendly sidebar menu items on mobile */
@media (max-width: 1023px) {
    :deep(a), :deep(button) {
        min-height: 44px;
        display: flex;
        align-items: center;
    }
}
</style>
