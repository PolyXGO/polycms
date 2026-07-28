<template>
  <div v-if="mode === 'settings'" class="mermaid-block-settings space-y-4">
    <div class="form-group">
      <label class="mb-2 block text-[10px] font-bold uppercase tracking-wider text-gray-400 font-bold">Mermaid Diagram Code</label>
      <textarea
        v-model="state.code"
        rows="10"
        class="w-full rounded-lg border border-admin-theme-border bg-admin-theme-base p-2.5 text-xs focus:ring-2 focus:ring-admin-theme-primary font-mono"
        placeholder="graph TD&#10;  A[Start] --> B(Process)&#10;  B --> C{Decision}&#10;  C -->|Yes| D[Success]&#10;  C -->|No| E[Fail]"
      ></textarea>
      <p class="mt-2 text-[10px] leading-4 text-admin-theme-text-muted">
        Use standard Mermaid syntax. The output is generated automatically.
      </p>
    </div>
  </div>

  <div v-else class="mermaid-chart-block-preview my-4 border border-admin-theme-border rounded-xl bg-admin-theme-base overflow-hidden relative shadow-sm transition-all">
    <div class="bg-admin-theme-input-bg px-3 py-2 flex items-center border-b border-admin-theme-border select-none">
      <svg class="w-5 h-5 text-emerald-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
      </svg>
      <span class="font-medium text-sm text-admin-theme-text">Mermaid Diagram</span>
    </div>
    
    <div class="p-6">
      <div v-if="!state.code" class="text-center text-admin-theme-text-muted py-8 text-sm bg-admin-theme-input-bg rounded-lg border border-dashed border-admin-theme-border">
        No Mermaid diagram code provided. Choose settings to add diagram code.
      </div>
      <div v-else class="w-full flex flex-col items-center justify-center">
        <!-- Render target -->
        <div ref="mermaidContainerRef" class="mermaid-render-output w-full flex justify-center bg-admin-theme-input-bg border border-admin-theme-border rounded-lg p-6 min-h-[150px] items-center overflow-x-auto transition-all">
          <span class="text-xs text-admin-theme-text-muted">Rendering diagram...</span>
        </div>
        <!-- Raw code preview toggler for debug -->
        <details class="w-full mt-4 text-xs">
          <summary class="cursor-pointer text-admin-theme-text-secondary select-none font-semibold">View Source Code</summary>
          <pre class="bg-admin-theme-input-bg p-3 border border-admin-theme-border rounded-lg mt-2 overflow-x-auto font-mono text-[11px] text-admin-theme-text-muted">{{ state.code }}</pre>
        </details>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, watch, onMounted, nextTick, onBeforeUnmount } from 'vue';

const props = defineProps<{
  modelValue: Record<string, any> | null;
  mode?: 'settings' | 'preview';
  data?: Record<string, any> | null;
}>();

const emit = defineEmits(['update:modelValue']);
const isSyncingFromProps = ref(false);

const mermaidContainerRef = ref<HTMLElement | null>(null);

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

function syncState(source?: Record<string, any>) {
  if (!source) return;
  isSyncingFromProps.value = true;
  state.code = source.code || '';
  nextTick(() => {
    isSyncingFromProps.value = false;
    renderChart();
  });
}

// Load self-hosted Mermaid library dynamically
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
    const id = `mermaid-block-${Math.round(Math.random() * 10000000)}`;
    const { svg } = await m.render(id, state.code);
    if (mermaidContainerRef.value) {
      mermaidContainerRef.value.innerHTML = svg;
    }
  } catch (error) {
    console.error('Mermaid block render error:', error);
    if (mermaidContainerRef.value) {
      mermaidContainerRef.value.innerHTML = `<div class="text-xs text-red-500 font-mono p-4">Error parsing diagram: ${error}</div>`;
    }
    // Reset syntax error states in library
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
    // CDN fallback
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

onMounted(() => {
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
