<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

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
    <Dialog :open="open" @update:open="(val: boolean) => !val && emit('cancel')">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription>{{ description }}</DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button
                    variant="outline"
                    :disabled="processing"
                    @click="emit('cancel')"
                >
                    {{ cancelText }}
                </Button>
                <Button
                    :variant="variant"
                    :disabled="processing"
                    @click="emit('confirm')"
                >
                    {{ confirmText }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
