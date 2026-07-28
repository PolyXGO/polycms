import { onMounted, onUnmounted, type Ref } from 'vue';

/**
 * Global Ctrl+S (Cmd+S on Mac) hotkey composable.
 * 
 * Prevents browser default save-page behavior and triggers the provided
 * save callback. Optionally respects a loading/saving guard ref to prevent
 * double-submit.
 * 
 * @param saveFn - The save function to execute on Ctrl+S / Cmd+S
 * @param guard - Optional ref<boolean> that when true, blocks the save (e.g. loading/saving state)
 */
export function useGlobalSaveHotkey(
    saveFn: () => void | Promise<void>,
    guard?: Ref<boolean>
) {
    const handler = (e: KeyboardEvent) => {
        // Ctrl+S (Windows/Linux) or Cmd+S (Mac)
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            e.stopPropagation();

            // Skip if guard ref is true (e.g. already saving)
            if (guard?.value) return;

            saveFn();
        }
    };

    onMounted(() => {
        window.addEventListener('keydown', handler, { capture: true });
    });

    onUnmounted(() => {
        window.removeEventListener('keydown', handler, { capture: true });
    });

    return { handler };
}
