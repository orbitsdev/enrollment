<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { type BreadcrumbItem, type Student } from '@/types';

interface GradeItem {
    subject_code: string;
    subject_name: string;
    midterm: number | null;
    finals: number | null;
    final_grade: number | null;
    remarks: string | null;
}

interface EnrollmentGrades {
    id: number;
    semester: string;
    section: string;
    strand: string;
    status: string;
    grades: GradeItem[];
}

const props = defineProps<{
    student: Student | null;
    enrollments: EnrollmentGrades[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'My Grades', href: '/my/grades' },
];
</script>

<template>
    <Head title="My Grades" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold tracking-tight">My Grades</h1>
                <p v-if="student" class="text-muted-foreground">
                    {{ student.full_name }} | LRN: {{ student.lrn }}
                </p>
            </div>

            <template v-if="enrollments.length > 0">
                <div class="space-y-6">
                    <Card v-for="enrollment in enrollments" :key="enrollment.id">
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <div>
                                    <CardTitle class="text-base">{{ enrollment.semester }}</CardTitle>
                                    <CardDescription>
                                        Section: {{ enrollment.section }} | Strand: {{ enrollment.strand }}
                                    </CardDescription>
                                </div>
                                <Badge :variant="enrollment.status === 'Enrolled' ? 'default' : 'secondary'">
                                    {{ enrollment.status }}
                                </Badge>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b text-left">
                                            <th class="pb-2 pr-4 font-medium">Subject</th>
                                            <th class="pb-2 pr-4 text-center font-medium">Midterm</th>
                                            <th class="pb-2 pr-4 text-center font-medium">Finals</th>
                                            <th class="pb-2 pr-4 text-center font-medium">Final Grade</th>
                                            <th class="pb-2 text-center font-medium">Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="grade in enrollment.grades"
                                            :key="grade.subject_code"
                                            class="border-b last:border-0"
                                        >
                                            <td class="py-2 pr-4">
                                                <span class="font-mono text-xs text-muted-foreground">{{ grade.subject_code }}</span>
                                                {{ grade.subject_name }}
                                            </td>
                                            <td class="py-2 pr-4 text-center">{{ grade.midterm ?? '-' }}</td>
                                            <td class="py-2 pr-4 text-center">{{ grade.finals ?? '-' }}</td>
                                            <td class="py-2 pr-4 text-center font-medium">{{ grade.final_grade ?? '-' }}</td>
                                            <td class="py-2 text-center">
                                                <Badge
                                                    v-if="grade.remarks"
                                                    :variant="grade.remarks === 'Passed' ? 'default' : 'destructive'"
                                                >
                                                    {{ grade.remarks }}
                                                </Badge>
                                                <span v-else class="text-muted-foreground">-</span>
                                            </td>
                                        </tr>
                                        <tr v-if="enrollment.grades.length === 0">
                                            <td colspan="5" class="py-4 text-center text-muted-foreground">
                                                No grades recorded yet.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </template>

            <Card v-else>
                <CardContent class="py-12 text-center text-muted-foreground">
                    <template v-if="!student">
                        No student profile found linked to your account.
                    </template>
                    <template v-else>
                        No grade records found.
                    </template>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
