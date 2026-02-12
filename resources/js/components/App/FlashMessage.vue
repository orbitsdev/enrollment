<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { toast } from 'vue-sonner';
import type { FlashMessages } from '@/types';

const page = usePage();

const flash = computed<FlashMessages>(
    () => (page.props.flash as FlashMessages) ?? {},
);

watch(
    () => flash.value.success,
    (message) => {
        if (message) {
            toast.success(message);
        }
    },
);

watch(
    () => flash.value.error,
    (message) => {
        if (message) {
            toast.error(message);
        }
    },
);

watch(
    () => flash.value.warning,
    (message) => {
        if (message) {
            toast.warning(message);
        }
    },
);

watch(
    () => flash.value.info,
    (message) => {
        if (message) {
            toast.info(message);
        }
    },
);
</script>

<template>
    <!-- This component has no visual template; it triggers toasts via watchers -->
</template>
