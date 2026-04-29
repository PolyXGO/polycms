<template>
    <div class="form-group mb-0" :class="{ 'w-full': hasLabel }">
        <label v-if="label" class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">{{ label }}</label>
        <div v-if="hasLabel" class="flex w-full items-center gap-3">
            <div class="color-picker-anchor shrink-0">
                <button type="button" class="color-picker-control group" :title="modelValue || label || 'Pick color'" @click="openPicker">
                    <span class="color-picker-surface">
                        <span class="color-picker-swatch" :style="{ background: swatchValue }"></span>
                    </span>
                </button>
                <input
                    ref="inputRef"
                    type="color"
                    :value="pickerValue"
                    @input="handleInput"
                    class="color-picker-native"
                    tabindex="-1"
                    aria-hidden="true"
                >
            </div>

            <div class="flex min-w-0 flex-1 items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 shadow-sm dark:border-gray-700 dark:bg-gray-900/70">
                <span class="truncate text-sm font-medium text-gray-700 dark:text-gray-200">{{ displayValue }}</span>
                <button
                    v-if="hasValue"
                    type="button"
                    class="ml-auto inline-flex items-center justify-center rounded-md border border-gray-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-gray-600 transition-colors hover:border-red-200 hover:bg-red-50 hover:text-red-600 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-red-500/40 dark:hover:bg-red-500/10 dark:hover:text-red-300"
                    @click="clearColor"
                >
                    Clear
                </button>
                <span v-else class="ml-auto text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-400 dark:text-gray-500">Default</span>
            </div>
        </div>

        <div v-else class="color-picker-anchor">
            <button
                type="button"
                class="color-picker-control group"
                :title="hasValue ? `${modelValue} (double-click or use clear to reset)` : (label || 'Pick color')"
                @click="openPicker"
                @dblclick.prevent.stop="clearColor"
            >
                <span class="color-picker-surface">
                    <span class="color-picker-swatch" :style="{ background: swatchValue }"></span>
                </span>
            </button>
            <input
                ref="inputRef"
                type="color"
                :value="pickerValue"
                @input="handleInput"
                class="color-picker-native"
                tabindex="-1"
                aria-hidden="true"
            >
            <button
                v-if="hasValue"
                type="button"
                class="color-picker-clear-badge"
                title="Clear color"
                @click.stop="clearColor"
            >
                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 6l8 8M14 6l-8 8" stroke-linecap="round" />
                </svg>
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';

const props = defineProps<{
    modelValue: string;
    label?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const inputRef = ref<HTMLInputElement | null>(null);
const hasLabel = computed(() => Boolean(props.label));
const hasValue = computed(() => Boolean(props.modelValue?.trim()));
const swatchValue = computed(() => props.modelValue || 'transparent');
const pickerValue = computed(() => normalizeColor(props.modelValue));
const displayValue = computed(() => props.modelValue || 'Default color');

function openPicker() {
    if (!inputRef.value) {
        return;
    }

    const picker = inputRef.value as HTMLInputElement & { showPicker?: () => void };

    if (typeof picker.showPicker === 'function') {
        picker.showPicker();
        return;
    }

    picker.click();
}

function handleInput(event: Event) {
    emitValue((event.target as HTMLInputElement).value);
}

function clearColor() {
    emitValue('');
}

function emitValue(value: string) {
    emit('update:modelValue', value);
}

function normalizeColor(value: string): string {
    if (!value) return '#000000';

    const trimmed = value.trim();

    if (/^#[0-9a-f]{6}$/i.test(trimmed)) {
        return trimmed;
    }

    if (/^#[0-9a-f]{3}$/i.test(trimmed)) {
        return `#${trimmed[1]}${trimmed[1]}${trimmed[2]}${trimmed[2]}${trimmed[3]}${trimmed[3]}`;
    }

    const rgbaMatch = trimmed.match(/^rgba?\(([^)]+)\)$/i);
    if (!rgbaMatch) {
        return '#000000';
    }

    const [r = '0', g = '0', b = '0'] = rgbaMatch[1].split(',').map((part) => part.trim());
    return `#${toHex(r)}${toHex(g)}${toHex(b)}`;
}

function toHex(value: string): string {
    const num = Math.max(0, Math.min(255, Number.parseInt(value, 10) || 0));
    return num.toString(16).padStart(2, '0');
}
</script>

<style scoped>
.color-picker-anchor {
    position: relative;
    display: inline-flex;
}

.color-picker-control {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 0.875rem;
    border: 1px solid #e5e7eb;
    background: #ffffff;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
    transition: border-color 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
    overflow: hidden;
}

.color-picker-control:hover {
    border-color: #a5b4fc;
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(99, 102, 241, 0.12);
}

.dark .color-picker-control {
    background: #111827;
    border-color: #374151;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.28);
}

.dark .color-picker-control:hover {
    border-color: rgba(129, 140, 248, 0.6);
    box-shadow: 0 8px 22px rgba(79, 70, 229, 0.18);
}

.color-picker-surface {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 1.9rem;
    height: 1.9rem;
    border-radius: 9999px;
    border: 1px solid rgba(148, 163, 184, 0.4);
    background-image:
        linear-gradient(45deg, #e5e7eb 25%, transparent 25%),
        linear-gradient(-45deg, #e5e7eb 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, #e5e7eb 75%),
        linear-gradient(-45deg, transparent 75%, #e5e7eb 75%);
    background-size: 10px 10px;
    background-position: 0 0, 0 5px, 5px -5px, -5px 0;
    overflow: hidden;
}

.dark .color-picker-surface {
    border-color: rgba(75, 85, 99, 0.9);
    background-image:
        linear-gradient(45deg, #1f2937 25%, transparent 25%),
        linear-gradient(-45deg, #1f2937 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, #1f2937 75%),
        linear-gradient(-45deg, transparent 75%, #1f2937 75%);
}

.color-picker-swatch {
    display: block;
    width: 100%;
    height: 100%;
    border-radius: inherit;
    box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.08);
}

.color-picker-native {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    margin: 0;
    padding: 0;
    border: 0;
    opacity: 0;
    pointer-events: none;
    cursor: pointer;
}

.color-picker-native::-webkit-color-swatch-wrapper {
    padding: 0;
}

.color-picker-native::-webkit-color-swatch {
    border: 0;
}

.color-picker-clear-badge {
    position: absolute;
    top: -0.3rem;
    right: -0.3rem;
    display: inline-flex;
    width: 1rem;
    height: 1rem;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
    border: 1px solid rgba(226, 232, 240, 0.9);
    background: #ffffff;
    color: #64748b;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.18);
    transition: color 0.18s ease, border-color 0.18s ease, background 0.18s ease;
}

.color-picker-clear-badge:hover {
    color: #dc2626;
    border-color: rgba(248, 113, 113, 0.45);
    background: #fef2f2;
}

.dark .color-picker-clear-badge {
    border-color: rgba(71, 85, 105, 0.95);
    background: #0f172a;
    color: #cbd5e1;
}

.dark .color-picker-clear-badge:hover {
    color: #fca5a5;
    border-color: rgba(248, 113, 113, 0.3);
    background: rgba(127, 29, 29, 0.45);
}

.color-picker-clear-badge svg {
    width: 0.65rem;
    height: 0.65rem;
}
</style>
