<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { type BreadcrumbItem, type Section, type Subject, type Student } from '@/types';

interface GradeRow {
    subject: Subject;
    midterm: number | null;
    finals: number | null;
    final_grade: number | null;
    remarks: string | null;
}

interface StudentGrade {
    student: Student;
    grades: GradeRow[];
}

const props = defineProps<{
    sections: Section[];
    subjects: Subject[];
    grades: StudentGrade[];
    filters: { section_id?: string; subject_id?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Reports', href: '/reports' },
    { title: 'Grade Summary', href: '/reports/grade-summary' },
];

const sectionId = ref(props.filters.section_id ?? '');
const subjectId = ref(props.filters.subject_id ?? '');

function applyFilter() {
    router.get('/reports/grade-summary', {
        section_id: sectionId.value || undefined,
        subject_id: subjectId.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function onSectionChange() {
    subjectId.value = '';
    applyFilter();
}
</script>

<template>
    <Head title="Grade Summary" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold tracking-tight">Grade Summary</h1>
                <p class="text-muted-foreground">View grades by section and subject.</p>
            </div>

            <!-- Filters -->
            <Card class="mb-6">
                <CardContent class="flex flex-wrap items-end gap-4 p-4">
                    <div class="min-w-[250px]">
                        <label class="mb-1 block text-sm font-medium">Section</label>
                        <select
                            v-model="sectionId"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                            @change="onSectionChange"
                        >
                            <option value="">Select section...</option>
                            <option
                                v-for="section in sections"
                                :key="section.id"
                                :value="section.id.toString()"
                            >
                                {{ section.name }} ({{ section.strand?.name }})
                            </option>
                        </select>
                    </div>
                    <div v-if="subjects.length > 0" class="min-w-[250px]">
                        <label class="mb-1 block text-sm font-medium">Subject (optional)</label>
                        <select
                            v-model="subjectId"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        >
                            <option value="">All Subjects</option>
                            <option
                                v-for="subject in subjects"
                                :key="subject.id"
                                :value="subject.id.toString()"
                            >
                                {{ subject.code }} - {{ subject.name }}
                            </option>
                        </select>
                    </div>
                    <Button @click="applyFilter" size="sm">Apply Filter</Button>
                </CardContent>
            </Card>

            <!-- Grades Table -->
            <Card v-if="grades.length > 0">
                <CardHeader>
                    <CardTitle class="text-base">Grade Report ({{ grades.length }} students)</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b text-left">
                                    <th class="pb-2 pr-4 font-medium">Student</th>
                                    <th class="pb-2 pr-4 font-medium">Subject</th>
                                    <th class="pb-2 pr-4 text-center font-medium">Midterm</th>
                                    <th class="pb-2 pr-4 text-center font-medium">Finals</th>
                                    <th class="pb-2 pr-4 text-center font-medium">Final Grade</th>
                                    <th class="pb-2 text-center font-medium">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-for="sg in grades" :key="sg.student.id">
                                    <tr
                                        v-for="(grade, gIndex) in sg.grades"
                                        :key="`${sg.student.id}-${gIndex}`"
                                        class="border-b last:border-0"
                                    >
                                        <td class="py-2 pr-4">
                                            <template v-if="gIndex === 0">{{ sg.student.full_name }}</template>
                                        </td>
                                        <td class="py-2 pr-4">{{ grade.subject?.name ?? '-' }}</td>
                                        <td class="py-2 pr-4 text-center">{{ grade.midterm ?? '-' }}</td>
                                        <td class="py-2 pr-4 text-center">{{ grade.finals ?? '-' }}</td>
                                        <td class="py-2 pr-4 text-center font-medium">{{ grade.final_grade ?? '-' }}</td>
                                        <td class="py-2 text-center">
                                            <Badge
                                                v-if="grade.remarks"
                                                :variant="grade.remarks === 'passed' ? 'default' : 'destructive'"
                                            >
                                                {{ grade.remarks }}
                                            </Badge>
                                            <span v-else class="text-muted-foreground">-</span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <div v-else-if="sectionId" class="py-12 text-center text-muted-foreground">
                No grade data found for this section.
            </div>

            <div v-else class="py-12 text-center text-muted-foreground">
                Select a section to view grade data.
            </div>
        </div>
    </AppLayout>
</template>
