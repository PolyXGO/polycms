<template>
  <div class="category-selector">
    <template v-if="categories.length > 0">
      <!-- Search Input Bar -->
      <div class="category-selector__search mb-1.5">
        <div class="relative flex items-center">
          <input
            v-model="searchQuery"
            type="text"
            :placeholder="searchPlaceholder"
            class="w-full pl-8 pr-7 py-1.5 text-xs rounded-lg border border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-1 focus:ring-admin-theme-primary focus:border-admin-theme-primary transition"
          />
          <svg class="w-3.5 h-3.5 absolute left-2.5 text-admin-theme-text-muted pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <button
            v-if="searchQuery"
            type="button"
            @click="searchQuery = ''"
            class="absolute right-2.5 text-admin-theme-text-muted hover:text-admin-theme-text text-xs"
          >
            ✕
          </button>
        </div>
      </div>

      <!-- Header Tabs & Expand/Collapse Toggle -->
      <div class="category-selector__tabs border-admin-theme-border flex items-center justify-between">
        <div class="flex gap-2">
          <button
            type="button"
            class="category-selector__tab"
            :class="{ 'category-selector__tab--active': activeTab === 'all' }"
            @click="activeTab = 'all'"
          >
            All {{ label }}
          </button>
          <button
            v-if="mostUsedCategories.length"
            type="button"
            class="category-selector__tab"
            :class="{ 'category-selector__tab--active': activeTab === 'most' }"
            @click="activeTab = 'most'"
          >
            Most Used
          </button>
        </div>

        <button
          v-if="activeTab === 'all' && hasTreeChildren && !searchQuery"
          type="button"
          class="text-[11px] text-admin-theme-text-secondary hover:text-admin-theme-primary font-medium transition cursor-pointer select-none"
          @click="toggleExpandAll"
        >
          {{ allCollapsed ? 'Expand All' : 'Collapse All' }}
        </button>
      </div>

      <!-- Category Tree List -->
      <div v-if="activeTab === 'all'" class="category-selector__list border-admin-theme-border bg-admin-theme-surface">
        <div
          v-for="node in visibleFlattenedCategories"
          :key="node.id"
          class="category-selector__item flex items-center gap-1.5"
          :style="getIndent(node.depth)"
        >
          <!-- Expand/Collapse Toggle Icon -->
          <button
            v-if="node.hasChildren"
            type="button"
            class="category-selector__toggle p-0.5 rounded text-admin-theme-text-secondary hover:text-admin-theme-text hover:bg-admin-theme-border/40 transition-transform duration-150 flex items-center justify-center cursor-pointer flex-shrink-0"
            :class="{ '-rotate-90': isCollapsed(node.id) && !searchQuery }"
            @click.stop.prevent="toggleExpand(node.id)"
            :title="isCollapsed(node.id) ? 'Expand' : 'Collapse'"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <span v-else class="w-4 h-4 inline-block flex-shrink-0"></span>

          <!-- Checkbox & Name -->
          <label class="category-selector__label text-admin-theme-text flex-1 cursor-pointer flex items-center gap-2 min-w-0">
            <input
              type="checkbox"
              :checked="isSelected(node.id)"
              @change="toggleCategory(node.id, $event)"
              class="rounded border-admin-theme-border text-admin-theme-primary focus:ring-admin-theme-primary flex-shrink-0"
            />
            <span class="truncate" :class="{ 'font-semibold': node.hasChildren }">{{ node.name }}</span>
          </label>

          <!-- Make Primary / Made as Primary Action -->
          <div v-if="showPrimary && isSelected(node.id)" class="category-selector__primary ml-auto flex-shrink-0">
            <span
              v-if="Number(primaryCategoryId) === node.id"
              class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800"
              title="Primary Category"
            >
              ★ Made as primary
            </span>
            <button
              v-else
              type="button"
              @click.stop.prevent="setPrimary(node.id)"
              class="text-[11px] text-admin-theme-text-muted hover:text-admin-theme-primary transition underline cursor-pointer"
            >
              Make primary
            </button>
          </div>
        </div>

        <div v-if="visibleFlattenedCategories.length === 0" class="text-xs text-admin-theme-text-muted py-3 text-center italic">
          No matching categories found.
        </div>
      </div>

      <!-- Most Used List -->
      <div v-else class="category-selector__list border-admin-theme-border bg-admin-theme-surface">
        <div v-for="node in filteredMostUsedCategories" :key="node.id" class="category-selector__item flex items-center gap-1.5">
          <label class="category-selector__label text-admin-theme-text flex-1 cursor-pointer flex items-center gap-2 min-w-0">
            <input
              type="checkbox"
              :checked="isSelected(node.id)"
              @change="toggleCategory(node.id, $event)"
              class="rounded border-admin-theme-border text-admin-theme-primary focus:ring-admin-theme-primary flex-shrink-0"
            />
            <span>{{ node.name }}</span>
          </label>
          <div v-if="showPrimary && isSelected(node.id)" class="category-selector__primary ml-auto flex-shrink-0">
            <span
              v-if="Number(primaryCategoryId) === node.id"
              class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800"
            >
              ★ Made as primary
            </span>
            <button
              v-else
              type="button"
              @click.stop.prevent="setPrimary(node.id)"
              class="text-[11px] text-admin-theme-text-muted hover:text-admin-theme-primary transition underline cursor-pointer"
            >
              Make primary
            </button>
          </div>
        </div>
        <div v-if="filteredMostUsedCategories.length === 0" class="text-xs text-admin-theme-text-muted py-3 text-center italic">
          No matching categories found.
        </div>
      </div>
    </template>

    <!-- Quick Add Form Section -->
    <div class="category-selector__actions">
      <button type="button" class="category-selector__add text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary-hover" @click="showAddForm = !showAddForm">
        {{ showAddForm ? 'Cancel' : addLabel }}
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
                {{ '—'.repeat(node.depth) }} {{ node.name }}
              </option>
            </select>
          </div>
          <button type="submit" class="w-max px-4 py-2 bg-admin-theme-primary hover:bg-admin-theme-primary-hover text-admin-theme-primary-content rounded-lg font-semibold disabled:opacity-50" :disabled="creating">
            {{ creating ? 'Creating…' : 'Add' }}
          </button>
        </form>
      </transition>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { useSlugify } from '../../../../composables/useSlugify';

interface CategoryNode {
  id: number;
  name: string;
  depth: number;
  hasChildren?: boolean;
  children?: CategoryNode[];
}

const props = withDefaults(defineProps<{
  type: string;
  label?: string;
  addLabel?: string;
  parentPlaceholder?: string;
  modelValue: number[];
  locale?: string;
  primaryCategoryId?: number | null;
  showPrimary?: boolean;
}>(), {
  showPrimary: true,
  primaryCategoryId: null,
});

const emit = defineEmits<{
  (e: 'update:modelValue', value: number[]): void;
  (e: 'update:primaryCategoryId', value: number | null): void;
  (e: 'created', category: { id: number; name: string }): void;
}>();

const { slugify } = useSlugify();

const activeTab = ref<'all' | 'most'>('all');
const searchQuery = ref('');
const collapsedIds = ref<Set<number>>(new Set());

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
    searchQuery.value = '';
    collapsedIds.value = new Set();
    void fetchCategories();
    void fetchMostUsed();
  }
);

const searchPlaceholder = computed(() => 'Search categories...');

const hasTreeChildren = computed(() => {
  return categories.value.some((node) => node.children && node.children.length > 0);
});

const isCollapsed = (id: number) => collapsedIds.value.has(id);

const toggleExpand = (id: number) => {
  const next = new Set(collapsedIds.value);
  if (next.has(id)) {
    next.delete(id);
  } else {
    next.add(id);
  }
  collapsedIds.value = next;
};

const allCollapsed = computed(() => {
  const parentIds = getParentCategoryIds(categories.value);
  return parentIds.length > 0 && parentIds.every((id) => collapsedIds.value.has(id));
});

const toggleExpandAll = () => {
  const parentIds = getParentCategoryIds(categories.value);
  if (allCollapsed.value) {
    collapsedIds.value = new Set();
  } else {
    collapsedIds.value = new Set(parentIds);
  }
};

function getParentCategoryIds(nodes: CategoryNode[]): number[] {
  let ids: number[] = [];
  nodes.forEach((node) => {
    if (node.children && node.children.length > 0) {
      ids.push(node.id);
      ids = ids.concat(getParentCategoryIds(node.children));
    }
  });
  return ids;
}

const visibleFlattenedCategories = computed(() => {
  const q = searchQuery.value.trim().toLowerCase();
  if (!q) {
    return flattenTreeWithCollapse(categories.value, 0, collapsedIds.value);
  }
  return filterAndFlattenTree(categories.value, q);
});

const filteredMostUsedCategories = computed(() => {
  const q = searchQuery.value.trim().toLowerCase();
  if (!q) return mostUsedCategories.value;
  return mostUsedCategories.value.filter((node) => node.name.toLowerCase().includes(q));
});

const flattenedCategories = computed(() => flattenTree(categories.value));
const label = computed(() => props.label ?? 'Categories');
const addLabel = computed(() => props.addLabel ?? 'Add Category');
const parentPlaceholder = computed(() => props.parentPlaceholder ?? 'Parent Category');

const apiEndpoint = computed(() => {
  if (props.type === 'product') {
    return '/api/v1/product-categories';
  }
  if (props.type === 'product_brand') {
    return '/api/v1/product-brands';
  }
  return '/api/v1/categories';
});

const fetchCategories = async () => {
  const params: any = {
    tree: true,
  };

  if (props.locale) {
    params.locale = props.locale;
  }

  // Only add type param if using the general categories endpoint
  if (apiEndpoint.value === '/api/v1/categories') {
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
  if (apiEndpoint.value === '/api/v1/categories') {
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

const setPrimary = (id: number) => {
  if (!selected.value.includes(id)) {
    selected.value = [...selected.value, id];
    emit('update:modelValue', selected.value);
  }
  emit('update:primaryCategoryId', id);
};

const toggleCategory = (id: number, event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.checked) {
    if (!selected.value.includes(id)) {
      selected.value = [...selected.value, id];
    }
    if (selected.value.length === 1 || !props.primaryCategoryId) {
      emit('update:primaryCategoryId', id);
    }
  } else {
    selected.value = selected.value.filter((item) => item !== id);
    if (Number(props.primaryCategoryId) === id) {
      const remaining = selected.value[0] ?? null;
      emit('update:primaryCategoryId', remaining);
    }
  }
  emit('update:modelValue', selected.value);
};

const isSelected = (id: number) => selected.value.includes(id);

const getIndent = (depth: number) => ({
  paddingLeft: `${Math.max(depth, 0) * 14}px`,
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
    if (apiEndpoint.value === '/api/v1/categories') {
      payload.type = props.type;
    }

    const response = await axios.post(apiEndpoint.value, payload);
    const category = response.data?.data;
    await Promise.all([fetchCategories(), fetchMostUsed()]);
    emit('created', category);
    newCategoryName.value = '';
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
  return items.map((item) => {
    const children = transformTree(item.children ?? [], depth + 1);
    return {
      id: item.id,
      name: item.name,
      depth,
      hasChildren: children.length > 0,
      children,
    };
  });
}

function flattenTree(nodes: CategoryNode[], depth = 0): CategoryNode[] {
  const result: CategoryNode[] = [];
  nodes.forEach((node) => {
    result.push({ id: node.id, name: node.name, depth, hasChildren: Boolean(node.children && node.children.length > 0) });
    if (node.children && node.children.length) {
      result.push(...flattenTree(node.children, depth + 1));
    }
  });
  return result;
}

function flattenTreeWithCollapse(nodes: CategoryNode[], depth = 0, collapsedSet: Set<number>): CategoryNode[] {
  const result: CategoryNode[] = [];
  nodes.forEach((node) => {
    const hasChildren = Boolean(node.children && node.children.length > 0);
    result.push({
      id: node.id,
      name: node.name,
      depth,
      hasChildren,
    });
    if (hasChildren && !collapsedSet.has(node.id) && node.children) {
      result.push(...flattenTreeWithCollapse(node.children, depth + 1, collapsedSet));
    }
  });
  return result;
}

function filterAndFlattenTree(nodes: CategoryNode[], query: string, depth = 0): CategoryNode[] {
  const result: CategoryNode[] = [];
  nodes.forEach((node) => {
    const nameMatches = node.name.toLowerCase().includes(query);
    const matchingChildren = node.children ? filterAndFlattenTree(node.children, query, depth + 1) : [];

    if (nameMatches || matchingChildren.length > 0) {
      const hasChildren = Boolean(node.children && node.children.length > 0);
      result.push({
        id: node.id,
        name: node.name,
        depth,
        hasChildren,
      });
      result.push(...matchingChildren);
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
  padding-bottom: 0.25rem;
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
