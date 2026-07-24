import { Node, mergeAttributes } from '@tiptap/core';
import { VueNodeViewRenderer } from '@tiptap/vue-3';
import YoutubeGalleryNodeView from './YoutubeGalleryNodeView.vue';

export const YoutubeGallery = Node.create({
  name: 'youtubeGallery',
  group: 'block',
  atom: true,

  addAttributes() {
    return {
      urls: {
        default: [],
      },
      layout: {
        default: 'grid', // grid, list, slider, gallery
      },
      sliderVisibleItems: {
        default: 1,
      },
      sliderAutoPlay: {
        default: false,
      },
      sliderContinuous: {
        default: false,
      },
      sliderDirection: {
        default: 'left', // left or right
      }
    };
  },

  parseHTML() {
    return [
      {
        tag: 'div[data-youtube-gallery]',
        getAttrs: (el) => {
          if (typeof el === 'string') return false;
          try {
            const htmlEl = el as HTMLElement;
            return {
              urls: JSON.parse(htmlEl.getAttribute('data-urls') || '[]'),
              layout: htmlEl.getAttribute('layout') || htmlEl.getAttribute('data-layout') || 'grid',
              sliderVisibleItems: parseInt(htmlEl.getAttribute('data-slider-visible-items') || '1', 10),
              sliderAutoPlay: htmlEl.getAttribute('data-slider-autoplay') === 'true',
              sliderContinuous: htmlEl.getAttribute('data-slider-continuous') === 'true',
              sliderDirection: htmlEl.getAttribute('data-slider-direction') || 'left'
            }
          } catch(e) {
            return false;
          }
        }
      },
    ];
  },

  renderHTML({ HTMLAttributes }) {
    const attrs = mergeAttributes(HTMLAttributes, { 'data-youtube-gallery': '' });
    if (attrs.urls && Array.isArray(attrs.urls)) {
      attrs['data-urls'] = JSON.stringify(attrs.urls);
    }
    if (attrs.sliderVisibleItems) {
      attrs['data-slider-visible-items'] = attrs.sliderVisibleItems;
    }
    if (attrs.sliderAutoPlay !== undefined) {
      attrs['data-slider-autoplay'] = attrs.sliderAutoPlay ? 'true' : 'false';
    }
    
    if (attrs.sliderContinuous !== undefined) {
      attrs['data-slider-continuous'] = attrs.sliderContinuous ? 'true' : 'false';
    }
    if (attrs.sliderDirection) {
      attrs['data-slider-direction'] = attrs.sliderDirection;
    }
    
    const sanitizedAttrs = { ...attrs };
    delete sanitizedAttrs.urls;
    delete sanitizedAttrs.sliderVisibleItems;
    delete sanitizedAttrs.sliderAutoPlay;
    delete sanitizedAttrs.sliderContinuous;
    delete sanitizedAttrs.sliderDirection;
    
    return ['div', sanitizedAttrs];
  },

  addNodeView() {
    return VueNodeViewRenderer(YoutubeGalleryNodeView);
  },
});
