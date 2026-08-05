<template>
  <div v-if="mode === 'settings'" class="mermaid-block-settings space-y-4 p-1 select-none">
    <!-- Quick Tab Selector: Project Hub Select vs Raw Code -->
    <div class="flex items-center space-x-2 border-b border-admin-theme-border pb-2">
      <button
        type="button"
        @click="settingsTab = 'project_hub'"
        :class="settingsTab === 'project_hub' ? 'bg-indigo-600 text-white font-bold shadow-sm' : 'bg-admin-theme-input-bg text-admin-theme-text-muted hover:text-admin-theme-text'"
        class="px-2.5 py-1 text-[11px] rounded-md transition-all flex items-center cursor-pointer"
      >
        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
        </svg>
        Product / Diagram
      </button>
      <button
        type="button"
        @click="settingsTab = 'raw_code'"
        :class="settingsTab === 'raw_code' ? 'bg-indigo-600 text-white font-bold shadow-sm' : 'bg-admin-theme-input-bg text-admin-theme-text-muted hover:text-admin-theme-text'"
        class="px-2.5 py-1 text-[11px] rounded-md transition-all flex items-center cursor-pointer"
      >
        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
        </svg>
        Edit Raw Code
      </button>
    </div>

    <!-- Tab 1: Project Hub Selector -->
    <div v-if="settingsTab === 'project_hub'" class="space-y-3">
      <!-- Select Product / Project Field -->
      <div class="form-group space-y-1">
        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400">Select Product / Project</label>
        <input
          v-model="projectSearchQuery"
          type="text"
          class="w-full rounded-lg border border-admin-theme-border bg-admin-theme-input-bg px-2.5 py-1.5 text-xs text-admin-theme-text focus:ring-2 focus:ring-admin-theme-primary placeholder-gray-400"
          placeholder="🔍 Search product or project..."
        />
        <select
          :value="selectedProjectCode"
          @change="onProjectSelectChange"
          class="w-full rounded-lg border border-admin-theme-border bg-admin-theme-base p-2 text-xs text-admin-theme-text focus:ring-2 focus:ring-admin-theme-primary font-semibold cursor-pointer"
          :disabled="projectsLoading"
        >
          <option value="">-- {{ projectsLoading ? 'Loading...' : 'Select Product / Project' }} --</option>
          <option v-for="p in filteredProjects" :key="p.id || p.code" :value="p.code || p.slug || p.id">
            {{ p.name }} ({{ p.diagrams?.length || 0 }} diagrams)
          </option>
        </select>
      </div>

      <!-- Select Related Diagram Field -->
      <div class="form-group space-y-1">
        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400">Select Related Diagram</label>
        <input
          v-model="diagramSearchQuery"
          type="text"
          class="w-full rounded-lg border border-admin-theme-border bg-admin-theme-input-bg px-2.5 py-1.5 text-xs text-admin-theme-text focus:ring-2 focus:ring-admin-theme-primary placeholder-gray-400"
          placeholder="🔍 Search diagram..."
          :disabled="!availableDiagrams.length"
        />
        <select
          :value="selectedDiagramCode"
          @change="onDiagramSelectChange"
          class="w-full rounded-lg border border-admin-theme-border bg-admin-theme-base p-2 text-xs text-admin-theme-text focus:ring-2 focus:ring-admin-theme-primary font-semibold cursor-pointer"
          :disabled="!availableDiagrams.length"
        >
          <option value="">-- {{ availableDiagrams.length ? 'Select Diagram' : 'No diagrams available' }} --</option>
          <option v-for="d in filteredDiagrams" :key="d.code || d.id" :value="d.code || d.id">
            {{ d.title }} [code: {{ d.code || d.id }}]
          </option>
        </select>
      </div>
    </div>

    <!-- Tab 2: Raw Code Input -->
    <div v-else class="form-group space-y-1">
      <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400">Mermaid Diagram Code</label>
      <textarea
        v-model="state.code"
        rows="8"
        class="w-full rounded-lg border border-admin-theme-border bg-admin-theme-base p-2.5 text-xs focus:ring-2 focus:ring-admin-theme-primary font-mono"
        placeholder="graph TD&#10;  A[Start] --> B(Process)"
      ></textarea>
    </div>
  </div>

  <div v-else class="mermaid-chart-block-preview my-4 w-full flex flex-col items-center justify-center overflow-x-auto">
    <div v-if="!state.code" class="text-center text-admin-theme-text-muted py-4 text-xs font-mono">
      No Mermaid diagram code provided. Choose settings to add diagram code.
    </div>
    <div v-else class="w-full flex flex-col items-center justify-center">
      <!-- Render target: Borderless, Transparent, 100% Full Width -->
      <div ref="mermaidContainerRef" class="mermaid-render-output w-full flex justify-center items-center overflow-x-auto p-0 border-0 bg-transparent shadow-none">
        <span class="text-xs text-admin-theme-text-muted">Rendering diagram...</span>
      </div>
      <!-- Raw code preview toggler for debug -->
      <details class="w-full mt-2 text-xs opacity-60 hover:opacity-100 transition-opacity">
        <summary class="cursor-pointer text-admin-theme-text-muted select-none text-[11px] font-mono">View Source Code</summary>
        <pre class="bg-admin-theme-input-bg p-2 border border-admin-theme-border rounded mt-1 overflow-x-auto font-mono text-[10px] text-admin-theme-text-muted">{{ state.code }}</pre>
      </details>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, watch, onMounted, nextTick } from 'vue';
import axios from 'axios';

const props = defineProps<{
  modelValue: Record<string, any> | null;
  mode?: 'settings' | 'preview';
  data?: Record<string, any> | null;
}>();

const emit = defineEmits(['update:modelValue']);
const isSyncingFromProps = ref(false);
const mermaidContainerRef = ref<HTMLElement | null>(null);

const settingsTab = ref<'project_hub' | 'raw_code'>('project_hub');
const projectsList = ref<any[]>([]);
const projectsLoading = ref(true);
const projectSearchQuery = ref('');
const diagramSearchQuery = ref('');
const selectedProjectCode = ref('');
const selectedDiagramCode = ref('');

function cloneValue<T>(value: T): T {
  if (value === undefined || value === null) return value;
  return JSON.parse(JSON.stringify(value));
}

function hasAttr(source: Record<string, any> | null | undefined, key: string) {
  return Boolean(source) && Object.prototype.hasOwnProperty.call(source, key);
}

function readAttr<T>(key: string, fallback: T): T {
  if (hasAttr(props.modelValue, key)) {
    return cloneValue(props.modelValue?.[key]) as T;
  }
  if (hasAttr(props.data, key)) {
    return cloneValue(props.data?.[key]) as T;
  }
  return cloneValue(fallback) as T;
}

const state = reactive({
  code: readAttr('code', '')
});

function buildPayload() {
  const base = props.modelValue || props.data || {};
  return {
    ...base,
    code: state.code
  };
}

const loadProjectsList = async () => {
  try {
    projectsLoading.value = true;
    const res = await axios.get('/api/v1/projects', { params: { per_page: 100 } });
    projectsList.value = res.data.data?.data || res.data.data || res.data || [];
  } catch (e) {
    console.error('Failed to load projects for Mermaid settings:', e);
  } finally {
    projectsLoading.value = false;
  }
};

const filteredProjects = computed(() => {
  if (!projectSearchQuery.value.trim()) {
    return projectsList.value;
  }
  const q = projectSearchQuery.value.toLowerCase().trim();
  return projectsList.value.filter(p => 
    (p.name && p.name.toLowerCase().includes(q)) ||
    (p.code && p.code.toLowerCase().includes(q)) ||
    (p.slug && p.slug.toLowerCase().includes(q))
  );
});

const selectedProjectObj = computed(() => {
  if (!selectedProjectCode.value) return null;
  const searchProj = selectedProjectCode.value.toLowerCase().trim();
  return projectsList.value.find(p => 
    String(p.code || '').toLowerCase() === searchProj ||
    String(p.slug || '').toLowerCase() === searchProj ||
    String(p.id) === searchProj
  ) || null;
});

const availableDiagrams = computed(() => {
  if (!selectedProjectObj.value || !Array.isArray(selectedProjectObj.value.diagrams)) return [];
  return selectedProjectObj.value.diagrams;
});

const filteredDiagrams = computed(() => {
  if (!diagramSearchQuery.value.trim()) {
    return availableDiagrams.value;
  }
  const q = diagramSearchQuery.value.toLowerCase().trim();
  return availableDiagrams.value.filter((d: any) => 
    (d.title && d.title.toLowerCase().includes(q)) ||
    (d.code && d.code.toLowerCase().includes(q)) ||
    (d.id && String(d.id).toLowerCase().includes(q))
  );
});

const onProjectSelectChange = (e: Event) => {
  const target = e.target as HTMLSelectElement;
  selectedProjectCode.value = target.value;
  diagramSearchQuery.value = '';
  if (availableDiagrams.value.length > 0) {
    const firstDiag = availableDiagrams.value[0];
    selectedDiagramCode.value = firstDiag.code || firstDiag.id || '';
    state.code = firstDiag.mermaid || firstDiag.code || firstDiag.id || '';
  } else {
    selectedDiagramCode.value = '';
  }
};

const onDiagramSelectChange = (e: Event) => {
  const target = e.target as HTMLSelectElement;
  selectedDiagramCode.value = target.value;
  const foundDiag = availableDiagrams.value.find((d: any) => (d.code || d.id) === target.value);
  if (foundDiag) {
    state.code = foundDiag.mermaid || foundDiag.code || foundDiag.id || '';
  }
};

const resolveMermaidCode = async (rawCode: string) => {
  if (!rawCode) return '';
  const trimmed = rawCode.trim();
  const isDirect = /^(flowchart|graph|sequenceDiagram|classDiagram|stateDiagram|erDiagram|gantt|pie|C4Context)/i.test(trimmed);
  if (isDirect) return trimmed;

  try {
    if (!projectsList.value.length) {
      const res = await axios.get('/api/v1/projects', { params: { per_page: 100 } });
      projectsList.value = res.data.data?.data || res.data.data || res.data || [];
    }
    const searchCode = trimmed.toLowerCase();
    for (const p of projectsList.value) {
      if (Array.isArray(p.diagrams)) {
        for (const d of p.diagrams) {
          const dCode = (d.code || d.id || '').toLowerCase().trim();
          if (dCode === searchCode && d.mermaid) {
            return d.mermaid;
          }
        }
      }
    }
  } catch (e) {}
  return rawCode;
};

const initMermaid = () => {
  if (typeof (window as any).mermaid !== 'undefined') {
    (window as any).mermaid.initialize({
      startOnLoad: false,
      theme: document.documentElement.classList.contains('dark') ? 'dark' : 'default',
      securityLevel: 'loose',
      themeVariables: {
        background: 'transparent',
        primaryColor: '#6366f1',
        textColor: '#334155',
        lineColor: '#cbd5e1'
      }
    });
  }
};

const renderChart = async () => {
  if (props.mode === 'settings' || !state.code || !mermaidContainerRef.value) return;

  const m = (window as any).mermaid;
  if (!m) return;

  try {
    const codeToRender = await resolveMermaidCode(state.code);
    if (!codeToRender) return;

    const id = `mermaid-block-${Math.round(Math.random() * 10000000)}`;
    const { svg } = await m.render(id, codeToRender);
    if (mermaidContainerRef.value) {
      mermaidContainerRef.value.innerHTML = svg;
    }
  } catch (error) {
    console.error('Mermaid block render error:', error);
    if (mermaidContainerRef.value) {
      mermaidContainerRef.value.innerHTML = `<div class="text-xs text-red-500 font-mono p-4">Error parsing diagram: ${error}</div>`;
    }
    try {
      m.parseError = () => {};
    } catch (_) {}
  }
};

const loadMermaidScript = () => {
  if (typeof (window as any).mermaid !== 'undefined') {
    initMermaid();
    renderChart();
    return;
  }

  const script = document.createElement('script');
  script.src = '/assets/vendor/mermaid-10.x/mermaid.min.js';
  script.onload = () => {
    initMermaid();
    renderChart();
  };
  script.onerror = () => {
    const cdnScript = document.createElement('script');
    cdnScript.src = 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js';
    cdnScript.onload = () => {
      initMermaid();
      renderChart();
    };
    document.head.appendChild(cdnScript);
  };
  document.head.appendChild(script);
};

function syncState(source?: Record<string, any>) {
  if (!source) return;
  isSyncingFromProps.value = true;
  state.code = source.code || '';
  nextTick(() => {
    isSyncingFromProps.value = false;
    renderChart();
  });
}

onMounted(() => {
  loadProjectsList();
  if (props.mode !== 'settings') {
    loadMermaidScript();
  }
});

watch(
  state,
  () => {
    if (isSyncingFromProps.value) return;
    if (props.mode === 'settings') {
      emit('update:modelValue', buildPayload());
    } else {
      renderChart();
    }
  },
  { deep: true }
);

watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue) syncState(newValue);
  },
  { deep: true, immediate: true }
);
</script>

<style scoped>
.mermaid-render-output :deep(svg) {
  max-width: 100% !important;
  height: auto !important;
}
</style>
