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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { Section, Semester, Strand, User } from '@/types';

const props = defineProps<{
    open: boolean;
    section?: Section | null;
    strands: Strand[];
    semesters: Semester[];
    teachers: User[];
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const form = useForm({
    name: '',
    strand_id: '',
    semester_id: '',
    grade_level: '',
    max_capacity: 40,
    adviser_id: '',
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            if (props.section) {
                form.name = props.section.name;
                form.strand_id = String(props.section.strand_id);
                form.semester_id = String(props.section.semester_id);
                form.grade_level = String(props.section.grade_level);
                form.max_capacity = props.section.max_capacity;
                form.adviser_id = props.section.adviser_id ? String(props.section.adviser_id) : '';
            } else {
                form.reset();
            }
            form.clearErrors();
        }
    },
);

function submit() {
    if (props.section) {
        form.put(`/sections/${props.section.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                emit('update:open', false);
            },
        });
    } else {
        form.post('/sections', {
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
                <DialogTitle>{{ section ? 'Edit Section' : 'Add Section' }}</DialogTitle>
                <DialogDescription>
                    {{ section ? 'Update section configuration.' : 'Create a new class section.' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-2">
                    <Label for="dlg_name">Section Name</Label>
                    <Input
                        id="dlg_name"
                        v-model="form.name"
                        type="text"
                        placeholder="e.g., STEM-11A"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="dlg_strand_id">Strand</Label>
                        <Select v-model="form.strand_id">
                            <SelectTrigger id="dlg_strand_id">
                                <SelectValue placeholder="Select strand" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="strand in strands"
                                    :key="strand.id"
                                    :value="String(strand.id)"
                                >
                                    {{ strand.code }} - {{ strand.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.strand_id" />
                    </div>

                    <div class="space-y-2">
                        <Label for="dlg_semester_id">Semester</Label>
                        <Select v-model="form.semester_id">
                            <SelectTrigger id="dlg_semester_id">
                                <SelectValue placeholder="Select semester" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="sem in semesters"
                                    :key="sem.id"
                                    :value="String(sem.id)"
                                >
                                    {{ sem.full_label ?? sem.label ?? `${sem.school_year?.name} - Sem ${sem.number}` }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.semester_id" />
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="dlg_grade_level">Grade Level</Label>
                        <Select v-model="form.grade_level">
                            <SelectTrigger id="dlg_grade_level">
                                <SelectValue placeholder="Select grade level" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="11">Grade 11</SelectItem>
                                <SelectItem value="12">Grade 12</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.grade_level" />
                    </div>

                    <div class="space-y-2">
                        <Label for="dlg_max_capacity">Max Capacity</Label>
                        <Input
                            id="dlg_max_capacity"
                            v-model.number="form.max_capacity"
                            type="number"
                            min="1"
                            max="100"
                            placeholder="40"
                        />
                        <InputError :message="form.errors.max_capacity" />
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="dlg_adviser_id">Adviser</Label>
                    <Select v-model="form.adviser_id">
                        <SelectTrigger id="dlg_adviser_id">
                            <SelectValue placeholder="Select adviser (optional)" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="teacher in teachers"
                                :key="teacher.id"
                                :value="String(teacher.id)"
                            >
                                {{ teacher.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.adviser_id" />
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
                        {{ section ? 'Update Section' : 'Create Section' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogScrollContent>
    </Dialog>
</template>
