<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem, type Section, type Enrollment } from '@/types';
import { FileText, Download, FileSpreadsheet } from 'lucide-vue-next';

const props = defineProps<{
    sections: Section[];
    enrollments: Enrollment[];
    filters: { section_id?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Reports', href: '/reports' },
    { title: 'School Forms', href: '/reports/school-forms' },
];

const sectionId = ref(props.filters.section_id ?? '');

function applyFilter() {
    router.get('/reports/school-forms', {
        section_id: sectionId.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function downloadSF1() {
    if (sectionId.value) {
        window.location.href = `/reports/generate/sf1/${sectionId.value}`;
    }
}

function downloadSF5() {
    if (sectionId.value) {
        window.location.href = `/reports/generate/sf5/${sectionId.value}`;
    }
}

function downloadSF9(enrollmentId: number) {
    window.location.href = `/reports/generate/sf9/${enrollmentId}`;
}

function downloadSF10(studentId: number) {
    window.location.href = `/reports/generate/sf10/${studentId}`;
}
</script>

<template>
    <Head title="School Forms" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold tracking-tight">School Forms</h1>
                <p class="text-muted-foreground">Generate official DepEd school forms (SF1, SF5, SF9, SF10).</p>
            </div>

            <!-- Info Cards -->
            <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-3">
                            <FileSpreadsheet class="h-5 w-5 text-orange-600" />
                            <CardTitle class="text-base">SF1 - School Register</CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <CardDescription>
                            School Register listing all enrolled learners per section. Exported as Excel per section.
                        </CardDescription>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-3">
                            <FileSpreadsheet class="h-5 w-5 text-purple-600" />
                            <CardTitle class="text-base">SF5 - Promotion Report</CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <CardDescription>
                            Report on Promotion and Level of Proficiency per section. Shows grades, averages, and promotion status.
                        </CardDescription>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-3">
                            <FileText class="h-5 w-5 text-blue-600" />
                            <CardTitle class="text-base">SF9 - Report Card</CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <CardDescription>
                            Learner Progress Report Card. Generated per enrollment (per student, per semester).
                        </CardDescription>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-3">
                            <FileText class="h-5 w-5 text-green-600" />
                            <CardTitle class="text-base">SF10 - Permanent Record</CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <CardDescription>
                            Learner's Permanent Academic Record. Contains all semesters and grades for a student.
                        </CardDescription>
                    </CardContent>
                </Card>
            </div>

            <!-- Section Selector -->
            <Card class="mb-6">
                <CardContent class="flex flex-wrap items-end gap-4 p-4">
                    <div class="min-w-[300px]">
                        <label class="mb-1 block text-sm font-medium">Select Section</label>
                        <select
                            v-model="sectionId"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
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
                    <Button @click="applyFilter" size="sm">Load Students</Button>
                </CardContent>
            </Card>

            <!-- Section-Level Forms (SF1 & SF5) -->
            <Card v-if="sectionId" class="mb-6">
                <CardHeader>
                    <CardTitle class="text-base">Section-Level Forms</CardTitle>
                    <CardDescription>These forms are generated for the entire section.</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-wrap gap-3">
                        <Button variant="outline" @click="downloadSF1">
                            <Download class="mr-2 h-4 w-4" />
                            Download SF1 - School Register
                        </Button>
                        <Button variant="outline" @click="downloadSF5">
                            <Download class="mr-2 h-4 w-4" />
                            Download SF5 - Promotion Report
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Student List with Form Generation (SF9 & SF10) -->
            <Card v-if="enrollments.length > 0">
                <CardHeader>
                    <CardTitle class="text-base">
                        Per-Student Forms ({{ enrollments.length }} students)
                    </CardTitle>
                    <CardDescription>SF9 and SF10 are generated per individual student.</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b text-left">
                                    <th class="pb-2 pr-4 font-medium">#</th>
                                    <th class="pb-2 pr-4 font-medium">LRN</th>
                                    <th class="pb-2 pr-4 font-medium">Student Name</th>
                                    <th class="pb-2 pr-4 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(enrollment, index) in enrollments"
                                    :key="enrollment.id"
                                    class="border-b last:border-0"
                                >
                                    <td class="py-2 pr-4">{{ index + 1 }}</td>
                                    <td class="py-2 pr-4 font-mono text-xs">{{ enrollment.student?.lrn ?? '-' }}</td>
                                    <td class="py-2 pr-4">{{ enrollment.student?.full_name ?? '-' }}</td>
                                    <td class="py-2 pr-4">
                                        <div class="flex gap-2">
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                @click="downloadSF9(enrollment.id)"
                                            >
                                                <Download class="mr-1 h-3 w-3" />
                                                SF9
                                            </Button>
                                            <Button
                                                v-if="enrollment.student"
                                                size="sm"
                                                variant="outline"
                                                @click="downloadSF10(enrollment.student.id)"
                                            >
                                                <Download class="mr-1 h-3 w-3" />
                                                SF10
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <div v-else-if="sectionId" class="py-12 text-center text-muted-foreground">
                No enrolled students found in this section.
            </div>

            <div v-else class="py-12 text-center text-muted-foreground">
                Select a section to view students and generate school forms.
            </div>
        </div>
    </AppLayout>
</template>
