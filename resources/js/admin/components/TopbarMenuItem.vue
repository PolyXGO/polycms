<template>
    <div 
        v-if="hasChildren" 
        :id="item.id"
        class="topbar-dropdown" 
        :class="[
            { 'submenu': isSubmenu },
            { 'touch-open': mobileOpen },
            alignmentClass
        ]"
        @mouseenter="checkAlignment"
        ref="dropdownRoot"
    >
        <a 
            v-if="item.method !== 'POST'"
            :href="item.url || '#'" 
            :class="[highlightClass, isSubmenu ? 'topbar-dropdown-item has-arrow' : '']" 
            :style="{ paddingLeft: isSubmenu && depth > 0 ? (16 + depth * 12) + 'px' : '16px' }"
            @click="handleDropdownTriggerClick($event, item)"
        >
            <TopbarIcon v-if="item.icon" :icon="item.icon" />
            <span>{{ item.label }}</span>
            <svg 
                v-if="isSubmenu" 
                class="submenu-arrow transition-transform" 
                :class="{ 'rotate-180': alignmentClass.includes('align-right') }"
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </a>
        <form
            v-else
            :action="item.url"
            method="POST"
            :class="isSubmenu ? '' : 'topbar-form'"
            @submit.prevent="handleSubmit(item.url)"
        >
            <button
                type="submit"
                :class="[isSubmenu ? 'topbar-dropdown-item has-arrow' : 'topbar-button', highlightClass]"
            >
                <TopbarIcon v-if="item.icon" :icon="item.icon" />
                <span>{{ item.label }}</span>
                <svg 
                    v-if="isSubmenu" 
                    class="submenu-arrow transition-transform" 
                    :class="{ 'rotate-180': alignmentClass.includes('align-right') }"
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </form>

        <div ref="dropdownContent" class="topbar-dropdown-content">
            <template v-for="child in item.children" :key="child.id">
                <TopbarMenuItem 
                    :item="child" 
                    :is-submenu="true" 
                    :momentum="currentMomentum"
                    :depth="depth + 1"
                />
            </template>
        </div>
    </div>
    <form
        v-else-if="item.method === 'POST'"
        :action="item.url"
        method="POST"
        :class="isSubmenu ? '' : 'topbar-form'"
        @submit.prevent="handleSubmit(item.url)"
    >
        <button
            type="submit"
            :id="item.id"
            :class="[isSubmenu ? 'topbar-dropdown-item' : 'topbar-button', highlightClass]"
        >
            <TopbarIcon v-if="item.icon" :icon="item.icon" />
            <span>{{ item.label }}</span>
        </button>
    </form>
    <a
        v-else
        :id="item.id"
        :href="item.url"
        :class="[highlightClass, isSubmenu ? 'topbar-dropdown-item' : '']"
        :style="{ paddingLeft: isSubmenu && depth > 0 ? (16 + depth * 12) + 'px' : '16px' }"
        @click="handleItemClick($event, item)"
    >
        <TopbarIcon v-if="item.icon" :icon="item.icon" />
        <span>{{ item.label }}</span>
    </a>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import TopbarIcon from './TopbarIcon.vue';
import TopbarMenuItem from './TopbarMenuItem.vue';

interface MenuItem {
    id: string;
    label: string;
    url?: string;
    icon?: string;
    highlight?: boolean;
    children?: MenuItem[];
    method?: 'GET' | 'POST';
}

interface Props {
    item: MenuItem;
    isSubmenu?: boolean;
    momentum?: 'left' | 'right';
    depth?: number;
}

const props = withDefaults(defineProps<Props>(), {
    momentum: 'right',
    depth: 0
});

const router = useRouter();
const authStore = useAuthStore();

// Touch device detection
const isTouchDevice = ref(false);
const mobileOpen = ref(false);
const dropdownRoot = ref<HTMLElement | null>(null);

// Unique ID for this dropdown instance
const dropdownId = `topbar-dd-${Math.random().toString(36).substr(2, 9)}`;

const detectTouch = () => {
    isTouchDevice.value = window.matchMedia('(hover: none) and (pointer: coarse)').matches;
};

// Toggle dropdown on click (works for both desktop and mobile)
const handleDropdownTriggerClick = (event: MouseEvent, item: MenuItem) => {
    if (hasChildren.value) {
        event.preventDefault();
        const willOpen = !mobileOpen.value;
        mobileOpen.value = willOpen;

        if (willOpen) {
            checkAlignment();
            // Notify all other dropdowns to close
            window.dispatchEvent(new CustomEvent('topbar-dropdown-open', { detail: dropdownId }));
        }
        return;
    }
    // No children: fall through to normal click behavior
    handleItemClick(event, item);
};

// Close when another dropdown opens
const handleOtherDropdownOpen = (event: Event) => {
    const customEvent = event as CustomEvent;
    if (customEvent.detail !== dropdownId && mobileOpen.value) {
        mobileOpen.value = false;
    }
};

// Close on outside click
const handleOutsideClick = (event: Event) => {
    if (!mobileOpen.value) return;
    const target = event.target as HTMLElement;
    if (dropdownRoot.value && !dropdownRoot.value.contains(target)) {
        mobileOpen.value = false;
    }
};

onMounted(() => {
    detectTouch();
    window.addEventListener('resize', detectTouch);
    document.addEventListener('click', handleOutsideClick);
    window.addEventListener('topbar-dropdown-open', handleOtherDropdownOpen);
});

onUnmounted(() => {
    window.removeEventListener('resize', detectTouch);
    document.removeEventListener('click', handleOutsideClick);
    window.removeEventListener('topbar-dropdown-open', handleOtherDropdownOpen);
});

const hasChildren = computed(() => {
    return !!(props.item.children && props.item.children.length > 0);
});

const highlightClass = computed(() => {
    return props.item.highlight ? 'topbar-highlight' : '';
});

const dropdownContent = ref<HTMLElement | null>(null);
const alignmentClass = ref('');
const currentMomentum = ref(props.momentum);

const checkAlignment = () => {
    if (!dropdownContent.value) return;

    // Reset alignment class to measure standard position based on inherited momentum
    alignmentClass.value = currentMomentum.value === 'left' ? 'align-right' : '';

    // Wait for DOM to reflect (show) to measure
    requestAnimationFrame(() => {
        if (!dropdownContent.value) return;

        const rect = dropdownContent.value.getBoundingClientRect();
        const winWidth = window.innerWidth;
        const winHeight = window.innerHeight;

        let vertical = '';
        let horizontal = '';

        // Check horizontal overflow and flip momentum if needed
        const initialMomentum = props.momentum;
        if (currentMomentum.value === 'right' && rect.right > winWidth) {
            currentMomentum.value = 'left';
        } else if (currentMomentum.value === 'left' && rect.left < 0) {
            currentMomentum.value = 'right';
        }

        // Apply alignment based on updated momentum
        // Rule: Only drop down (align-below) when we flip direction (start a new row)
        // or when moving left to ensure we don't overlap the parent if flyout isn't possible.
        // Actually, following the "snake" diagram: flip point = down, others = flyout.
        if (currentMomentum.value === 'left') {
            horizontal = 'align-right';
        } else {
            horizontal = ''; 
        }

        if (props.isSubmenu && initialMomentum !== currentMomentum.value) {
            vertical = 'align-below';
        }

        // Check vertical overflow (if item hits bottom)
        if (rect.bottom > winHeight) {
            vertical = 'align-below';
        }

        alignmentClass.value = `${horizontal} ${vertical} momentum-${currentMomentum.value}`.trim();
    });
};

const handleItemClick = (event: MouseEvent, item: MenuItem) => {
    // If it's a POST request or if searching for '#' let the default happen or be handled by handleSubmit
    if (item.method === 'POST' || !item.url || item.url === '#') {
        return;
    }

    // Check if the URL is internal to the SPA
    const currentOrigin = window.location.origin;
    if (item.url.startsWith(currentOrigin) || item.url.startsWith('/')) {
        const path = item.url.startsWith(currentOrigin) 
            ? item.url.substring(currentOrigin.length) 
            : item.url;
        
        // Only use router if it's an admin path
        if (path.startsWith('/admin')) {
            event.preventDefault();
            router.push(path);
        }
    }
};

const handleSubmit = async (url?: string) => {
    if (!url) return;

    // If it's a logout, use the auth store for unified logout logic
    if (url.includes('logout')) {
        await authStore.logout();
        return;
    }

    // For other POST requests, use fetch
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Content-Type': 'application/json',
            },
        });

        if (response.redirected) {
            window.location.href = response.url;
        } else if (response.ok) {
            // Reload page or handle success
            window.location.reload();
        }
    } catch (error) {
        console.error('Form submission error:', error);
    }
};
</script>

<style scoped>
.topbar-dropdown {
    position: relative;
    flex-shrink: 0;
    overflow: visible;
}

/* Touch-open state: show dropdown via click toggle */
.topbar-dropdown.touch-open > .topbar-dropdown-content {
    display: flex !important;
    flex-direction: column !important;
    opacity: 1 !important;
    visibility: visible !important;
}

/* Mobile: inline submenus instead of flyout */
@media (max-width: 767px) {
    .topbar-dropdown.submenu > .topbar-dropdown-content {
        position: relative !important;
        top: auto !important;
        left: auto !important;
        right: auto !important;
        margin: 0 !important;
        box-shadow: none !important;
        border: none !important;
        border-radius: 0 !important;
        padding-left: 12px !important;
        background: rgba(255, 255, 255, 0.03) !important;
    }

    .topbar-dropdown-item {
        min-height: 44px !important;
    }
}

/* Hover dropdown — desktop only (≥1024px), mobile uses click toggle */
@media (min-width: 1024px) {
    .topbar-dropdown:hover > a ~ .topbar-dropdown-content,
    .topbar-dropdown .topbar-dropdown-content:hover {
        display: block;
        opacity: 1;
        visibility: visible;
    }
}

.topbar-dropdown > a {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: flex-start !important;
    white-space: nowrap !important;
    flex-shrink: 0 !important;
    overflow: visible !important;
    gap: 6px !important;
    line-height: 32px !important;
    vertical-align: middle !important;
}

.topbar-dropdown-content {
    display: none;
    opacity: 0;
    visibility: hidden;
    position: absolute;
    top: 100%;
    margin-top: 4px;
    background: #1f2937;
    min-width: 200px;
    max-width: 300px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    border-radius: 6px;
    padding: 6px 0;
    z-index: 100000;
    transition: opacity 0.15s ease, visibility 0.15s ease;
    border: 1px solid rgba(255, 255, 255, 0.08);
    left: 0;
    right: auto;
}

/* Quick Access Menu Special layout - Restoring vertical flow for siblings */
[id="quick-access-menu"] .topbar-dropdown-content,
[id="quick-access-menu"] .topbar-dropdown-content .topbar-dropdown-content {
    max-width: 300px; /* Restored more standard max width */
    width: max-content;
    min-width: 200px !important;
    padding: 6px 0; /* Standard vertical padding */
}

[id="quick-access-menu"]:hover > .topbar-dropdown-content,
[id="quick-access-menu"] .topbar-dropdown:hover > .topbar-dropdown-content {
    display: flex !important;
    flex-direction: column !important; /* Forces items below each other */
    flex-wrap: nowrap !important;
    gap: 0;
}

[id="quick-access-menu"] .topbar-dropdown-item:not([id="quick-item-add-new"]),
[id="quick-access-menu"] .topbar-dropdown {
    width: 100% !important;
    flex-shrink: 0;
}

/* Ensure "Add new link" stays at the bottom and full width with adjusted padding */
[id="quick-item-add-new"] {
    flex-basis: 100% !important;
    width: 100% !important;
    order: 9999 !important;
    border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
    margin-top: 4px !important;
    padding: 8px 16px !important;
}

/* Zig-zag momentum: flow items in the current direction */
.topbar-dropdown.momentum-left > .topbar-dropdown-content {
    flex-direction: row-reverse !important;
}

/* If momentum is left, children will naturally start from the right */

[id="quick-access-menu"] .topbar-dropdown-item,
[id="quick-access-menu"] .topbar-dropdown {
    width: auto !important;
    flex-shrink: 0;
}

/* Flyout for submenus */
.topbar-dropdown.submenu .topbar-dropdown-content {
    top: 0;
    left: 100%;
    margin-top: -6px;
}

/* Create invisible bridge between trigger and dropdown to prevent hiding on gap */
.topbar-dropdown-content::before {
    content: '';
    position: absolute;
    bottom: 100%;
    left: 0;
    right: 0;
    height: 14px; /* Standard bridge height (gap is usually 4px-12px) */
    background: transparent;
}

/* Ensure the bridge is tall enough for the 12px gap when aligned below */
.topbar-dropdown.align-below > .topbar-dropdown-content::before {
    height: 14px; /* Covers the 12px margin-top */
    bottom: 100%;
}

.topbar-dropdown.submenu > .topbar-dropdown-content::before {
    bottom: auto;
    left: auto;
    right: 100%;
    top: 0;
    width: 14px;
    height: 100%;
}

/* If flyout is to the left (align-right), the bridge must be on the right side of the content */
.topbar-dropdown.submenu.align-right > .topbar-dropdown-content::before {
    right: auto;
    left: 100%;
}

/* If it's a submenu AND aligned below, the bridge MUST be on top */
.topbar-dropdown.submenu.align-below > .topbar-dropdown-content::before {
    width: 100%;
    height: 14px;
    bottom: 100%;
    top: auto;
    left: 0;
    right: 0;
}

/* Show dropdown when hovering over trigger or dropdown itself */
.topbar-dropdown:hover > .topbar-dropdown-content,
.topbar-dropdown-content:hover {
    display: flex;
    flex-direction: column;
    opacity: 1;
    visibility: visible;
}

/* Dynamic Alignment Classes */
.topbar-dropdown.align-right > .topbar-dropdown-content {
    left: auto !important;
    right: 100% !important;
    margin-right: 12px;
}

.topbar-dropdown.align-below > .topbar-dropdown-content {
    top: 100% !important;
    left: 0 !important;
    right: auto !important;
    margin-top: 12px !important;
    margin-left: 0 !important;
    position: absolute !important;
}

/* If both, stick to the right edge but stay below */
.topbar-dropdown.align-below.align-right > .topbar-dropdown-content {
    left: auto !important;
    right: 0 !important;
}

/* Submenu specific flyout behavior */
.topbar-dropdown.submenu:not(.align-below) > .topbar-dropdown-content {
    top: 0 !important;
    left: 100% !important;
    margin-top: -6px !important;
    margin-left: 0px !important; /* Managed by bridge */
}

/* Ensure hover chain works deep into multi-level */
.topbar-dropdown:hover > .topbar-dropdown-content,
.topbar-dropdown.submenu:hover > .topbar-dropdown-content {
    display: flex !important;
    opacity: 1 !important;
    visibility: visible !important;
}

.topbar-dropdown.submenu.align-right:not(.align-below) > .topbar-dropdown-content {
    left: auto;
    right: 100%;
    margin-right: 2px;
}

.topbar-dropdown-item {
    background: transparent !important;
    border: none !important;
    width: 100% !important;
    text-align: left !important;
    cursor: pointer !important;
    padding: 10px 16px !important;
    color: #d1d5db !important;
    display: flex !important;
    align-items: center !important;
    justify-content: flex-start !important;
    gap: 10px !important;
    font-size: 13px !important;
    font-weight: 400 !important;
    font-family: inherit !important;
    line-height: 1.5 !important;
    text-decoration: none !important;
    height: auto !important;
    border-radius: 0 !important;
    margin: 0 !important;
    transition: all 0.15s ease !important;
    box-sizing: border-box !important;
}

.topbar-dropdown-item:hover {
    background: rgba(255, 255, 255, 0.1) !important;
    color: #60a5fa !important;
}

.topbar-dropdown-item.has-arrow .submenu-arrow {
    width: 12px;
    height: 12px;
    margin-left: auto;
    opacity: 0.5;
}

.topbar-dropdown-item:first-child {
    border-radius: 6px 6px 0 0 !important;
}

.topbar-dropdown-item:last-child {
    border-radius: 0 0 6px 6px !important;
}

/* Single item dropdown - fully rounded */
.topbar-dropdown-item:only-child {
    border-radius: 6px !important;
}

/* Form styling */
.topbar-form {
    margin: 0;
    display: inline;
    padding: 0;
}

.topbar-dropdown-form {
    margin: 0;
    padding: 0;
}

.topbar-button {
    background: transparent !important;
    border: none !important;
    color: #d1d5db !important;
    padding: 0 10px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: flex-start !important;
    gap: 6px !important;
    border-radius: 3px !important;
    cursor: pointer !important;
    font-size: 13px !important;
    font-weight: 400 !important;
    font-family: inherit !important;
    line-height: 32px !important;
    height: 32px !important;
    min-height: 32px !important;
    max-height: 32px !important;
    white-space: nowrap !important;
    transition: all 0.15s ease !important;
    flex-shrink: 0 !important;
    overflow: visible !important;
    box-sizing: border-box !important;
    vertical-align: middle !important;
}

.topbar-button:hover {
    color: #60a5fa !important;
    background: rgba(255, 255, 255, 0.1) !important;
}
</style>

