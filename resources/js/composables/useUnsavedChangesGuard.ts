import { router } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';

/**
 * Warns the user when navigating away from a page with unsaved form changes.
 * Works with both Inertia navigation and browser navigation (back/refresh).
 */
export function useUnsavedChangesGuard(form: { isDirty: boolean }) {
    const removeListener = router.on('before', (event) => {
        if (form.isDirty && !confirm('You have unsaved changes. Are you sure you want to leave?')) {
            event.preventDefault();
        }
    });

    function onBeforeUnload(event: BeforeUnloadEvent) {
        if (form.isDirty) {
            event.preventDefault();
        }
    }

    onMounted(() => {
        window.addEventListener('beforeunload', onBeforeUnload);
    });

    onUnmounted(() => {
        removeListener();
        window.removeEventListener('beforeunload', onBeforeUnload);
    });
}
