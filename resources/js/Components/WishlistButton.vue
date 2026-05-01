<template>
    <button
        type="button"
        @click.prevent.stop="handleToggle"
        class="wishlist-btn"
        :class="{ 'wishlist-btn--active': isActive, 'wishlist-btn--loading': loading }"
        :title="isActive ? 'Remove from Wishlist' : 'Add to Wishlist'"
        :aria-label="isActive ? 'Remove from Wishlist' : 'Add to Wishlist'"
    >
        <svg
            class="wishlist-btn__icon"
            :class="{ 'wishlist-btn__icon--filled': isActive }"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            :fill="isActive ? 'currentColor' : 'none'"
            stroke="currentColor"
            stroke-width="2"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
            />
        </svg>
        <span v-if="showLabel" class="wishlist-btn__label">{{ isActive ? 'Wishlisted' : 'Wishlist' }}</span>
    </button>
</template>

<script setup lang="ts">
import { computed, ref, onMounted } from 'vue';
import { useWishlistStore } from '@/Stores/wishlistStore';

const props = defineProps({
    productId: { type: Number, required: true },
    variantId: { type: Number, default: null },
    showLabel: { type: Boolean, default: false },
});

const wishlist = useWishlistStore();
const loading = ref(false);

const isActive = computed(() => wishlist.isWishlisted(props.productId));

onMounted(() => {
    wishlist.checkProduct(props.productId);
});

async function handleToggle() {
    if (loading.value) return;
    loading.value = true;
    try {
        await wishlist.toggle(props.productId, props.variantId);
    } catch (e) {
        console.error('Wishlist toggle failed:', e);
    } finally {
        loading.value = false;
    }
}
</script>

<style scoped>
.wishlist-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.5rem;
    border: none;
    background: transparent;
    cursor: pointer;
    border-radius: 50%;
    transition: all 0.2s ease;
    color: #9ca3af;
}
.wishlist-btn:hover {
    color: #ef4444;
    background: rgba(239, 68, 68, 0.08);
    transform: scale(1.1);
}
.wishlist-btn--active {
    color: #ef4444;
}
.wishlist-btn--active:hover {
    color: #dc2626;
}
.wishlist-btn--loading {
    opacity: 0.6;
    pointer-events: none;
}
.wishlist-btn__icon {
    width: 1.25rem;
    height: 1.25rem;
    transition: all 0.25s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}
.wishlist-btn__icon--filled {
    animation: wishlist-pop 0.35s ease;
}
.wishlist-btn__label {
    font-size: 0.8125rem;
    font-weight: 500;
}

@keyframes wishlist-pop {
    0%   { transform: scale(1); }
    50%  { transform: scale(1.3); }
    100% { transform: scale(1); }
}
</style>
