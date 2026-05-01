<template>
    <div class="row-layout-picker">
        <label v-if="label" class="row-layout-picker__label">{{ label }}</label>

        <div class="row-layout-picker__list" :class="`row-layout-picker__list--${size}`">
            <button
                v-for="preset in ROW_LAYOUT_PRESETS"
                :key="preset.id"
                type="button"
                class="row-layout-picker__option"
                :class="{ 'is-active': preset.id === modelValue }"
                :title="preset.label"
                @click="selectPreset(preset.id)"
            >
                <div
                    class="row-layout-picker__preview"
                    :style="{ gridTemplateColumns: buildRowTemplateColumns(preset.widths) }"
                >
                    <span
                        v-for="(width, index) in preset.widths"
                        :key="`${preset.id}-${index}-${width}`"
                        class="row-layout-picker__cell"
                    >
                        {{ width }}
                    </span>
                </div>
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ROW_LAYOUT_PRESETS, buildRowTemplateColumns } from '@/admin/editor/rowLayoutPresets';

withDefaults(defineProps<{
    modelValue?: string;
    label?: string;
    size?: 'sm' | 'md';
}>(), {
    modelValue: '',
    label: '',
    size: 'md',
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

function selectPreset(presetId: string) {
    emit('update:modelValue', presetId);
}
</script>

<style scoped>
.row-layout-picker {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.row-layout-picker__label {
    display: block;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #9ca3af;
}

.row-layout-picker__list {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}

.row-layout-picker__option {
    display: block;
    width: 100%;
    border: 1px solid rgba(148, 163, 184, 0.14);
    border-radius: 0;
    background: rgba(15, 23, 42, 0.22);
    padding: 0.5rem;
    transition: border-color 0.18s ease, background 0.18s ease;
}

.row-layout-picker__option:hover {
    border-color: rgba(129, 140, 248, 0.48);
    background: rgba(79, 70, 229, 0.08);
}

.row-layout-picker__option.is-active {
    border-color: rgba(99, 102, 241, 0.75);
    background: rgba(79, 70, 229, 0.18);
    box-shadow: inset 0 0 0 1px rgba(99, 102, 241, 0.22);
}

.row-layout-picker__preview {
    display: grid;
    gap: 0.5rem;
}

.row-layout-picker__cell {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0;
    background: rgba(100, 116, 139, 0.22);
    color: #cbd5e1;
    font-weight: 500;
    letter-spacing: 0.02em;
    line-height: 1;
}

.row-layout-picker__list--sm .row-layout-picker__cell {
    min-height: 1.9rem;
    font-size: 0.85rem;
}

.row-layout-picker__list--md .row-layout-picker__cell {
    min-height: 2.2rem;
    font-size: 0.95rem;
}

.dark .row-layout-picker__option {
    border-color: rgba(71, 85, 105, 0.55);
    background: rgba(17, 24, 39, 0.64);
}

.dark .row-layout-picker__option:hover {
    border-color: rgba(129, 140, 248, 0.66);
    background: rgba(30, 41, 59, 0.94);
}

.dark .row-layout-picker__option.is-active {
    background: rgba(79, 70, 229, 0.16);
}

.dark .row-layout-picker__cell {
    background: rgba(51, 65, 85, 0.84);
    color: #a5b4fc;
}
</style>
