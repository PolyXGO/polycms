<template>
    <div class="relative" ref="container">
        <!-- Selected Tags / Input (Multiple) -->
        <div v-if="multiple" class="min-h-[42px] p-2 border rounded-md bg-white dark:bg-gray-700 flex flex-wrap gap-2 items-center cursor-text" :class="error ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'" @click="openDropdown">
            <template v-for="(item, index) in localValue" :key="index">
                <span class="inline-flex items-center bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-300 rounded px-2 py-1 text-sm">
                    {{ getDisplayName(item) }}
                    <button type="button" @click.stop="removeItem(index)" class="ml-1 text-indigo-500 hover:text-indigo-700 focus:outline-none">
                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </button>
                </span>
            </template>
            <input 
                ref="searchInput"
                v-model="search"
                type="text"
                class="flex-1 outline-none min-w-[120px] bg-transparent text-gray-900 dark:text-white"
                :placeholder="localValue.length === 0 ? placeholder : ''"
                @focus="isOpen = true"
            />
        </div>

        <!-- Single Select -->
        <div v-else class="relative">
            <!-- Closed state -->
            <button v-if="!isOpen" type="button" @click.stop="openDropdown" class="w-full text-left px-3 py-2 border rounded-md bg-white dark:bg-gray-700 focus:outline-none flex items-center justify-between min-h-[42px]" :class="error ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'">
                <span class="block text-sm truncate" :class="localValue && (localValue as any).country ? 'text-gray-900 dark:text-white' : 'text-gray-400'">
                    {{ (localValue && (localValue as any).country) ? getDisplayName(localValue as any) : placeholder }}
                </span>
                <span class="flex items-center gap-1 shrink-0 ml-2">
                    <span v-if="localValue && (localValue as any).country" @click.stop="clearSingle" class="p-1 text-gray-400 hover:text-gray-600 cursor-pointer">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </span>
                    <svg class="h-5 w-5 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </span>
            </button>
            
            <!-- Open state -->
            <div v-else class="relative">
                <input 
                    ref="searchInput"
                    v-model="search"
                    type="text"
                    class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white outline-none min-h-[42px]"
                    :class="error ? 'border-red-500' : 'border-indigo-500 ring-1 ring-indigo-500'"
                    :placeholder="$t('Search regions...')"
                    @input="handleSingleSearch"
                />
            </div>
        </div>

        <!-- Dropdown -->
        <div v-if="isOpen" class="absolute z-50 mt-1 w-full max-h-60 overflow-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg py-1">
            <div v-if="loading" class="p-4 text-center text-gray-500 text-sm">
                {{ $t('Loading locations...') }}
            </div>
            <div v-else-if="filteredLocations.length === 0" class="p-4 text-center text-gray-500 text-sm">
                {{ $t('No matches found.') }}
            </div>
            <template v-else>
                <div v-for="item in filteredLocations" :key="item.id" 
                    class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer flex items-center"
                    :class="{ 'pl-8 text-sm text-gray-600 dark:text-gray-400': !item.isCountry, 'font-medium text-gray-900 dark:text-white': item.isCountry }"
                    @click="selectItem(item)"
                >
                    <div v-if="multiple" class="mr-3 flex-shrink-0">
                        <input type="checkbox" :checked="isSelected(item)" class="h-4 w-4 text-indigo-600 border-gray-300 rounded pointer-events-none" />
                    </div>
                    <span>{{ item.label }} <span v-if="item.isCountry && item.code !== '*'" class="text-xs text-gray-400 ml-1">({{ item.code }})</span></span>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import axios from 'axios';
import { useTranslation } from '../../composables/useTranslation';

const { t } = useTranslation();

const props = defineProps({
    modelValue: {
        type: [Array, Object],
        default: () => []
    },
    multiple: {
        type: Boolean,
        default: false
    },
    placeholder: {
        type: String,
        default: 'Select regions...'
    },
    error: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue']);

const container = ref<HTMLElement | null>(null);
const searchInput = ref<HTMLInputElement | null>(null);
const isOpen = ref(false);
const search = ref('');
const locations = ref<any[]>([]);
const loading = ref(false);

const localValue = ref<any>(props.modelValue);

watch(() => props.modelValue, (newVal) => {
    localValue.value = newVal;
}, { deep: true });

// Global cache to prevent multiple fetches
const globalLocationCache = ref<any[] | null>(null);

const loadLocations = async () => {
    if (globalLocationCache.value) {
        locations.value = globalLocationCache.value;
        return;
    }
    
    loading.value = true;
    try {
        const response = await axios.get('/api/v1/locations/countries');
        locations.value = response.data.data;
        globalLocationCache.value = locations.value;
    } catch (e) {
        console.error('Failed to load locations', e);
    } finally {
        loading.value = false;
    }
};

const flatList = computed(() => {
    const list: any[] = [];
    locations.value.forEach(country => {
        list.push({
            id: country.code,
            isCountry: true,
            label: country.name,
            code: country.code,
            countryCode: country.code,
            stateCode: null
        });
        
        if (country.states && country.states.length) {
            country.states.forEach((state: any) => {
                list.push({
                    id: `${country.code}-${state.code}`,
                    isCountry: false,
                    label: state.name,
                    code: state.code,
                    countryCode: country.code,
                    stateCode: state.code,
                    countryName: country.name
                });
            });
        }
    });
    return list;
});

const filteredLocations = computed(() => {
    if (!search.value) return flatList.value;
    const lowerSearch = String(search.value).toLowerCase();
    return flatList.value.filter(item => {
        const lbl = String(item.label || '').toLowerCase();
        const cd = String(item.code || '').toLowerCase();
        const cName = String(item.countryName || '').toLowerCase();
        return lbl.includes(lowerSearch) || 
               cd.includes(lowerSearch) ||
               (item.countryName && cName.includes(lowerSearch));
    });
});

const getDisplayName = (item: { country: string, state?: string | null }) => {
    if (!item.country) return '';
    
    const country = locations.value.find(c => c.code === item.country);
    if (!country && item.country === '*') return t('Everywhere');
    if (!country) return item.country;

    if (item.state) {
        const state = country.states?.find((s: any) => s.code === item.state);
        return state ? `${state.name}, ${country.name}` : `${item.state}, ${country.name}`;
    }
    
    return country.name;
};

const isSelected = (item: any) => {
    if (!props.multiple) return false;
    if (!Array.isArray(localValue.value)) return false;
    
    return localValue.value.some((val: any) => 
        val.country === item.countryCode && 
        (val.state || null) === item.stateCode
    );
};

const selectItem = (item: any) => {
    const selectedObj = {
        country: item.countryCode,
        state: item.stateCode || null
    };

    if (props.multiple) {
        if (!Array.isArray(localValue.value)) localValue.value = [];
        
        const existingIndex = localValue.value.findIndex((val: any) => 
            val.country === selectedObj.country && 
            (val.state || null) === selectedObj.state
        );
        
        if (existingIndex >= 0) {
            localValue.value.splice(existingIndex, 1);
        } else {
            localValue.value.push(selectedObj);
        }
        search.value = '';
        if (searchInput.value) searchInput.value.focus();
    } else {
        localValue.value = selectedObj;
        search.value = '';
        isOpen.value = false;
    }
    
    emit('update:modelValue', localValue.value);
};

const removeItem = (index: number) => {
    if (Array.isArray(localValue.value)) {
        localValue.value.splice(index, 1);
        emit('update:modelValue', localValue.value);
    }
};

const clearSingle = () => {
    localValue.value = { country: '', state: null };
    search.value = '';
    emit('update:modelValue', localValue.value);
};

const handleSingleSearch = () => {
    if (localValue.value && localValue.value.country) {
        localValue.value = { country: '', state: null };
        emit('update:modelValue', localValue.value);
    }
};

const openDropdown = async () => {
    isOpen.value = true;
    await nextTick();
    if (searchInput.value) {
        searchInput.value.focus();
    }
};

const handleClickOutside = (event: MouseEvent) => {
    if (container.value && !container.value.contains(event.target as Node)) {
        isOpen.value = false;
        if (props.multiple) {
            search.value = '';
        }
    }
};

onMounted(() => {
    loadLocations();
    document.addEventListener('click', handleClickOutside);
    // Init single value if strings provided
    if (!props.multiple && !localValue.value) {
        localValue.value = { country: '', state: null };
    }
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>
