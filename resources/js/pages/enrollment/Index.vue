<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, Plus } from 'lucide-vue-next';
import { ref } from 'vue';
import DataTable from '@/components/App/DataTable.vue';
import PageHeader from '@/components/App/PageHeader.vue';
import SearchInput from '@/components/App/SearchInput.vue';
import StatusBadge from '@/components/App/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { TableCell } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type {
    BreadcrumbItem,
    Enrollment,
    PaginatedData,
    Section,
    Semester,
    Strand,
    Student,
} from '@/types';

type EnrollmentWithRelations = Enrollment & {
    student: Student;
    section: Section & { strand: Strand };
};

const props = defineProps<{
    enrollments: PaginatedData<EnrollmentWithRelations>;
    filters: { search: string; status: string; semester_id: string };
    semesters: Semester[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Enrollment' },
];

const columns = [
    { key: 'student', label: 'Student' },
    { key: 'section', label: 'Section', class: 'w-[140px]' },
    { key: 'strand', label: 'Strand', class: 'w-[100px]' },
    { key: 'grade_level', label: 'Grade Level', class: 'w-[110px]' },
    { key: 'status', label: 'Status', class: 'w-[120px]' },
    { key: 'date', label: 'Date', class: 'w-[120px]' },
    { key: 'actions', label: '', class: 'w-[80px] text-right' },
];

const search = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status ?? '');
const semesterFilter = ref(props.filters.semester_id ?? '');

function applyFilters() {
    router.get(
        '/enrollment',
        {
            search: search.value || undefined,
            status: statusFilter.value || undefined,
            semester_id: semesterFilter.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['enrollments'],
        },
    );
}

function onStatusChange(value: string) {
    statusFilter.value = value === 'all' ? '' : value;
    applyFilters();
}

function onSemesterChange(value: string) {
    semesterFilter.value = value === 'all' ? '' : value;
    applyFilters();
}

function formatDate(dateStr: string | null): string {
    if (!dateStr) return '--';
    return new Date(dateStr).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}
</script>

<template>
    <Head title="Enrollment" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <PageHeader
                title="Enrollment"
                description="Manage student enrollments."
            >
                <template #actions>
                    <Button as-child>
                        <Link href="/enrollment/create">
                            <Plus class="size-4" />
                            New Enrollment
                        </Link>
                    </Button>
                </template>
            </PageHeader>

            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-4">
                <SearchInput
                    v-model="search"
                    placeholder="Search by LRN or student name..."
                    :only="['enrollments']"
                />
                <Select
                    :model-value="statusFilter || 'all'"
                    @update:model-value="onStatusChange"
                >
                    <SelectTrigger class="w-[180px]">
                        <SelectValue placeholder="Filter by status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Statuses</SelectItem>
                        <SelectItem value="pending">Pending</SelectItem>
                        <SelectItem value="enrolled">Enrolled</SelectItem>
                        <SelectItem value="dropped">Dropped</SelectItem>
                        <SelectItem value="transferred">Transferred</SelectItem>
                    </SelectContent>
                </Select>
                <Select
                    :model-value="semesterFilter || 'all'"
                    @update:model-value="onSemesterChange"
                >
                    <SelectTrigger class="w-[220px]">
                        <SelectValue placeholder="Filter by semester" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Semesters</SelectItem>
                        <SelectItem
                            v-for="sem in semesters"
                            :key="sem.id"
                            :value="String(sem.id)"
                        >
                            {{ sem.label ?? `${sem.school_year?.name} - Sem ${sem.number}` }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- DataTable -->
            <DataTable
                :columns="columns"
                :data="enrollments.data"
                empty-message="No enrollments found."
            >
                <template #row="{ row }">
                    <TableCell>
                        <div>
                            <span class="font-mono text-xs text-muted-foreground">
                                {{ row.student.lrn }}
                            </span>
                            <div class="font-medium">
                                {{ row.student.full_name ?? `${row.student.last_name}, ${row.student.first_name}` }}
                            </div>
                        </div>
                    </TableCell>
                    <TableCell>
                        {{ row.section?.name ?? '--' }}
                    </TableCell>
                    <TableCell>
                        {{ row.section?.strand?.code ?? '--' }}
                    </TableCell>
                    <TableCell>
                        Grade {{ row.section?.grade_level ?? '--' }}
                    </TableCell>
                    <TableCell>
                        <StatusBadge :status="row.status" />
                    </TableCell>
                    <TableCell>
                        {{ formatDate(row.enrolled_at ?? row.created_at) }}
                    </TableCell>
                    <TableCell class="text-right">
                        <Button variant="ghost" size="icon-sm" as-child>
                            <Link :href="`/enrollment/${row.id}`" prefetch>
                                <Eye class="size-4" />
                            </Link>
                        </Button>
                    </TableCell>
                </template>
            </DataTable>

            <!-- Pagination -->
            <div
                v-if="enrollments.last_page > 1"
                class="flex items-center justify-between"
            >
                <p class="text-sm text-muted-foreground">
                    Showing {{ enrollments.from }} to {{ enrollments.to }} of
                    {{ enrollments.total }} results
                </p>
                <div class="flex items-center gap-2">
                    <Button
                        v-for="link in enrollments.links"
                        :key="link.label"
                        variant="outline"
                        size="sm"
                        :disabled="!link.url || link.active"
                        as-child
                    >
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-state
                            preserve-scroll
                            v-html="link.label"
                        />
                        <span v-else v-html="link.label" />
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
