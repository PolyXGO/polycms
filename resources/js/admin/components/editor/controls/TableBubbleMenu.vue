<template>
  <bubble-menu
    v-if="editor"
    :editor="editor"
    :tippy-options="{ placement: 'top', duration: 100, maxWidth: 'none' }"
    :should-show="shouldShowMenu"
    class="bg-white dark:bg-admin-theme-surface border border-admin-theme-border rounded-lg shadow-xl p-1 flex items-center gap-1 z-50 text-admin-theme-text-secondary"
  >
    <button type="button"
      @click="openProps('table')"
      class="p-1.5 rounded hover:bg-admin-theme-base hover:text-admin-theme-text transition-colors"
      title="Table Properties"
    >
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
    </button>
    <button type="button"
      @click="editor.chain().focus().deleteTable().run()"
      class="p-1.5 rounded hover:bg-admin-theme-base hover:text-admin-theme-text transition-colors"
      title="Delete Table"
    >
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line><path d="M13 13l4 4m0-4l-4 4"></path></svg>
    </button>
    
    <div class="w-px h-4 bg-admin-theme-border mx-1"></div>
    
    <button type="button"
      @click="editor.chain().focus().addRowBefore().run()"
      class="p-1.5 rounded hover:bg-admin-theme-base hover:text-admin-theme-text transition-colors"
      title="Insert Row Above"
    >
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="14" width="18" height="6" rx="2" ry="2"></rect><line x1="12" y1="10" x2="12" y2="4"></line><line x1="9" y1="7" x2="15" y2="7"></line></svg>
    </button>
    <button type="button"
      @click="editor.chain().focus().addRowAfter().run()"
      class="p-1.5 rounded hover:bg-admin-theme-base hover:text-admin-theme-text transition-colors"
      title="Insert Row Below"
    >
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="6" rx="2" ry="2"></rect><line x1="12" y1="14" x2="12" y2="20"></line><line x1="9" y1="17" x2="15" y2="17"></line></svg>
    </button>
    <button type="button"
      @click="editor.chain().focus().deleteRow().run()"
      class="p-1.5 rounded hover:bg-admin-theme-base hover:text-admin-theme-text transition-colors"
      title="Delete Row"
    >
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="9" width="18" height="6" rx="2" ry="2"></rect><line x1="10" y1="10" x2="14" y2="14"></line><line x1="14" y1="10" x2="10" y2="14"></line></svg>
    </button>

    <div class="w-px h-4 bg-admin-theme-border mx-1"></div>

    <button type="button"
      @click="editor.chain().focus().addColumnBefore().run()"
      class="p-1.5 rounded hover:bg-admin-theme-base hover:text-admin-theme-text transition-colors"
      title="Insert Column Left"
    >
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="14" y="3" width="6" height="18" rx="2" ry="2"></rect><line x1="10" y1="12" x2="4" y2="12"></line><line x1="7" y1="9" x2="7" y2="15"></line></svg>
    </button>
    <button type="button"
      @click="editor.chain().focus().addColumnAfter().run()"
      class="p-1.5 rounded hover:bg-admin-theme-base hover:text-admin-theme-text transition-colors"
      title="Insert Column Right"
    >
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="3" width="6" height="18" rx="2" ry="2"></rect><line x1="14" y1="12" x2="20" y2="12"></line><line x1="17" y1="9" x2="17" y2="15"></line></svg>
    </button>
    <button type="button"
      @click="editor.chain().focus().deleteColumn().run()"
      class="p-1.5 rounded hover:bg-admin-theme-base hover:text-admin-theme-text transition-colors"
      title="Delete Column"
    >
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="3" width="6" height="18" rx="2" ry="2"></rect><line x1="10" y1="10" x2="14" y2="14"></line><line x1="14" y1="10" x2="10" y2="14"></line></svg>
    </button>
    
  </bubble-menu>
</template>

<script setup lang="ts">
import { BubbleMenu } from '@tiptap/vue-3/menus';
import { useDialog } from '@/admin/composables/useDialog';
import { useTranslation } from '@/admin/composables/useTranslation';
import TablePropertiesModal from '@/admin/components/dialogs/TablePropertiesModal.vue';

const props = defineProps<{
  editor: any
}>();

const { t: $t } = useTranslation();
const dialog = useDialog();

const shouldShowMenu = ({ editor }: any) => {
  return editor.isActive('table');
};

const openProps = (type: string) => {
  let nodeType = type;
  if (type === 'cell') {
    nodeType = props.editor.isActive('tableHeader') ? 'tableHeader' : 'tableCell';
  } else if (type === 'row') {
    nodeType = 'tableRow';
  }

  const attrs = props.editor.getAttributes(nodeType);

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
        const command = props.editor.chain().focus();
        if (payload.style) {
          command.updateAttributes(nodeType, { style: payload.style }).run();
        } else {
          command.updateAttributes(nodeType, { style: null }).run();
        }
      }
    }
  });
};
</script>

