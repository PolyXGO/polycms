<template>
  <node-view-wrapper 
    class="mermaid-chart-node my-6 border rounded-xl bg-admin-theme-base overflow-hidden relative shadow-sm transition-all" 
    :class="{'ring-2 ring-admin-theme-primary border-transparent': selected}"
  >
    <!-- Header Controls -->
    <div class="bg-admin-theme-input-bg px-4 py-2.5 flex items-center justify-between border-b border-admin-theme-border cursor-pointer select-none group" contenteditable="false">
      <div class="flex items-center gap-3">
        <!-- Chart Icon -->
        <div class="p-1.5 bg-admin-theme-primary/10 rounded-lg text-admin-theme-primary">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
          </svg>
        </div>
        <div>
          <span class="font-semibold text-sm text-admin-theme-text block">Mermaid Diagram</span>
          <span class="text-xs text-admin-theme-text-muted">Interactive Chart Engine</span>
        </div>

        <!-- Tabs -->
        <div class="flex items-center bg-admin-theme-base border border-admin-theme-border rounded-lg p-0.5 ml-4">
          <button 
            type="button"
            @click.stop="activeTab = 'preview'"
            class="text-xs px-3 py-1 rounded-md font-medium transition-all"
            :class="activeTab === 'preview' ? 'bg-admin-theme-primary text-admin-theme-primary-content shadow-sm' : 'text-admin-theme-text-muted hover:text-admin-theme-text'"
          >
            Preview
          </button>
          <button 
            type="button"
            @click.stop="activeTab = 'edit'"
            class="text-xs px-3 py-1 rounded-md font-medium transition-all"
            :class="activeTab === 'edit' ? 'bg-admin-theme-primary text-admin-theme-primary-content shadow-sm' : 'text-admin-theme-text-muted hover:text-admin-theme-text'"
          >
            Edit Code
          </button>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <!-- Quick Templates Selector -->
        <div class="relative" ref="templateDropdownRef">
          <button 
            type="button"
            @click.stop="showTemplates = !showTemplates"
            class="text-xs px-2.5 py-1.5 bg-admin-theme-base border border-admin-theme-border text-admin-theme-text rounded-lg hover:bg-admin-theme-input-bg transition-colors font-medium flex items-center gap-1"
          >
            Templates
            <svg class="w-3.5 h-3.5 text-admin-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- Dropdown menu -->
          <div 
            v-if="showTemplates" 
            class="absolute right-0 mt-1 w-56 bg-admin-theme-base border border-admin-theme-border rounded-lg shadow-lg py-1 z-50 overflow-hidden"
          >
            <a 
              v-for="tmpl in templates" 
              :key="tmpl.name"
              href="javascript:void(0)"
              @click.stop="applyTemplate(tmpl.code)"
              class="block px-4 py-2 text-xs text-admin-theme-text hover:bg-admin-theme-primary/10 transition-colors font-medium"
            >
              <div class="font-semibold">{{ tmpl.name }}</div>
              <div class="text-[10px] text-admin-theme-text-muted">{{ tmpl.desc }}</div>
            </a>
          </div>
        </div>

        <!-- Delete button -->
        <button 
          type="button" 
          @click.stop="deleteNode"
          class="p-1.5 bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white rounded-lg opacity-0 group-hover:opacity-100 transition-all shadow-sm flex items-center justify-center"
          title="Delete Diagram"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
        </button>
      </div>
    </div>
    
    <!-- Body Content -->
    <div class="p-4" contenteditable="false">
      <!-- Loading State -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-12 text-admin-theme-text-muted">
        <svg class="animate-spin h-8 w-8 text-admin-theme-primary mb-3" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-sm font-medium">Loading Diagram Engine...</span>
      </div>

      <!-- Tab Panels -->
      <div v-else>
        <!-- Preview Panel -->
        <div v-show="activeTab === 'preview'" class="flex flex-col items-center">
          <!-- Syntax Error Block -->
          <div v-if="syntaxError" class="w-full bg-red-500/10 border border-red-500/20 text-red-500 px-4 py-3 rounded-lg flex items-start gap-3 my-2 text-sm">
            <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
              <span class="font-semibold block mb-0.5">Syntax Error Detected</span>
              <pre class="text-xs font-mono bg-black/5 dark:bg-white/5 p-2 rounded-md mt-1 overflow-x-auto whitespace-pre-wrap">{{ syntaxError }}</pre>
            </div>
          </div>

          <!-- Dynamic SVG Render Container -->
          <div 
            v-else
            ref="mermaidContainerRef" 
            class="mermaid-render-output w-full flex justify-center bg-admin-theme-input-bg border border-admin-theme-border rounded-lg p-6 min-h-[150px] items-center overflow-x-auto transition-all"
          >
            <!-- SVG injected here dynamically -->
          </div>
        </div>

        <!-- Editor Panel -->
        <div v-show="activeTab === 'edit'" class="flex flex-col gap-3">
          <!-- Live Status Indicator -->
          <div class="flex items-center justify-between text-xs">
            <span class="text-admin-theme-text-muted font-medium">Syntax Editor:</span>
            <div class="flex items-center gap-1.5">
              <span 
                class="w-2.5 h-2.5 rounded-full inline-block"
                :class="syntaxError ? 'bg-red-500 animate-pulse' : 'bg-green-500'"
              ></span>
              <span class="font-semibold" :class="syntaxError ? 'text-red-500' : 'text-green-500'">
                {{ syntaxError ? 'Invalid Syntax' : 'Valid Syntax' }}
              </span>
            </div>
          </div>

          <!-- Syntax Textarea -->
          <div class="relative border border-admin-theme-border rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-admin-theme-primary focus-within:border-transparent">
            <textarea 
              v-model="localCode"
              @input="handleInput"
              rows="8"
              class="w-full p-4 font-mono text-xs bg-admin-theme-input-bg text-admin-theme-text border-0 outline-none focus:ring-0 leading-relaxed"
              placeholder="e.g. graph TD&#10;    A[Start] --> B[Process]&#10;    B --> C[End]"
            ></textarea>
          </div>

          <!-- Editor Actions / Helper Bar -->
          <div class="flex flex-wrap items-center justify-between gap-2 border-t border-admin-theme-border pt-3">
            <div class="flex items-center gap-1.5">
              <button 
                type="button" 
                @click.stop="insertHelper(' --> ')"
                class="text-[10px] px-2 py-1 bg-admin-theme-base border border-admin-theme-border rounded hover:bg-admin-theme-input-bg text-admin-theme-text font-semibold font-mono"
              >
                --> Arrow
              </button>
              <button 
                type="button" 
                @click.stop="insertHelper('[Text]')"
                class="text-[10px] px-2 py-1 bg-admin-theme-base border border-admin-theme-border rounded hover:bg-admin-theme-input-bg text-admin-theme-text font-semibold font-mono"
              >
                [Node]
              </button>
              <button 
                type="button" 
                @click.stop="insertHelper('{Q}')"
                class="text-[10px] px-2 py-1 bg-admin-theme-base border border-admin-theme-border rounded hover:bg-admin-theme-input-bg text-admin-theme-text font-semibold font-mono"
              >
                {Decision}
              </button>
              <button 
                type="button" 
                @click.stop="insertHelper('|Text| ')"
                class="text-[10px] px-2 py-1 bg-admin-theme-base border border-admin-theme-border rounded hover:bg-admin-theme-input-bg text-admin-theme-text font-semibold font-mono"
              >
                |label|
              </button>
            </div>
            <button 
              type="button" 
              @click.stop="activeTab = 'preview'"
              class="text-xs px-3.5 py-1.5 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg font-semibold hover:bg-admin-theme-primary-hover shadow-sm"
            >
              Update & Preview
            </button>
          </div>
        </div>
      </div>
    </div>
  </node-view-wrapper>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, onBeforeUnmount } from 'vue';
import { NodeViewWrapper, nodeViewProps } from '@tiptap/vue-3';

const props = defineProps(nodeViewProps);

const activeTab = ref('preview');
const localCode = ref(props.node.attrs.code || '');
const loading = ref(true);
const syntaxError = ref<string | null>(null);
const showTemplates = ref(false);

const mermaidContainerRef = ref<HTMLElement | null>(null);
const templateDropdownRef = ref<HTMLElement | null>(null);

// Preset templates to wows the user
const templates = [
  {
    name: 'Flowchart (Vertical)',
    desc: 'Top-down flow diagram',
    code: 'graph TD\n    A[Start] --> B[Check Condition]\n    B -->|Yes| C[Perform Action]\n    B -->|No| D[Raise Error]\n    C --> E[Success]\n    D --> E'
  },
  {
    name: 'Flowchart (Horizontal)',
    desc: 'Left-to-right flow diagram',
    code: 'graph LR\n    A[Input Data] --> B(Process Logic) --> C{Is Valid?}\n    C -->|Yes| D[Save/Store]\n    C -->|No| E[Cancel]'
  },
  {
    name: 'Sequence Diagram',
    desc: 'Interaction between objects',
    code: 'sequenceDiagram\n    actor User as Customer\n    participant App as Web App\n    participant API as Backend Server\n    \n    User->>App: Submit Order Request\n    App->>API: POST /checkout\n    API-->>App: Return 200 OK\n    App-->>User: Display Order Confirmed'
  },
  {
    name: 'State Diagram',
    desc: 'Lifecycle transitions',
    code: 'stateDiagram-v2\n    [*] --> New\n    New --> Processing : Begin\n    Processing --> Completed : Success\n    Processing --> Failed : System Error\n    Failed --> Processing : Retry\n    Completed --> [*]'
  }
];

// Load self-hosted Mermaid library dynamically
const initMermaid = () => {
  if (typeof (window as any).mermaid !== 'undefined') {
    const isDark = document.documentElement.classList.contains('dark');
    (window as any).mermaid.initialize({
      startOnLoad: false,
      theme: 'base',
      securityLevel: 'loose',
      themeVariables: isDark ? {
        background: '#0a0a0a',
        primaryColor: '#0a0a0a',
        primaryTextColor: '#ededed',
        primaryBorderColor: '#333333',
        lineColor: '#444444',
        secondaryColor: '#1f1f1f',
        tertiaryColor: '#121212',
        noteBkgColor: '#1f1f1f',
        noteTextColor: '#ededed',
        actorBkg: '#0a0a0a',
        actorBorder: '#333333',
        actorTextColor: '#ededed',
        signalColor: '#ededed',
        signalTextColor: '#a0a0a0',
        labelBoxBkgColor: '#0a0a0a',
        labelBoxBorderColor: '#333333',
        labelTextColor: '#a0a0a0',
        loopBkgColor: '#121212',
        loopBorderColor: '#333333',
        fontSize: '12px',
        fontFamily: 'Inter, system-ui, -apple-system, sans-serif'
      } : {
        background: '#ffffff',
        primaryColor: '#ffffff',
        primaryTextColor: '#111111',
        primaryBorderColor: '#e2e8f0',
        lineColor: '#cbd5e1',
        secondaryColor: '#f8fafc',
        tertiaryColor: '#f1f5f9',
        noteBkgColor: '#f8fafc',
        noteTextColor: '#111111',
        actorBkg: '#ffffff',
        actorBorder: '#e2e8f0',
        actorTextColor: '#111111',
        signalColor: '#111111',
        signalTextColor: '#475569',
        labelBoxBkgColor: '#ffffff',
        labelBoxBorderColor: '#e2e8f0',
        labelTextColor: '#475569',
        loopBkgColor: '#f8fafc',
        loopBorderColor: '#e2e8f0',
        fontSize: '12px',
        fontFamily: 'Inter, system-ui, -apple-system, sans-serif'
      }
    });
    loading.value = false;
    renderChart();
  }
};

const loadMermaidScript = () => {
  if (typeof (window as any).mermaid !== 'undefined') {
    initMermaid();
    return;
  }
  const script = document.createElement('script');
  script.src = '/assets/vendor/mermaid-10.x/mermaid.min.js';
  script.onload = () => {
    initMermaid();
  };
  script.onerror = () => {
    // Fallback to jsDelivr CDN if local asset fails
    const cdnScript = document.createElement('script');
    cdnScript.src = 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js';
    cdnScript.onload = () => {
      initMermaid();
    };
    document.head.appendChild(cdnScript);
  };
  document.head.appendChild(script);
};

// Generate unique DOM element IDs for mermaid rendering
const uniqueId = `mermaid-${Math.round(Math.random() * 10000000)}`;

const renderChart = async () => {
  if (loading.value || !localCode.value.trim()) return;

  const m = (window as any).mermaid;
  if (!m) return;

  try {
    // 1. Verify/Parse syntax
    await m.parse(localCode.value);
    syntaxError.value = null;

    // 2. Render SVG
    const { svg } = await m.render(`${uniqueId}-svg`, localCode.value);
    
    // Inject rendered SVG safely
    if (mermaidContainerRef.value) {
      mermaidContainerRef.value.innerHTML = svg;
    }
  } catch (error: any) {
    console.error('Mermaid render error:', error);
    syntaxError.value = error.message || String(error);
  }
};

// Handle reactive updating to Node attributes
const handleInput = () => {
  props.updateAttributes({ code: localCode.value });
  // Check syntax instantly on keyup
  validateSyntax();
};

const validateSyntax = async () => {
  const m = (window as any).mermaid;
  if (!m) return;
  try {
    await m.parse(localCode.value);
    syntaxError.value = null;
  } catch (error: any) {
    syntaxError.value = error.message || String(error);
  }
};

const applyTemplate = (code: string) => {
  localCode.value = code;
  showTemplates.value = false;
  handleInput();
  renderChart();
};

const insertHelper = (syntax: string) => {
  localCode.value += syntax;
  handleInput();
  renderChart();
};

// Watchers
watch(() => activeTab.value, (newTab) => {
  if (newTab === 'preview') {
    // Render on switching back to Preview tab
    setTimeout(renderChart, 0);
  }
});

watch(() => props.node.attrs.code, (newCode) => {
  if (newCode !== localCode.value) {
    localCode.value = newCode;
    renderChart();
  }
});

// Click Outside listener to close dropdown
const handleClickOutside = (e: MouseEvent) => {
  if (templateDropdownRef.value && !templateDropdownRef.value.contains(e.target as Node)) {
    showTemplates.value = false;
  }
};

onMounted(() => {
  loadMermaidScript();
  window.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
  window.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
/* Ensure standard styling overrides inside Tiptap wrapper */
.mermaid-render-output :deep(svg) {
  max-width: 100% !important;
  height: auto !important;
}
</style>
