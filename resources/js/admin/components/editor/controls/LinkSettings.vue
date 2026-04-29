<template>
    <div class="space-y-3">
        <div class="form-group">
            <label class="block text-[9px] font-bold uppercase text-gray-400 mb-1">URL / Link</label>
            <div class="relative">
                <input 
                    type="text" 
                    :value="url"
                    @input="$emit('update:url', ($event.target as HTMLInputElement).value)"
                    class="w-full bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg py-1.5 px-3 pl-8 text-[11px] focus:ring-1 focus:ring-indigo-500 transition-all"
                    placeholder="https://example.com"
                >
                <div class="absolute inset-y-0 left-0 flex items-center pl-2.5 text-gray-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div class="form-group">
                <label class="block text-[9px] font-bold uppercase text-gray-400 mb-1">Target</label>
                <div class="relative">
                    <select 
                        :value="target"
                        @change="$emit('update:target', ($event.target as HTMLSelectElement).value)"
                        class="w-full bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg py-1 px-2 pr-6 text-[10px] appearance-none focus:ring-1 focus:ring-indigo-500 transition-all"
                    >
                        <option value="_self">Same Tab</option>
                        <option value="_blank">New Tab</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                        <svg class="h-3 w-3 fill-current" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="block text-[9px] font-bold uppercase text-gray-400 mb-1">Relationship (Rel)</label>
                <input 
                    type="text" 
                    :value="rel"
                    @input="$emit('update:rel', ($event.target as HTMLInputElement).value)"
                    class="w-full bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg py-1 px-2 text-[10px] focus:ring-1 focus:ring-indigo-500 transition-all"
                    placeholder="nofollow, noopener"
                >
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    url: string;
    target?: string;
    rel?: string;
}>();

defineEmits<{
    (e: 'update:url', value: string): void;
    (e: 'update:target', value: string): void;
    (e: 'update:rel', value: string): void;
}>();
</script>
