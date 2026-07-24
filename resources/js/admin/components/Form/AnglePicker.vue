<template>
    <div class="angle-picker flex flex-col items-center">
        <!-- Circular Dial -->
        <div 
            class="relative w-24 h-24 rounded-full border-2 border-admin-theme-border bg-admin-theme-surface select-none cursor-pointer mb-3"
            @mousedown="onMouseDown"
            @touchstart.prevent="onTouchStart"
            ref="dialRef"
        >
            <!-- Center Dot -->
            <div class="absolute inset-0 m-auto w-1 h-1 rounded-full bg-admin-theme-text-muted"></div>
            
            <!-- Angle Indicators (0, 90, 180, 270) -->
            <div class="absolute top-2 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-admin-theme-text-muted"></div>
            <div class="absolute bottom-2 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-admin-theme-text-muted"></div>
            <div class="absolute left-2 top-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-admin-theme-text-muted"></div>
            <div class="absolute right-2 top-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-admin-theme-text-muted"></div>

            <!-- Draggable Knob -->
            <div 
                class="absolute w-5 h-5 rounded-full bg-admin-theme-primary shadow-lg border-[3px] border-admin-theme-base -translate-x-1/2 -translate-y-1/2 pointer-events-none transition-transform active:scale-110"
                :style="knobStyle"
            ></div>
        </div>

        <!-- Number Input -->
        <div class="w-24 relative">
            <input 
                type="number" 
                :value="modelValue"
                @input="onInput"
                class="w-full pl-3 pr-7 py-1.5 border border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text rounded-md shadow-sm sm:text-sm text-center font-mono focus:border-admin-theme-primary focus:ring-admin-theme-primary/20 transition-colors"
                min="0"
                max="360"
            />
            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-admin-theme-text-muted text-xs font-mono">&deg;</span>
        </div>
        <p v-if="showLabel" class="text-[10px] text-admin-theme-text-muted mt-2 text-center max-w-[120px] leading-tight">Drag handle or enter degrees</p>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = withDefaults(defineProps<{
    modelValue?: number;
    showLabel?: boolean;
}>(), {
    modelValue: 0,
    showLabel: true
});

const emit = defineEmits(['update:modelValue']);
const dialRef = ref<HTMLElement | null>(null);
const isDragging = ref(false);

const updateAngle = (event: MouseEvent | TouchEvent) => {
    if (!dialRef.value) return;

    const rect = dialRef.value.getBoundingClientRect();
    const centerX = rect.left + rect.width / 2;
    const centerY = rect.top + rect.height / 2;

    let clientX, clientY;
    if (window.TouchEvent && event instanceof TouchEvent) {
        clientX = event.touches[0].clientX;
        clientY = event.touches[0].clientY;
    } else {
        clientX = (event as MouseEvent).clientX;
        clientY = (event as MouseEvent).clientY;
    }

    const dx = clientX - centerX;
    const dy = clientY - centerY;

    let angleRad = Math.atan2(dy, dx);
    let angleDeg = (angleRad * 180) / Math.PI;
    
    // CSS gradient angle: 0deg is bottom-to-top (pointing up), 90deg is left-to-right (pointing right)
    angleDeg = angleDeg + 90;
    if (angleDeg < 0) {
        angleDeg += 360;
    }
    
    emit('update:modelValue', Math.round(angleDeg));
};

const onMouseDown = (e: MouseEvent) => {
    isDragging.value = true;
    updateAngle(e);
    window.addEventListener('mousemove', onMouseMove);
    window.addEventListener('mouseup', onMouseUp);
};

const onMouseMove = (e: MouseEvent) => {
    if (isDragging.value) {
        updateAngle(e);
    }
};

const onMouseUp = () => {
    isDragging.value = false;
    window.removeEventListener('mousemove', onMouseMove);
    window.removeEventListener('mouseup', onMouseUp);
};

const onTouchStart = (e: TouchEvent) => {
    isDragging.value = true;
    updateAngle(e);
    window.addEventListener('touchmove', onTouchMove, { passive: false });
    window.addEventListener('touchend', onTouchEnd);
};

const onTouchMove = (e: TouchEvent) => {
    if (isDragging.value) {
        e.preventDefault();
        updateAngle(e);
    }
};

const onTouchEnd = () => {
    isDragging.value = false;
    window.removeEventListener('touchmove', onTouchMove);
    window.removeEventListener('touchend', onTouchEnd);
};

onUnmounted(() => {
    window.removeEventListener('mousemove', onMouseMove);
    window.removeEventListener('mouseup', onMouseUp);
    window.removeEventListener('touchmove', onTouchMove);
    window.removeEventListener('touchend', onTouchEnd);
});

const onInput = (e: Event) => {
    let val = parseInt((e.target as HTMLInputElement).value, 10);
    if (isNaN(val)) val = 0;
    if (val > 360) val = val % 360;
    if (val < 0) val = (val % 360) + 360;
    emit('update:modelValue', val);
};

const knobStyle = computed(() => {
    const angle = props.modelValue || 0;
    const rad = (angle - 90) * (Math.PI / 180);
    const radius = 38; // Radius from center
    
    const x = Math.cos(rad) * radius + 48; // 48 is center of 96px (w-24)
    const y = Math.sin(rad) * radius + 48;
    
    return {
        left: `${x}px`,
        top: `${y}px`
    };
});
</script>
