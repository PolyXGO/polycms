<template>
  <div v-if="mode === 'settings'" class="project-diagram-block-settings space-y-4 p-1 select-none">
    <!-- Project Selector Field -->
    <div class="form-group space-y-1.5">
      <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-400">
        Select Project
      </label>
      
      <!-- Live Search Input for Projects -->
      <div class="relative">
        <input
          v-model="projectSearchQuery"
          type="text"
          class="w-full rounded-lg border border-admin-theme-border bg-admin-theme-input-bg px-3 py-2 text-xs text-admin-theme-text focus:ring-2 focus:ring-admin-theme-primary font-medium placeholder-gray-400"
          placeholder="🔍 Search project name or code..."
        />
      </div>

      <!-- Project Select Dropdown (Strict Pick from Existing Projects) -->
      <select
        :value="selectedProjectCode"
        @change="onProjectDropdownChange"
        class="w-full rounded-lg border border-admin-theme-border bg-admin-theme-base p-2.5 text-xs text-admin-theme-text focus:ring-2 focus:ring-admin-theme-primary font-semibold cursor-pointer"
        :disabled="projectsLoading"
      >
        <option value="">-- {{ projectsLoading ? 'Loading projects...' : 'Select Project' }} --</option>
        <option
          v-for="p in filteredProjects"
          :key="p.id || p.code"
          :value="p.code || p.slug || p.id"
        >
          {{ p.name }} ({{ p.diagrams?.length || 0 }} diagrams)
        </option>
      </select>
    </div>

    <!-- Diagram Selector Field -->
    <div class="form-group space-y-1.5">
      <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-400">
        Select Diagram
      </label>

      <!-- Live Search Input for Diagrams -->
      <div class="relative">
        <input
          v-model="diagramSearchQuery"
          type="text"
          class="w-full rounded-lg border border-admin-theme-border bg-admin-theme-input-bg px-3 py-2 text-xs text-admin-theme-text focus:ring-2 focus:ring-admin-theme-primary font-medium placeholder-gray-400"
          placeholder="🔍 Search diagram title or code..."
          :disabled="!availableDiagrams.length"
        />
      </div>

      <!-- Diagram Select Dropdown (Strict Pick from Existing Diagrams) -->
      <select
        v-model="state.code"
        class="w-full rounded-lg border border-admin-theme-border bg-admin-theme-base p-2.5 text-xs text-admin-theme-text focus:ring-2 focus:ring-admin-theme-primary font-semibold cursor-pointer"
        :disabled="!availableDiagrams.length"
      >
        <option value="">-- {{ availableDiagrams.length ? 'Select Diagram' : 'No diagrams available in project' }} --</option>
        <option
          v-for="d in filteredDiagrams"
          :key="d.code || d.id"
          :value="d.code || d.id"
        >
          {{ d.title }} [code: {{ d.code || d.id }}]
        </option>
      </select>
    </div>
  </div>

  <div v-else class="mermaid-chart-block-preview my-4 border border-admin-theme-border rounded-xl bg-admin-theme-base overflow-hidden relative shadow-sm transition-all">
    <div class="bg-admin-theme-input-bg px-3 py-2 flex items-center justify-between border-b border-admin-theme-border select-none">
      <div class="flex items-center">
        <svg class="w-5 h-5 text-emerald-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
        </svg>
        <span class="font-medium text-sm text-admin-theme-text">Mermaid Diagram</span>
      </div>
      <div class="flex items-center space-x-2 text-[11px] font-mono">
        <span v-if="state.project" class="px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-400 font-bold">{{ state.project }}</span>
        <span v-if="state.code" class="px-2 py-0.5 rounded bg-admin-theme-base text-admin-theme-text-muted font-bold">code: {{ state.code }}</span>
      </div>
    </div>
    
    <div class="p-6">
      <div v-if="loading" class="text-center text-admin-theme-text-muted py-8 text-sm bg-admin-theme-input-bg rounded-lg border border-dashed border-admin-theme-border">
        Loading Project Hub Diagram...
      </div>
      <div v-else-if="!resolvedMermaidCode" class="text-center text-admin-theme-text-muted py-8 text-sm bg-admin-theme-input-bg rounded-lg border border-dashed border-admin-theme-border">
        No Mermaid diagram code found for project: {{ state.project || 'all' }} (code: {{ state.code }}).
      </div>
      <div v-else class="w-full flex flex-col items-center justify-center">
        <!-- Render target -->
        <div ref="mermaidContainerRef" class="mermaid-render-output w-full flex justify-center bg-white border border-gray-200/80 rounded-xl p-6 min-h-[150px] items-center overflow-x-auto transition-all shadow-sm">
          <span class="text-xs text-gray-500">Rendering diagram...</span>
        </div>
        <!-- Raw code preview toggler for debug -->
        <details class="w-full mt-4 text-xs">
          <summary class="cursor-pointer text-admin-theme-text-secondary select-none font-semibold">View Source Code</summary>
          <pre class="bg-admin-theme-input-bg p-3 border border-admin-theme-border rounded-lg mt-2 overflow-x-auto font-mono text-[11px] text-admin-theme-text-muted">{{ resolvedMermaidCode }}</pre>
        </details>
      </div>
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
const loading = ref(false);
const resolvedMermaidCode = ref<string>('');

const projectsList = ref<any[]>([]);
const projectsLoading = ref(true);
const projectSearchQuery = ref('');
const diagramSearchQuery = ref('');

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
  project: readAttr('project', readAttr('project_code', '')),
  code: readAttr('code', '')
});

function buildPayload() {
  const base = props.modelValue || props.data || {};
  return {
    ...base,
    project: state.project,
    code: state.code
  };
}

const loadProjectsList = async () => {
  try {
    projectsLoading.value = true;
    const res = await axios.get('/api/v1/projects', { params: { per_page: 100 } });
    projectsList.value = res.data.data?.data || res.data.data || res.data || [];
  } catch (e) {
    console.error('Failed to load projects list for block settings:', e);
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

const selectedProject = computed(() => {
  if (!state.project) return null;
  const searchProj = String(state.project).toLowerCase().trim();
  return projectsList.value.find(p => 
    String(p.code || '').toLowerCase() === searchProj ||
    String(p.slug || '').toLowerCase() === searchProj ||
    String(p.id) === searchProj
  ) || null;
});

const selectedProjectCode = computed(() => {
  if (!selectedProject.value) return state.project || '';
  return selectedProject.value.code || selectedProject.value.slug || String(selectedProject.value.id);
});

const availableDiagrams = computed(() => {
  if (!selectedProject.value || !Array.isArray(selectedProject.value.diagrams)) return [];
  return selectedProject.value.diagrams;
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

const onProjectDropdownChange = (e: Event) => {
  const target = e.target as HTMLSelectElement;
  state.project = target.value;
  diagramSearchQuery.value = '';
  if (availableDiagrams.value.length > 0) {
    state.code = availableDiagrams.value[0].code || availableDiagrams.value[0].id || '';
  } else {
    state.code = '';
  }
};

// Fetch diagram code from ProjectHub API
const resolveDiagramCode = async () => {
  if (!state.code) {
    resolvedMermaidCode.value = '';
    return;
  }

  // Check if state.code is direct Mermaid syntax
  const isDirectMermaid = /^(flowchart|graph|sequenceDiagram|classDiagram|stateDiagram|erDiagram|gantt|pie)/i.test(state.code.trim());
  if (isDirectMermaid) {
    resolvedMermaidCode.value = state.code;
    renderChart();
    return;
  }

  loading.value = true;
  try {
    if (!projectsList.value.length) {
      const res = await axios.get('/api/v1/projects', { params: { per_page: 100 } });
      projectsList.value = res.data.data?.data || res.data.data || res.data || [];
    }

    let foundCode = '';
    const searchCode = state.code.toLowerCase().trim();
    const searchProject = (state.project || '').toLowerCase().trim();

    for (const p of projectsList.value) {
      if (searchProject && p.code?.toLowerCase() !== searchProject && p.slug?.toLowerCase() !== searchProject) {
        continue;
      }
      if (Array.isArray(p.diagrams)) {
        for (const d of p.diagrams) {
          const dCode = (d.code || d.id || '').toLowerCase().trim();
          if (dCode === searchCode && d.mermaid) {
            foundCode = d.mermaid;
            break;
          }
        }
      }
      if (foundCode) break;
    }

    if (!foundCode) {
      for (const p of projectsList.value) {
        if (Array.isArray(p.diagrams)) {
          for (const d of p.diagrams) {
            const dCode = (d.code || d.id || '').toLowerCase().trim();
            if (dCode === searchCode && d.mermaid) {
              foundCode = d.mermaid;
              break;
            }
          }
        }
        if (foundCode) break;
      }
    }

    resolvedMermaidCode.value = foundCode;
  } catch (err) {
    console.error('Failed to resolve project diagram:', err);
    resolvedMermaidCode.value = '';
  } finally {
    loading.value = false;
    nextTick(() => {
      renderChart();
    });
  }
};

const initMermaid = () => {
  if (typeof (window as any).mermaid !== 'undefined') {
    (window as any).mermaid.initialize({
      startOnLoad: false,
      theme: 'default',
      securityLevel: 'loose',
      themeVariables: {
        fontFamily: 'system-ui, -apple-system, sans-serif',
        fontSize: '13px',
        primaryColor: '#ffffff',
        primaryTextColor: '#0f172a',
        primaryBorderColor: '#94a3b8',
        lineColor: '#64748b',
        secondaryColor: '#f8fafc',
        tertiaryColor: '#ffffff'
      }
    });
  }
};

const renderChart = async () => {
  if (props.mode === 'settings' || !resolvedMermaidCode.value || !mermaidContainerRef.value) return;

  const m = (window as any).mermaid;
  if (!m) return;

  try {
    const id = `mermaid-proj-block-${Math.round(Math.random() * 10000000)}`;
    const { svg } = await m.render(id, resolvedMermaidCode.value);
    if (mermaidContainerRef.value) {
      mermaidContainerRef.value.innerHTML = svg;
    }
  } catch (error) {
    console.error('Mermaid render error:', error);
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
    resolveDiagramCode();
    return;
  }

  const script = document.createElement('script');
  script.src = '/assets/vendor/mermaid-10.x/mermaid.min.js';
  script.onload = () => {
    initMermaid();
    resolveDiagramCode();
  };
  script.onerror = () => {
    const cdnScript = document.createElement('script');
    cdnScript.src = 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js';
    cdnScript.onload = () => {
      initMermaid();
      resolveDiagramCode();
    };
    document.head.appendChild(cdnScript);
  };
  document.head.appendChild(script);
};

function syncState(source?: Record<string, any>) {
  if (!source) return;
  isSyncingFromProps.value = true;
  state.project = source.project || source.project_code || '';
  state.code = source.code || '';
  nextTick(() => {
    isSyncingFromProps.value = false;
    resolveDiagramCode();
  });
}

onMounted(() => {
  loadProjectsList();
  loadMermaidScript();
});

watch(
  () => [props.modelValue, props.data],
  () => {
    syncState(props.modelValue || props.data || undefined);
  },
  { deep: true, immediate: true }
);

watch(
  () => [state.project, state.code],
  () => {
    if (isSyncingFromProps.value) return;
    emit('update:modelValue', buildPayload());
    resolveDiagramCode();
  }
);
</script>
