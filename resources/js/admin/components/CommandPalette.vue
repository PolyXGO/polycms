<template>
    <Teleport to="body">
        <transition name="palette-fade">
            <div v-if="isOpen" class="command-palette-overlay" @click.self="close">
                <div class="command-palette" @keydown.escape="close">
                    <div class="command-palette__search">
                        <svg class="command-palette__search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            ref="inputRef"
                            v-model="query"
                            type="text"
                            class="command-palette__input"
                            :placeholder="$t('Search products, orders, posts, users...')"
                            @input="debouncedSearch"
                            @keydown.down.prevent="moveDown"
                            @keydown.up.prevent="moveUp"
                            @keydown.enter.prevent="selectCurrent"
                        />
                        <kbd class="command-palette__kbd">ESC</kbd>
                    </div>

                    <div class="command-palette__results" v-if="results.length || loading">
                        <div v-if="loading" class="command-palette__loading">
                            <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        <template v-else>
                            <div
                                v-for="(result, index) in results"
                                :key="`${result.type}-${result.id}`"
                                class="command-palette__item"
                                :class="{ 'command-palette__item--active': index === activeIndex }"
                                @click="navigate(result)"
                                @mouseenter="activeIndex = index"
                            >
                                <div class="command-palette__item-icon" :class="`command-palette__item-icon--${result.type}`">
                                    <svg v-if="result.type === 'product'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    <svg v-else-if="result.type === 'order'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    <svg v-else-if="result.type === 'post' || result.type === 'page'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <svg v-else-if="result.type === 'user'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                                <div class="command-palette__item-body">
                                    <span class="command-palette__item-title">{{ result.title }}</span>
                                    <span class="command-palette__item-subtitle" v-if="result.subtitle">{{ result.subtitle }}</span>
                                </div>
                                <span class="command-palette__item-type">{{ result.type }}</span>
                            </div>
                        </template>
                    </div>

                    <div v-else-if="query.length >= 2 && !loading" class="command-palette__empty">
                        {{ $t('No results found') }}
                    </div>

                    <div class="command-palette__footer">
                        <span><kbd>↑↓</kbd> {{ $t('Navigate') }}</span>
                        <span><kbd>↵</kbd> {{ $t('Open') }}</span>
                        <span><kbd>ESC</kbd> {{ $t('Close') }}</span>
                    </div>
                </div>
            </div>
        </transition>
    </Teleport>
</template>

<script setup lang="ts">
import { ref, nextTick, onMounted, onUnmounted, getCurrentInstance } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || ((s: string) => s);

const router = useRouter();
const isOpen = ref(false);
const query = ref('');
const results = ref<any[]>([]);
const loading = ref(false);
const activeIndex = ref(0);
const inputRef = ref<HTMLInputElement | null>(null);

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const open = () => {
    isOpen.value = true;
    query.value = '';
    results.value = [];
    activeIndex.value = 0;
    nextTick(() => inputRef.value?.focus());
};

const close = () => {
    isOpen.value = false;
};

const toggle = () => {
    isOpen.value ? close() : open();
};

const debouncedSearch = () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(search, 300);
};

const search = async () => {
    if (query.value.length < 2) {
        results.value = [];
        return;
    }
    loading.value = true;
    try {
        const { data } = await axios.get('/api/v1/search', { params: { q: query.value } });
        results.value = data.results || [];
        activeIndex.value = 0;
    } catch (e) {
        console.error('Search failed:', e);
    } finally {
        loading.value = false;
    }
};

const navigate = (result: any) => {
    close();
    router.push(result.url);
};

const moveDown = () => {
    if (activeIndex.value < results.value.length - 1) activeIndex.value++;
};

const moveUp = () => {
    if (activeIndex.value > 0) activeIndex.value--;
};

const selectCurrent = () => {
    if (results.value[activeIndex.value]) {
        navigate(results.value[activeIndex.value]);
    }
};

const handleKeydown = (e: KeyboardEvent) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        toggle();
    }
};

onMounted(() => document.addEventListener('keydown', handleKeydown));
onUnmounted(() => document.removeEventListener('keydown', handleKeydown));

defineExpose({ open, close, toggle });
</script>

<style scoped>
.command-palette-overlay {
    position: fixed;
    inset: 0;
    z-index: 999999;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding-top: 15vh;
}

.command-palette {
    width: 100%;
    max-width: 620px;
    background: #1e293b;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 16px;
    box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.5);
    overflow: hidden;
}

.command-palette__search {
    display: flex;
    align-items: center;
    padding: 14px 18px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    gap: 12px;
}

.command-palette__search-icon {
    width: 20px;
    height: 20px;
    color: #94a3b8;
    flex-shrink: 0;
}

.command-palette__input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    font-size: 15px;
    color: #f1f5f9;
    font-family: inherit;
}

.command-palette__input::placeholder { color: #64748b; }

.command-palette__kbd {
    font-size: 10px;
    padding: 2px 6px;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 4px;
    color: #64748b;
    font-family: monospace;
}

.command-palette__results {
    max-height: 360px;
    overflow-y: auto;
    padding: 6px;
}

.command-palette__loading {
    display: flex;
    justify-content: center;
    padding: 24px;
}

.command-palette__item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 10px;
    cursor: pointer;
    transition: background 0.1s;
}

.command-palette__item--active {
    background: rgba(99, 102, 241, 0.15);
}

.command-palette__item-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.command-palette__item-icon svg {
    width: 16px;
    height: 16px;
}

.command-palette__item-icon--product { background: rgba(16, 185, 129, 0.15); color: #10b981; }
.command-palette__item-icon--order { background: rgba(99, 102, 241, 0.15); color: #6366f1; }
.command-palette__item-icon--post { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
.command-palette__item-icon--page { background: rgba(168, 85, 247, 0.15); color: #a855f7; }
.command-palette__item-icon--user { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }

.command-palette__item-body {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.command-palette__item-title {
    font-size: 13px;
    font-weight: 500;
    color: #f1f5f9;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.command-palette__item-subtitle {
    font-size: 11px;
    color: #64748b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.command-palette__item-type {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #475569;
    font-weight: 600;
    flex-shrink: 0;
}

.command-palette__empty {
    padding: 32px;
    text-align: center;
    color: #64748b;
    font-size: 14px;
}

.command-palette__footer {
    display: flex;
    gap: 16px;
    padding: 10px 18px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    font-size: 11px;
    color: #475569;
}

.command-palette__footer kbd {
    font-size: 10px;
    padding: 1px 5px;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 3px;
    margin-right: 4px;
    font-family: monospace;
}

/* Transitions */
.palette-fade-enter-active { transition: opacity 0.15s ease; }
.palette-fade-leave-active { transition: opacity 0.1s ease; }
.palette-fade-enter-from, .palette-fade-leave-to { opacity: 0; }
</style>
