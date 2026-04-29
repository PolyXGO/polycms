<template>
    <div class="form-group mb-0 option-control">
        <label v-if="label" class="option-control__label">{{ label }}</label>

        <div v-if="type === 'icons'" class="option-control__buttons">
            <button
                v-for="option in options"
                :key="option.value"
                type="button"
                class="option-control__button"
                :class="{ 'is-active': modelValue === option.value }"
                :title="option.label"
                @click="$emit('update:modelValue', option.value)"
            >
                <span
                    v-if="option.icon"
                    class="option-control__icon"
                    v-html="option.icon"
                ></span>
                <span v-else class="option-control__text">
                    {{ option.shortLabel || option.label }}
                </span>
            </button>
        </div>

        <div v-else class="option-control__select-wrap">
            <select
                :value="modelValue"
                class="option-control__select"
                @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
            >
                <option
                    v-for="option in options"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
            </select>
            <span class="option-control__chevron" aria-hidden="true">
                <svg viewBox="0 0 20 20" fill="currentColor">
                    <path d="M5.293 7.293a1 1 0 0 1 1.414 0L10 10.586l3.293-3.293a1 1 0 1 1 1.414 1.414l-4 4a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 0-1.414Z" />
                </svg>
            </span>
        </div>
    </div>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
    modelValue: string;
    label?: string;
    type?: 'dropdown' | 'icons';
    options: Array<{
        value: string;
        label: string;
        icon?: string;
        shortLabel?: string;
    }>;
}>(), {
    label: '',
    type: 'dropdown',
});

defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();
</script>

<style scoped>
.option-control {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}

.option-control__label {
    display: block;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: #9ca3af;
}

.option-control__buttons {
    display: grid;
    grid-auto-flow: column;
    grid-auto-columns: minmax(0, 1fr);
    gap: 0.375rem;
}

.option-control__button {
    display: inline-flex;
    min-height: 2.35rem;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(148, 163, 184, 0.18);
    background: rgba(255, 255, 255, 0.86);
    color: #64748b;
    transition: border-color 0.18s ease, background 0.18s ease, color 0.18s ease;
}

.option-control__button:hover {
    border-color: rgba(129, 140, 248, 0.42);
    background: rgba(79, 70, 229, 0.08);
    color: #334155;
}

.option-control__button.is-active {
    border-color: rgba(99, 102, 241, 0.72);
    background: rgba(79, 70, 229, 0.18);
    color: #4f46e5;
    box-shadow: inset 0 0 0 1px rgba(99, 102, 241, 0.18);
}

.option-control__icon {
    display: inline-flex;
    width: 1rem;
    height: 1rem;
}

.option-control__icon :deep(svg) {
    width: 100%;
    height: 100%;
}

.option-control__text {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.option-control__select-wrap {
    position: relative;
}

.option-control__select {
    width: 100%;
    appearance: none;
    border: 1px solid rgba(148, 163, 184, 0.2);
    background: rgba(255, 255, 255, 0.9);
    padding: 0.6rem 2.2rem 0.6rem 0.8rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #334155;
    transition: border-color 0.18s ease, background 0.18s ease;
}

.option-control__select:focus {
    border-color: rgba(99, 102, 241, 0.72);
    outline: none;
    box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.14);
}

.option-control__chevron {
    pointer-events: none;
    position: absolute;
    top: 50%;
    right: 0.8rem;
    display: inline-flex;
    width: 0.9rem;
    height: 0.9rem;
    transform: translateY(-50%);
    color: #94a3b8;
}

.dark .option-control__button {
    border-color: rgba(71, 85, 105, 0.65);
    background: rgba(17, 24, 39, 0.8);
    color: #94a3b8;
}

.dark .option-control__button:hover {
    border-color: rgba(129, 140, 248, 0.6);
    background: rgba(30, 41, 59, 0.92);
    color: #e2e8f0;
}

.dark .option-control__button.is-active {
    background: rgba(79, 70, 229, 0.18);
    color: #a5b4fc;
}

.dark .option-control__select {
    border-color: rgba(71, 85, 105, 0.65);
    background: rgba(17, 24, 39, 0.82);
    color: #f3f4f6;
}

.dark .option-control__select:focus {
    box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.16);
}
</style>
