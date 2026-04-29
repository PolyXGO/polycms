import { mergeAttributes } from '@tiptap/core';
import Image from '@tiptap/extension-image';

export interface ImageOptions {
    inline: boolean;
    allowBase64: boolean;
    HTMLAttributes: Record<string, any>;
}

export const TiptapImageResize = Image.extend<ImageOptions>({
    name: 'image',

    addAttributes() {
        return {
            ...this.parent?.(),
            width: {
                default: null,
                renderHTML: attributes => {
                    if (!attributes.width) {
                        return {};
                    }
                    return {
                        width: attributes.width,
                        style: `width: ${attributes.width}px`,
                    };
                },
                parseHTML: element => {
                    const width = element.getAttribute('width') || element.style.width;
                    return width ? parseInt(width.toString().replace('px', ''), 10) : null;
                },
            },
            height: {
                default: null,
                renderHTML: attributes => {
                    if (!attributes.height) {
                        return {};
                    }
                    return {
                        height: attributes.height,
                        style: `height: ${attributes.height}px`,
                    };
                },
                parseHTML: element => {
                    const height = element.getAttribute('height') || element.style.height;
                    return height ? parseInt(height.toString().replace('px', ''), 10) : null;
                },
            },
        };
    },

    addNodeView() {
        return ({ node, HTMLAttributes, getPos, editor }) => {
            const dom = document.createElement('div');
            dom.className = 'image-resize-wrapper';

            const img = document.createElement('img');
            Object.entries(mergeAttributes(HTMLAttributes, node.attrs)).forEach(([key, value]) => {
                if (key === 'width' || key === 'height') {
                    if (value) {
                        img.style[key] = `${value}px`;
                    }
                } else if (key !== 'src' && key !== 'alt' && key !== 'title') {
                    img.setAttribute(key, value);
                }
            });

            img.src = node.attrs.src;
            img.alt = node.attrs.alt || '';
            img.title = node.attrs.title || '';
            img.draggable = false;
            img.className = 'resizable-image';

            // Create resize handles
            const createHandle = (position: string) => {
                const handle = document.createElement('div');
                handle.className = `resize-handle resize-handle-${position}`;
                handle.setAttribute('data-position', position);
                return handle;
            };

            const handles = [
                createHandle('nw'), // top-left
                createHandle('ne'), // top-right
                createHandle('sw'), // bottom-left
                createHandle('se'), // bottom-right
            ];

            handles.forEach(handle => {
                dom.appendChild(handle);
            });

            dom.appendChild(img);

            // Resize functionality
            let isResizing = false;
            let currentHandle: string | null = null;
            let startX = 0;
            let startY = 0;
            let startWidth = 0;
            let startHeight = 0;
            let startAspectRatio = 0;

            const startResize = (e: MouseEvent, position: string) => {
                e.preventDefault();
                e.stopPropagation();
                isResizing = true;
                currentHandle = position;
                startX = e.clientX;
                startY = e.clientY;
                startWidth = img.offsetWidth;
                startHeight = img.offsetHeight;
                startAspectRatio = startWidth / startHeight;

                document.addEventListener('mousemove', doResize);
                document.addEventListener('mouseup', stopResize);
                dom.classList.add('resizing');
            };

            const doResize = (e: MouseEvent) => {
                if (!isResizing || !currentHandle) return;

                const deltaX = e.clientX - startX;
                const deltaY = e.clientY - startY;

                let newWidth = startWidth;
                let newHeight = startHeight;

                switch (currentHandle) {
                    case 'se': // bottom-right
                        newWidth = startWidth + deltaX;
                        newHeight = startHeight + deltaY;
                        break;
                    case 'sw': // bottom-left
                        newWidth = startWidth - deltaX;
                        newHeight = startHeight + deltaY;
                        break;
                    case 'ne': // top-right
                        newWidth = startWidth + deltaX;
                        newHeight = startHeight - deltaY;
                        break;
                    case 'nw': // top-left
                        newWidth = startWidth - deltaX;
                        newHeight = startHeight - deltaY;
                        break;
                }

                // Maintain aspect ratio when Shift is held
                if (e.shiftKey) {
                    const aspectRatio = startAspectRatio;
                    if (Math.abs(deltaX) > Math.abs(deltaY)) {
                        newHeight = newWidth / aspectRatio;
                    } else {
                        newWidth = newHeight * aspectRatio;
                    }
                }

                // Minimum size
                newWidth = Math.max(50, newWidth);
                newHeight = Math.max(50, newHeight);

                img.style.width = `${newWidth}px`;
                img.style.height = `${newHeight}px`;
            };

            const stopResize = () => {
                if (!isResizing) return;

                isResizing = false;
                dom.classList.remove('resizing');

                const width = parseInt(img.style.width, 10);
                const height = parseInt(img.style.height, 10);

                if (typeof getPos === 'function') {
                    editor.commands.updateAttributes('image', {
                        width,
                        height,
                    });
                }

                document.removeEventListener('mousemove', doResize);
                document.removeEventListener('mouseup', stopResize);
            };

            handles.forEach(handle => {
                handle.addEventListener('mousedown', (e) => {
                    const position = handle.getAttribute('data-position');
                    if (position) {
                        startResize(e, position);
                    }
                });
            });

            // Show handles on hover
            dom.addEventListener('mouseenter', () => {
                if (!isResizing) {
                    dom.classList.add('hover');
                }
            });

            dom.addEventListener('mouseleave', () => {
                if (!isResizing) {
                    dom.classList.remove('hover');
                }
            });

            return {
                dom,
            };
        };
    },
});
