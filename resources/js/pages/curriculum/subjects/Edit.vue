<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import PageHeader from '@/components/App/PageHeader.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useUnsavedChangesGuard } from '@/composables/useUnsavedChangesGuard';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Strand, Subject } from '@/types';

const props = defineProps<{
    subject: Subject & { strands: Strand[] };
    allStrands: Strand[];
    subjects: Subject[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Curriculum' },
    { title: 'Subjects', href: '/curriculum/subjects' },
    { title: `Edit ${props.subject.name}` },
];

// Build initial strand mappings from the subject's existing strands with pivot data
function buildInitialStrands(): Array<{ strand_id: number; grade_level: number; semester: number }> {
    const mappings: Array<{ strand_id: number; grade_level: number; semester: number }> = [];

    if (props.subject.strands) {
        for (const strand of props.subject.strands) {
            if (strand.pivot) {
                mappings.push({
                    strand_id: strand.id,
                    grade_level: strand.pivot.grade_level,
                    semester: strand.pivot.semester,
                });
            }
        }
    }

    return mappings;
}

const form = useForm({
    code: props.subject.code,
    name: props.subject.name,
    type: props.subject.type,
    hours: props.subject.hours,
    prerequisite_id: props.subject.prerequisite_id,
    strands: buildInitialStrands(),
});

// Semester column definitions for the mapping matrix
const semesterColumns = [
    { grade_level: 11, semester: 1, label: 'Grade 11 - Sem 1' },
    { grade_level: 11, semester: 2, label: 'Grade 11 - Sem 2' },
    { grade_level: 12, semester: 1, label: 'Grade 12 - Sem 1' },
    { grade_level: 12, semester: 2, label: 'Grade 12 - Sem 2' },
];

function isStrandMapped(strandId: number, gradeLevel: number, semester: number): boolean {
    return form.strands.some(
        (s) => s.strand_id === strandId && s.grade_level === gradeLevel && s.semester === semester,
    );
}

function toggleStrandMapping(strandId: number, gradeLevel: number, semester: number) {
    const index = form.strands.findIndex(
        (s) => s.strand_id === strandId && s.grade_level === gradeLevel && s.semester === semester,
    );

    if (index >= 0) {
        form.strands.splice(index, 1);
    } else {
        form.strands.push({ strand_id: strandId, grade_level: gradeLevel, semester });
    }
}

useUnsavedChangesGuard(form);

function submit() {
    form.put(`/curriculum/subjects/${props.subject.id}`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="`Edit ${subject.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <PageHeader
                :title="`Edit ${subject.name}`"
                description="Update subject details and strand mappings."
            >
                <template #actions>
                    <Button variant="outline" as-child>
                        <Link href="/curriculum/subjects">Back to Subjects</Link>
                    </Button>
                </template>
            </PageHeader>

            <form
                class="mx-auto w-full max-w-4xl space-y-6"
                @submit.prevent="submit"
            >
                <!-- Subject Details Card -->
                <Card>
                    <CardHeader>
                        <CardTitle>Subject Details</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="code">Subject Code</Label>
                                <Input
                                    id="code"
                                    v-model="form.code"
                                    type="text"
                                    placeholder="e.g. CORE-101"
                                />
                                <InputError :message="form.errors.code" />
                            </div>

                            <div class="space-y-2">
                                <Label for="name">Subject Name</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    placeholder="e.g. Oral Communication"
                                />
                                <InputError :message="form.errors.name" />
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <Label for="type">Type</Label>
                                <Select v-model="form.type">
                                    <SelectTrigger id="type">
                                        <SelectValue placeholder="Select type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="core">Core</SelectItem>
                                        <SelectItem value="specialized">Specialized</SelectItem>
                                        <SelectItem value="applied">Applied</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.type" />
                            </div>

                            <div class="space-y-2">
                                <Label for="hours">Hours</Label>
                                <Input
                                    id="hours"
                                    v-model="form.hours"
                                    type="number"
                                    min="1"
                                    placeholder="e.g. 80"
                                />
                                <InputError :message="form.errors.hours" />
                            </div>

                            <div class="space-y-2">
                                <Label for="prerequisite_id">Prerequisite</Label>
                                <Select
                                    :model-value="form.prerequisite_id !== null ? String(form.prerequisite_id) : 'none'"
                                    @update:model-value="(val: string) => form.prerequisite_id = val === 'none' ? null : Number(val)"
                                >
                                    <SelectTrigger id="prerequisite_id">
                                        <SelectValue placeholder="None" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="none">None</SelectItem>
                                        <SelectItem
                                            v-for="subj in subjects.filter(s => s.id !== subject.id)"
                                            :key="subj.id"
                                            :value="String(subj.id)"
                                        >
                                            {{ subj.code }} - {{ subj.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.prerequisite_id" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Strand Mapping Matrix Card -->
                <Card>
                    <CardHeader>
                        <CardTitle>Strand Mapping</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="mb-4 text-sm text-muted-foreground">
                            Select which strands and semesters this subject is offered in.
                        </p>

                        <InputError :message="form.errors.strands" class="mb-4" />

                        <div v-if="allStrands.length > 0" class="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead class="w-[250px]">Strand</TableHead>
                                        <TableHead
                                            v-for="col in semesterColumns"
                                            :key="`${col.grade_level}-${col.semester}`"
                                            class="text-center"
                                        >
                                            {{ col.label }}
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow
                                        v-for="strand in allStrands"
                                        :key="strand.id"
                                    >
                                        <TableCell class="font-medium">
                                            <div>
                                                <span>{{ strand.name }}</span>
                                                <span class="ml-2 text-xs text-muted-foreground">
                                                    ({{ strand.code }})
                                                </span>
                                            </div>
                                        </TableCell>
                                        <TableCell
                                            v-for="col in semesterColumns"
                                            :key="`${strand.id}-${col.grade_level}-${col.semester}`"
                                            class="text-center"
                                        >
                                            <div class="flex items-center justify-center">
                                                <Checkbox
                                                    :model-value="isStrandMapped(strand.id, col.grade_level, col.semester)"
                                                    @update:model-value="toggleStrandMapping(strand.id, col.grade_level, col.semester)"
                                                />
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <p
                            v-else
                            class="py-8 text-center text-sm text-muted-foreground"
                        >
                            No strands available. Create strands in the Tracks page first.
                        </p>
                    </CardContent>
                </Card>

                <!-- Form Actions -->
                <div class="flex justify-end gap-4">
                    <Button variant="outline" type="button" as-child>
                        <Link href="/curriculum/subjects">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        Update Subject
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
