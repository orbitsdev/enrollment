<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem, type SchoolYear, type Semester } from '@/types';
import { Download } from 'lucide-vue-next';

interface ReportData {
    by_track: Record<string, number>;
    by_strand: Record<string, number>;
    by_grade_level: Record<string, number>;
    by_status: Record<string, number>;
    total: number;
}

const props = defineProps<{
    school_years: SchoolYear[];
    selected_semester: Semester | null;
    report_data: ReportData;
    filters: { semester_id?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Reports', href: '/reports' },
    { title: 'Enrollment Summary', href: '/reports/enrollment-summary' },
];

const selectedSemesterId = ref(props.filters.semester_id ?? props.selected_semester?.id?.toString() ?? '');

function applyFilter() {
    router.get('/reports/enrollment-summary', {
        semester_id: selectedSemesterId.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function exportReport() {
    window.location.href = `/reports/export/enrollment-summary?semester_id=${selectedSemesterId.value}`;
}
</script>

<template>
    <Head title="Enrollment Summary" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-6">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Enrollment Summary</h1>
                    <p class="text-muted-foreground">Overview of enrollment statistics for the selected semester.</p>
                </div>
                <Button @click="exportReport" variant="outline" :disabled="!report_data.total">
                    <Download class="mr-2 h-4 w-4" />
                    Export Excel
                </Button>
            </div>

            <!-- Filters -->
            <Card class="mb-6">
                <CardContent class="flex flex-wrap items-end gap-4 p-4">
                    <div class="min-w-[250px]">
                        <label class="mb-1 block text-sm font-medium">Semester</label>
                        <select
                            v-model="selectedSemesterId"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        >
                            <option value="">Select semester...</option>
                            <template v-for="sy in school_years" :key="sy.id">
                                <option
                                    v-for="sem in sy.semesters"
                                    :key="sem.id"
                                    :value="sem.id.toString()"
                                >
                                    SY {{ sy.name }} - {{ sem.number === 1 ? '1st' : '2nd' }} Semester
                                </option>
                            </template>
                        </select>
                    </div>
                    <Button @click="applyFilter" size="sm">Apply Filter</Button>
                </CardContent>
            </Card>

            <template v-if="report_data.total">
                <!-- Summary Cards -->
                <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardContent class="p-4 text-center">
                            <p class="text-3xl font-bold text-primary">{{ report_data.total }}</p>
                            <p class="text-sm text-muted-foreground">Total Enrolled</p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Detail Tables -->
                <div class="grid gap-4 lg:grid-cols-2">
                    <!-- By Track -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base">By Track</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b">
                                        <th class="pb-2 text-left font-medium">Track</th>
                                        <th class="pb-2 text-right font-medium">Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(count, track) in report_data.by_track" :key="track" class="border-b last:border-0">
                                        <td class="py-2">{{ track }}</td>
                                        <td class="py-2 text-right font-medium">{{ count }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </CardContent>
                    </Card>

                    <!-- By Strand -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base">By Strand</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b">
                                        <th class="pb-2 text-left font-medium">Strand</th>
                                        <th class="pb-2 text-right font-medium">Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(count, strand) in report_data.by_strand" :key="strand" class="border-b last:border-0">
                                        <td class="py-2">{{ strand }}</td>
                                        <td class="py-2 text-right font-medium">{{ count }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </CardContent>
                    </Card>

                    <!-- By Grade Level -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base">By Grade Level</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b">
                                        <th class="pb-2 text-left font-medium">Grade Level</th>
                                        <th class="pb-2 text-right font-medium">Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(count, level) in report_data.by_grade_level" :key="level" class="border-b last:border-0">
                                        <td class="py-2">{{ level }}</td>
                                        <td class="py-2 text-right font-medium">{{ count }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </CardContent>
                    </Card>

                    <!-- By Status -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base">By Status</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b">
                                        <th class="pb-2 text-left font-medium">Status</th>
                                        <th class="pb-2 text-right font-medium">Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(count, status) in report_data.by_status" :key="status" class="border-b last:border-0">
                                        <td class="py-2">{{ status }}</td>
                                        <td class="py-2 text-right font-medium">{{ count }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </CardContent>
                    </Card>
                </div>
            </template>

            <div v-else class="py-12 text-center text-muted-foreground">
                <p>No enrollment data available. Select a semester and apply the filter.</p>
            </div>
        </div>
    </AppLayout>
</template>
