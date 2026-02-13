<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';
import { toast } from 'vue-sonner';
import type { FlashMessages } from '@/types';

function showFlash(flash: FlashMessages | undefined | null) {
    if (!flash) return;
    if (flash.success) toast.success(flash.success);
    if (flash.error) toast.error(flash.error);
    if (flash.warning) toast.warning(flash.warning);
    if (flash.info) toast.info(flash.info);
}

// Show flash on initial page load (e.g., after full-page redirect)
const page = usePage();
showFlash(page.props.flash as FlashMessages);

// Listen for all subsequent Inertia visits
let removeListener: (() => void) | undefined;

onMounted(() => {
    removeListener = router.on('success', (event) => {
        showFlash((event.detail.page.props.flash ?? {}) as FlashMessages);
    });
});

onUnmounted(() => {
    removeListener?.();
});
</script>

<template>
    <!-- This component has no visual template; it triggers toasts via router events -->
</template>
