<template>
 <div class="tiptap-editor" :class="{'fixed inset-0 z-[100] h-screen w-screen bg-admin-theme-base p-4': isFullscreen }">
 <div class="border border-admin-theme-border rounded-lg bg-admin-theme-input-bg overflow-hidden flex flex-col" :class="{'h-full': isFullscreen }">
 <!-- Toolbar -->
 <div v-if="editable" class="border-b border-admin-theme-border bg-admin-theme-base p-2 flex flex-wrap items-center gap-1">
 <!-- Undo/Redo -->
 <button
 type="button"
 @click="editor?.chain().focus().undo().run()"
 :disabled="!editor || !editor.can().undo()"
 class="px-2 py-1 rounded text-sm font-medium transition-colors bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-base disabled:opacity-50"
 :title="$t('Undo') ||'Undo'"
 >
 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
 </svg>
 </button>
 <button
 type="button"
 @click="editor?.chain().focus().redo().run()"
 :disabled="!editor || !editor.can().redo()"
 class="px-2 py-1 rounded text-sm font-medium transition-colors bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-base disabled:opacity-50"
 :title="$t('Redo') ||'Redo'"
 >
 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6" />
 </svg>
 </button>

 <!-- Divider -->
 <div class="w-px h-6 bg-admin-theme-border mx-1"></div>

 <!-- Font Styling -->
 <select
 :value="editor?.getAttributes('textStyle').fontFamily || ''"
 @change="handleFontFamilyChange"
 :disabled="!editor"
 class="px-2 py-1 rounded text-xs border border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text hover:bg-admin-theme-base focus:ring-1 focus:ring-admin-theme-primary appearance-none cursor-pointer w-[130px]"
 >
 <option value="">Default Font</option>
 <option value="'Andale Mono', monospace" style="font-family: 'Andale Mono', monospace">Andale Mono</option>
 <option value="Arial, Helvetica, sans-serif" style="font-family: Arial, Helvetica, sans-serif">Arial</option>
 <option value="'Arial Black', sans-serif" style="font-family: 'Arial Black', sans-serif">Arial Black</option>
 <option value="'Book Antiqua', serif" style="font-family: 'Book Antiqua', serif">Book Antiqua</option>
 <option value="'Comic Sans MS', cursive" style="font-family: 'Comic Sans MS', cursive">Comic Sans MS</option>
 <option value="'Courier New', Courier, monospace" style="font-family: 'Courier New', Courier, monospace">Courier New</option>
 <option value="Georgia, serif" style="font-family: Georgia, serif">Georgia</option>
 <option value="Helvetica, sans-serif" style="font-family: Helvetica, sans-serif">Helvetica</option>
 <option value="Impact, charcoal, sans-serif" style="font-family: Impact, charcoal, sans-serif">Impact</option>
 <option value="Symbol" style="font-family: Symbol">Symbol</option>
 <option value="Tahoma, Geneva, sans-serif" style="font-family: Tahoma, Geneva, sans-serif">Tahoma</option>
 <option value="Terminal, Monaco, monospace" style="font-family: Terminal, Monaco, monospace">Terminal</option>
 <option value="'Times New Roman', Times, serif" style="font-family: 'Times New Roman', Times, serif">Times New Roman</option>
 <option value="'Trebuchet MS', Helvetica, sans-serif" style="font-family: 'Trebuchet MS', Helvetica, sans-serif">Trebuchet MS</option>
 <option value="Verdana, Geneva, sans-serif" style="font-family: Verdana, Geneva, sans-serif">Verdana</option>
 <option value="Webdings" style="font-family: Webdings">Webdings</option>
 <option value="Wingdings" style="font-family: Wingdings">Wingdings</option>
 </select>

 <select
 :value="editor?.getAttributes('textStyle').fontSize || ''"
 @change="handleFontSizeChange"
 :disabled="!editor"
 class="px-2 py-1 rounded text-xs border border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text hover:bg-admin-theme-base focus:ring-1 focus:ring-admin-theme-primary appearance-none cursor-pointer w-[65px]"
 >
 <option value="">Size</option>
 <option value="8pt">8pt</option>
 <option value="9pt">9pt</option>
 <option value="10pt">10pt</option>
 <option value="11pt">11pt</option>
 <option value="12pt">12pt</option>
 <option value="14pt">14pt</option>
 <option value="16pt">16pt</option>
 <option value="18pt">18pt</option>
 <option value="20pt">20pt</option>
 <option value="22pt">22pt</option>
 <option value="24pt">24pt</option>
 <option value="26pt">26pt</option>
 <option value="28pt">28pt</option>
 <option value="36pt">36pt</option>
 <option value="48pt">48pt</option>
 <option value="72pt">72pt</option>
 </select>

 <!-- Divider -->
 <div class="w-px h-6 bg-admin-theme-border mx-1"></div>

 <!-- Headings -->
 <button
 type="button"
 @click="editor?.chain().focus().toggleHeading({ level: 1 }).run()"
 :disabled="!editor"
 :class="[
'px-2 py-1 rounded text-sm font-medium transition-colors',
 editor?.isActive('heading', { level: 1 })
 ?'bg-admin-theme-primary text-admin-theme-primary-content'
 :'bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-base'
 ]"
 :title="$t('Heading 1') ||'Heading 1'"
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
 ?'bg-admin-theme-primary text-admin-theme-primary-content'
 :'bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-base'
 ]"
 :title="$t('Heading 2') ||'Heading 2'"
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
 ?'bg-admin-theme-primary text-admin-theme-primary-content'
 :'bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-base'
 ]"
 :title="$t('Heading 3') ||'Heading 3'"
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
 ?'bg-admin-theme-primary text-admin-theme-primary-content'
 :'bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-base'
 ]"
 :title="$t('Paragraph') ||'Paragraph'"
 >
 P
 </button>

 <!-- Divider -->
 <div class="w-px h-6 bg-admin-theme-border mx-1"></div>

 <!-- Lists -->
 <button
 type="button"
 @click="editor?.chain().focus().toggleBulletList().run()"
 :disabled="!editor"
 :class="[
'px-2 py-1 rounded text-sm font-medium transition-colors',
 editor?.isActive('bulletList')
 ?'bg-admin-theme-primary text-admin-theme-primary-content'
 :'bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-base'
 ]"
 :title="$t('Bullet List') ||'Bullet List'"
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
 ?'bg-admin-theme-primary text-admin-theme-primary-content'
 :'bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-base'
 ]"
 :title="$t('Ordered List') ||'Ordered List'"
 >
 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
 </svg>
 </button>

 <!-- Divider -->
 <div class="w-px h-6 bg-admin-theme-border mx-1"></div>

 <!-- Text Formatting -->
 <ToolbarColorPicker
 :model-value="editor?.getAttributes('textStyle').color || ''"
 @update:model-value="$event ? editor?.chain().focus().setColor($event).run() : editor?.chain().focus().unsetColor().run()"
 :disabled="!editor"
 :title="$t('Text Color') || 'Text Color'"
 >
 <template #icon>
 <span class="font-bold font-serif text-lg leading-none mb-1">A</span>
 </template>
 </ToolbarColorPicker>

 <ToolbarColorPicker
 :model-value="editor?.getAttributes('highlight').color || ''"
 @update:model-value="$event ? editor?.chain().focus().setHighlight({ color: $event }).run() : editor?.chain().focus().unsetHighlight().run()"
 :disabled="!editor"
 :title="$t('Background Color') || 'Background Color'"
 >
 <template #icon>
 <svg class="w-4 h-4 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
 </svg>
 </template>
 </ToolbarColorPicker>

 <div class="w-px h-6 bg-admin-theme-border mx-1"></div>

 <button
 type="button"
 @click="editor?.chain().focus().toggleBold().run()"
 :disabled="!editor"
 :class="[
'px-2 py-1 rounded text-sm font-medium transition-colors',
 editor?.isActive('bold')
 ?'bg-admin-theme-primary text-admin-theme-primary-content'
 :'bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-base'
 ]"
 :title="$t('Bold') ||'Bold'"
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
 ?'bg-admin-theme-primary text-admin-theme-primary-content'
 :'bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-base'
 ]"
 :title="$t('Italic') ||'Italic'"
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
 ?'bg-admin-theme-primary text-admin-theme-primary-content'
 :'bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-base'
 ]"
 :title="$t('Strikethrough') ||'Strikethrough'"
 >
 <span style="text-decoration: line-through;">S</span>
 </button>
 <!-- Code -->
 <button
 type="button"
 @click="editor?.chain().focus().toggleCode().run()"
 :disabled="!editor"
 :class="[
 'px-2 py-1 rounded text-sm font-medium transition-colors',
 editor?.isActive('code')
 ?'bg-admin-theme-primary text-admin-theme-primary-content'
 :'bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-base'
 ]"
 :title="$t('Code') ||'Code'"
 >
 &lt;/&gt;
 </button>
 <!-- Code Block -->
 <button
 type="button"
 @click="editor?.chain().focus().toggleCodeBlock().run()"
 :disabled="!editor"
 :class="[
 'px-2 py-1 rounded text-sm font-medium transition-colors flex items-center',
 editor?.isActive('codeBlock')
 ?'bg-admin-theme-primary text-admin-theme-primary-content'
 :'bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-base'
 ]"
 :title="$t('Code Block') ||'Code Block'"
 >
 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
 </svg>
 </button>
 <button
 type="button"
 @click="editor?.chain().focus().toggleUnderline().run()"
 :disabled="!editor"
 :class="[
'px-2 py-1 rounded text-sm font-medium transition-colors',
 editor?.isActive('underline')
 ?'bg-admin-theme-primary text-admin-theme-primary-content'
 :'bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-base'
 ]"
 :title="$t('Underline') ||'Underline'"
 >
 <u>U</u>
 </button>
 <!-- Removed Highlight from here because it's replaced by ToolbarColorPicker -->

 <!-- Divider -->
 <div class="w-px h-6 bg-admin-theme-border mx-1"></div>

 <!-- Link -->
 <button
 type="button"
 @click="setLink"
 :disabled="!editor"
 :class="[
'px-2 py-1 rounded text-sm font-medium transition-colors',
 editor?.isActive('link')
 ?'bg-admin-theme-primary text-admin-theme-primary-content'
 :'bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-base'
 ]"
 :title="$t('Link') ||'Link'"
 >
 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
 </svg>
 </button>

 <!-- Divider -->
 <div class="w-px h-6 bg-admin-theme-border mx-1"></div>

 <!-- Superscript/Subscript -->
 <button
 type="button"
 @click="editor?.chain().focus().toggleSuperscript().run()"
 :disabled="!editor"
 :class="[
'px-2 py-1 rounded text-sm font-medium transition-colors',
 editor?.isActive('superscript')
 ?'bg-admin-theme-primary text-admin-theme-primary-content'
 :'bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-base'
 ]"
 :title="$t('Superscript') ||'Superscript'"
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
 ?'bg-admin-theme-primary text-admin-theme-primary-content'
 :'bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-base'
 ]"
 :title="$t('Subscript') ||'Subscript'"
 >
 x<sub>2</sub>
 </button>

 <!-- Divider -->
 <div class="w-px h-6 bg-admin-theme-border mx-1"></div>

 <!-- Text Align -->
 <button
 type="button"
 @click="editor?.chain().focus().setTextAlign('left').run()"
 :disabled="!editor"
 :class="[
'px-2 py-1 rounded text-sm font-medium transition-colors',
 editor?.isActive({ textAlign:'left' })
 ?'bg-admin-theme-primary text-admin-theme-primary-content'
 :'bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-base'
 ]"
 :title="$t('Align Left') ||'Align Left'"
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
 editor?.isActive({ textAlign:'center' })
 ?'bg-admin-theme-primary text-admin-theme-primary-content'
 :'bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-base'
 ]"
 :title="$t('Align Center') ||'Align Center'"
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
 editor?.isActive({ textAlign:'right' })
 ?'bg-admin-theme-primary text-admin-theme-primary-content'
 :'bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-base'
 ]"
 :title="$t('Align Right') ||'Align Right'"
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
 editor?.isActive({ textAlign:'justify' })
 ?'bg-admin-theme-primary text-admin-theme-primary-content'
 :'bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-base'
 ]"
 :title="$t('Justify') ||'Justify'"
 >
 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M3 18h18M3 6h18" />
 </svg>
 </button>

 <!-- Divider -->
 <div class="w-px h-6 bg-admin-theme-border mx-1"></div>

 <!-- Blockquote -->
 <button
 type="button"
 @click="editor?.chain().focus().toggleBlockquote().run()"
 :disabled="!editor"
 :class="[
'px-2 py-1 rounded text-sm font-medium transition-colors',
 editor?.isActive('blockquote')
 ?'bg-admin-theme-primary text-admin-theme-primary-content'
 :'bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-base'
 ]"
 :title="$t('Blockquote') ||'Blockquote'"
 >
 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
 </svg>
 </button>

 <!-- Removed Code Block from here -->

 <!-- Image Upload -->
 <button
 type="button"
 @click="openMediaPicker"
 :disabled="!editor"
 class="px-2 py-1 rounded text-sm font-medium transition-colors bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-base disabled:opacity-50 flex items-center gap-1"
 :title="$t('Add Image') ||'Add Image'"
 >
 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
 </svg>
 <span class="text-xs">{{ $t('Add') ||'Add' }}</span>
 </button>

 <!-- Table -->
 <ToolbarTableDropdown :editor="editor" />

 <!-- Youtube Gallery -->
 <button
 type="button"
 @click="openYoutubeGalleryModal()"
 :disabled="!editor"
 class="px-2 py-1 rounded text-sm font-medium transition-colors text-red-500 bg-admin-theme-input-bg hover:bg-admin-theme-base"
 :title="$t('Insert YouTube Gallery')"
 >
 <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>
 </button>

  <!-- Modal Link (Popup Label) -->
  <button
  type="button"
  @click="openModalLinkModal()"
  :disabled="!editor"
  class="px-2 py-1 rounded text-sm font-medium transition-colors text-blue-500 bg-admin-theme-input-bg hover:bg-admin-theme-base"
  :title="$t('Insert Modal Link') || 'Insert Modal Link'"
  >
  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path>
  </svg>
  </button>

  <!-- Mermaid Chart -->
  <button
  type="button"
  @click="insertMermaidChart()"
  :disabled="!editor"
  class="px-2 py-1 rounded text-sm font-medium transition-colors text-emerald-600 dark:text-emerald-500 bg-admin-theme-input-bg hover:bg-admin-theme-base flex items-center justify-center"
  :title="$t('Insert Mermaid Diagram') || 'Insert Mermaid Diagram'"
  >
  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
  </svg>
  </button>

 <!-- Landing Blocks -->
 <button
 v-if="!compact"
 type="button"
 @click="openBlockPicker"
 :disabled="!editor"
 class="px-2 py-1 rounded text-sm font-bold transition-colors bg-admin-theme-primary/10 text-admin-theme-primary hover:bg-admin-theme-primary/15 flex items-center gap-1 border border-admin-theme-primary/30"
 :title="$t('Insert Landing Block') ||'Insert Landing Block'"
 >
 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
 </svg>
 <span class="text-xs">Landing</span>
 </button>

  <!-- Project Hub -->
  <button
  v-if="!compact"
  type="button"
  @click="openProjectHubModal"
  :disabled="!editor"
  class="px-2 py-1 rounded text-sm font-bold transition-colors bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-500/15 flex items-center gap-1 border border-indigo-500/30"
  :title="$t('Insert Project Hub Element') ||'Insert Project Hub Element'"
  >
  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z" />
  </svg>
  <span class="text-xs">Project Hub</span>
  </button>

 <div class="ml-auto flex items-center gap-2">
 <button
 type="button"
 @click="toggleFullscreen"
 class="px-2 py-1 rounded text-sm font-medium transition-colors border border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text hover:bg-admin-theme-base"
 :title="isFullscreen ? ($t('Exit Fullscreen') ||'Exit Fullscreen') : ($t('Fullscreen') ||'Fullscreen')"
 >
 <svg v-if="!isFullscreen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
 </svg>
 <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 14h4v4m0-4l-5 5m11-5h4v4m-4-4l5 5M4 10h4V6m0 4l-5-5m11 5h4V6m-4 4l5-5" />
 </svg>
 </button>

 <div class="flex items-center border border-admin-theme-border rounded-lg overflow-hidden">
 <button
 type="button"
 @click="setVisualMode"
 :class="[
'px-3 py-1 text-xs font-medium transition-colors',
 !isHtmlMode
 ?'bg-admin-theme-primary text-admin-theme-primary-content'
 :'bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-base'
 ]"
 >
 {{ $t('Visual') ||'Visual' }}
 </button>
 <button
 type="button"
 @click="setHtmlMode"
 :class="[
'px-3 py-1 text-xs font-mono transition-colors border-l border-admin-theme-border',
 isHtmlMode
 ?'bg-admin-theme-primary text-admin-theme-primary-content'
 :'bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-base'
 ]"
 >
 HTML
 </button>
 </div>
 </div>
 </div>

 <!-- Editor Content -->
 <div class="flex-1 overflow-y-auto">
 <EditorContent
 v-if="editor && !isHtmlMode"
 :editor="editor"
 class="prose prose-sm max-w-none dark:prose-invert p-4"
 />
 <textarea
 v-else
 v-model="htmlSource"
 class="w-full h-full min-h-[200px] max-h-[600px] overflow-y-auto p-4 border-0 bg-admin-theme-input-bg text-admin-theme-text font-mono text-sm leading-6 focus:outline-none resize-y"
 :class="{'max-h-none': isFullscreen }"
 :placeholder="$t('Edit raw HTML content...') ||'Edit raw HTML content...'"
 :readonly="!editable"
 @input="handleHtmlInput"
 ></textarea>
 </div>
 </div>

 <MediaPicker
 ref="mediaPickerRef"
 :multiple="false"
 :accepted-types="['image']"
 @select="handleMediaSelect"
 />

 <TableBubbleMenu :editor="editor" />

 <!-- Block Picker -->
 <LandingBlockPicker
 ref="blockPickerRef"
 @select="handleBlockSelect"
 />
 </div>
</template>

<script setup lang="ts">
import { useEditor, EditorContent } from'@tiptap/vue-3';
import { getMarkRange, Extension } from'@tiptap/core';
import StarterKit from'@tiptap/starter-kit';
import Placeholder from'@tiptap/extension-placeholder';
import Link from'@tiptap/extension-link';
import { TiptapImageResize } from'./TiptapImageResize';
import TextAlign from'@tiptap/extension-text-align';
import Underline from'@tiptap/extension-underline';
import Strike from'@tiptap/extension-strike';
import Code from'@tiptap/extension-code';
import Highlight from '@tiptap/extension-highlight';
import Superscript from '@tiptap/extension-superscript';
import Subscript from '@tiptap/extension-subscript';
import { TextStyle, FontFamily, FontSize, Color } from '@tiptap/extension-text-style';
import { LandingBlock } from'../editor/LandingBlock';
import ToolbarColorPicker from'./editor/controls/ToolbarColorPicker.vue';
import { watch, onBeforeUnmount, onMounted, getCurrentInstance, ref } from'vue';
import { useTranslation } from'@/admin/composables/useTranslation';
import { useDialog } from'@/admin/composables/useDialog';
import MediaPicker from'@/admin/components/MediaPicker.vue';
import LandingBlockPicker from'./editor/LandingBlockPicker.vue';
import EmailLinkModal from'@/admin/components/dialogs/EmailLinkModal.vue';
import { useLandingStore } from'@/admin/stores/landingStore';

import { Table, TableView } from '@tiptap/extension-table';
import { TableRow } from '@tiptap/extension-table-row';
import { TableHeader } from '@tiptap/extension-table-header';
import { TableCell } from '@tiptap/extension-table-cell';
import ToolbarTableDropdown from './editor/controls/ToolbarTableDropdown.vue';
import TableBubbleMenu from './editor/controls/TableBubbleMenu.vue';

import YoutubeGalleryModal from '@/admin/components/dialogs/YoutubeGalleryModal.vue';
import { YoutubeGallery } from './editor/extensions/YoutubeGallery';
import ModalLinkModal from '@/admin/components/dialogs/ModalLinkModal.vue';
import { ModalLink } from './editor/extensions/ModalLink';
import { MermaidChart } from './editor/extensions/MermaidChart';
import InsertProjectHubModal from '@/admin/components/dialogs/InsertProjectHubModal.vue';

const handleGlobalMouseUp = () => {
  (window as any).isDraggingMediaGesture = false;
};
window.addEventListener('mouseup', handleGlobalMouseUp, { passive: true });
window.addEventListener('touchend', handleGlobalMouseUp, { passive: true });

onBeforeUnmount(() => {
  window.removeEventListener('mouseup', handleGlobalMouseUp);
  window.removeEventListener('touchend', handleGlobalMouseUp);
});

const props = withDefaults(defineProps<{
 modelValue: string;
 json?: any;
 placeholder?: string;
 editable?: boolean;
 compact?: boolean;
}>(), {
 editable: true,
 compact: false,
});

const emit = defineEmits<{
'update:modelValue': [value: string];
'update:json': [value: any];
}>();

const transformShortcodesToHtml = (content: string): string => {
  if (!content || typeof content !== 'string') return content;
  
  // Unescape &quot; HTML entities back to real double quotes for clean HTML attribute parsing
  let cleaned = content.replace(/&quot;/g, '"');
  
  // Regex to match [landing_block type="..." data="..."]
  // Pattern: \[landing_block\s+([^\]]+)\]
  const shortcodeRegex = /\[landing_block\s+([^\]]+)\]/g;
  
  return cleaned.replace(shortcodeRegex, (match, attrsString) => {
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
const htmlSource = ref(props.modelValue ||'');
let isUpdating = false;

const isFullscreen = ref(false);
const toggleFullscreen = () => {
  isFullscreen.value = !isFullscreen.value;
};

const handleFontFamilyChange = (event: Event) => {
  const target = event.target as HTMLSelectElement | null;
  if (!editor.value || editor.value.isDestroyed || !target) return;
  const value = target.value;
  if (value) {
    editor.value.chain().focus().setFontFamily(value).run();
  } else {
    editor.value.chain().focus().unsetFontFamily().run();
  }
};

const handleFontSizeChange = (event: Event) => {
  const target = event.target as HTMLSelectElement | null;
  if (!editor.value || editor.value.isDestroyed || !target) return;
  const value = target.value;
  if (value) {
    editor.value.chain().focus().setFontSize(value).run();
  } else {
    editor.value.chain().focus().unsetFontSize().run();
  }
};

interface LinkContext {
 from: number;
 to: number;
 href: string;
 target: string;
 rel: string;
 text: string;
}

const TableStyleExtension = Extension.create({
  name: 'tableStyles',
  addGlobalAttributes() {
    return [
      {
        types: ['table', 'tableCell', 'tableHeader', 'tableRow'],
        attributes: {
          style: {
            default: null,
            parseHTML: element => element.getAttribute('style'),
            renderHTML: attributes => {
              if (!attributes.style) return {};
              return { style: attributes.style };
            },
          },
        },
      },
    ];
  },
});

class CustomTableView extends TableView {
  update(node: any) {
    const wasUpdated = super.update(node);
    if (wasUpdated) {
      if (node.attrs.style) {
        // Save the width/minWidth computed by updateColumns in super.update()
        const computedWidth = this.table.style.width;
        const computedMinWidth = this.table.style.minWidth;
        
        // Apply user style
        this.table.style.cssText = node.attrs.style;
        
        // Restore width/minWidth so resizing isn't broken
        if (computedWidth) this.table.style.width = computedWidth;
        if (computedMinWidth) this.table.style.minWidth = computedMinWidth;
      } else {
        const computedWidth = this.table.style.width;
        const computedMinWidth = this.table.style.minWidth;
        this.table.style.cssText = '';
        if (computedWidth) this.table.style.width = computedWidth;
        if (computedMinWidth) this.table.style.minWidth = computedMinWidth;
      }
    }
    return wasUpdated;
  }
}

const editor = useEditor({
 extensions: [
 YoutubeGallery,
 ModalLink,
 MermaidChart,
 TableStyleExtension,
 Table.configure({
 resizable: true,
 HTMLAttributes: { class: 'min-w-full border-collapse table-auto' },
 View: CustomTableView,
 }),
 TableRow,
 TableHeader,
 TableCell,
    StarterKit.configure({
      heading: {
        levels: [1, 2, 3],
      },
      link: {
        openOnClick: false,
        HTMLAttributes: {
          class: 'hover:underline',
        },
      },
    }),
 Placeholder.configure({
 placeholder: props.placeholder || ($t('Start typing...') ||'Start typing...'),
 }),
 TiptapImageResize.configure({
 inline: true,
 allowBase64: false,
 HTMLAttributes: {
 class:'max-w-full h-auto rounded',
 },
 }),
 TextAlign.configure({
 types: ['heading','paragraph'],
 }),
 Highlight.configure({
 multicolor: true,
 }),
 Superscript,
 Subscript,
 TextStyle,
 FontFamily,
 FontSize,
 Color,
 LandingBlock,
 ],
 content: props.json || transformShortcodesToHtml(props.modelValue ||''),
 editable: props.editable,
 onUpdate: ({ editor }) => {
 if (isHtmlMode.value) {
 return;
 }
 const html = editor.getHTML();
 const json = editor.getJSON();
 
 // Only emit if content is not empty (not just empty paragraph)
 isUpdating = true;
 if (html && html.trim() !=='<p></p>' && html.trim() !=='<p><br></p>') {
 emit('update:modelValue', html);
 emit('update:json', json);
 } else {
 emit('update:modelValue','');
 emit('update:json', null);
 }
 setTimeout(() => { isUpdating = false; }, 0);
 },
 editorProps: {
    attributes: {
      class:'prose prose-sm max-w-none dark:prose-invert focus:outline-none',
    },
    handleDOMEvents: {
        mousedown: (_view: any, event: MouseEvent) => {
          const target = event.target as HTMLElement | null;
          if (target?.closest('button') || target?.closest('.mtxf-action-box') || target?.closest('.mtxf-action-btn')) {
            return false;
          }
          if (target?.closest('.showcase-img') || target?.closest('.mtxf-transform-overlay')) {
            event.preventDefault();
            event.stopPropagation();
            (window as any).isDraggingMediaGesture = true;
            return true;
          }
          return false;
        },
        touchstart: (_view: any, event: TouchEvent) => {
          const target = event.target as HTMLElement | null;
          if (target?.closest('button') || target?.closest('.mtxf-action-box') || target?.closest('.mtxf-action-btn')) {
            return false;
          }
          if (target?.closest('.showcase-img') || target?.closest('.mtxf-transform-overlay')) {
            event.preventDefault();
            event.stopPropagation();
            (window as any).isDraggingMediaGesture = true;
            return true;
          }
          return false;
        },
      dragstart: (_view: any, event: DragEvent) => {
        if ((window as any).isDraggingMediaGesture || (window as any).isHoveringMedia || (window as any).isTransformingMedia) {
          event.preventDefault();
          event.stopPropagation();
          return true;
        }
        const target = event.target as HTMLElement | null;
        if (target?.closest('.showcase-img') || target?.closest('.mtxf-transform-overlay')) {
          event.preventDefault();
          event.stopPropagation();
          return true;
        }
        return false;
      }
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
watch(() => props.editable, (value) => {
 if (editor.value) {
 editor.value.setEditable(value);
 }
});

watch(() => props.modelValue, (value) => {
 const normalized = value ||'';
 if (isHtmlMode.value) {
 if (htmlSource.value !== normalized) {
 htmlSource.value = normalized;
 }
 return;
 }

 if (isUpdating) return;

 if (editor.value && !props.json && editor.value.getHTML() !== value) {
 editor.value.commands.setContent(transformShortcodesToHtml(value ||''));
 }
});

watch(() => props.json, (value) => {
 if (isHtmlMode.value) {
 return;
 }
 if (editor.value && JSON.stringify(editor.value.getJSON()) !== JSON.stringify(value)) {
 editor.value.commands.setContent(value ||'');
 }
}, { deep: true });

// Handle link insertion
const setLink = () => {
 if (!editor.value) return;

 const selection = editor.value.state.selection;
 const linkContext = getCurrentLinkContext();
 const selectedText = linkContext?.text || editor.value.state.doc.textBetween(selection.from, selection.to,'');
 const linkAttrs = linkContext
 ? { href: linkContext.href, target: linkContext.target }
 : (editor.value.getAttributes('link') || {});
 const effectiveFrom = linkContext?.from ?? selection.from;
 const effectiveTo = linkContext?.to ?? selection.to;

 dialog.showModal({
 title: $t('Insert Link') ||'Insert Link',
 component: EmailLinkModal,
 size:'md',
 props: {
 initialUrl: linkAttrs.href ||'',
 initialText: selectedText ||'',
 initialOpenInNewTab: linkAttrs.target ==='_blank',
 initialRelMode: resolveRelModeFromRel(linkContext?.rel || linkAttrs.rel ||''),
 onSubmit: (payload: { url: string; text: string; openInNewTab: boolean; relMode: string; remove?: boolean }) => {
 applyLink(payload, {
 from: effectiveFrom,
 to: effectiveTo,
 href: linkAttrs.href ||'',
 target: linkAttrs.target ||'',
 rel: linkContext?.rel || linkAttrs.rel ||'',
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
 alt: selected.name || selected.file_name ||'Image',
 }).run();
};

const setHtmlMode = () => {
 if (!editor.value) return;
 htmlSource.value = editor.value.getHTML() || props.modelValue ||'';
 isHtmlMode.value = true;
};

const setVisualMode = () => {
 if (!editor.value) return;
 isHtmlMode.value = false;
 editor.value.commands.setContent(htmlSource.value ||'');
 emit('update:modelValue', htmlSource.value ||'');
};

const handleHtmlInput = () => {
 emit('update:modelValue', htmlSource.value);
};

const escapeHtml = (value: string): string => {
 return value
 .replace(/&/g,'&amp;')
 .replace(/</g,'&lt;')
 .replace(/>/g,'&gt;')
 .replace(/"/g,'&quot;')
 .replace(/'/g,'&#39;');
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
 const text = state.doc.textBetween(range.from, range.to,'');
 const attrsFromNode = state.doc.nodeAt(range.from)?.marks?.find((mark) => mark.type === linkType)?.attrs
 || (range.from > 0 ? state.doc.nodeAt(range.from - 1)?.marks?.find((mark) => mark.type === linkType)?.attrs : null)
 || {};
 return {
 from: range.from,
 to: range.to,
 href: attrsFromNode.href ||'',
 target: attrsFromNode.target ||'',
 rel: attrsFromNode.rel ||'',
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
 const relAttr = relValue ? ` rel="${escapeHtml(relValue)}"` :'';
 const targetAttr = payload.openInNewTab ?' target="_blank"' :'';
 const classAttr =' class="text-admin-theme-primary dark:text-admin-theme-primary hover:underline"';
 editor.value
 .chain()
 .focus()
 .insertContentAt({ from, to }, `<a href="${safeHref}"${targetAttr}${relAttr}${classAttr}>${safeText}</a>`)
 .run();
};

const resolveRelModeFromRel = (relValue: string): string => {
 const normalized = relValue.toLowerCase().trim();
 if (!normalized) return'follow';
 const parts = new Set(normalized.split(/\s+/).filter(Boolean));
 const hasNofollow = parts.has('nofollow');
 const hasUgc = parts.has('ugc');
 const hasSponsored = parts.has('sponsored');

 if (hasNofollow && hasUgc) return'nofollow_ugc';
 if (hasNofollow && hasSponsored) return'nofollow_sponsored';
 if (hasSponsored) return'sponsored';
 if (hasUgc) return'ugc';
 if (hasNofollow) return'nofollow';
 return'follow';
};

const buildRelValue = (relMode: string, openInNewTab: boolean): string => {
 const relTokens = new Set<string>();
 switch (relMode) {
 case'nofollow':
 relTokens.add('nofollow');
 break;
 case'ugc':
 relTokens.add('ugc');
 break;
 case'sponsored':
 relTokens.add('sponsored');
 break;
 case'nofollow_ugc':
 relTokens.add('nofollow');
 relTokens.add('ugc');
 break;
 case'nofollow_sponsored':
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

 return Array.from(relTokens).join('').trim();
};

const blockPickerRef = ref<InstanceType<typeof LandingBlockPicker> | null>(null);

const openBlockPicker = () => {
  blockPickerRef.value?.open();
};

const openProjectHubModal = () => {
  if (!editor.value || editor.value.isDestroyed) return;
  dialog.showModal({
    title: $t('Insert Project Hub Element') || 'Insert Project Hub Element',
    component: InsertProjectHubModal,
    size: 'md',
    props: {
      editor: editor.value
    }
  });
};

const handleBlockSelect = (block: any) => {
  if (!editor.value) return;

  if (block?.isReusablePart) {
    if (Array.isArray(block.documentContent) && block.documentContent.length > 0) {
      try {
        editor.value.chain().focus().insertContent(block.documentContent).run();
        return;
      } catch (e) {
        console.warn('Direct documentContent insertion failed, falling back to HTML snippet', e);
      }
    }
    const partTitle = block.label || block.asset?.name || 'Reusable Element';
    const htmlSnippet = `<div class="reusable-part-snippet p-4 my-2 rounded-xl border border-admin-theme-border bg-slate-900/80 text-white font-bold">● ${partTitle}</div>`;
    editor.value.chain().focus().insertContent(htmlSnippet).run();
    return;
  }
  
  const chain = editor.value.chain().focus() as any;
  if (chain && typeof chain.setLandingBlock === 'function') {
    try {
      chain.setLandingBlock({
        type: block.key,
        data: block.defaultAttrs || {},
      }).run();
      return;
    } catch (e) {
      console.warn('setLandingBlock execution failed, using HTML fallback', e);
    }
  }

  const htmlSnippet = block.html || block.template || block.defaultAttrs?.content || 
    `<div class="landing-block-snippet p-4 my-2 rounded-xl border border-admin-theme-border bg-slate-900/60 text-white shadow-md">` +
    `<div class="font-extrabold text-sm text-emerald-400 mb-1">● ${block.label || block.title || block.key}</div>` +
    `<div class="text-xs opacity-90">${block.description || ''}</div>` +
    `</div>`;
  editor.value.chain().focus().insertContent(htmlSnippet).run();
};

const openYoutubeGalleryModal = (initialUrls = [], initialLayout = 'grid', initialSliderVisibleItems = 1, initialSliderAutoPlay = false, initialSliderContinuous = false, initialSliderDirection = 'left', getPos: any = null) => {
   if (!editor.value || editor.value.isDestroyed) return;
   
   dialog.showModal({
     title: $t('YouTube Video Gallery') || 'YouTube Video Gallery',
     component: YoutubeGalleryModal,
     size: 'lg',
     props: {
       initialUrls,
       initialLayout,
       initialSliderVisibleItems,
       initialSliderAutoPlay,
       initialSliderContinuous,
       initialSliderDirection,
       onSubmit: (payload: { urls: string[], layout: string, sliderVisibleItems: number, sliderAutoPlay: boolean, sliderContinuous: boolean, sliderDirection: string }) => {
         if (!editor.value || editor.value.isDestroyed) return;
         
         if (getPos) {
             // Update existing
             const pos = getPos();
             editor.value.chain().focus().setNodeSelection(pos).updateAttributes('youtubeGallery', {
                 urls: payload.urls,
                 layout: payload.layout,
                 sliderVisibleItems: payload.sliderVisibleItems,
                 sliderAutoPlay: payload.sliderAutoPlay,
                 sliderContinuous: payload.sliderContinuous,
                 sliderDirection: payload.sliderDirection
             }).run();
         } else {
             // Insert new
             editor.value.chain().focus().insertContent({
                 type: 'youtubeGallery',
                 attrs: {
                     urls: payload.urls,
                     layout: payload.layout,
                     sliderVisibleItems: payload.sliderVisibleItems,
                     sliderAutoPlay: payload.sliderAutoPlay,
                     sliderContinuous: payload.sliderContinuous,
                     sliderDirection: payload.sliderDirection
                 }
             }).run();
         }
       }
     }
   });
};

const handleOpenYoutubeGalleryModal = (event: Event) => {
  const e = event as CustomEvent;
  if (e.detail) {
    if (e.detail.editor && e.detail.editor !== editor.value) {
      return;
    }
    openYoutubeGalleryModal(e.detail.urls, e.detail.layout, e.detail.sliderVisibleItems, e.detail.sliderAutoPlay, e.detail.sliderContinuous, e.detail.sliderDirection, e.detail.getPos);
  }
};

const openModalLinkModal = (initialLabelText = 'Click here', initialModalSize = 'lg', initialContentType = 'html', initialContentHtml = '', initialIframeUrl = '', initialDisplayMode = 'button', getPos: any = null) => {
  if (!editor.value || editor.value.isDestroyed) return;
  
  dialog.showModal({
    title: $t('Landing Modal Link') || 'Landing Modal Link',
    component: ModalLinkModal,
    size: 'lg',
    props: {
      initialLabelText,
      initialModalSize,
      initialContentType,
      initialContentHtml,
      initialIframeUrl,
      initialDisplayMode,
      onSubmit: (payload: { labelText: string, modalSize: string, contentType: string, contentHtml: string, iframeUrl: string, displayMode: string }) => {
        if (!editor.value || editor.value.isDestroyed) return;
        
        if (getPos) {
          const pos = getPos();
          editor.value.chain().focus().setNodeSelection(pos).updateAttributes('modalLink', payload).run();
        } else {
          editor.value.chain().focus().insertContent({ type: 'modalLink', attrs: payload }).run();
        }
      }
    }
  });
};

const insertMermaidChart = () => {
  if (!editor.value || editor.value.isDestroyed) return;
  editor.value.chain().focus().insertContent({
    type: 'mermaidChart',
    attrs: {
      code: 'graph TD\n    A[Start] --> B[Check Condition]\n    B -->|Yes| C[Perform Action]\n    B -->|No| D[Raise Error]\n    C --> E[Success]\n    D --> E'
    }
  }).run();
};

const handleOpenModalLinkModal = (event: Event) => {
  const e = event as CustomEvent;
  if (e.detail) {
    if (e.detail.editor && e.detail.editor !== editor.value) {
      return;
    }
    openModalLinkModal(e.detail.labelText, e.detail.modalSize, e.detail.contentType, e.detail.contentHtml, e.detail.iframeUrl, e.detail.displayMode || 'button', e.detail.getPos);
  }
};

onMounted(() => {
  window.addEventListener('open-youtube-gallery-modal', handleOpenYoutubeGalleryModal);
  window.addEventListener('open-modal-link-modal', handleOpenModalLinkModal);
});

onBeforeUnmount(() => {
  window.removeEventListener('open-youtube-gallery-modal', handleOpenYoutubeGalleryModal);
  window.removeEventListener('open-modal-link-modal', handleOpenModalLinkModal);
  if (editor.value) {
      editor.value.destroy();
  }
});

const insertContent = (content: string) => {
    if (editor.value && !editor.value.isDestroyed) {
        editor.value.chain().focus().insertContent(content).run();
    }
};

defineExpose({
    insertContent
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

.tiptap-editor .ProseMirror table {
  border-collapse: collapse;
  table-layout: fixed;
  width: 100%;
  margin: 0;
  overflow: hidden;
  border-color: #d1d5db;
  border-style: solid;
  border-width: 1px;
}

.dark .tiptap-editor .ProseMirror table {
  border-color: #4b5563;
}

.tiptap-editor .ProseMirror table tbody,
.tiptap-editor .ProseMirror table tr,
.tiptap-editor .ProseMirror table td,
.tiptap-editor .ProseMirror table th {
  border-color: inherit;
  border-style: inherit;
  border-width: inherit;
}

.tiptap-editor .ProseMirror table td,
.tiptap-editor .ProseMirror table th {
  min-width: 1em;
  padding: 8px;
  vertical-align: top;
  box-sizing: border-box;
  position: relative;
}

/* Remove the old dark mode border for td/th since it now inherits from table */

.tiptap-editor .ProseMirror table th {
  font-weight: 600;
  text-align: left;
  background-color: rgba(0, 0, 0, 0.05);
}

.dark .tiptap-editor .ProseMirror table th {
  background-color: rgba(255, 255, 255, 0.05);
}

.tiptap-editor .ProseMirror table td > p,
.tiptap-editor .ProseMirror table th > p {
  margin: 0;
  line-height: 1.5;
}

.tiptap-editor .ProseMirror table td > p:empty::after,
.tiptap-editor .ProseMirror table th > p:empty::after {
  content: "\00A0";
}

.tiptap-editor .ProseMirror table .column-resize-handle {
  position: absolute;
  right: -2px;
  top: 0;
  bottom: 0;
  width: 4px;
  background-color: #60a5fa;
  pointer-events: none;
}
</style>
