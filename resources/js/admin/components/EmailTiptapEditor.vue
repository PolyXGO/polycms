<template>
    <div class="email-tiptap-editor">
        <div class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 overflow-hidden">
            <!-- Toolbar -->
            <div class="border-b border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 p-2 flex flex-wrap items-center gap-1">
                <!-- Undo/Redo -->
                <button
                    type="button"
                    @click="editor?.chain().focus().undo().run()"
                    :disabled="!editor || !editor.can().undo()"
                    class="px-2 py-1 rounded text-sm font-medium transition-colors bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 disabled:opacity-50"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                    </svg>
                </button>
                <button
                    type="button"
                    @click="editor?.chain().focus().redo().run()"
                    :disabled="!editor || !editor.can().redo()"
                    class="px-2 py-1 rounded text-sm font-medium transition-colors bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 disabled:opacity-50"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6" />
                    </svg>
                </button>

                <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1"></div>

                <!-- Headings -->
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleHeading({ level: 1 }).run()"
                    :class="['px-2 py-1 rounded text-sm font-medium', editor?.isActive('heading', { level: 1 }) ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600']"
                >H1</button>
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleHeading({ level: 2 }).run()"
                    :class="['px-2 py-1 rounded text-sm font-medium', editor?.isActive('heading', { level: 2 }) ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600']"
                >H2</button>
                <button
                    type="button"
                    @click="editor?.chain().focus().setParagraph().run()"
                    :class="['px-2 py-1 rounded text-sm font-medium', editor?.isActive('paragraph') ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600']"
                >P</button>

                <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1"></div>

                <!-- Formatting -->
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleBold().run()"
                    :class="['px-2 py-1 rounded text-sm font-medium', editor?.isActive('bold') ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600']"
                >B</button>
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleItalic().run()"
                    :class="['px-2 py-1 rounded text-sm font-medium', editor?.isActive('italic') ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600']"
                >I</button>
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleUnderline().run()"
                    :class="['px-2 py-1 rounded text-sm font-medium', editor?.isActive('underline') ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600']"
                >U</button>

                <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1"></div>

                <!-- Lists -->
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleBulletList().run()"
                    :class="['px-2 py-1 rounded text-sm font-medium', editor?.isActive('bulletList') ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600']"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleOrderedList().run()"
                    :class="['px-2 py-1 rounded text-sm font-medium', editor?.isActive('orderedList') ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600']"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h10M7 16h10 M5 8v0 M5 12v0 M5 16v0" />
                    </svg>
                </button>

                <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1"></div>

                <!-- Alignment -->
                <button
                    type="button"
                    @click="editor?.chain().focus().setTextAlign('left').run()"
                    :class="['px-2 py-1 rounded text-sm font-medium', editor?.isActive({ textAlign: 'left' }) ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600']"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M3 18h18M3 6h18" />
                    </svg>
                </button>
                <button
                    type="button"
                    @click="editor?.chain().focus().setTextAlign('center').run()"
                    :class="['px-2 py-1 rounded text-sm font-medium', editor?.isActive({ textAlign: 'center' }) ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600']"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M6 14h12M4 18h16M3 6h18" />
                    </svg>
                </button>

                <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1"></div>

                <!-- Link & Image -->
                <button
                    type="button"
                    @click="setLink"
                    :class="['px-2 py-1 rounded text-sm font-medium', editor?.isActive('link') ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600']"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                </button>
                <button
                    type="button"
                    @click="openMediaPicker"
                    class="px-2 py-1 rounded text-sm font-medium transition-colors bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 flex items-center gap-1"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </button>

                <div class="ml-auto flex items-center border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
                    <button
                        type="button"
                        @click="setVisualMode"
                        :class="[
                            'px-3 py-1 text-xs font-medium transition-colors',
                            !isHtmlMode
                                ? 'bg-indigo-600 text-white'
                                : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                        ]"
                    >
                        {{ t('Visual') }}
                    </button>
                    <button
                        type="button"
                        @click="setHtmlMode"
                        :class="[
                            'px-3 py-1 text-xs font-mono transition-colors border-l border-gray-300 dark:border-gray-600',
                            isHtmlMode
                                ? 'bg-indigo-600 text-white'
                                : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                        ]"
                    >
                        HTML
                    </button>
                </div>
            </div>

            <!-- Editor Content -->
            <EditorContent
                v-if="editor && !isHtmlMode"
                :editor="editor"
                class="prose prose-sm max-w-none dark:prose-invert"
            />
            <textarea
                v-else
                v-model="htmlSource"
                class="w-full min-h-[300px] p-4 border-0 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 font-mono text-sm leading-6 focus:outline-none resize-y"
                :placeholder="t('Edit raw HTML content...')"
                @input="handleHtmlInput"
            ></textarea>
        </div>

        <MediaPicker ref="mediaPickerRef" :multiple="false" :accepted-types="['image']" @select="handleMediaSelect" />
    </div>
</template>

<script setup lang="ts">
import { useEditor, EditorContent } from '@tiptap/vue-3';
import { getMarkRange } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import TextAlign from '@tiptap/extension-text-align';
import Underline from '@tiptap/extension-underline';
import Image from '@tiptap/extension-image';
import { watch, onBeforeUnmount, ref } from 'vue';
import MediaPicker from '@/admin/components/MediaPicker.vue';
import EmailLinkModal from '@/admin/components/dialogs/EmailLinkModal.vue';
import { useDialog } from '@/admin/composables/useDialog';
import { useTranslation } from '@/admin/composables/useTranslation';

const props = defineProps<{
    modelValue: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const mediaPickerRef = ref<InstanceType<typeof MediaPicker> | null>(null);
const isHtmlMode = ref(false);
const htmlSource = ref(props.modelValue || '');
const { t } = useTranslation();
const dialog = useDialog();

interface LinkContext {
    from: number;
    to: number;
    href: string;
    target: string;
    rel: string;
    text: string;
}

const editor = useEditor({
    extensions: [
        StarterKit.configure({
            heading: { levels: [1, 2, 3] },
            // Disable extensions that might cause issues in email
            codeBlock: false,
            blockquote: false,
        }),
        Link.configure({
            openOnClick: false,
            HTMLAttributes: {},
        }),
        TextAlign.configure({
            types: ['heading', 'paragraph'],
        }),
        Underline,
        Image.configure({
            inline: true,
            HTMLAttributes: { class: 'max-w-full h-auto rounded' },
        }),
    ],
    content: props.modelValue || '',
    onUpdate: ({ editor }) => {
        if (isHtmlMode.value) {
            return;
        }
        const html = editor.getHTML();
        if (html && html.trim() !== '<p></p>' && html.trim() !== '<p><br></p>') {
            emit('update:modelValue', html);
        } else {
            emit('update:modelValue', '');
        }
    },
    editorProps: {
        attributes: {
            class: 'prose prose-sm max-w-none dark:prose-invert text-gray-900 dark:text-gray-100 focus:outline-none min-h-[300px] p-4',
        },
        handleClick: (_view: any, _pos: number, event: MouseEvent) => {
            const target = event.target as HTMLElement | null;
            if (target?.closest('a')) {
                event.preventDefault();
                return true;
            }
            return false;
        },
    },
});

watch(() => props.modelValue, (value) => {
    const normalized = value || '';
    if (isHtmlMode.value) {
        if (htmlSource.value !== normalized) {
            htmlSource.value = normalized;
        }
        return;
    }

    if (editor.value && editor.value.getHTML() !== normalized) {
        editor.value.commands.setContent(value || '');
    }
});

const setLink = () => {
    if (!editor.value) return;

    const selection = editor.value.state.selection;
    const linkContext = getCurrentLinkContext();
    const selectedText = linkContext?.text || editor.value.state.doc.textBetween(selection.from, selection.to, ' ');
    const linkAttrs = linkContext
        ? { href: linkContext.href, target: linkContext.target }
        : (editor.value.getAttributes('link') || {});

    const effectiveFrom = linkContext?.from ?? selection.from;
    const effectiveTo = linkContext?.to ?? selection.to;

    dialog.showModal({
        title: t('Insert Link'),
        component: EmailLinkModal,
        size: 'md',
        props: {
            initialUrl: linkAttrs.href || '',
            initialText: selectedText || '',
            initialOpenInNewTab: linkAttrs.target === '_blank',
            initialRelMode: resolveRelModeFromRel(linkContext?.rel || linkAttrs.rel || ''),
            onSubmit: (payload: { url: string; text: string; openInNewTab: boolean; relMode: string; remove?: boolean }) => {
                applyLink(
                    payload,
                    {
                        from: effectiveFrom,
                        to: effectiveTo,
                        href: linkAttrs.href || '',
                        target: linkAttrs.target || '',
                        rel: linkContext?.rel || linkAttrs.rel || '',
                        text: selectedText,
                    },
                );
            },
        },
    });
};

const openMediaPicker = () => mediaPickerRef.value?.open();

const handleMediaSelect = (media: any) => {
    if (!editor.value) return;
    const selected = Array.isArray(media) ? media[0] : media;
    if (selected?.url) {
        editor.value.chain().focus().setImage({ src: selected.url, alt: selected.name }).run();
    }
};

const insertContent = (content: string) => {
    if (isHtmlMode.value) {
        htmlSource.value += content;
        emit('update:modelValue', htmlSource.value);
        return;
    }

    if (editor.value) {
        editor.value.chain().focus().insertContent(content).run();
    }
};

const setHtmlMode = () => {
    if (!editor.value) return;
    htmlSource.value = editor.value.getHTML() || props.modelValue || '';
    isHtmlMode.value = true;
};

const setVisualMode = () => {
    if (!editor.value) return;
    isHtmlMode.value = false;
    editor.value.commands.setContent(htmlSource.value || '');
    emit('update:modelValue', htmlSource.value || '');
};

const handleHtmlInput = () => {
    emit('update:modelValue', htmlSource.value);
};

const escapeHtml = (value: string): string => {
    return value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
};

const getCurrentLinkContext = (): LinkContext | null => {
    if (!editor.value) return null;

    const { state } = editor.value;
    const linkType = state.schema.marks.link;
    if (!linkType) return null;

    const { from, to, empty } = state.selection;
    const resolvePositions = [from];
    if (from > 0) resolvePositions.push(from - 1);
    if (!empty) {
        resolvePositions.push(to);
        if (to > 0) resolvePositions.push(to - 1);
    }

    for (const pos of resolvePositions) {
        const $pos = state.doc.resolve(pos);
        const range = getMarkRange($pos, linkType);
        if (range) {
            const text = state.doc.textBetween(range.from, range.to, ' ');
            const attrsFromNode = state.doc.nodeAt(range.from)?.marks?.find((mark) => mark.type === linkType)?.attrs
                || (range.from > 0 ? state.doc.nodeAt(range.from - 1)?.marks?.find((mark) => mark.type === linkType)?.attrs : null)
                || {};
            return {
                from: range.from,
                to: range.to,
                href: attrsFromNode.href || '',
                target: attrsFromNode.target || '',
                rel: attrsFromNode.rel || '',
                text,
            };
        }
    }

    return null;
};

const applyLink = (
    payload: { url: string; text: string; openInNewTab: boolean; relMode: string; remove?: boolean },
    context: LinkContext
) => {
    if (!editor.value) return;
    const { from, to, text: previousText } = context;

    if (payload.remove || !payload.url.trim()) {
        editor.value
            .chain()
            .focus()
            .setTextSelection({ from, to })
            .unsetLink()
            .run();
        return;
    }

    const anchorText = payload.text.trim() || previousText || payload.url.trim();

    const safeText = escapeHtml(anchorText);
    const safeHref = escapeHtml(payload.url.trim());
    const relValue = buildRelValue(payload.relMode, payload.openInNewTab);
    const relAttr = relValue ? ` rel="${escapeHtml(relValue)}"` : '';
    const targetAttr = payload.openInNewTab ? ' target="_blank"' : '';
    editor.value
        .chain()
        .focus()
        .insertContentAt({ from, to }, `<a href="${safeHref}"${targetAttr}${relAttr}>${safeText}</a>`)
        .run();
};

const resolveRelModeFromRel = (relValue: string): string => {
    const normalized = relValue.toLowerCase().trim();
    if (!normalized) return 'follow';
    const parts = new Set(normalized.split(/\s+/).filter(Boolean));
    const hasNofollow = parts.has('nofollow');
    const hasUgc = parts.has('ugc');
    const hasSponsored = parts.has('sponsored');

    if (hasNofollow && hasUgc) return 'nofollow_ugc';
    if (hasNofollow && hasSponsored) return 'nofollow_sponsored';
    if (hasSponsored) return 'sponsored';
    if (hasUgc) return 'ugc';
    if (hasNofollow) return 'nofollow';
    return 'follow';
};

const buildRelValue = (relMode: string, openInNewTab: boolean): string => {
    const relTokens = new Set<string>();
    switch (relMode) {
        case 'nofollow':
            relTokens.add('nofollow');
            break;
        case 'ugc':
            relTokens.add('ugc');
            break;
        case 'sponsored':
            relTokens.add('sponsored');
            break;
        case 'nofollow_ugc':
            relTokens.add('nofollow');
            relTokens.add('ugc');
            break;
        case 'nofollow_sponsored':
            relTokens.add('nofollow');
            relTokens.add('sponsored');
            break;
        default:
            break;
    }

    if (openInNewTab) {
        relTokens.add('noopener');
        relTokens.add('noreferrer');
    }

    return Array.from(relTokens).join(' ').trim();
};

defineExpose({
    insertContent
});

onBeforeUnmount(() => editor.value?.destroy());
</script>

<style>
.email-tiptap-editor .ProseMirror {
    outline: none;
    min-height: 300px;
}

.email-tiptap-editor .ProseMirror a {
    color: #4f46e5;
    text-decoration: underline;
    text-underline-offset: 2px;
}

.email-tiptap-editor .ProseMirror a:hover {
    color: #4338ca;
}

.dark .email-tiptap-editor .ProseMirror a {
    color: #818cf8;
}

.dark .email-tiptap-editor .ProseMirror a:hover {
    color: #a5b4fc;
}
</style>
