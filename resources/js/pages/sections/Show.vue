<script setup lang="ts">
import { Head, Link, WhenVisible } from '@inertiajs/vue3';
import { Download, Pencil, Printer } from 'lucide-vue-next';
import CapacityBar from '@/components/App/CapacityBar.vue';
import PageHeader from '@/components/App/PageHeader.vue';
import StatusBadge from '@/components/App/StatusBadge.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Skeleton } from '@/components/ui/skeleton';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Enrollment, Section, Student } from '@/types';

type SectionWithInfo = Section & {
    strand?: { id: number; code: string; name: string };
    semester?: { id: number; number: number; label?: string; school_year?: { name: string } };
    adviser?: { id: number; name: string };
};

const props = defineProps<{
    section: SectionWithInfo;
    enrollments?: Array<Enrollment & { student: Student }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Sections', href: '/sections' },
    { title: props.section.name },
];
</script>

<template>
    <Head :title="`Section: ${section.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <PageHeader
                :title="section.name"
                description="Section roster and details."
            >
                <template #actions>
                    <Button variant="outline" as-child>
                        <a :href="`/sections/${section.id}/export/pdf`" target="_blank">
                            <Printer class="size-4" />
                            PDF
                        </a>
                    </Button>
                    <Button variant="outline" as-child>
                        <a :href="`/sections/${section.id}/export/excel`" target="_blank">
                            <Download class="size-4" />
                            Excel
                        </a>
                    </Button>
                    <Button as-child>
                        <Link :href="`/sections/${section.id}/edit`">
                            <Pencil class="size-4" />
                            Edit
                        </Link>
                    </Button>
                </template>
            </PageHeader>

            <!-- Section Info -->
            <Card>
                <CardHeader>
                    <CardTitle>Section Information</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Strand</p>
                            <div class="mt-1">
                                <Badge variant="outline">
                                    {{ section.strand?.code ?? '--' }}
                                </Badge>
                                <span class="ml-2 text-sm">{{ section.strand?.name }}</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Semester</p>
                            <p class="mt-1 text-sm">
                                {{ section.semester?.label ?? `${section.semester?.school_year?.name} - Sem ${section.semester?.number}` }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Grade Level</p>
                            <p class="mt-1 text-sm">Grade {{ section.grade_level }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Adviser</p>
                            <p class="mt-1 text-sm">{{ section.adviser?.name ?? 'Not assigned' }}</p>
                        </div>
                    </div>
                    <Separator class="my-4" />
                    <div class="max-w-xs">
                        <p class="mb-2 text-sm font-medium text-muted-foreground">Capacity</p>
                        <CapacityBar :current="enrollments?.length ?? 0" :max="section.max_capacity" />
                    </div>
                </CardContent>
            </Card>

            <!-- Roster Table (loaded when visible) -->
            <WhenVisible data="enrollments">
                <template #fallback>
                    <Card>
                        <CardHeader>
                            <CardTitle>Class Roster</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <Skeleton class="h-8 w-full" />
                            <Skeleton class="h-8 w-full" />
                            <Skeleton class="h-8 w-full" />
                            <Skeleton class="h-8 w-full" />
                            <Skeleton class="h-8 w-full" />
                        </CardContent>
                    </Card>
                </template>

                <Card>
                    <CardHeader>
                        <CardTitle>Class Roster</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead class="w-[60px]">#</TableHead>
                                        <TableHead class="w-[140px]">LRN</TableHead>
                                        <TableHead>Student Name</TableHead>
                                        <TableHead class="w-[100px]">Gender</TableHead>
                                        <TableHead class="w-[120px]">Status</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <template v-if="enrollments && enrollments.length > 0">
                                        <TableRow
                                            v-for="(enrollment, index) in enrollments"
                                            :key="enrollment.id"
                                        >
                                            <TableCell class="font-medium">
                                                {{ index + 1 }}
                                            </TableCell>
                                            <TableCell class="font-mono text-sm">
                                                {{ enrollment.student.lrn }}
                                            </TableCell>
                                            <TableCell>
                                                <Link
                                                    :href="`/students/${enrollment.student.id}`"
                                                    class="font-medium hover:underline"
                                                    prefetch
                                                >
                                                    {{ enrollment.student.full_name ?? `${enrollment.student.last_name}, ${enrollment.student.first_name} ${enrollment.student.middle_name ?? ''}`.trim() }}
                                                </Link>
                                            </TableCell>
                                            <TableCell class="capitalize">
                                                {{ enrollment.student.gender }}
                                            </TableCell>
                                            <TableCell>
                                                <StatusBadge :status="enrollment.status" />
                                            </TableCell>
                                        </TableRow>
                                    </template>
                                    <template v-else>
                                        <TableRow>
                                            <TableCell
                                                :colspan="5"
                                                class="h-24 text-center text-muted-foreground"
                                            >
                                                No students enrolled in this section yet.
                                            </TableCell>
                                        </TableRow>
                                    </template>
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>
            </WhenVisible>
        </div>
    </AppLayout>
</template>
