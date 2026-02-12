<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast } from 'vue-sonner';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogScrollContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Track } from '@/types';

const props = defineProps<{
    open: boolean;
    track?: Track | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const form = useForm({
    name: '',
    code: '',
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            if (props.track) {
                form.name = props.track.name;
                form.code = props.track.code;
            } else {
                form.reset();
            }
            form.clearErrors();
        }
    },
);

function submit() {
    if (props.track) {
        form.put(`/curriculum/tracks/${props.track.id}`, {
            preserveScroll: true,
            onSuccess: (page) => {
                page.props.flash = {};
                toast.success('Track updated successfully.');
                emit('update:open', false);
            },
        });
    } else {
        form.post('/curriculum/tracks', {
            preserveScroll: true,
            onSuccess: (page) => {
                page.props.flash = {};
                toast.success('Track created successfully.');
                emit('update:open', false);
            },
        });
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogScrollContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>{{ track ? 'Edit Track' : 'Add Track' }}</DialogTitle>
                <DialogDescription>
                    {{ track ? 'Update track information.' : 'Create a new academic track.' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-2">
                    <Label for="dlg_track_name">Name</Label>
                    <Input
                        id="dlg_track_name"
                        v-model="form.name"
                        type="text"
                        placeholder="e.g. Academic Track"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="space-y-2">
                    <Label for="dlg_track_code">Code</Label>
                    <Input
                        id="dlg_track_code"
                        v-model="form.code"
                        type="text"
                        placeholder="e.g. ACAD"
                    />
                    <InputError :message="form.errors.code" />
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="emit('update:open', false)"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ track ? 'Update Track' : 'Create Track' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogScrollContent>
    </Dialog>
</template>
