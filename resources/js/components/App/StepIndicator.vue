<script setup lang="ts">
import { Check } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    steps: Array<{ label: string }>;
    currentStep: number;
}>();

function stepStatus(index: number): 'completed' | 'active' | 'pending' {
    if (index < props.currentStep) return 'completed';
    if (index === props.currentStep) return 'active';
    return 'pending';
}
</script>

<template>
    <nav aria-label="Progress">
        <ol class="flex items-center">
            <li
                v-for="(step, index) in steps"
                :key="index"
                class="flex items-center"
                :class="{ 'flex-1': index < steps.length - 1 }"
            >
                <div class="flex flex-col items-center gap-1">
                    <!-- Circle -->
                    <div
                        class="flex size-8 items-center justify-center rounded-full border-2 text-sm font-medium transition-colors"
                        :class="{
                            'border-primary bg-primary text-primary-foreground': stepStatus(index) === 'completed',
                            'border-primary bg-primary/10 text-primary': stepStatus(index) === 'active',
                            'border-muted-foreground/30 text-muted-foreground': stepStatus(index) === 'pending',
                        }"
                    >
                        <Check
                            v-if="stepStatus(index) === 'completed'"
                            class="size-4"
                        />
                        <span v-else>{{ index + 1 }}</span>
                    </div>
                    <!-- Label -->
                    <span
                        class="text-xs font-medium whitespace-nowrap"
                        :class="{
                            'text-primary': stepStatus(index) === 'active' || stepStatus(index) === 'completed',
                            'text-muted-foreground': stepStatus(index) === 'pending',
                        }"
                    >
                        {{ step.label }}
                    </span>
                </div>
                <!-- Connecting line -->
                <div
                    v-if="index < steps.length - 1"
                    class="mx-2 mt-[-1rem] h-0.5 flex-1"
                    :class="{
                        'bg-primary': stepStatus(index) === 'completed',
                        'bg-muted-foreground/30': stepStatus(index) !== 'completed',
                    }"
                />
            </li>
        </ol>
    </nav>
</template>
