<template>
 <div class="category-selector">
 <template v-if="categories.length > 0">
 <div class="category-selector__tabs border-admin-theme-border">
 <button
 type="button"
 class="category-selector__tab"
 :class="{'category-selector__tab--active': activeTab ==='all' }"
 @click="activeTab ='all'"
 >
 All {{ label }}
 </button>
 <button
 v-if="mostUsedCategories.length"
 type="button"
 class="category-selector__tab"
 :class="{'category-selector__tab--active': activeTab ==='most' }"
 @click="activeTab ='most'"
 >
 Most Used
 </button>
 </div>

 <div v-if="activeTab ==='all'" class="category-selector__list border-admin-theme-border bg-admin-theme-surface">
 <div v-for="node in flattenedCategories" :key="node.id" class="category-selector__item" :style="getIndent(node.depth)">
 <label class="category-selector__label text-admin-theme-text">
 <input type="checkbox" :checked="isSelected(node.id)" @change="toggleCategory(node.id, $event)" class="rounded border-admin-theme-border text-admin-theme-primary focus:ring-admin-theme-primary" />
 <span>{{ node.name }}</span>
 </label>
 </div>
 </div>

 <div v-else class="category-selector__list border-admin-theme-border bg-admin-theme-surface">
 <div v-for="node in mostUsedCategories" :key="node.id" class="category-selector__item">
 <label class="category-selector__label text-admin-theme-text">
 <input type="checkbox" :checked="isSelected(node.id)" @change="toggleCategory(node.id, $event)" class="rounded border-admin-theme-border text-admin-theme-primary focus:ring-admin-theme-primary" />
 <span>{{ node.name }}</span>
 </label>
 </div>
 </div>
 </template>

 <div class="category-selector__actions">
 <button type="button" class="category-selector__add text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary-hover" @click="showAddForm = !showAddForm">
 {{ showAddForm ?'Cancel' : addLabel }}
 </button>

 <transition name="fade">
 <form v-if="showAddForm" class="category-selector__form bg-admin-theme-base border-admin-theme-border" @submit.prevent="createCategory">
 <div class="category-selector__form-group">
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">Name</label>
 <input v-model="newCategoryName" type="text" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-admin-theme-primary focus:border-admin-theme-primary" required />
 </div>
 <div class="category-selector__form-group">
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">Parent</label>
 <select v-model="newCategoryParent" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary">
 <option :value="null">— {{ parentPlaceholder }} —</option>
 <option v-for="node in flattenedCategories" :key="node.id" :value="node.id">
 {{'—'.repeat(node.depth) }} {{ node.name }}
 </option>
 </select>
 </div>
 <button type="submit" class="w-max px-4 py-2 bg-admin-theme-primary hover:bg-admin-theme-primary-hover text-admin-theme-primary-content rounded-lg font-semibold disabled:opacity-50" :disabled="creating">
 {{ creating ?'Creating…' :'Add' }}
 </button>
 </form>
 </transition>
 </div>
 </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from'vue';
import axios from'axios';
import { useSlugify } from'../../../../composables/useSlugify';

interface CategoryNode {
 id: number;
 name: string;
 depth: number;
 children?: CategoryNode[];
}

const props = defineProps<{
 type: string;
 label?: string;
 addLabel?: string;
 parentPlaceholder?: string;
 modelValue: number[];
 locale?: string;
}>();

const emit = defineEmits<{
 (e:'update:modelValue', value: number[]): void;
 (e:'created', category: { id: number; name: string }): void;
}>();

const { slugify } = useSlugify();

const activeTab = ref<'all' |'most'>('all');
const categories = ref<CategoryNode[]>([]);
const mostUsedCategories = ref<CategoryNode[]>([]);
const selected = ref<number[]>(props.modelValue ?? []);
const showAddForm = ref(false);
const newCategoryName = ref('');
const newCategoryParent = ref<number | null>(null);
const creating = ref(false);

watch(
 () => props.modelValue,
 (value) => {
 selected.value = Array.from(new Set(value ?? []));
 }
);

watch(
 () => props.locale,
 () => {
  void fetchCategories();
  void fetchMostUsed();
 }
);

const flattenedCategories = computed(() => flattenTree(categories.value));
const label = computed(() => props.label ??'Categories');
const addLabel = computed(() => props.addLabel ??'Add Category');
const parentPlaceholder = computed(() => props.parentPlaceholder ??'Parent Category');

const apiEndpoint = computed(() => {
 if (props.type ==='product') {
 return'/api/v1/product-categories';
 }
 if (props.type ==='product_brand') {
 return'/api/v1/product-brands';
 }
 return'/api/v1/categories';
});

const fetchCategories = async () => {
 const params: any = {
 tree: true,
 };
 
 if (props.locale) {
  params.locale = props.locale;
 }
 
 // Only add type param if using the general categories endpoint
 if (apiEndpoint.value ==='/api/v1/categories') {
 params.type = props.type;
 }

 const response = await axios.get(apiEndpoint.value, { params });
 const tree = (response.data?.data ?? []) as any[];
 categories.value = transformTree(tree);
};

const fetchMostUsed = async () => {
 const params: any = {
 most_used: true,
 limit: 20,
 };

 if (props.locale) {
  params.locale = props.locale;
 }

 // Only add type param if using the general categories endpoint
 if (apiEndpoint.value ==='/api/v1/categories') {
  params.type = props.type;
 }

 const response = await axios.get(apiEndpoint.value, { params });
 const list = (response.data?.data ?? []) as any[];
 mostUsedCategories.value = list.map((node) => ({
 id: node.id,
 name: node.name,
 depth: 0,
 }));
};

const toggleCategory = (id: number, event: Event) => {
 const target = event.target as HTMLInputElement;
 if (target.checked) {
 if (!selected.value.includes(id)) {
 selected.value = [...selected.value, id];
 }
 } else {
 selected.value = selected.value.filter((item) => item !== id);
 }
 emit('update:modelValue', selected.value);
};

const isSelected = (id: number) => selected.value.includes(id);

const getIndent = (depth: number) => ({
 paddingLeft: `${Math.max(depth, 0) * 18}px`,
});

const createCategory = async () => {
 if (!newCategoryName.value.trim()) {
  return;
 }
 creating.value = true;
 try {
  const payload: any = {
   name: newCategoryName.value.trim(),
   slug: slugify(newCategoryName.value.trim()),
   parent_id: newCategoryParent.value,
  };

  if (props.locale) {
   payload.locale = props.locale;
  }

  // Only add type param if using the general categories endpoint
  if (apiEndpoint.value ==='/api/v1/categories') {
   payload.type = props.type;
  }

 const response = await axios.post(apiEndpoint.value, payload);
 const category = response.data?.data;
 await Promise.all([fetchCategories(), fetchMostUsed()]);
 emit('created', category);
 newCategoryName.value ='';
 newCategoryParent.value = null;
 showAddForm.value = false;
 } finally {
 creating.value = false;
 }
};

onMounted(async () => {
 await Promise.all([fetchCategories(), fetchMostUsed()]);
});

function transformTree(items: any[], depth = 0): CategoryNode[] {
 return items.map((item) => ({
 id: item.id,
 name: item.name,
 depth,
 children: transformTree(item.children ?? [], depth + 1),
 }));
}

function flattenTree(nodes: CategoryNode[], depth = 0): CategoryNode[] {
 const result: CategoryNode[] = [];
 nodes.forEach((node) => {
 result.push({ id: node.id, name: node.name, depth });
 if (node.children && node.children.length) {
 result.push(...flattenTree(node.children, depth + 1));
 }
 });
 return result;
}
</script>

<style scoped>
.category-selector {
 display: flex;
 flex-direction: column;
 gap: 0.75rem;
}

.category-selector__tabs {
 display: flex;
 gap: 0.5rem;
 border-bottom-width: 1px;
}

.category-selector__tab {
 padding: 0.35rem 0.75rem;
 border: none;
 background: none;
 font-size: 0.85rem;
 color: rgb(var(--admin-theme-text-secondary));
 cursor: pointer;
 border-bottom: 2px solid transparent;
}

.category-selector__tab--active {
 color: rgb(var(--admin-theme-primary));
 border-color: rgb(var(--admin-theme-primary));
 font-weight: 600;
}

.category-selector__list {
 max-height: 260px;
 overflow-y: auto;
 border-width: 1px;
 border-radius: 0.75rem;
 padding: 0.75rem;
}

.category-selector__item + .category-selector__item {
 margin-top: 0.35rem;
}

.category-selector__label {
 display: flex;
 align-items: center;
 gap: 0.5rem;
 font-size: 0.9rem;
 cursor: pointer;
}

.category-selector__actions {
 display: flex;
 flex-direction: column;
 gap: 0.5rem;
}

.category-selector__add {
 padding: 0;
 background: none;
 border: none;
 font-weight: 600;
 cursor: pointer;
 width: max-content;
}

.category-selector__form {
 display: grid;
 gap: 0.75rem;
 padding: 0.75rem;
 border-width: 1px;
 border-radius: 0.75rem;
}

.category-selector__form-group {
 display: grid;
 gap: 0.35rem;
}

.fade-enter-active,
.fade-leave-active {
 transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
 opacity: 0;
}
</style>

