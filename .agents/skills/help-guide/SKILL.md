---
name: help-guide
description: PolyCMS UI Guideline Modal and Help Icon pattern for displaying multi-language help documentation and best practice guidelines across Admin panels.
---

# PolyCMS UI Skill: HelpGuide & Guideline Modal Pattern

## Overview
PolyCMS Core provides a universal Vue component `<HelpGuide>` registered globally in `resources/js/admin/main.ts`. It renders a styled `?` help icon button that opens a high-priority Teleport Modal (`z-[999999]`) containing multi-language guidelines, best practices, or setup instructions.

## Global Component Reference
- **Component**: `<HelpGuide>` (Globally registered in Admin Vue application)
- **Source File**: [HelpGuide.vue](file:///d:/PolyXGO/2026/PolyCMS/polycms/polycms/resources/js/admin/components/HelpGuide.vue)

## Props & Options
| Prop | Type | Default | Description |
| :--- | :--- | :--- | :--- |
| `title` | `String` | *(Required)* | Modal header title (always wrap in `t('...')` for i18n) |
| `description` | `String` | `''` | Optional raw HTML string for body text |
| `tooltip` | `String` | `''` | Tooltip text on hover over `?` button |
| `iconClass` | `String` | `'ml-1'` | Tailwind classes for fine-tuning button margins/styles |

## Standard Implementation Pattern

```vue
<template>
  <div class="flex items-center gap-1.5">
    <span class="font-bold text-admin-theme-text">{{ title }}</span>
    <HelpGuide 
      :title="t('Feature Guide Title')"
      :tooltip="t('Click for guidelines')"
    >
      <div class="space-y-3 text-xs leading-relaxed">
        <p class="text-admin-theme-text-secondary border-b border-admin-theme-border/60 pb-2 font-medium">
          {{ t('Overview explanation of this section or panel...') }}
        </p>
        <ul class="space-y-2 list-disc pl-4 text-admin-theme-text-secondary">
          <li>
            <strong class="text-admin-theme-text">{{ t('Metric / Field Name') }}:</strong>
            {{ t('Explanation of what this indicator or setting controls...') }}
          </li>
        </ul>
      </div>
    </HelpGuide>
  </div>
</template>

<script setup lang="ts">
import { useTranslation } from '@/admin/composables/useTranslation';

const { t } = useTranslation();
</script>
```

## Best Practices
1. **Always use i18n `t('...')`**: Wrap all titles, labels, and bullet point explanations with `t('...')` to support seamless multi-language switching.
2. **Teleport Modal Layer**: `<HelpGuide>` automatically teleports its modal to `body` with `z-[999999]`, keeping it above any panel frames, sidebars, or backdrop overlays.
3. **Consistent Icon Styling**: Use standard SVG help icon provided inside `HelpGuide.vue` to maintain UI uniformity across Admin panels, Settings, Payments, Accounting, and Editor views.
