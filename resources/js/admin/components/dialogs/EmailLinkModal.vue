<template>
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                {{ t('URL') }}
            </label>
            <input
                v-model.trim="form.url"
                type="text"
                :placeholder="urlPlaceholder"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            />
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                {{ t('Anchor Text') }}
            </label>
            <input
                v-model="form.text"
                type="text"
                :placeholder="t('Optional anchor text')"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            />
        </div>

        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <input
                v-model="form.openInNewTab"
                type="checkbox"
                class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500"
            />
            <span>{{ t('Open in new tab') }}</span>
        </label>

        <div class="relative" ref="relDropdownRef">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                {{ t('Link Relation (rel)') }}
            </label>
            <button
                ref="relTriggerRef"
                type="button"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-left text-gray-900 dark:text-white flex items-center justify-between"
                @click="relDropdownOpen = !relDropdownOpen"
            >
                <span>{{ t(selectedRelOption.label) }}</span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ t(selectedRelOption.description) }}
            </p>
        </div>
        <Teleport to="body">
            <div
                v-if="relDropdownOpen"
                ref="relMenuRef"
                class="fixed z-[1000001] rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg overflow-hidden"
                :style="dropdownStyle"
            >
                <button
                    v-for="option in REL_OPTIONS"
                    :key="option.value"
                    type="button"
                    class="w-full px-3 py-2 text-left hover:bg-gray-50 dark:hover:bg-gray-700 border-b last:border-b-0 border-gray-100 dark:border-gray-700"
                    @click="selectRelMode(option.value)"
                >
                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                        <span v-if="form.relMode === option.value" class="mr-1"><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>{{ t(option.label) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ t(option.description) }}
                    </div>
                </button>
            </div>
        </Teleport>

        <div class="pt-2 flex items-center justify-between gap-3">
            <button
                type="button"
                class="px-4 py-2 border border-red-300 dark:border-red-600 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20"
                @click="removeLink"
            >
                {{ t('Remove Link') }}
            </button>

            <div class="flex items-center gap-3">
                <button
                    type="button"
                    class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
                    @click="emit('close')"
                >
                    {{ t('Cancel') }}
                </button>
                <button
                    type="button"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                    @click="submit"
                >
                    {{ t('Apply Link') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { reactive, ref, watch, computed, onMounted, onBeforeUnmount } from 'vue';
import { useTranslation } from '@/admin/composables/useTranslation';

interface LinkPayload {
    url: string;
    text: string;
    openInNewTab: boolean;
    relMode: string;
    remove?: boolean;
}

const props = defineProps<{
    initialUrl?: string;
    initialText?: string;
    initialOpenInNewTab?: boolean;
    initialRelMode?: string;
    onSubmit?: (payload: LinkPayload) => void;
}>();

const emit = defineEmits<{
    close: [];
}>();

const { t } = useTranslation();

const form = reactive({
    url: props.initialUrl || '',
    text: props.initialText || '',
    openInNewTab: !!props.initialOpenInNewTab,
    relMode: props.initialRelMode || '',
});
const relTouched = ref(false);
const relDropdownOpen = ref(false);
const relDropdownRef = ref<HTMLElement | null>(null);
const relTriggerRef = ref<HTMLElement | null>(null);
const relMenuRef = ref<HTMLElement | null>(null);
const dropdownStyle = ref<Record<string, string>>({});

const REL_OPTIONS = [
    { value: 'follow', label: 'Follow', description: 'Rel Follow Description' },
    { value: 'nofollow', label: 'Nofollow', description: 'Rel Nofollow Description' },
    { value: 'ugc', label: 'UGC', description: 'Rel UGC Description' },
    { value: 'sponsored', label: 'Sponsored', description: 'Rel Sponsored Description' },
    { value: 'nofollow_ugc', label: 'Nofollow + UGC', description: 'Rel Nofollow UGC Description' },
    { value: 'nofollow_sponsored', label: 'Nofollow + Sponsored', description: 'Rel Nofollow Sponsored Description' },
];

const selectedRelOption = computed(() => {
    return REL_OPTIONS.find((item) => item.value === form.relMode) || REL_OPTIONS[0];
});
const urlPlaceholder = computed(() => {
    if (typeof window === 'undefined') return 'https://example.com';
    return window.location.origin;
});

const isInternalUrl = (value: string): boolean => {
    const url = value.trim();
    if (!url) return true;
    if (url.startsWith('#') || url.startsWith('/')) return true;
    if (url.startsWith('mailto:') || url.startsWith('tel:')) return true;

    try {
        const parsed = new URL(url, window.location.origin);
        return parsed.origin === window.location.origin;
    } catch {
        return true;
    }
};

const suggestRelMode = (url: string): string => {
    return isInternalUrl(url) ? 'follow' : 'nofollow';
};

watch(
    () => form.url,
    (value) => {
        if (relTouched.value) return;
        form.relMode = suggestRelMode(value);
    },
    { immediate: true }
);

const submit = () => {
    props.onSubmit?.({
        url: form.url,
        text: form.text,
        openInNewTab: form.openInNewTab,
        relMode: form.relMode || suggestRelMode(form.url),
        remove: false,
    });
    emit('close');
};

const removeLink = () => {
    props.onSubmit?.({
        url: '',
        text: form.text,
        openInNewTab: false,
        relMode: 'follow',
        remove: true,
    });
    emit('close');
};

const selectRelMode = (value: string) => {
    form.relMode = value;
    relTouched.value = true;
    relDropdownOpen.value = false;
};

const onClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement | null;
    const inTrigger = relDropdownRef.value?.contains(target || null);
    const inMenu = relMenuRef.value?.contains(target || null);
    if (!inTrigger && !inMenu) {
        relDropdownOpen.value = false;
    }
};

const updateDropdownPosition = () => {
    if (!relDropdownOpen.value || !relTriggerRef.value) return;
    const rect = relTriggerRef.value.getBoundingClientRect();
    dropdownStyle.value = {
        top: `${Math.round(rect.bottom + 6)}px`,
        left: `${Math.round(rect.left)}px`,
        width: `${Math.round(rect.width)}px`,
        maxHeight: '280px',
        overflowY: 'auto',
    };
};

watch(relDropdownOpen, (open) => {
    if (open) {
        updateDropdownPosition();
        window.addEventListener('resize', updateDropdownPosition);
        window.addEventListener('scroll', updateDropdownPosition, true);
    } else {
        window.removeEventListener('resize', updateDropdownPosition);
        window.removeEventListener('scroll', updateDropdownPosition, true);
    }
});

onMounted(() => {
    document.addEventListener('click', onClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', onClickOutside);
    window.removeEventListener('resize', updateDropdownPosition);
    window.removeEventListener('scroll', updateDropdownPosition, true);
});
</script>
