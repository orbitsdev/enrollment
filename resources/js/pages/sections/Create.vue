<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import PageHeader from '@/components/App/PageHeader.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useUnsavedChangesGuard } from '@/composables/useUnsavedChangesGuard';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Semester, Strand, User } from '@/types';

defineProps<{
    strands: Strand[];
    semesters: Semester[];
    teachers: User[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Sections', href: '/sections' },
    { title: 'Add Section' },
];

const form = useForm({
    name: '',
    strand_id: '',
    semester_id: '',
    grade_level: '',
    max_capacity: 40,
    adviser_id: '',
});

useUnsavedChangesGuard(form);

function submit() {
    form.post('/sections', {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Add Section" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <PageHeader
                title="Add Section"
                description="Create a new class section."
            >
                <template #actions>
                    <Button variant="outline" as-child>
                        <Link href="/sections">Cancel</Link>
                    </Button>
                </template>
            </PageHeader>

            <form
                class="mx-auto w-full max-w-2xl"
                @submit.prevent="submit"
            >
                <Card>
                    <CardHeader>
                        <CardTitle>Section Details</CardTitle>
                        <CardDescription>
                            Configure the section name, strand, and schedule.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name">Section Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="e.g., STEM-11A"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="strand_id">Strand</Label>
                                <Select v-model="form.strand_id">
                                    <SelectTrigger id="strand_id">
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
                                <Label for="semester_id">Semester</Label>
                                <Select v-model="form.semester_id">
                                    <SelectTrigger id="semester_id">
                                        <SelectValue placeholder="Select semester" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="sem in semesters"
                                            :key="sem.id"
                                            :value="String(sem.id)"
                                        >
                                            {{ sem.label ?? `${sem.school_year?.name} - Sem ${sem.number}` }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.semester_id" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="grade_level">Grade Level</Label>
                                <Select v-model="form.grade_level">
                                    <SelectTrigger id="grade_level">
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
                                <Label for="max_capacity">Max Capacity</Label>
                                <Input
                                    id="max_capacity"
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
                            <Label for="adviser_id">Adviser</Label>
                            <Select v-model="form.adviser_id">
                                <SelectTrigger id="adviser_id">
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
                    </CardContent>
                </Card>

                <div class="mt-6 flex justify-end gap-4">
                    <Button variant="outline" type="button" as-child>
                        <Link href="/sections">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        Create Section
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
