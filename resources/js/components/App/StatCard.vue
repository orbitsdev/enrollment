<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card';
import type { Component } from 'vue';

interface Props {
    title: string;
    value: string | number;
    icon: Component;
    description?: string;
    trend?: string;
}

defineProps<Props>();
</script>

<template>
    <Card>
        <CardContent class="flex items-center gap-4 p-6">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                <component :is="icon" class="h-6 w-6" />
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-muted-foreground">{{ title }}</p>
                <p class="text-2xl font-bold tracking-tight">{{ value }}</p>
                <p v-if="description" class="text-xs text-muted-foreground">{{ description }}</p>
                <p v-if="trend" class="text-xs font-medium text-green-600">{{ trend }}</p>
            </div>
        </CardContent>
    </Card>
</template>
