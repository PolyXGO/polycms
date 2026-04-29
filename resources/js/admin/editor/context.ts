import type { InjectionKey, Ref } from 'vue';

export interface EditorContext<TForm = any> {
    type: 'post' | 'product' | 'page' | string;
    form: Ref<TForm>;
    loading: Ref<boolean>;
    helpers: Record<string, any>;
    state: Record<string, any>;
}

export const EditorContextKey: InjectionKey<EditorContext> = Symbol('EditorContext');

export function createEditorContext<TForm>(context: EditorContext<TForm>): EditorContext<TForm> {
    return context;
}

