<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Input } from '@/components/ui/input';

const props = withDefaults(
    defineProps<{
        modelValue?: string;
        placeholder?: string;
        only?: string[];
    }>(),
    {
        modelValue: '',
        placeholder: 'Search...',
        only: () => [],
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const search = ref(props.modelValue);
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

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

    debounceTimer = setTimeout(() => {
        router.reload({
            data: { search: value || undefined },
            only: props.only.length > 0 ? props.only : undefined,
            preserveState: true,
            preserveScroll: true,
        });
    }, 300);
}
</script>

<template>
    <div class="relative w-full max-w-sm">
        <Search
            class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
        />
        <Input
            :value="search"
            :placeholder="placeholder"
            class="pl-9"
            @input="onInput"
        />
    </div>
</template>
