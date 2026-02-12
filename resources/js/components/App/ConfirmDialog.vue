<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';

withDefaults(
    defineProps<{
        open: boolean;
        title?: string;
        description?: string;
        confirmText?: string;
        cancelText?: string;
        variant?: 'default' | 'destructive';
        processing?: boolean;
    }>(),
    {
        title: 'Are you sure?',
        description: 'This action cannot be undone.',
        confirmText: 'Confirm',
        cancelText: 'Cancel',
        variant: 'default',
        processing: false,
    },
);

const emit = defineEmits<{
    confirm: [];
    cancel: [];
}>();
</script>

<template>
    <AlertDialog :open="open" @update:open="(val: boolean) => !val && emit('cancel')">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{ title }}</AlertDialogTitle>
                <AlertDialogDescription>{{ description }}</AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel
                    as-child
                >
                    <Button
                        variant="outline"
                        :disabled="processing"
                        @click="emit('cancel')"
                    >
                        {{ cancelText }}
                    </Button>
                </AlertDialogCancel>
                <AlertDialogAction
                    as-child
                >
                    <Button
                        :variant="variant"
                        :disabled="processing"
                        @click="emit('confirm')"
                    >
                        {{ confirmText }}
                    </Button>
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
