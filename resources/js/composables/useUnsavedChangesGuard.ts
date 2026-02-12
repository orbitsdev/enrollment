import { router } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';

/**
 * Warns the user when navigating away from a page with unsaved form changes.
 * Works with both Inertia navigation and browser navigation (back/refresh).
 *
 * Only guards against link navigation (GET requests). Form submissions
 * (POST/PUT/PATCH/DELETE) are intentional and are not intercepted.
 */
export function useUnsavedChangesGuard(form: { isDirty: boolean }) {
    const removeListener = router.on('before', (event) => {
        const method = event.detail.visit.method;

        // Only guard against navigating away (GET), not form submissions
        if (method !== 'get') return;

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
