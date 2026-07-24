import { Node, mergeAttributes } from '@tiptap/core';
import { VueNodeViewRenderer } from '@tiptap/vue-3';
import ModalLinkNodeView from './ModalLinkNodeView.vue';

export const ModalLink = Node.create({
  name: 'modalLink',
  group: 'inline',
  inline: true,
  atom: true,

  addAttributes() {
    return {
      labelText: {
        default: 'Click here',
      },
      modalSize: {
        default: 'lg', // sm, lg, full
      },
      contentType: {
        default: 'html', // html or iframe
      },
      contentHtml: {
        default: '',
      },
      iframeUrl: {
        default: '',
      },
      displayMode: {
        default: 'button', // button or link
      }
    };
  },

  parseHTML() {
    return [
      {
        tag: '[data-modal-link]',
        getAttrs: (el) => {
          if (typeof el === 'string') return false;
          try {
            const htmlEl = el as HTMLElement;
            return {
              labelText: htmlEl.getAttribute('data-label-text') || htmlEl.textContent || 'Click here',
              modalSize: htmlEl.getAttribute('data-modal-size') || 'lg',
              contentType: htmlEl.getAttribute('data-content-type') || 'html',
              contentHtml: htmlEl.getAttribute('data-content-html') || '',
              iframeUrl: htmlEl.getAttribute('data-iframe-url') || '',
              displayMode: htmlEl.getAttribute('data-display-mode') || 'button'
            }
          } catch(e) {
            return false;
          }
        }
      },
    ];
  },

  renderHTML({ HTMLAttributes }) {
    const attrs = mergeAttributes(HTMLAttributes, { 'data-modal-link': '' });
    
    if (attrs.labelText) {
      attrs['data-label-text'] = attrs.labelText;
    }
    if (attrs.modalSize) {
      attrs['data-modal-size'] = attrs.modalSize;
    }
    if (attrs.contentType) {
      attrs['data-content-type'] = attrs.contentType;
    }
    if (attrs.contentHtml) {
      attrs['data-content-html'] = attrs.contentHtml;
    }
    if (attrs.iframeUrl) {
      attrs['data-iframe-url'] = attrs.iframeUrl;
    }
    if (attrs.displayMode) {
      attrs['data-display-mode'] = attrs.displayMode;
    }
    
    const sanitizedAttrs = { ...attrs };
    delete sanitizedAttrs.labelText;
    delete sanitizedAttrs.modalSize;
    delete sanitizedAttrs.contentType;
    delete sanitizedAttrs.contentHtml;
    delete sanitizedAttrs.iframeUrl;
    delete sanitizedAttrs.displayMode;
    
    return ['a', mergeAttributes(sanitizedAttrs, { href: 'javascript:void(0)' }), attrs.labelText || 'Click here'];
  },

  addNodeView() {
    return VueNodeViewRenderer(ModalLinkNodeView);
  },
});
