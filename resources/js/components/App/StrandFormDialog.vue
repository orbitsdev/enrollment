<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
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
import type { Strand } from '@/types';

const props = defineProps<{
    open: boolean;
    strand?: Strand | null;
    trackId?: number;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const form = useForm({
    name: '',
    code: '',
    course: '',
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            if (props.strand) {
                form.name = props.strand.name;
                form.code = props.strand.code;
                form.course = props.strand.course ?? '';
            } else {
                form.reset();
            }
            form.clearErrors();
        }
    },
);

function submit() {
    if (props.strand) {
        form.put(`/curriculum/strands/${props.strand.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                emit('update:open', false);
            },
        });
    } else {
        form.transform((data) => ({ ...data, track_id: props.trackId })).post('/curriculum/strands', {
            preserveScroll: true,
            onSuccess: () => {
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
                <DialogTitle>{{ strand ? 'Edit Strand' : 'Add Strand' }}</DialogTitle>
                <DialogDescription>
                    {{ strand ? 'Update strand information.' : 'Create a new strand for this track.' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-2">
                    <Label for="dlg_strand_name">Strand Name</Label>
                    <Input
                        id="dlg_strand_name"
                        v-model="form.name"
                        type="text"
                        placeholder="e.g. Science, Technology, Engineering, and Mathematics"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="space-y-2">
                    <Label for="dlg_strand_code">Code</Label>
                    <Input
                        id="dlg_strand_code"
                        v-model="form.code"
                        type="text"
                        placeholder="e.g. STEM"
                    />
                    <InputError :message="form.errors.code" />
                </div>

                <div class="space-y-2">
                    <Label for="dlg_strand_course">TESDA Course</Label>
                    <Input
                        id="dlg_strand_course"
                        v-model="form.course"
                        type="text"
                        placeholder="e.g. Computer System Servicing (NC II)"
                    />
                    <p class="text-xs text-muted-foreground">For TVL strands only. Leave blank for Academic strands.</p>
                    <InputError :message="form.errors.course" />
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
                        {{ strand ? 'Update Strand' : 'Create Strand' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogScrollContent>
    </Dialog>
</template>
