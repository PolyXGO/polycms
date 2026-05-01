<template>
    <div v-if="form" class="space-y-4">
        <TemplateSelector
            v-model="form.template_theme"
            view-type="products.show"
            help="Select a specific template from your active themes to render this product."
        />
    </div>
</template>

<script setup lang="ts">
import { inject } from 'vue';
import { EditorContextKey } from '../../../../editor/context';
import TemplateSelector from '../../../../components/TemplateSelector.vue';

const context = inject(EditorContextKey);
if (!context) {
    throw new Error('ProductThemeTemplatePanel must be used within editor context');
}

const form = context.form;
</script>
