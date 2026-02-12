<script setup lang="ts">
import { computed } from 'vue';
import { Progress } from '@/components/ui/progress';

const props = defineProps<{
    current: number;
    max: number;
}>();

const percentage = computed(() => {
    if (props.max <= 0) return 0;
    return Math.round((props.current / props.max) * 100);
});

const colorClass = computed(() => {
    if (percentage.value >= 90) return 'text-red-600 dark:text-red-400';
    if (percentage.value >= 70) return 'text-yellow-600 dark:text-yellow-400';
    return 'text-green-600 dark:text-green-400';
});

const progressClass = computed(() => {
    if (percentage.value >= 90) return '[&>div]:bg-red-500';
    if (percentage.value >= 70) return '[&>div]:bg-yellow-500';
    return '[&>div]:bg-green-500';
});
</script>

<template>
    <div class="space-y-1">
        <div class="flex items-center justify-between text-sm">
            <span :class="colorClass" class="font-medium">
                {{ current }}/{{ max }}
            </span>
            <span :class="colorClass" class="text-xs">
                {{ percentage }}%
            </span>
        </div>
        <Progress
            :model-value="percentage"
            :class="progressClass"
            class="h-2"
        />
    </div>
</template>
