<template>
    <div class="tiptap-editor">
        <div class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 overflow-hidden">
            <!-- Toolbar -->
            <div class="border-b border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 p-2 flex flex-wrap items-center gap-1">
                <!-- Undo/Redo -->
                <button
                    type="button"
                    @click="editor?.chain().focus().undo().run()"
                    :disabled="!editor || !editor.can().undo()"
                    class="px-2 py-1 rounded text-sm font-medium transition-colors bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 disabled:opacity-50"
                    :title="$t('Undo') || 'Undo'"
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
                    :title="$t('Redo') || 'Redo'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6" />
                    </svg>
                </button>
                
                <!-- Divider -->
                <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1"></div>
                
                <!-- Headings -->
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleHeading({ level: 1 }).run()"
                    :disabled="!editor"
                    :class="[
                        'px-2 py-1 rounded text-sm font-medium transition-colors',
                        editor?.isActive('heading', { level: 1 })
                            ? 'bg-indigo-600 text-white'
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                    ]"
                    :title="$t('Heading 1') || 'Heading 1'"
                >
                    H1
                </button>
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleHeading({ level: 2 }).run()"
                    :disabled="!editor"
                    :class="[
                        'px-2 py-1 rounded text-sm font-medium transition-colors',
                        editor?.isActive('heading', { level: 2 })
                            ? 'bg-indigo-600 text-white'
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                    ]"
                    :title="$t('Heading 2') || 'Heading 2'"
                >
                    H2
                </button>
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleHeading({ level: 3 }).run()"
                    :disabled="!editor"
                    :class="[
                        'px-2 py-1 rounded text-sm font-medium transition-colors',
                        editor?.isActive('heading', { level: 3 })
                            ? 'bg-indigo-600 text-white'
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                    ]"
                    :title="$t('Heading 3') || 'Heading 3'"
                >
                    H3
                </button>
                <button
                    type="button"
                    @click="editor?.chain().focus().setParagraph().run()"
                    :disabled="!editor"
                    :class="[
                        'px-2 py-1 rounded text-sm font-medium transition-colors',
                        editor?.isActive('paragraph')
                            ? 'bg-indigo-600 text-white'
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                    ]"
                    :title="$t('Paragraph') || 'Paragraph'"
                >
                    P
                </button>
                
                <!-- Divider -->
                <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1"></div>
                
                <!-- Lists -->
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleBulletList().run()"
                    :disabled="!editor"
                    :class="[
                        'px-2 py-1 rounded text-sm font-medium transition-colors',
                        editor?.isActive('bulletList')
                            ? 'bg-indigo-600 text-white'
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                    ]"
                    :title="$t('Bullet List') || 'Bullet List'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6h13M8 12h13m-7 6h7" />
                    </svg>
                </button>
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleOrderedList().run()"
                    :disabled="!editor"
                    :class="[
                        'px-2 py-1 rounded text-sm font-medium transition-colors',
                        editor?.isActive('orderedList')
                            ? 'bg-indigo-600 text-white'
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                    ]"
                    :title="$t('Ordered List') || 'Ordered List'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                    </svg>
                </button>
                
                <!-- Divider -->
                <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1"></div>
                
                <!-- Text Formatting -->
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleBold().run()"
                    :disabled="!editor"
                    :class="[
                        'px-2 py-1 rounded text-sm font-medium transition-colors',
                        editor?.isActive('bold')
                            ? 'bg-indigo-600 text-white'
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                    ]"
                    :title="$t('Bold') || 'Bold'"
                >
                    <strong>B</strong>
                </button>
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleItalic().run()"
                    :disabled="!editor"
                    :class="[
                        'px-2 py-1 rounded text-sm font-medium transition-colors',
                        editor?.isActive('italic')
                            ? 'bg-indigo-600 text-white'
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                    ]"
                    :title="$t('Italic') || 'Italic'"
                >
                    <em>I</em>
                </button>
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleStrike().run()"
                    :disabled="!editor"
                    :class="[
                        'px-2 py-1 rounded text-sm font-medium transition-colors',
                        editor?.isActive('strike')
                            ? 'bg-indigo-600 text-white'
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                    ]"
                    :title="$t('Strikethrough') || 'Strikethrough'"
                >
                    <span style="text-decoration: line-through;">S</span>
                </button>
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleCode().run()"
                    :disabled="!editor"
                    :class="[
                        'px-2 py-1 rounded text-sm font-medium transition-colors',
                        editor?.isActive('code')
                            ? 'bg-indigo-600 text-white'
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                    ]"
                    :title="$t('Code') || 'Code'"
                >
                    &lt;/&gt;
                </button>
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleUnderline().run()"
                    :disabled="!editor"
                    :class="[
                        'px-2 py-1 rounded text-sm font-medium transition-colors',
                        editor?.isActive('underline')
                            ? 'bg-indigo-600 text-white'
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                    ]"
                    :title="$t('Underline') || 'Underline'"
                >
                    <u>U</u>
                </button>
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleHighlight().run()"
                    :disabled="!editor"
                    :class="[
                        'px-2 py-1 rounded text-sm font-medium transition-colors',
                        editor?.isActive('highlight')
                            ? 'bg-indigo-600 text-white'
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                    ]"
                    :title="$t('Highlight') || 'Highlight'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                </button>
                
                <!-- Divider -->
                <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1"></div>
                
                <!-- Link -->
                <button
                    type="button"
                    @click="setLink"
                    :disabled="!editor"
                    :class="[
                        'px-2 py-1 rounded text-sm font-medium transition-colors',
                        editor?.isActive('link')
                            ? 'bg-indigo-600 text-white'
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                    ]"
                    :title="$t('Link') || 'Link'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                </button>
                
                <!-- Divider -->
                <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1"></div>
                
                <!-- Superscript/Subscript -->
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleSuperscript().run()"
                    :disabled="!editor"
                    :class="[
                        'px-2 py-1 rounded text-sm font-medium transition-colors',
                        editor?.isActive('superscript')
                            ? 'bg-indigo-600 text-white'
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                    ]"
                    :title="$t('Superscript') || 'Superscript'"
                >
                    x<sup>2</sup>
                </button>
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleSubscript().run()"
                    :disabled="!editor"
                    :class="[
                        'px-2 py-1 rounded text-sm font-medium transition-colors',
                        editor?.isActive('subscript')
                            ? 'bg-indigo-600 text-white'
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                    ]"
                    :title="$t('Subscript') || 'Subscript'"
                >
                    x<sub>2</sub>
                </button>
                
                <!-- Divider -->
                <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1"></div>
                
                <!-- Text Align -->
                <button
                    type="button"
                    @click="editor?.chain().focus().setTextAlign('left').run()"
                    :disabled="!editor"
                    :class="[
                        'px-2 py-1 rounded text-sm font-medium transition-colors',
                        editor?.isActive({ textAlign: 'left' })
                            ? 'bg-indigo-600 text-white'
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                    ]"
                    :title="$t('Align Left') || 'Align Left'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M3 18h18M3 6h18" />
                    </svg>
                </button>
                <button
                    type="button"
                    @click="editor?.chain().focus().setTextAlign('center').run()"
                    :disabled="!editor"
                    :class="[
                        'px-2 py-1 rounded text-sm font-medium transition-colors',
                        editor?.isActive({ textAlign: 'center' })
                            ? 'bg-indigo-600 text-white'
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                    ]"
                    :title="$t('Align Center') || 'Align Center'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M6 14h12M4 18h16M3 6h18" />
                    </svg>
                </button>
                <button
                    type="button"
                    @click="editor?.chain().focus().setTextAlign('right').run()"
                    :disabled="!editor"
                    :class="[
                        'px-2 py-1 rounded text-sm font-medium transition-colors',
                        editor?.isActive({ textAlign: 'right' })
                            ? 'bg-indigo-600 text-white'
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                    ]"
                    :title="$t('Align Right') || 'Align Right'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M9 14h12M5 18h16M3 6h18" />
                    </svg>
                </button>
                <button
                    type="button"
                    @click="editor?.chain().focus().setTextAlign('justify').run()"
                    :disabled="!editor"
                    :class="[
                        'px-2 py-1 rounded text-sm font-medium transition-colors',
                        editor?.isActive({ textAlign: 'justify' })
                            ? 'bg-indigo-600 text-white'
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                    ]"
                    :title="$t('Justify') || 'Justify'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M3 18h18M3 6h18" />
                    </svg>
                </button>
                
                <!-- Divider -->
                <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1"></div>
                
                <!-- Blockquote -->
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleBlockquote().run()"
                    :disabled="!editor"
                    :class="[
                        'px-2 py-1 rounded text-sm font-medium transition-colors',
                        editor?.isActive('blockquote')
                            ? 'bg-indigo-600 text-white'
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                    ]"
                    :title="$t('Blockquote') || 'Blockquote'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </button>
                
                <!-- Code Block -->
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleCodeBlock().run()"
                    :disabled="!editor"
                    :class="[
                        'px-2 py-1 rounded text-sm font-medium transition-colors',
                        editor?.isActive('codeBlock')
                            ? 'bg-indigo-600 text-white'
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                    ]"
                    :title="$t('Code Block') || 'Code Block'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                </button>
                
                <!-- Divider -->
                <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1"></div>
                
                <!-- Image Upload -->
                <button
                    type="button"
                    @click="handleImageUpload"
                    :disabled="!editor || uploading"
                    class="px-2 py-1 rounded text-sm font-medium transition-colors bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 disabled:opacity-50 flex items-center gap-1"
                    :title="$t('Add Image') || 'Add Image'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-xs">{{ $t('Add') || 'Add' }}</span>
                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/*"
                        class="hidden"
                        @change="uploadImage"
                    />
                </button>
            </div>
            
            <!-- Editor Content -->
            <EditorContent v-if="editor" :editor="editor" class="prose prose-sm max-w-none dark:prose-invert" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Placeholder from '@tiptap/extension-placeholder';
import Link from '@tiptap/extension-link';
import Image from '@tiptap/extension-image';
import TextAlign from '@tiptap/extension-text-align';
import Underline from '@tiptap/extension-underline';
import Strike from '@tiptap/extension-strike';
import Code from '@tiptap/extension-code';
import Highlight from '@tiptap/extension-highlight';
import Superscript from '@tiptap/extension-superscript';
import Subscript from '@tiptap/extension-subscript';
import { watch, onBeforeUnmount, getCurrentInstance, ref } from 'vue';
import { useTranslation } from '@/admin/composables/useTranslation';
import axios from 'axios';

const props = defineProps<{
    modelValue: string;
    placeholder?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;

const fileInput = ref<HTMLInputElement | null>(null);
const uploading = ref(false);

const editor = useEditor({
    extensions: [
        StarterKit.configure({
            heading: {
                levels: [1, 2, 3],
            },
        }),
        Placeholder.configure({
            placeholder: props.placeholder || ($t('Start typing...') || 'Start typing...'),
        }),
        Link.configure({
            openOnClick: false,
            HTMLAttributes: {
                class: 'text-indigo-600 dark:text-indigo-400 hover:underline',
            },
        }),
        Image.configure({
            inline: true,
            allowBase64: false,
            HTMLAttributes: {
                class: 'max-w-full h-auto rounded',
            },
        }),
        TextAlign.configure({
            types: ['heading', 'paragraph'],
        }),
        Underline,
        Strike,
        Code,
        Highlight.configure({
            multicolor: true,
        }),
        Superscript,
        Subscript,
    ],
    content: props.modelValue || '',
    onUpdate: ({ editor }) => {
        const html = editor.getHTML();
        // Only emit if content is not empty (not just empty paragraph)
        if (html && html.trim() !== '<p></p>' && html.trim() !== '<p><br></p>') {
            emit('update:modelValue', html);
        } else {
            emit('update:modelValue', '');
        }
    },
    editorProps: {
        attributes: {
            class: 'prose prose-sm max-w-none dark:prose-invert focus:outline-none',
        },
    },
});

// Watch for external changes
watch(() => props.modelValue, (value) => {
    if (editor.value && editor.value.getHTML() !== value) {
        editor.value.commands.setContent(value || '');
    }
});

// Handle link insertion
const setLink = () => {
    if (!editor.value) return;
    
    const previousUrl = editor.value.getAttributes('link').href;
    const url = window.prompt($t('Enter URL') || 'Enter URL', previousUrl);
    
    if (url === null) {
        return;
    }
    
    if (url === '') {
        editor.value.chain().focus().extendMarkRange('link').unsetLink().run();
        return;
    }
    
    editor.value.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
};

// Handle image upload button click
const handleImageUpload = () => {
    fileInput.value?.click();
};

// Upload image and insert into editor
const uploadImage = async (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    
    if (!file || !editor.value) return;
    
    uploading.value = true;
    
    try {
        const formData = new FormData();
        formData.append('image', file);
        
        // Axios interceptor will automatically remove Content-Type for FormData
        const response = await axios.post('/api/v1/upload/image', formData);
        
        if (response.data.success && response.data.data?.url) {
            // Insert image into editor
            editor.value.chain().focus().setImage({ 
                src: response.data.data.url,
                alt: file.name,
            }).run();
        } else {
            throw new Error('Upload failed');
        }
    } catch (error: any) {
        console.error('Error uploading image:', error);
        alert($t('Failed to upload image') || 'Failed to upload image');
    } finally {
        uploading.value = false;
        // Reset input
        if (target) {
            target.value = '';
        }
    }
};

onBeforeUnmount(() => {
    if (editor.value) {
        editor.value.destroy();
    }
});
</script>

<style>
.tiptap-editor .ProseMirror {
    outline: none;
    padding: 1rem;
    min-height: 200px;
    max-height: 600px;
    overflow-y: auto;
    color: rgb(17 24 39);
}

.dark .tiptap-editor .ProseMirror {
    color: rgb(243 244 246);
}

.tiptap-editor .ProseMirror p.is-editor-empty:first-child::before {
    content: attr(data-placeholder);
    float: left;
    color: #adb5bd;
    pointer-events: none;
    height: 0;
}

.dark .tiptap-editor .ProseMirror p.is-editor-empty:first-child::before {
    color: #6b7280;
}

.tiptap-editor .ProseMirror h1,
.tiptap-editor .ProseMirror h2,
.tiptap-editor .ProseMirror h3 {
    font-weight: 600;
    margin-top: 1.5em;
    margin-bottom: 0.5em;
}

.tiptap-editor .ProseMirror h1 {
    font-size: 2em;
}

.tiptap-editor .ProseMirror h2 {
    font-size: 1.5em;
}

.tiptap-editor .ProseMirror h3 {
    font-size: 1.25em;
}

.tiptap-editor .ProseMirror ul,
.tiptap-editor .ProseMirror ol {
    padding-left: 1.5em;
    margin: 1em 0;
}

.tiptap-editor .ProseMirror blockquote {
    border-left: 4px solid #e5e7eb;
    padding-left: 1em;
    margin: 1em 0;
    font-style: italic;
}

.dark .tiptap-editor .ProseMirror blockquote {
    border-left-color: #4b5563;
}

.tiptap-editor .ProseMirror code {
    background-color: #f3f4f6;
    padding: 0.2em 0.4em;
    border-radius: 0.25rem;
    font-size: 0.9em;
}

.dark .tiptap-editor .ProseMirror code {
    background-color: #374151;
}

.tiptap-editor .ProseMirror pre {
    background-color: #f3f4f6;
    padding: 1em;
    border-radius: 0.5rem;
    margin: 1em 0;
    overflow-x: auto;
}

.dark .tiptap-editor .ProseMirror pre {
    background-color: #1f2937;
}

.tiptap-editor .ProseMirror pre code {
    background-color: transparent;
    padding: 0;
}

.tiptap-editor .ProseMirror img {
    max-width: 100%;
    height: auto;
    margin: 1em 0;
    border-radius: 0.5rem;
}

.tiptap-editor .ProseMirror mark {
    background-color: #fef08a;
    padding: 0.1em 0.2em;
    border-radius: 0.2em;
}

.dark .tiptap-editor .ProseMirror mark {
    background-color: #854d0e;
}
</style>
