<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';
import { Users, BookOpen, Download } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Import Data', href: '/import' },
];

function downloadTemplate(type: string) {
    window.location.href = `/import/template/${type}`;
}
</script>

<template>
    <Head title="Import Data" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold tracking-tight">Import Data</h1>
                <p class="text-muted-foreground">Import students and grades from Excel files.</p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <!-- Student Import -->
                <Card class="flex flex-col">
                    <CardHeader>
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                                <Users class="h-5 w-5 text-blue-600" />
                            </div>
                            <div>
                                <CardTitle class="text-base">Import Students</CardTitle>
                                <CardDescription>Upload an Excel file with student data.</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="flex flex-1 flex-col justify-end gap-3">
                        <p class="text-sm text-muted-foreground">
                            Required columns: lrn, last_name, first_name, birthdate, gender.
                            Optional: middle_name, suffix, address, contact_number, guardian_name, guardian_contact.
                        </p>
                        <div class="flex gap-2">
                            <Link href="/import/students" class="flex-1">
                                <Button class="w-full">Start Import</Button>
                            </Link>
                            <Button variant="outline" @click="downloadTemplate('students')">
                                <Download class="mr-2 h-4 w-4" />
                                Template
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Grade Import -->
                <Card class="flex flex-col">
                    <CardHeader>
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                                <BookOpen class="h-5 w-5 text-green-600" />
                            </div>
                            <div>
                                <CardTitle class="text-base">Import Grades</CardTitle>
                                <CardDescription>Upload an Excel file with grade data.</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="flex flex-1 flex-col justify-end gap-3">
                        <p class="text-sm text-muted-foreground">
                            Required columns: lrn, subject_code.
                            Optional: midterm (50-100), finals (50-100).
                        </p>
                        <div class="flex gap-2">
                            <Link href="/import/grades" class="flex-1">
                                <Button class="w-full">Start Import</Button>
                            </Link>
                            <Button variant="outline" @click="downloadTemplate('grades')">
                                <Download class="mr-2 h-4 w-4" />
                                Template
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
