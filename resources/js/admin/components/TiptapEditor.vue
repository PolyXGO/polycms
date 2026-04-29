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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6h18M3 10h14M3 14h18M3 18h14" />
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
                    @click="openMediaPicker"
                    :disabled="!editor"
                    class="px-2 py-1 rounded text-sm font-medium transition-colors bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 disabled:opacity-50 flex items-center gap-1"
                    :title="$t('Add Image') || 'Add Image'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-xs">{{ $t('Add') || 'Add' }}</span>
                </button>

                <!-- Landing Blocks -->
                <button
                    type="button"
                    @click="openBlockPicker"
                    :disabled="!editor"
                    class="px-2 py-1 rounded text-sm font-bold transition-colors bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 flex items-center gap-1 border border-indigo-200 dark:border-indigo-800"
                    :title="$t('Insert Landing Block') || 'Insert Landing Block'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    <span class="text-xs">Landing</span>
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
                        {{ $t('Visual') || 'Visual' }}
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
                class="w-full min-h-[200px] max-h-[600px] overflow-y-auto p-4 border-0 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 font-mono text-sm leading-6 focus:outline-none resize-y"
                :placeholder="$t('Edit raw HTML content...') || 'Edit raw HTML content...'"
                @input="handleHtmlInput"
            ></textarea>
        </div>

        <MediaPicker
            ref="mediaPickerRef"
            :multiple="false"
            :accepted-types="['image']"
            @select="handleMediaSelect"
        />

        <!-- Block Picker -->
        <LandingBlockPicker
            ref="blockPickerRef"
            @select="handleBlockSelect"
        />
    </div>
</template>

<script setup lang="ts">
import { useEditor, EditorContent } from '@tiptap/vue-3';
import { getMarkRange } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Placeholder from '@tiptap/extension-placeholder';
import Link from '@tiptap/extension-link';
import { TiptapImageResize } from './TiptapImageResize';
import TextAlign from '@tiptap/extension-text-align';
import Underline from '@tiptap/extension-underline';
import Strike from '@tiptap/extension-strike';
import Code from '@tiptap/extension-code';
import Highlight from '@tiptap/extension-highlight';
import Superscript from '@tiptap/extension-superscript';
import Subscript from '@tiptap/extension-subscript';
import { LandingBlock } from '../editor/LandingBlock';
import { watch, onBeforeUnmount, getCurrentInstance, ref } from 'vue';
import { useTranslation } from '@/admin/composables/useTranslation';
import { useDialog } from '@/admin/composables/useDialog';
import MediaPicker from '@/admin/components/MediaPicker.vue';
import LandingBlockPicker from './editor/LandingBlockPicker.vue';
import EmailLinkModal from '@/admin/components/dialogs/EmailLinkModal.vue';
import { useLandingStore } from '@/admin/stores/landingStore';

const props = defineProps<{
    modelValue: string;
    json?: any;
    placeholder?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
    'update:json': [value: any];
}>();

const transformShortcodesToHtml = (content: string): string => {
    if (!content || typeof content !== 'string') return content;
    
    // Regex to match [landing_block type="..." data="..."]
    // Pattern: \[landing_block\s+([^\]]+)\]
    const shortcodeRegex = /\[landing_block\s+([^\]]+)\]/g;
    
    return content.replace(shortcodeRegex, (match, attrsString) => {
        const attrs: Record<string, string> = {};
        // Match key="value"
        const attrRegex = /(\w+)\s*=\s*"([^"]*)"/g;
        let m;
        while ((m = attrRegex.exec(attrsString)) !== null) {
            attrs[m[1]] = m[2];
        }

        const type = attrs.type || 'unknown';
        delete attrs.type;
        
        // Wrap in div that LandingBlock extension expects
        return `<div data-type="landing-block" data-block-type="${type}" data-block-data='${JSON.stringify(attrs)}'></div>`;
    });
};

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;

const mediaPickerRef = ref<InstanceType<typeof MediaPicker> | null>(null);
const landingStore = useLandingStore();
const dialog = useDialog();
const isHtmlMode = ref(false);
const htmlSource = ref(props.modelValue || '');

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
        TiptapImageResize.configure({
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
        LandingBlock,
    ],
    content: props.json || transformShortcodesToHtml(props.modelValue || ''),
    onUpdate: ({ editor }) => {
        if (isHtmlMode.value) {
            return;
        }
        const html = editor.getHTML();
        const json = editor.getJSON();
        
        // Only emit if content is not empty (not just empty paragraph)
        if (html && html.trim() !== '<p></p>' && html.trim() !== '<p><br></p>') {
            emit('update:modelValue', html);
            emit('update:json', json);
        } else {
            emit('update:modelValue', '');
            emit('update:json', null);
        }
    },
    editorProps: {
        attributes: {
            class: 'prose prose-sm max-w-none dark:prose-invert focus:outline-none',
        },
        handleDrop: (_view, event) => {
            if (!landingStore.draggingBlock) {
                return false;
            }

            const target = event.target as HTMLElement | null;
            const validDropZone = target?.closest(
                '.landing-block-drop-zone, .row-block-preview__drop-zone, .section-block-drop-zone, .section-block-empty, .section-block-list, .row-block-preview__column'
            );

            if (!validDropZone) {
                event.preventDefault();
                landingStore.endBlockDrag();
                return true;
            }

            return false;
        },
        handleClick: (_view: any, _pos: number, event: MouseEvent) => {
            const target = (event as MouseEvent).target as HTMLElement | null;
            if (target?.closest('a')) {
                event.preventDefault();
                return true;
            }
            return false;
        },
    },
});

// Watch for external changes
watch(() => props.modelValue, (value) => {
    const normalized = value || '';
    if (isHtmlMode.value) {
        if (htmlSource.value !== normalized) {
            htmlSource.value = normalized;
        }
        return;
    }

    if (editor.value && !props.json && editor.value.getHTML() !== value) {
        editor.value.commands.setContent(transformShortcodesToHtml(value || ''));
    }
});

watch(() => props.json, (value) => {
    if (isHtmlMode.value) {
        return;
    }
    if (editor.value && JSON.stringify(editor.value.getJSON()) !== JSON.stringify(value)) {
        editor.value.commands.setContent(value || '');
    }
}, { deep: true });

// Handle link insertion
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
        title: $t('Insert Link') || 'Insert Link',
        component: EmailLinkModal,
        size: 'md',
        props: {
            initialUrl: linkAttrs.href || '',
            initialText: selectedText || '',
            initialOpenInNewTab: linkAttrs.target === '_blank',
            initialRelMode: resolveRelModeFromRel(linkContext?.rel || linkAttrs.rel || ''),
            onSubmit: (payload: { url: string; text: string; openInNewTab: boolean; relMode: string; remove?: boolean }) => {
                applyLink(payload, {
                    from: effectiveFrom,
                    to: effectiveTo,
                    href: linkAttrs.href || '',
                    target: linkAttrs.target || '',
                    rel: linkContext?.rel || linkAttrs.rel || '',
                    text: selectedText,
                });
            },
        },
    });
};

// Open Media Picker
const openMediaPicker = () => {
    mediaPickerRef.value?.open();
};

// Handle media selection from MediaPicker
const handleMediaSelect = (media: any) => {
    if (!editor.value) return;

    const selected = Array.isArray(media) ? media[0] : media;
    if (!selected || !selected.url) return;

    // Insert image into editor
    editor.value.chain().focus().setImage({
        src: selected.url,
        alt: selected.name || selected.file_name || 'Image',
    }).run();
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
    const classAttr = ' class="text-indigo-600 dark:text-indigo-400 hover:underline"';
    editor.value
        .chain()
        .focus()
        .insertContentAt({ from, to }, `<a href="${safeHref}"${targetAttr}${relAttr}${classAttr}>${safeText}</a>`)
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

const blockPickerRef = ref<InstanceType<typeof LandingBlockPicker> | null>(null);

const openBlockPicker = () => {
    blockPickerRef.value?.open();
};

const handleBlockSelect = (block: any) => {
    if (!editor.value) return;

    if (block?.isReusablePart && Array.isArray(block.documentContent) && block.documentContent.length > 0) {
        editor.value.chain().focus().insertContent(block.documentContent).run();
        return;
    }
    
    editor.value.chain().focus().setLandingBlock({
        type: block.key,
        data: block.defaultAttrs || {},
    }).run();
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

/* Image Resize Styles */
.tiptap-editor .ProseMirror .image-resize-wrapper {
    position: relative;
    display: inline-block;
    max-width: 100%;
    margin: 1em 0;
}

.tiptap-editor .ProseMirror .image-resize-wrapper .resizable-image {
    display: block;
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
    user-select: none;
}

.tiptap-editor .ProseMirror .image-resize-wrapper .resize-handle {
    position: absolute;
    width: 12px;
    height: 12px;
    background: #3b82f6;
    border: 2px solid #ffffff;
    border-radius: 50%;
    cursor: nwse-resize;
    opacity: 0;
    transition: opacity 0.2s;
    z-index: 10;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.tiptap-editor .ProseMirror .image-resize-wrapper:hover .resize-handle,
.tiptap-editor .ProseMirror .image-resize-wrapper.hover .resize-handle,
.tiptap-editor .ProseMirror .image-resize-wrapper.resizing .resize-handle {
    opacity: 1;
}

.tiptap-editor .ProseMirror .image-resize-wrapper .resize-handle-nw {
    top: -6px;
    left: -6px;
    cursor: nwse-resize;
}

.tiptap-editor .ProseMirror .image-resize-wrapper .resize-handle-ne {
    top: -6px;
    right: -6px;
    cursor: nesw-resize;
}

.tiptap-editor .ProseMirror .image-resize-wrapper .resize-handle-sw {
    bottom: -6px;
    left: -6px;
    cursor: nesw-resize;
}

.tiptap-editor .ProseMirror .image-resize-wrapper .resize-handle-se {
    bottom: -6px;
    right: -6px;
    cursor: nwse-resize;
}

.tiptap-editor .ProseMirror .image-resize-wrapper.resizing {
    user-select: none;
}

.tiptap-editor .ProseMirror .image-resize-wrapper.resizing .resizable-image {
    pointer-events: none;
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
