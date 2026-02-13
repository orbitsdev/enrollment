<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

defineProps<{
    title?: string;
    description?: string;
}>();

const LAKE_SEBU_IMAGE = '/images/lake-sebu.jpg';
</script>

<template>
    <div class="relative grid min-h-svh lg:grid-cols-2">
        <!-- Left side: Lake Sebu image panel (hidden on mobile) -->
        <div class="relative hidden lg:flex lg:flex-col">
            <img
                :src="LAKE_SEBU_IMAGE"
                alt="Lake Sebu landscape"
                class="absolute inset-0 h-full w-full object-cover"
            />
            <div class="absolute inset-0 bg-black/50" />
            <div class="relative z-10 flex h-full flex-col justify-between p-10 text-white">
                <Link
                    href="/"
                    class="flex items-center gap-2 text-lg font-medium"
                >
                    <AppLogoIcon class="size-8 text-white" />
                </Link>
                <div class="space-y-2">
                    <h2 class="text-2xl font-bold tracking-tight">
                        Lake Sebu National High School
                    </h2>
                    <p class="text-base text-white/80">
                        Senior High School Enrollment System
                    </p>
                </div>
            </div>
        </div>

        <!-- Right side: Login form -->
        <div class="flex flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
            <div class="w-full max-w-sm">
                <div class="flex flex-col gap-8">
                    <div class="flex flex-col items-center gap-4">
                        <Link
                            href="/"
                            class="flex flex-col items-center gap-2 font-medium"
                        >
                            <div class="mb-1 flex h-9 w-9 items-center justify-center rounded-md">
                                <AppLogoIcon
                                    class="size-9 text-[var(--foreground)] dark:text-white"
                                />
                            </div>
                            <span class="sr-only">{{ title }}</span>
                        </Link>
                        <div class="space-y-2 text-center">
                            <h1 class="text-xl font-medium">{{ title }}</h1>
                            <p class="text-center text-sm text-muted-foreground">
                                {{ description }}
                            </p>
                        </div>
                    </div>
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
