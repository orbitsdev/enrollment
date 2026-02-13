<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { CalendarDays, GraduationCap, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import PageHeader from '@/components/App/PageHeader.vue';
import StatusBadge from '@/components/App/StatusBadge.vue';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Enrollment, Section, Strand, Student } from '@/types';

type EnrollmentWithDetails = Enrollment & {
    student: Student;
    section: Section & {
        strand: Strand;
    };
};

const props = defineProps<{
    enrollments: EnrollmentWithDetails[];
}>();

const page = usePage();
const activeSemester = computed(() => page.props.activeSemester as { full_label: string } | null);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'My Students' },
];

// Filters
const searchQuery = ref('');
const sectionFilter = ref('all');

// Unique sections for filter
const uniqueSections = computed(() => {
    const sections = new Map<number, { id: number; name: string }>();
    for (const enrollment of props.enrollments) {
        if (enrollment.section) {
            sections.set(enrollment.section.id, {
                id: enrollment.section.id,
                name: enrollment.section.name,
            });
        }
    }
    return Array.from(sections.values()).sort((a, b) => a.name.localeCompare(b.name));
});

// Filtered enrollments
const filteredEnrollments = computed(() => {
    return props.enrollments.filter((enrollment) => {
        const student = enrollment.student;
        const query = searchQuery.value.toLowerCase();
        const matchesSearch = !query ||
            student?.lrn?.toLowerCase().includes(query) ||
            student?.last_name?.toLowerCase().includes(query) ||
            student?.first_name?.toLowerCase().includes(query) ||
            `${student?.last_name}, ${student?.first_name}`.toLowerCase().includes(query);
        const matchesSection = sectionFilter.value === 'all' ||
            enrollment.section_id?.toString() === sectionFilter.value;
        return matchesSearch && matchesSection;
    });
});

function studentFullName(student: Student): string {
    const parts = [student.last_name, student.first_name];
    if (student.middle_name) {
        parts.push(student.middle_name.charAt(0) + '.');
    }
    if (student.suffix) {
        parts.push(student.suffix);
    }
    return parts.join(', ');
}
</script>

<template>
    <Head title="My Students" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <PageHeader
                title="My Students"
                description="Students enrolled in your advisory sections."
            />

            <!-- Active Semester Context -->
            <div class="rounded-lg border bg-muted/50 px-4 py-3">
                <div class="flex items-center gap-2 text-sm">
                    <CalendarDays class="size-4 text-muted-foreground" />
                    <span class="font-medium">
                        {{ activeSemester?.full_label ?? 'No active semester' }}
                    </span>
                </div>
            </div>

            <template v-if="enrollments.length > 0">
                <!-- Filter Bar -->
                <div class="flex flex-wrap items-center gap-3">
                    <div class="relative min-w-[220px] flex-1 max-w-sm">
                        <Search class="absolute left-2.5 top-2.5 size-4 text-muted-foreground" />
                        <Input
                            v-model="searchQuery"
                            placeholder="Search by name or LRN..."
                            class="pl-9"
                        />
                    </div>
                    <Select v-model="sectionFilter">
                        <SelectTrigger class="w-[200px]">
                            <SelectValue placeholder="All Sections" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Sections</SelectItem>
                            <SelectItem
                                v-for="section in uniqueSections"
                                :key="section.id"
                                :value="section.id.toString()"
                            >
                                {{ section.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Students Table -->
                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>LRN</TableHead>
                                <TableHead>Student Name</TableHead>
                                <TableHead>Section</TableHead>
                                <TableHead>Strand</TableHead>
                                <TableHead>Grade Level</TableHead>
                                <TableHead>Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <template v-if="filteredEnrollments.length > 0">
                                <TableRow v-for="enrollment in filteredEnrollments" :key="enrollment.id">
                                    <TableCell class="font-mono text-sm">{{ enrollment.student?.lrn }}</TableCell>
                                    <TableCell class="font-medium">{{ studentFullName(enrollment.student) }}</TableCell>
                                    <TableCell>{{ enrollment.section?.name }}</TableCell>
                                    <TableCell>
                                        <Badge variant="outline">{{ enrollment.section?.strand?.code ?? '--' }}</Badge>
                                    </TableCell>
                                    <TableCell>Grade {{ enrollment.section?.grade_level }}</TableCell>
                                    <TableCell>
                                        <StatusBadge :status="enrollment.status" />
                                    </TableCell>
                                </TableRow>
                            </template>
                            <TableRow v-else>
                                <TableCell colspan="6" class="text-center py-8">
                                    <p class="text-muted-foreground">No matching students. Try adjusting your search or filter.</p>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </template>

            <!-- Empty state -->
            <div v-else class="flex flex-col items-center justify-center rounded-lg border border-dashed p-12">
                <GraduationCap class="mb-4 size-12 text-muted-foreground/50" />
                <p class="text-lg font-medium text-muted-foreground">No students found</p>
                <p class="text-sm text-muted-foreground">
                    There are no enrolled students in your advisory sections.
                </p>
            </div>
        </div>
    </AppLayout>
</template>
