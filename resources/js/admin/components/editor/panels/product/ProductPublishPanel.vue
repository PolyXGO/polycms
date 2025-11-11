<template>
    <div v-if="form" class="publish-panel">
        <div class="publish-panel__group">
            <div class="publish-panel__row">
                <div class="publish-panel__pair">
                    <span class="publish-panel__meta-label">Status:</span>
                    <strong class="publish-panel__meta-value">{{ statusLabel }}</strong>
                </div>
                <button type="button" class="publish-panel__link" @click="toggleStatus">
                    Edit
                </button>
            </div>
            <div v-if="showStatusEditor" class="publish-panel__editor">
                <select v-model="draftStatus" class="publish-panel__input">
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="archived">Archived</option>
                </select>
                <div class="publish-panel__editor-actions">
                    <button type="button" class="publish-panel__button publish-panel__button--primary" @click="confirmStatus">
                        OK
                    </button>
                    <button type="button" class="publish-panel__button" @click="cancelStatus">
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <div class="publish-panel__group">
            <div class="publish-panel__row">
                <div class="publish-panel__pair">
                    <span class="publish-panel__meta-label">Publish Date:</span>
                    <strong class="publish-panel__meta-value">{{ scheduledLabel }}</strong>
                </div>
                <button type="button" class="publish-panel__link" @click="toggleDate">
                    Edit
                </button>
            </div>
            <div v-if="showDateEditor" class="publish-panel__editor">
                <input v-model="draftDate" type="datetime-local" class="publish-panel__input" />
                <div class="publish-panel__editor-actions">
                    <button type="button" class="publish-panel__button publish-panel__button--primary" @click="confirmDate">
                        OK
                    </button>
                    <button type="button" class="publish-panel__button" @click="cancelDate">
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <div class="publish-panel__meta">
            <div v-if="form.created_at" class="publish-panel__meta-item">
                <span class="publish-panel__meta-label">Created on:</span>
                <span class="publish-panel__meta-value">{{ formatDate(form.created_at) }}</span>
            </div>
            <div v-if="form.updated_at" class="publish-panel__meta-item">
                <span class="publish-panel__meta-label">Last updated:</span>
                <span class="publish-panel__meta-value">{{ formatDate(form.updated_at) }}</span>
            </div>
        </div>

        <div class="publish-panel__actions">
            <button type="button" class="publish-panel__button" @click="helpers.preview?.()">
                Preview Changes
            </button>
            <button
                type="button"
                class="publish-panel__button publish-panel__button--primary"
                :disabled="context.loading.value"
                @click="helpers.save?.()"
            >
                {{ context.loading.value ? 'Saving…' : 'Update' }}
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, inject, ref, watch } from 'vue';
import { EditorContextKey } from '../../../../editor/context';

const context = inject(EditorContextKey);
if (!context) {
    throw new Error('ProductPublishPanel must be used within editor context');
}

const form = context.form;
const helpers = context.helpers ?? {};

if (!form.value) {
    form.value = {
        name: '',
        slug: '',
        sku: '',
        type: context.type ?? 'product',
        status: 'draft',
        price: 0,
        sale_price: null,
        stock_quantity: 0,
        stock_status: 'in_stock',
        manage_stock: false,
        featured: false,
        meta_title: '',
        meta_description: '',
        meta_keywords: '',
        created_at: null,
        updated_at: null,
        published_at: null,
        scheduled_at: null,
    } as any;
}

const toLocalDateInput = (value: string) => {
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return '';
    }
    const offset = date.getTimezoneOffset();
    const local = new Date(date.getTime() - offset * 60 * 1000);
    return local.toISOString().slice(0, 16);
};

const showStatusEditor = ref(false);
const showDateEditor = ref(false);
const draftStatus = ref(form.value.status);
const initialDate =
    form.value.scheduled_at ??
    form.value.published_at ??
    form.value.created_at ??
    null;
const draftDate = ref<string | null>(initialDate ? toLocalDateInput(initialDate) : null);

const statusLabel = computed(() => {
    const labels: Record<string, string> = {
        draft: 'Draft',
        published: 'Published',
        archived: 'Archived',
    };
    return labels[form.value.status] ?? form.value.status;
});

const scheduledLabel = computed(() => {
    if (form.value.scheduled_at) {
        return formatDate(form.value.scheduled_at);
    }
    if (form.value.published_at) {
        return formatDate(form.value.published_at);
    }
    if (form.value.created_at) {
        return formatDate(form.value.created_at);
    }
    return 'Not set';
});

const toggleStatus = () => {
    showStatusEditor.value = !showStatusEditor.value;
    draftStatus.value = form.value.status;
};

const confirmStatus = () => {
    form.value.status = draftStatus.value;
    if (form.value.status === 'published') {
        const nowIso = new Date().toISOString();
        if (!form.value.published_at || new Date(form.value.published_at).getTime() > Date.now()) {
            form.value.published_at = nowIso;
        }
        form.value.scheduled_at = null;
    } else {
        form.value.published_at = null;
    }
    showStatusEditor.value = false;
};

const cancelStatus = () => {
    showStatusEditor.value = false;
    draftStatus.value = form.value.status;
};

const toggleDate = () => {
    showDateEditor.value = !showDateEditor.value;
    const source =
        form.value.scheduled_at ??
        form.value.published_at ??
        form.value.created_at ??
        null;
    draftDate.value = source ? toLocalDateInput(source) : null;
};

const confirmDate = () => {
    if (!draftDate.value) {
        form.value.scheduled_at = null;
        return;
    }

    const selected = new Date(draftDate.value);
    const now = new Date();
    if (Number.isNaN(selected.getTime())) {
        showDateEditor.value = false;
        return;
    }

    const iso = selected.toISOString();
    if (selected <= now) {
        form.value.published_at = iso;
        form.value.scheduled_at = null;
        if (form.value.status !== 'published') {
            form.value.status = 'published';
        }
    } else {
        form.value.scheduled_at = iso;
        form.value.published_at = null;
        if (form.value.status === 'published') {
            form.value.status = 'draft';
        }
    }

    showDateEditor.value = false;
};

const cancelDate = () => {
    showDateEditor.value = false;
    draftDate.value = form.value.scheduled_at
        ? toLocalDateInput(form.value.scheduled_at)
        : form.value.published_at
            ? toLocalDateInput(form.value.published_at)
            : null;
};

const formatDate = (value: string | null | undefined) => {
    if (!value) {
        return '';
    }
    try {
        return new Date(value).toLocaleString();
    } catch (error) {
        return value;
    }
};

watch(
    () => form.value.status,
    (value) => {
        if (!showStatusEditor.value) {
            draftStatus.value = value;
        }
    }
);

watch(
    () => form.value.scheduled_at,
    (value) => {
        if (!showDateEditor.value) {
            draftDate.value = value
                ? toLocalDateInput(value)
                : form.value.published_at
                    ? toLocalDateInput(form.value.published_at)
                    : null;
        }
    }
);

watch(
    () => form.value.published_at,
    (value) => {
        if (!showDateEditor.value && !form.value.scheduled_at) {
            draftDate.value = value ? toLocalDateInput(value) : null;
        }
    }
);
</script>

<style scoped>
.publish-panel {
    display: grid;
    gap: 1rem;
}

.publish-panel__group {
    display: grid;
    gap: 0.5rem;
}

.publish-panel__row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}

.publish-panel__pair {
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.publish-panel__input {
    width: 100%;
    padding: 0.55rem 0.75rem;
    border-radius: 0.65rem;
    border: 1px solid #d1d5db;
    background: #ffffff;
}

.publish-panel__meta {
    display: grid;
    gap: 0.35rem;
    font-size: 0.85rem;
    color: #475569;
}

.publish-panel__meta-item {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
}

.publish-panel__meta-label {
    font-weight: 600;
}

.publish-panel__meta-value {
    margin-left: 0.25rem;
}

.publish-panel__link {
    border: none;
    background: none;
    color: #6366f1;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    padding: 0;
}

.publish-panel__link:hover {
    text-decoration: underline;
}

.publish-panel__editor {
    display: grid;
    gap: 0.5rem;
    padding-top: 0.25rem;
}

.publish-panel__editor-actions {
    display: flex;
    gap: 0.5rem;
}

.publish-panel__actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.publish-panel__button {
    padding: 0.55rem 0.75rem;
    border-radius: 0.65rem;
    border: 1px solid #d1d5db;
    background: #f1f5f9;
    cursor: pointer;
    font-weight: 600;
}

.publish-panel__button--primary {
    background: #4f46e5;
    border-color: #4f46e5;
    color: #ffffff;
}
</style>

