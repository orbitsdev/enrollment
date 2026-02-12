<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Download, GraduationCap, Layers, BarChart3, Users } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, SchoolYear, Semester } from '@/types';

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
    router.reload({
        data: { semester_id: selectedSemesterId.value },
        only: ['report_data', 'selected_semester', 'filters'],
        preserveState: true,
        preserveScroll: true,
    });
}

function exportReport() {
    window.location.href = `/reports/export/enrollment-summary?semester_id=${selectedSemesterId.value}`;
}

// Helpers
function maxCount(data: Record<string, number>): number {
    const values = Object.values(data);
    return values.length > 0 ? Math.max(...values) : 1;
}

function percentage(count: number, total: number): string {
    if (total <= 0) return '0';
    return ((count / total) * 100).toFixed(1);
}

const statusColors: Record<string, string> = {
    Enrolled: 'bg-green-500',
    Dropped: 'bg-red-500',
    Pending: 'bg-yellow-500',
    Transferred: 'bg-blue-500',
};

function getStatusColor(status: string): string {
    return statusColors[status] ?? 'bg-muted-foreground';
}

// Summary stats
const trackCount = computed(() => Object.keys(props.report_data.by_track ?? {}).length);
const strandCount = computed(() => Object.keys(props.report_data.by_strand ?? {}).length);
const gradeLevelCount = computed(() => Object.keys(props.report_data.by_grade_level ?? {}).length);
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

            <!-- Filter Bar -->
            <div class="mb-6 flex flex-wrap items-end gap-3">
                <div class="min-w-[280px]">
                    <label class="mb-1 block text-sm font-medium">Semester</label>
                    <Select v-model="selectedSemesterId">
                        <SelectTrigger>
                            <SelectValue placeholder="Select semester..." />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup v-for="sy in school_years" :key="sy.id">
                                <SelectLabel>SY {{ sy.name }}</SelectLabel>
                                <SelectItem
                                    v-for="sem in sy.semesters"
                                    :key="sem.id"
                                    :value="sem.id.toString()"
                                >
                                    {{ sem.number === 1 ? '1st' : '2nd' }} Semester
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                </div>
                <Button @click="applyFilter" size="sm">Apply Filter</Button>
            </div>

            <template v-if="report_data.total">
                <!-- Summary Stats Row -->
                <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardContent class="flex items-center gap-3 p-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                                <Users class="h-5 w-5 text-primary" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold">{{ report_data.total }}</p>
                                <p class="text-xs text-muted-foreground">Total Enrolled</p>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="flex items-center gap-3 p-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                                <Layers class="h-5 w-5 text-primary" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold">{{ trackCount }}</p>
                                <p class="text-xs text-muted-foreground">Tracks</p>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="flex items-center gap-3 p-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                                <BarChart3 class="h-5 w-5 text-primary" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold">{{ strandCount }}</p>
                                <p class="text-xs text-muted-foreground">Strands</p>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="flex items-center gap-3 p-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                                <GraduationCap class="h-5 w-5 text-primary" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold">{{ gradeLevelCount }}</p>
                                <p class="text-xs text-muted-foreground">Grade Levels</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Tabs -->
                <Tabs default-value="by-track" class="w-full">
                    <TabsList>
                        <TabsTrigger value="by-track">By Track</TabsTrigger>
                        <TabsTrigger value="by-strand">By Strand</TabsTrigger>
                        <TabsTrigger value="by-grade-level">By Grade Level</TabsTrigger>
                        <TabsTrigger value="by-status">By Status</TabsTrigger>
                    </TabsList>

                    <!-- By Track -->
                    <TabsContent value="by-track">
                        <Card>
                            <CardContent class="p-0">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b">
                                            <th class="px-4 py-3 text-left font-medium">Track</th>
                                            <th class="px-4 py-3 text-right font-medium w-[100px]">Count</th>
                                            <th class="px-4 py-3 text-right font-medium w-[80px]">%</th>
                                            <th class="px-4 py-3 font-medium w-[200px]"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(count, track) in report_data.by_track"
                                            :key="track"
                                            class="border-b last:border-0"
                                        >
                                            <td class="px-4 py-3 font-medium">{{ track }}</td>
                                            <td class="px-4 py-3 text-right font-semibold">{{ count }}</td>
                                            <td class="px-4 py-3 text-right text-muted-foreground">
                                                {{ percentage(count, report_data.total) }}%
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="h-2 w-full rounded-full bg-muted">
                                                    <div
                                                        class="h-2 rounded-full bg-primary transition-all"
                                                        :style="{ width: `${(count / maxCount(report_data.by_track)) * 100}%` }"
                                                    />
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <!-- By Strand -->
                    <TabsContent value="by-strand">
                        <Card>
                            <CardContent class="p-0">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b">
                                            <th class="px-4 py-3 text-left font-medium">Strand</th>
                                            <th class="px-4 py-3 text-right font-medium w-[100px]">Count</th>
                                            <th class="px-4 py-3 text-right font-medium w-[80px]">%</th>
                                            <th class="px-4 py-3 font-medium w-[200px]"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(count, strand) in report_data.by_strand"
                                            :key="strand"
                                            class="border-b last:border-0"
                                        >
                                            <td class="px-4 py-3 font-medium">{{ strand }}</td>
                                            <td class="px-4 py-3 text-right font-semibold">{{ count }}</td>
                                            <td class="px-4 py-3 text-right text-muted-foreground">
                                                {{ percentage(count, report_data.total) }}%
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="h-2 w-full rounded-full bg-muted">
                                                    <div
                                                        class="h-2 rounded-full bg-primary transition-all"
                                                        :style="{ width: `${(count / maxCount(report_data.by_strand)) * 100}%` }"
                                                    />
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <!-- By Grade Level -->
                    <TabsContent value="by-grade-level">
                        <Card>
                            <CardContent class="p-0">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b">
                                            <th class="px-4 py-3 text-left font-medium">Grade Level</th>
                                            <th class="px-4 py-3 text-right font-medium w-[100px]">Count</th>
                                            <th class="px-4 py-3 text-right font-medium w-[80px]">%</th>
                                            <th class="px-4 py-3 font-medium w-[200px]"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(count, level) in report_data.by_grade_level"
                                            :key="level"
                                            class="border-b last:border-0"
                                        >
                                            <td class="px-4 py-3 font-medium">{{ level }}</td>
                                            <td class="px-4 py-3 text-right font-semibold">{{ count }}</td>
                                            <td class="px-4 py-3 text-right text-muted-foreground">
                                                {{ percentage(count, report_data.total) }}%
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="h-2 w-full rounded-full bg-muted">
                                                    <div
                                                        class="h-2 rounded-full bg-primary transition-all"
                                                        :style="{ width: `${(count / maxCount(report_data.by_grade_level)) * 100}%` }"
                                                    />
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <!-- By Status -->
                    <TabsContent value="by-status">
                        <Card>
                            <CardContent class="p-0">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b">
                                            <th class="px-4 py-3 text-left font-medium">Status</th>
                                            <th class="px-4 py-3 text-right font-medium w-[100px]">Count</th>
                                            <th class="px-4 py-3 text-right font-medium w-[80px]">%</th>
                                            <th class="px-4 py-3 font-medium w-[200px]"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(count, status) in report_data.by_status"
                                            :key="status"
                                            class="border-b last:border-0"
                                        >
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2">
                                                    <span
                                                        class="inline-block h-2.5 w-2.5 rounded-full"
                                                        :class="getStatusColor(status as string)"
                                                    />
                                                    <span class="font-medium">{{ status }}</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-right font-semibold">{{ count }}</td>
                                            <td class="px-4 py-3 text-right text-muted-foreground">
                                                {{ percentage(count, report_data.total) }}%
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="h-2 w-full rounded-full bg-muted">
                                                    <div
                                                        class="h-2 rounded-full transition-all"
                                                        :class="getStatusColor(status as string)"
                                                        :style="{ width: `${(count / maxCount(report_data.by_status)) * 100}%` }"
                                                    />
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </template>

            <div v-else class="py-12 text-center text-muted-foreground">
                <p>No enrollment data available. Select a semester and apply the filter.</p>
            </div>
        </div>
    </AppLayout>
</template>
