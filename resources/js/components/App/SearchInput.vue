<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Loader2, Search } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Input } from '@/components/ui/input';

const props = withDefaults(
    defineProps<{
        modelValue?: string;
        placeholder?: string;
        only?: string[];
        extraData?: Record<string, unknown>;
    }>(),
    {
        modelValue: '',
        placeholder: 'Search...',
        only: () => [],
        extraData: () => ({}),
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const search = ref(props.modelValue);
const loading = ref(false);
let debounceTimer: ReturnType<typeof setTimeout> | null = null;
let abortController: AbortController | null = null;

watch(
    () => props.modelValue,
    (value) => {
        search.value = value;
    },
);

function onInput(event: Event) {
    const value = (event.target as HTMLInputElement).value;
    search.value = value;
    emit('update:modelValue', value);

    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }

    // Cancel any in-flight request
    if (abortController) {
        abortController.abort();
    }

    debounceTimer = setTimeout(() => {
        abortController = new AbortController();
        loading.value = true;

        router.reload({
            data: { ...props.extraData, search: value || undefined },
            only: props.only.length > 0 ? props.only : undefined,
            preserveState: true,
            preserveScroll: true,
            showProgress: false,
            onCancelToken: (token) => {
                abortController!.signal.addEventListener('abort', () => token.cancel());
            },
            onFinish: () => {
                abortController = null;
                loading.value = false;
            },
        });
    }, 300);
}
</script>

<template>
    <div class="relative w-full max-w-sm">
        <Loader2
            v-if="loading"
            class="absolute left-3 top-1/2 size-4 -translate-y-1/2 animate-spin text-muted-foreground"
        />
        <Search
            v-else
            class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
        />
        <Input
            v-model="search"
            :placeholder="placeholder"
            class="pl-9"
            @input="onInput"
        />
    </div>
</template>
