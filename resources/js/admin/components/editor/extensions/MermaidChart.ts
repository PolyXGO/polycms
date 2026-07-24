import { Node, mergeAttributes } from '@tiptap/core';
import { VueNodeViewRenderer } from '@tiptap/vue-3';
import MermaidChartNodeView from './MermaidChartNodeView.vue';

export const MermaidChart = Node.create({
  name: 'mermaidChart',
  group: 'block',
  atom: true,

  addAttributes() {
    return {
      code: {
        default: 'graph TD\n    A[Start] --> B[Process]\n    B --> C[End]',
      },
    };
  },

  parseHTML() {
    return [
      {
        tag: 'pre',
        getAttrs: (el) => {
          if (typeof el === 'string') return false;
          const preEl = el as HTMLElement;
          const codeEl = preEl.querySelector('code.language-mermaid');
          if (!codeEl) return false;
          return {
            code: codeEl.textContent?.trim() || '',
          };
        },
        priority: 100, // Higher priority than default codeBlock to intercept language-mermaid
      },
    ];
  },

  renderHTML({ HTMLAttributes }) {
    const sanitized = { ...HTMLAttributes };
    const code = sanitized.code || '';
    delete sanitized.code;

    return [
      'pre',
      mergeAttributes(sanitized),
      ['code', { class: 'language-mermaid' }, code],
    ];
  },

  addNodeView() {
    return VueNodeViewRenderer(MermaidChartNodeView);
  },
});
