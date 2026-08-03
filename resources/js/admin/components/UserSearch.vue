<template>
 <div class="relative">
 <label v-if="label" class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ label }}</label>
 
 <!-- Selected Items -->
 <div class="flex flex-wrap gap-2 mb-2" v-if="modelValue && modelValue.length > 0">
 <div v-for="email in modelValue" :key="email" class="bg-admin-theme-primary/15 text-admin-theme-primary px-2 py-1 rounded-md text-sm flex items-center">
 <span>{{ email }}</span>
 <button type="button" @click="remove(email)" class="ml-1 text-admin-theme-primary hover:text-admin-theme-primary-hover">
 &times;
 </button>
 </div>
 </div>

 <!-- Search Input -->
 <input 
 type="text" 
 v-model="searchQuery" 
 @input="handleInput"
 @keydown.enter.prevent="handleEnter"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text"
 :placeholder="placeholder || t('Search users by name or email...')"
 />
 <p class="text-[11px] text-admin-theme-text-muted mt-1">{{ t('Type an email and press Enter to add directly, or search for existing users.') }}</p>
 
 <!-- Dropdown -->
 <div v-if="showDropdown && (results.length > 0 || loading || showDirectAdd)" class="absolute z-10 w-full mt-1 bg-admin-theme-surface border border-admin-theme-border rounded-md shadow-lg max-h-60 overflow-y-auto">
 <div v-if="loading" class="p-3 text-sm text-admin-theme-text-muted text-center">{{ t('Loading...') }}</div>
 <ul v-else>
 <!-- Direct email add option -->
 <li
   v-if="showDirectAdd"
   @click="addDirect"
   class="px-4 py-2 hover:bg-admin-theme-base cursor-pointer text-sm text-admin-theme-text border-b border-admin-theme-border bg-admin-theme-primary/5"
 >
   <div class="font-medium flex items-center gap-1.5">
     <svg class="w-4 h-4 text-admin-theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
     {{ t('Add email') }}: <span class="text-admin-theme-primary font-semibold">{{ searchQuery.trim() }}</span>
   </div>
 </li>
 <li 
 v-for="user in results" 
 :key="user.id" 
 @click="select(user)" 
 class="px-4 py-2 hover:bg-admin-theme-base cursor-pointer text-sm text-admin-theme-text"
 >
 <div class="font-medium">{{ user.name }}</div>
 <div class="text-admin-theme-text-muted text-xs">{{ user.email }}</div>
 </li>
 </ul>
 </div>
 </div>
</template>

<script setup lang="ts">
import { ref, computed, getCurrentInstance } from'vue';
import axios from'axios';
import { debounce } from'lodash';

const instance = getCurrentInstance();
const t = instance?.appContext.config.globalProperties.$t || ((v: string) => v);

const props = defineProps({
 modelValue: {
 type: Array as () => string[], // Array of emails
 default: () => []
 },
 label: {
 type: String,
 default:''
 },
 placeholder: {
  type: String,
  default: ''
 }
});

const emit = defineEmits(['update:modelValue']);

const searchQuery = ref('');
const results = ref<any[]>([]);
const loading = ref(false);
const showDropdown = ref(false);

// Check if current query looks like a valid email
const isValidEmail = (str: string) => {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(str.trim());
};

// Show "Add email directly" option when input looks like an email and not already added
const showDirectAdd = computed(() => {
  const q = searchQuery.value.trim();
  return q.length > 0 && isValidEmail(q) && !props.modelValue.includes(q);
});

const handleInput = debounce(async () => {
 if (searchQuery.value.length < 2) {
 results.value = [];
 showDropdown.value = showDirectAdd.value;
 return;
 }
 
 loading.value = true;
 showDropdown.value = true;
 
 try {
 const response = await axios.get('/api/v1/admin/users/search', {
 params: { q: searchQuery.value }
 });
 results.value = response.data;
 } catch (error) {
 console.error('UserId search error', error);
 results.value = [];
 } finally {
 loading.value = false;
 showDropdown.value = true;
 }
}, 300);

// Handle Enter key — add email directly if valid
const handleEnter = () => {
  const q = searchQuery.value.trim();
  if (isValidEmail(q) && !props.modelValue.includes(q)) {
    const newValue = [...props.modelValue, q];
    emit('update:modelValue', newValue);
    searchQuery.value = '';
    showDropdown.value = false;
    results.value = [];
  }
};

// Add email directly from dropdown option
const addDirect = () => {
  const q = searchQuery.value.trim();
  if (isValidEmail(q) && !props.modelValue.includes(q)) {
    const newValue = [...props.modelValue, q];
    emit('update:modelValue', newValue);
    searchQuery.value = '';
    showDropdown.value = false;
    results.value = [];
  }
};

const select = (user: any) => {
 if (!props.modelValue.includes(user.email)) {
 const newValue = [...props.modelValue, user.email];
 emit('update:modelValue', newValue);
 }
 searchQuery.value ='';
 showDropdown.value = false;
};

const remove = (email: string) => {
 const newValue = props.modelValue.filter(e => e !== email);
 emit('update:modelValue', newValue);
};

// Close dropdown on click outside logic could be added here
</script>
