<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Save } from 'lucide-vue-next';
import PageHeader from '@/components/App/PageHeader.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    settings: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'School Settings' },
];

const form = useForm({
    school_name: props.settings.school_name ?? '',
    school_id: props.settings.school_id ?? '',
    school_address: props.settings.school_address ?? '',
    passing_grade: props.settings.passing_grade ?? '75',
    midterm_weight: props.settings.midterm_weight ?? '50',
    finals_weight: props.settings.finals_weight ?? '50',
    default_capacity: props.settings.default_capacity ?? '40',
});

function submit() {
    form.put('/school-settings', {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="School Settings" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <PageHeader
                title="School Settings"
                description="Manage school information, grading configuration, and enrollment defaults."
            />

            <form
                class="mx-auto w-full max-w-3xl space-y-6"
                @submit.prevent="submit"
            >
                <!-- School Info Card -->
                <Card>
                    <CardHeader>
                        <CardTitle>School Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="school_name">School Name</Label>
                            <Input
                                id="school_name"
                                v-model="form.school_name"
                                type="text"
                                placeholder="Enter school name"
                            />
                            <InputError :message="form.errors.school_name" />
                        </div>

                        <div class="space-y-2">
                            <Label for="school_id">School ID</Label>
                            <Input
                                id="school_id"
                                v-model="form.school_id"
                                type="text"
                                placeholder="Enter school ID"
                            />
                            <InputError :message="form.errors.school_id" />
                        </div>

                        <div class="space-y-2">
                            <Label for="school_address">School Address</Label>
                            <Input
                                id="school_address"
                                v-model="form.school_address"
                                type="text"
                                placeholder="Enter school address"
                            />
                            <InputError :message="form.errors.school_address" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Grading Config Card -->
                <Card>
                    <CardHeader>
                        <CardTitle>Grading Configuration</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="passing_grade">Passing Grade</Label>
                            <Input
                                id="passing_grade"
                                v-model="form.passing_grade"
                                type="number"
                                min="0"
                                max="100"
                                placeholder="e.g. 75"
                            />
                            <InputError :message="form.errors.passing_grade" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="midterm_weight">Midterm Weight (%)</Label>
                                <Input
                                    id="midterm_weight"
                                    v-model="form.midterm_weight"
                                    type="number"
                                    min="0"
                                    max="100"
                                    placeholder="e.g. 50"
                                />
                                <InputError :message="form.errors.midterm_weight" />
                            </div>

                            <div class="space-y-2">
                                <Label for="finals_weight">Finals Weight (%)</Label>
                                <Input
                                    id="finals_weight"
                                    v-model="form.finals_weight"
                                    type="number"
                                    min="0"
                                    max="100"
                                    placeholder="e.g. 50"
                                />
                                <InputError :message="form.errors.finals_weight" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Enrollment Config Card -->
                <Card>
                    <CardHeader>
                        <CardTitle>Enrollment Configuration</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="default_capacity">Default Section Capacity</Label>
                            <Input
                                id="default_capacity"
                                v-model="form.default_capacity"
                                type="number"
                                min="1"
                                placeholder="e.g. 40"
                            />
                            <InputError :message="form.errors.default_capacity" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Submit -->
                <div class="flex justify-end">
                    <Button type="submit" :disabled="form.processing">
                        <Save class="mr-2 size-4" />
                        Save Settings
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
