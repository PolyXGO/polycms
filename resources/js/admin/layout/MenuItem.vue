<template>
    <div>
        <!-- Menu Item with Children (Collapsible) -->
        <div v-if="item.children && item.children.length > 0">
            <button
                @click="toggleMenu"
                :class="[
                    'w-full flex items-center justify-between px-4 py-2.5 rounded transition duration-200',
                    isOpen ? 'bg-gray-900 dark:bg-gray-700 text-white' : 'text-gray-300 dark:text-gray-400 hover:bg-gray-700 dark:hover:bg-gray-700 hover:text-white'
                ]"
            >
                <div class="flex items-center">
                    <svg
                        v-if="item.icon"
                        class="w-5 h-5 mr-3"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            :d="item.icon"
                        />
                    </svg>
                    <span class="font-medium">{{ item.label }}</span>
                </div>
                <svg
                    class="w-4 h-4 transition-transform duration-200"
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
            <div v-show="isOpen" class="ml-4 mt-1 space-y-1">
                <router-link
                    v-for="child in item.children"
                    :key="child.key"
                    :to="getChildRoute(child)"
                    :class="[
                        'block px-4 py-2 rounded transition duration-200',
                        isChildActive(child) ? 'bg-gray-900 dark:bg-gray-700 text-white' : 'text-gray-300 dark:text-gray-400 hover:bg-gray-700 dark:hover:bg-gray-700 hover:text-white'
                    ]"
                >
                    {{ child.label }}
                </router-link>
            </div>
        </div>

        <!-- Menu Item without Children (Simple Link) -->
        <router-link
            v-else
            :to="getItemRoute()"
            :class="[
                'flex items-center px-4 py-2.5 rounded transition duration-200',
                isItemActive() ? 'bg-gray-900 dark:bg-gray-700 text-white' : 'text-gray-300 dark:text-gray-400 hover:bg-gray-700 dark:hover:bg-gray-700 hover:text-white'
            ]"
        >
            <svg
                v-if="item.icon"
                class="w-5 h-5 mr-3"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    :d="item.icon"
                />
            </svg>
            {{ item.label }}
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
    icon?: string | null;
    order?: number;
}

interface MenuItemData {
    key: string;
    label: string;
    route?: string;
    routeParams?: Record<string, any>;
    icon?: string;
    order?: number;
    children?: MenuChild[];
}

interface Props {
    item: MenuItemData;
    route: any;
}

const props = defineProps<Props>();

const isOpen = ref(false);

// Check if menu should be open based on route
const checkRouteMatch = () => {
    const routeName = props.route.name;
    const routeType = props.route.query?.type;

    if (!props.item.children || props.item.children.length === 0) {
        return;
    }

    // Check if any child route matches current route
    const hasActiveChild = props.item.children.some(child => {
        if (!child.route) return false;

        // Check exact route match
        if (child.route === routeName) {
            // Also check routeParams for categories
            if (child.routeParams?.type && routeType === child.routeParams.type) {
                return true;
            }
            if (!child.routeParams || Object.keys(child.routeParams).length === 0) {
                return true;
            }
            return false;
        }

        // Check if route name starts with child route (for nested routes)
        if (typeof routeName === 'string' && routeName.toString().startsWith(child.route)) {
            if (child.routeParams?.type && routeType === child.routeParams.type) {
                return true;
            }
            if (!child.routeParams || Object.keys(child.routeParams).length === 0) {
                return true;
            }
            return false;
        }

        return false;
    });

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

    return routeConfig;
};

const isItemActive = (): boolean => {
    if (!props.item.route) {
        return false;
    }

    const routeName = props.route.name;
    
    if (routeName === props.item.route) {
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

    // Check exact route match
    if (child.route === routeName) {
        // Also check routeParams for categories
        if (child.routeParams?.type && routeType === child.routeParams.type) {
            return true;
        }
        if (!child.routeParams || Object.keys(child.routeParams).length === 0) {
            return true;
        }
        return false;
    }

    // Check if route name starts with child route (for nested routes)
    if (typeof routeName === 'string' && routeName.toString().startsWith(child.route)) {
        if (child.routeParams?.type && routeType === child.routeParams.type) {
            return true;
        }
        if (!child.routeParams || Object.keys(child.routeParams).length === 0) {
            return true;
        }
        return false;
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
