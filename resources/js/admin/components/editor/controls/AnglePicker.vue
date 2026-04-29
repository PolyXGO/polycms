<template>
    <div class="form-group mb-0">
        <label v-if="label" class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">{{ label }}</label>
        <button
            type="button"
            class="angle-picker-control group"
            :title="label ? `${label}: ${modelValue}°` : `${modelValue}°`"
            @mousedown.prevent="startRotating"
            @dragstart.prevent
        >
            <span class="angle-picker-ring"></span>
            <span
                class="angle-picker-needle"
                :style="{ transform: `rotate(${modelValue}deg)` }"
            ></span>
            <span class="angle-picker-center"></span>
            <span class="angle-picker-value">{{ modelValue }}&deg;</span>
        </button>
    </div>
</template>

<script setup lang="ts">
const props = defineProps<{
    modelValue: number;
    label?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: number): void;
}>();

const startRotating = (event: MouseEvent) => {
    const rect = (event.currentTarget as HTMLElement).getBoundingClientRect();
    const centerX = rect.left + rect.width / 2;
    const centerY = rect.top + rect.height / 2;

    const moveHandler = (e: MouseEvent) => {
        const dx = e.clientX - centerX;
        const dy = e.clientY - centerY;
        let angle = Math.atan2(dy, dx) * (180 / Math.PI) + 90;
        
        // Normalize angle to 0-360
        angle = Math.round(angle < 0 ? angle + 360 : angle);
        
        emit('update:modelValue', angle);
    };

    const upHandler = () => {
        window.removeEventListener('mousemove', moveHandler);
        window.removeEventListener('mouseup', upHandler);
    };

    window.addEventListener('mousemove', moveHandler);
    window.addEventListener('mouseup', upHandler);
};
</script>

<style scoped>
.angle-picker-control {
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
    cursor: crosshair;
    transition: border-color 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
}

.angle-picker-control:hover {
    border-color: #a5b4fc;
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(99, 102, 241, 0.12);
}

.dark .angle-picker-control {
    background: #111827;
    border-color: #374151;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.28);
}

.dark .angle-picker-control:hover {
    border-color: rgba(129, 140, 248, 0.6);
    box-shadow: 0 8px 22px rgba(79, 70, 229, 0.18);
}

.angle-picker-ring {
    position: absolute;
    inset: 0.4rem;
    border-radius: 9999px;
    border: 2px dashed #94a3b8;
    opacity: 0.75;
}

.dark .angle-picker-ring {
    border-color: #6b7280;
}

.angle-picker-needle {
    position: absolute;
    width: 2px;
    height: 0.82rem;
    margin-bottom: 0.82rem;
    border-radius: 9999px;
    background: #6366f1;
    transform-origin: bottom center;
    transition: transform 0.12s linear;
}

.angle-picker-center {
    width: 0.32rem;
    height: 0.32rem;
    border-radius: 9999px;
    background: #6b7280;
    z-index: 1;
}

.angle-picker-value {
    position: absolute;
    bottom: -1.45rem;
    left: 50%;
    transform: translateX(-50%);
    padding: 0.1rem 0.35rem;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.92);
    color: #6366f1;
    font-size: 0.6rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.15s ease;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.1);
}

.dark .angle-picker-value {
    background: rgba(17, 24, 39, 0.96);
    color: #a5b4fc;
}

.angle-picker-control:hover .angle-picker-value,
.angle-picker-control:focus-visible .angle-picker-value {
    opacity: 1;
}
</style>
