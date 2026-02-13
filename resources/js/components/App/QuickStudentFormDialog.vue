<script setup lang="ts">
import { Loader2, Plus } from 'lucide-vue-next';
import { ref, watch } from 'vue';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { Student } from '@/types';

const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'created': [student: Student];
}>();

const form = ref({
    lrn: '',
    last_name: '',
    first_name: '',
    middle_name: '',
    gender: '',
    birthdate: '',
});

const errors = ref<Record<string, string>>({});
const processing = ref(false);

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            form.value = {
                lrn: '',
                last_name: '',
                first_name: '',
                middle_name: '',
                gender: '',
                birthdate: '',
            };
            errors.value = {};
        }
    },
);

async function submit() {
    processing.value = true;
    errors.value = {};

    try {
        const response = await fetch('/api/students/quick-create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(
                    document.cookie
                        .split('; ')
                        .find((row) => row.startsWith('XSRF-TOKEN='))
                        ?.split('=')[1] ?? '',
                ),
            },
            body: JSON.stringify(form.value),
        });

        const data = await response.json();

        if (!response.ok) {
            if (response.status === 422 && data.errors) {
                errors.value = Object.fromEntries(
                    Object.entries(data.errors).map(([key, msgs]) => [
                        key,
                        Array.isArray(msgs) ? msgs[0] : msgs,
                    ]),
                );
            }
            return;
        }

        emit('created', data.student);
        emit('update:open', false);
    } finally {
        processing.value = false;
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogScrollContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>Quick Student Registration</DialogTitle>
                <DialogDescription>
                    Create a new student record for enrollment.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="qs_lrn">LRN</Label>
                        <Input
                            id="qs_lrn"
                            v-model="form.lrn"
                            maxlength="12"
                            placeholder="12-digit LRN"
                        />
                        <InputError :message="errors.lrn" />
                    </div>
                    <div class="space-y-2">
                        <Label for="qs_gender">Gender</Label>
                        <Select v-model="form.gender">
                            <SelectTrigger id="qs_gender">
                                <SelectValue placeholder="Select" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="male">Male</SelectItem>
                                <SelectItem value="female">Female</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.gender" />
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="qs_last_name">Last Name</Label>
                        <Input
                            id="qs_last_name"
                            v-model="form.last_name"
                            placeholder="Last name"
                        />
                        <InputError :message="errors.last_name" />
                    </div>
                    <div class="space-y-2">
                        <Label for="qs_first_name">First Name</Label>
                        <Input
                            id="qs_first_name"
                            v-model="form.first_name"
                            placeholder="First name"
                        />
                        <InputError :message="errors.first_name" />
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="qs_middle_name">Middle Name</Label>
                        <Input
                            id="qs_middle_name"
                            v-model="form.middle_name"
                            placeholder="Middle name (optional)"
                        />
                        <InputError :message="errors.middle_name" />
                    </div>
                    <div class="space-y-2">
                        <Label for="qs_birthdate">Birthdate</Label>
                        <Input
                            id="qs_birthdate"
                            v-model="form.birthdate"
                            type="date"
                        />
                        <InputError :message="errors.birthdate" />
                    </div>
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="emit('update:open', false)"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="processing">
                        <Loader2 v-if="processing" class="size-4 animate-spin" />
                        <Plus v-else class="size-4" />
                        Create & Select
                    </Button>
                </DialogFooter>
            </form>
        </DialogScrollContent>
    </Dialog>
</template>
