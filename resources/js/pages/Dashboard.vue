<script setup lang="ts">
import { Head, Deferred } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import StatCard from '@/components/App/StatCard.vue';
import EnrollmentByTrackChart from '@/components/Charts/EnrollmentByTrackChart.vue';
import SectionCapacityChart from '@/components/Charts/SectionCapacityChart.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Users, UserCheck, LayoutGrid, GraduationCap } from 'lucide-vue-next';

interface Enrollment {
    id: number;
    status: string;
    created_at: string;
    student?: { full_name: string; lrn: string };
    section?: { name: string; strand?: { name: string } };
}

const props = defineProps<{
    auth_user: { name: string; email: string };
    total_students?: number;
    total_enrolled?: number;
    total_sections?: number;
    total_teachers?: number;
    enrollment_by_track?: Record<string, number>;
    section_capacity?: Record<string, { enrolled: number; max: number }>;
    recent_enrollments?: Enrollment[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Stat Cards -->
            <Deferred data="total_students,total_enrolled,total_sections,total_teachers">
                <template #fallback>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <Card v-for="i in 4" :key="i">
                            <CardContent class="flex items-center gap-4 p-6">
                                <div class="h-12 w-12 animate-pulse rounded-lg bg-muted" />
                                <div class="flex-1 space-y-2">
                                    <div class="h-3 w-20 animate-pulse rounded bg-muted" />
                                    <div class="h-6 w-16 animate-pulse rounded bg-muted" />
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </template>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <StatCard
                        title="Total Students"
                        :value="total_students ?? 0"
                        :icon="Users"
                        description="All registered students"
                    />
                    <StatCard
                        title="Enrolled This Semester"
                        :value="total_enrolled ?? 0"
                        :icon="UserCheck"
                        description="Currently enrolled"
                    />
                    <StatCard
                        title="Sections"
                        :value="total_sections ?? 0"
                        :icon="LayoutGrid"
                        description="Active sections"
                    />
                    <StatCard
                        title="Teachers"
                        :value="total_teachers ?? 0"
                        :icon="GraduationCap"
                        description="Teacher accounts"
                    />
                </div>
            </Deferred>

            <!-- Charts Row -->
            <Deferred data="enrollment_by_track,section_capacity">
                <template #fallback>
                    <div class="grid gap-4 lg:grid-cols-2">
                        <Card v-for="i in 2" :key="i">
                            <CardContent class="p-6">
                                <div class="h-[300px] animate-pulse rounded bg-muted" />
                            </CardContent>
                        </Card>
                    </div>
                </template>

                <div class="grid gap-4 lg:grid-cols-2">
                    <Card>
                        <CardContent class="p-6">
                            <EnrollmentByTrackChart
                                v-if="enrollment_by_track && Object.keys(enrollment_by_track).length > 0"
                                :data="enrollment_by_track"
                            />
                            <div v-else class="flex h-[300px] items-center justify-center text-muted-foreground">
                                No enrollment data available
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="p-6">
                            <SectionCapacityChart
                                v-if="section_capacity && Object.keys(section_capacity).length > 0"
                                :data="section_capacity"
                            />
                            <div v-else class="flex h-[300px] items-center justify-center text-muted-foreground">
                                No section data available
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </Deferred>

            <!-- Recent Enrollments -->
            <Deferred data="recent_enrollments">
                <template #fallback>
                    <Card>
                        <CardHeader>
                            <CardTitle>Recent Enrollments</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div v-for="i in 5" :key="i" class="flex items-center gap-4">
                                    <div class="h-4 w-24 animate-pulse rounded bg-muted" />
                                    <div class="h-4 w-40 animate-pulse rounded bg-muted" />
                                    <div class="h-4 w-20 animate-pulse rounded bg-muted" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </template>

                <Card>
                    <CardHeader>
                        <CardTitle>Recent Enrollments</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b text-left text-muted-foreground">
                                        <th class="pb-2 pr-4 font-medium">Student</th>
                                        <th class="pb-2 pr-4 font-medium">LRN</th>
                                        <th class="pb-2 pr-4 font-medium">Section</th>
                                        <th class="pb-2 pr-4 font-medium">Strand</th>
                                        <th class="pb-2 pr-4 font-medium">Status</th>
                                        <th class="pb-2 font-medium">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="enrollment in recent_enrollments"
                                        :key="enrollment.id"
                                        class="border-b last:border-0"
                                    >
                                        <td class="py-2 pr-4">{{ enrollment.student?.full_name ?? '-' }}</td>
                                        <td class="py-2 pr-4 font-mono text-xs">{{ enrollment.student?.lrn ?? '-' }}</td>
                                        <td class="py-2 pr-4">{{ enrollment.section?.name ?? '-' }}</td>
                                        <td class="py-2 pr-4">{{ enrollment.section?.strand?.name ?? '-' }}</td>
                                        <td class="py-2 pr-4">
                                            <Badge
                                                :variant="enrollment.status === 'enrolled' ? 'default' : 'secondary'"
                                            >
                                                {{ enrollment.status }}
                                            </Badge>
                                        </td>
                                        <td class="py-2">{{ formatDate(enrollment.created_at) }}</td>
                                    </tr>
                                    <tr v-if="!recent_enrollments || recent_enrollments.length === 0">
                                        <td colspan="6" class="py-8 text-center text-muted-foreground">
                                            No recent enrollments
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </Deferred>
        </div>
    </AppLayout>
</template>
