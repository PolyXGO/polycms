<template>
  <div class="relative inline-block text-left">
    <!-- Trigger Button -->
    <button
      type="button"
      @click="isOpen = !isOpen"
      :class="[
        'px-2 py-1 rounded text-sm font-medium transition-colors flex items-center gap-1',
        editor?.isActive('table')
          ? 'bg-admin-theme-primary text-admin-theme-primary-content'
          : 'bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-base'
      ]"
      :title="$t('Table') || 'Table'"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
      </svg>
      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>

    <!-- Overlay to close -->
    <div 
      v-if="isOpen" 
      @click="isOpen = false" 
      class="fixed inset-0 z-40"
    ></div>

    <!-- Main Dropdown -->
    <div
      v-if="isOpen"
      class="absolute left-0 top-full mt-1 w-48 bg-admin-theme-surface border border-admin-theme-border rounded-lg shadow-xl z-50 py-1"
    >
      <!-- Insert Table (Nested) -->
      <div class="relative group/insert px-3 py-2 hover:bg-admin-theme-base flex items-center justify-between cursor-pointer text-admin-theme-text text-sm">
        <div class="flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
          {{ $t('Table') || 'Table' }}
        </div>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        
        <!-- Grid Popover with Bridge -->
        <div class="hidden group-hover/insert:block absolute left-full top-0 -ml-2 pl-2 z-50">
          <div class="bg-admin-theme-surface border border-admin-theme-border rounded-lg shadow-xl p-2">
            <div class="flex flex-col gap-1">
              <div v-for="r in maxRows" :key="'r'+r" class="flex gap-1">
                <div 
                  v-for="c in maxCols" :key="'c'+c"
                  @mouseenter="hoverGrid(r, c)"
                  @click="createTable(r, c)"
                  :class="[
                    'w-4 h-4 border border-admin-theme-border rounded-[2px] cursor-pointer transition-colors',
                    (r <= hoveredRow && c <= hoveredCol) ? 'bg-admin-theme-primary/30 border-admin-theme-primary' : 'bg-admin-theme-base hover:border-admin-theme-primary'
                  ]"
                ></div>
              </div>
              <div class="text-center text-xs text-admin-theme-text-muted mt-1">
                {{ hoveredCol }}x{{ hoveredRow }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="h-px bg-admin-theme-border my-1"></div>

      <!-- Cell (Nested) -->
      <div class="relative group/cell px-3 py-2 hover:bg-admin-theme-base flex items-center justify-between cursor-pointer text-admin-theme-text text-sm" :class="{'opacity-50 pointer-events-none': !editor?.isActive('table')}">
        <div class="flex items-center gap-2">
          <span>Cell</span>
        </div>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        
        <!-- Cell Popover with Bridge -->
        <div class="hidden group-hover/cell:block absolute left-full top-0 -ml-2 pl-2 pt-0 pb-2 z-50 w-44">
          <div class="bg-admin-theme-surface border border-admin-theme-border rounded-lg shadow-xl py-1">
            <button type="button" @click="openProps('cell')" class="w-full text-left px-3 py-2 hover:bg-admin-theme-base text-sm text-admin-theme-text">Cell properties</button>
            <div class="h-px bg-admin-theme-border my-1"></div>
            <button type="button" @click="editor?.chain().focus().mergeCells().run()" class="w-full text-left px-3 py-2 hover:bg-admin-theme-base text-sm text-admin-theme-text" :disabled="!editor?.can().mergeCells()">Merge cells</button>
            <button type="button" @click="editor?.chain().focus().splitCell().run()" class="w-full text-left px-3 py-2 hover:bg-admin-theme-base text-sm text-admin-theme-text" :disabled="!editor?.can().splitCell()">Split cell</button>
          </div>
        </div>
      </div>

      <!-- Row (Nested) -->
      <div class="relative group/row px-3 py-2 hover:bg-admin-theme-base flex items-center justify-between cursor-pointer text-admin-theme-text text-sm" :class="{'opacity-50 pointer-events-none': !editor?.isActive('table')}">
        <div class="flex items-center gap-2">
          <span>Row</span>
        </div>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        
        <!-- Row Popover with Bridge -->
        <div class="hidden group-hover/row:block absolute left-full top-0 -ml-2 pl-2 pt-0 pb-2 z-50 w-44">
          <div class="bg-admin-theme-surface border border-admin-theme-border rounded-lg shadow-xl py-1">
            <button type="button" @click="editor?.chain().focus().addRowBefore().run()" class="w-full text-left px-3 py-2 hover:bg-admin-theme-base text-sm text-admin-theme-text">Insert row before</button>
            <button type="button" @click="editor?.chain().focus().addRowAfter().run()" class="w-full text-left px-3 py-2 hover:bg-admin-theme-base text-sm text-admin-theme-text">Insert row after</button>
            <button type="button" @click="editor?.chain().focus().deleteRow().run()" class="w-full text-left px-3 py-2 hover:bg-admin-theme-base text-sm text-red-500">Delete row</button>
            <div class="h-px bg-admin-theme-border my-1"></div>
            <button type="button" @click="openProps('row')" class="w-full text-left px-3 py-2 hover:bg-admin-theme-base text-sm text-admin-theme-text">Row properties</button>
          </div>
        </div>
      </div>

      <!-- Column (Nested) -->
      <div class="relative group/col px-3 py-2 hover:bg-admin-theme-base flex items-center justify-between cursor-pointer text-admin-theme-text text-sm" :class="{'opacity-50 pointer-events-none': !editor?.isActive('table')}">
        <div class="flex items-center gap-2">
          <span>Column</span>
        </div>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        
        <!-- Column Popover with Bridge -->
        <div class="hidden group-hover/col:block absolute left-full top-0 -ml-2 pl-2 pt-0 pb-2 z-50 w-48">
          <div class="bg-admin-theme-surface border border-admin-theme-border rounded-lg shadow-xl py-1">
            <button type="button" @click="editor?.chain().focus().addColumnBefore().run()" class="w-full text-left px-3 py-2 hover:bg-admin-theme-base text-sm text-admin-theme-text">Insert column before</button>
            <button type="button" @click="editor?.chain().focus().addColumnAfter().run()" class="w-full text-left px-3 py-2 hover:bg-admin-theme-base text-sm text-admin-theme-text">Insert column after</button>
            <button type="button" @click="editor?.chain().focus().deleteColumn().run()" class="w-full text-left px-3 py-2 hover:bg-admin-theme-base text-sm text-red-500">Delete column</button>
            <div class="h-px bg-admin-theme-border my-1"></div>
            <button type="button" @click="openProps('col')" class="w-full text-left px-3 py-2 hover:bg-admin-theme-base text-sm text-admin-theme-text">Column properties</button>
          </div>
        </div>
      </div>

      <div class="h-px bg-admin-theme-border my-1"></div>

      <!-- Table properties -->
      <button type="button" 
        @click="openProps('table')"
        class="w-full text-left px-3 py-2 hover:bg-admin-theme-base flex items-center gap-2 text-sm text-admin-theme-text-secondary"
        :class="{'opacity-50 pointer-events-none': !editor?.isActive('table')}"
      >
        Table properties
      </button>

      <!-- Delete Table -->
      <button type="button" 
        @click="editor?.chain().focus().deleteTable().run(); isOpen = false"
        class="w-full text-left px-3 py-2 hover:bg-admin-theme-base flex items-center gap-2 text-sm text-red-500"
        :class="{'opacity-50 pointer-events-none': !editor?.isActive('table')}"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        Delete table
      </button>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useDialog } from '@/admin/composables/useDialog';
import { useTranslation } from '@/admin/composables/useTranslation';
import TablePropertiesModal from '@/admin/components/dialogs/TablePropertiesModal.vue';

const { t: $t } = useTranslation();
const dialog = useDialog();

const props = defineProps<{
  editor: any
}>();

const isOpen = ref(false);
const maxRows = 10;
const maxCols = 10;
const hoveredRow = ref(3);
const hoveredCol = ref(3);

const hoverGrid = (row: number, col: number) => {
  hoveredRow.value = row;
  hoveredCol.value = col;
};

const createTable = (rows: number, cols: number) => {
  if (props.editor) {
    props.editor.chain().focus().insertTable({ rows, cols, withHeaderRow: true }).run();
  }
  isOpen.value = false;
};

const openProps = (type: string) => {
  isOpen.value = false;
  
  // Map type to tiptap node name
  let nodeType = type;
  if (type === 'cell') {
    // It could be tableCell or tableHeader
    nodeType = props.editor.isActive('tableHeader') ? 'tableHeader' : 'tableCell';
  } else if (type === 'row') {
    nodeType = 'tableRow';
  }

  // Column properties are complex because Tiptap doesn't have a column node.
  if (type === 'col') {
    alert('Column properties are not supported directly by Tiptap schema.');
    return;
  }

  const attrs = props.editor.getAttributes(nodeType);
  isOpen.value = false;

  dialog.showModal({
    title: $t(`${type.charAt(0).toUpperCase() + type.slice(1)} Properties`) || `${type} Properties`,
    component: TablePropertiesModal,
    size: 'md',
    props: {
      type,
      initialStyle: attrs?.style || '',
      onPreview: (payload: any) => {
        const command = props.editor.chain();
        if (payload.style) {
          command.updateAttributes(nodeType, { style: payload.style }).run();
        } else {
          command.updateAttributes(nodeType, { style: null }).run();
        }
      },
      onSubmit: (payload: any) => {
        console.log('Submitting properties for', nodeType, 'with payload:', payload);
        const command = props.editor.chain().focus();
        if (payload.style) {
          command.updateAttributes(nodeType, { style: payload.style }).run();
        } else {
          command.updateAttributes(nodeType, { style: null }).run();
        }
        console.log('Attributes updated, current table attributes:', props.editor.getAttributes('table'));
      }
    }
  });
};
</script>

