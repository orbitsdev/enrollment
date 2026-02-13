<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { toast } from 'vue-sonner';
import type { FlashMessages } from '@/types';

const page = usePage();

const flash = computed<FlashMessages>(
    () => (page.props.flash as FlashMessages) ?? {},
);

function clearFlash(key: keyof FlashMessages) {
    if (page.props.flash && typeof page.props.flash === 'object') {
        (page.props.flash as Record<string, unknown>)[key] = undefined;
    }
}

watch(
    () => flash.value.success,
    (message) => {
        if (message) {
            toast.success(message);
            clearFlash('success');
        }
    },
    { immediate: true },
);

watch(
    () => flash.value.error,
    (message) => {
        if (message) {
            toast.error(message);
            clearFlash('error');
        }
    },
    { immediate: true },
);

watch(
    () => flash.value.warning,
    (message) => {
        if (message) {
            toast.warning(message);
            clearFlash('warning');
        }
    },
    { immediate: true },
);

watch(
    () => flash.value.info,
    (message) => {
        if (message) {
            toast.info(message);
            clearFlash('info');
        }
    },
    { immediate: true },
);
</script>

<template>
    <!-- This component has no visual template; it triggers toasts via watchers -->
</template>
