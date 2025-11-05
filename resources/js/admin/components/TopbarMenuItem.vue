<template>
    <div v-if="hasChildren" class="topbar-dropdown">
        <a href="#" :class="highlightClass">
            <TopbarIcon v-if="item.icon" :icon="item.icon" />
            <span>{{ item.label }}</span>
        </a>
        <div class="topbar-dropdown-content">
            <template v-for="child in item.children" :key="child.id">
                <form
                    v-if="child.method === 'POST'"
                    :action="child.url"
                    method="POST"
                    style="margin: 0;"
                    @submit.prevent="handleSubmit(child.url)"
                >
                    <button
                        type="submit"
                        class="topbar-dropdown-item"
                    >
                        <TopbarIcon v-if="child.icon" :icon="child.icon" />
                        <span>{{ child.label }}</span>
                    </button>
                </form>
                <a
                    v-else
                    :href="child.url"
                    class="topbar-dropdown-item"
                >
                    <TopbarIcon v-if="child.icon" :icon="child.icon" />
                    <span>{{ child.label }}</span>
                </a>
            </template>
        </div>
    </div>
    <form
        v-else-if="item.method === 'POST'"
        :action="item.url"
        method="POST"
        style="margin: 0; display: inline;"
        @submit.prevent="handleSubmit(item.url)"
    >
        <button
            type="submit"
            :class="['topbar-button', highlightClass]"
        >
            <TopbarIcon v-if="item.icon" :icon="item.icon" />
            <span>{{ item.label }}</span>
        </button>
    </form>
    <a
        v-else
        :href="item.url"
        :class="highlightClass"
    >
        <TopbarIcon v-if="item.icon" :icon="item.icon" />
        <span>{{ item.label }}</span>
    </a>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import TopbarIcon from './TopbarIcon.vue';

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
}

const props = defineProps<Props>();
const router = useRouter();

const hasChildren = computed(() => {
    return !!(props.item.children && props.item.children.length > 0);
});

const highlightClass = computed(() => {
    return props.item.highlight ? 'topbar-highlight' : '';
});

const handleSubmit = async (url?: string) => {
    if (!url) return;
    
    // If it's a logout, handle specially
    if (url.includes('logout')) {
        // This will be handled by the auth store
        window.location.href = url;
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

/* Ensure dropdown stays visible when hovering over the bridge area */
.topbar-dropdown:hover > a ~ .topbar-dropdown-content,
.topbar-dropdown .topbar-dropdown-content:hover {
    display: block;
    opacity: 1;
    visibility: visible;
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
    margin-top: 0;
    padding-top: 4px;
    background: transparent;
    min-width: 200px;
    z-index: 100000;
    /* Default: left items align to left edge */
    left: 0;
    right: auto;
    pointer-events: none;
}

/* The actual dropdown menu */
.topbar-dropdown-content::after {
    content: '';
    display: block;
    background: #1f2937;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    border-radius: 6px;
    padding: 6px 0;
    border: 1px solid rgba(255, 255, 255, 0.08);
    pointer-events: auto;
}

/* Show dropdown when hovering over trigger or dropdown itself */
.topbar-dropdown:hover .topbar-dropdown-content,
.topbar-dropdown-content:hover {
    display: block;
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
}

/* Delay hiding to allow mouse movement */
.topbar-dropdown-content {
    transition: opacity 0.1s ease 0.05s, visibility 0.1s ease 0.05s;
}

.topbar-dropdown-item {
    background: none;
    border: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
    padding: 10px 16px;
    color: #d1d5db;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    text-decoration: none;
    height: auto;
    border-radius: 0;
    transition: all 0.15s ease;
}

.topbar-dropdown-item:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #60a5fa;
}

.topbar-dropdown-item:first-child {
    border-radius: 4px 4px 0 0;
}

.topbar-dropdown-item:last-child {
    border-radius: 0 0 4px 4px;
}

.topbar-button {
    background: none;
    border: none;
    color: #d1d5db;
    padding: 0 10px;
    display: inline-flex;
    align-items: center;
    justify-content: flex-start;
    gap: 6px;
    border-radius: 3px;
    cursor: pointer;
    font-size: 13px;
    line-height: 32px;
    height: 32px;
    min-height: 32px;
    max-height: 32px;
    font-weight: 400;
    white-space: nowrap;
    transition: all 0.15s ease;
    flex-shrink: 0;
    overflow: visible;
    box-sizing: border-box;
    vertical-align: middle;
}

.topbar-button:hover {
    color: #60a5fa;
    background: rgba(255, 255, 255, 0.1);
}
</style>

