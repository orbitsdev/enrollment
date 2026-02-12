<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, Pencil, Plus } from 'lucide-vue-next';
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
import type { BreadcrumbItem, PaginatedData, Student } from '@/types';

const props = defineProps<{
    students: PaginatedData<Student>;
    filters: { search: string; status: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Students' },
];

const columns = [
    { key: 'lrn', label: 'LRN', class: 'w-[140px]' },
    { key: 'name', label: 'Name' },
    { key: 'gender', label: 'Gender', class: 'w-[100px]' },
    { key: 'status', label: 'Status', class: 'w-[120px]' },
    { key: 'actions', label: '', class: 'w-[120px] text-right' },
];

const search = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status ?? '');

function onStatusChange(value: string) {
    statusFilter.value = value === 'all' ? '' : value;
    router.get(
        '/students',
        {
            search: search.value || undefined,
            status: statusFilter.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['students'],
        },
    );
}
</script>

<template>
    <Head title="Students" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <PageHeader
                title="Students"
                description="Manage student records and profiles."
            >
                <template #actions>
                    <Button as-child>
                        <Link href="/students/create">
                            <Plus class="size-4" />
                            Add Student
                        </Link>
                    </Button>
                </template>
            </PageHeader>

            <!-- Filters -->
            <div class="flex items-center gap-4">
                <SearchInput
                    v-model="search"
                    placeholder="Search by LRN or name..."
                    :only="['students']"
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
                        <SelectItem value="active">Active</SelectItem>
                        <SelectItem value="transferred">Transferred</SelectItem>
                        <SelectItem value="dropped">Dropped</SelectItem>
                        <SelectItem value="graduated">Graduated</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- DataTable -->
            <DataTable
                :columns="columns"
                :data="students.data"
                empty-message="No students found."
            >
                <template #row="{ row }">
                    <TableCell class="font-mono text-sm">
                        {{ row.lrn }}
                    </TableCell>
                    <TableCell class="font-medium">
                        {{ row.full_name ?? `${row.last_name}, ${row.first_name} ${row.middle_name ?? ''}`.trim() }}
                    </TableCell>
                    <TableCell class="capitalize">
                        {{ row.gender }}
                    </TableCell>
                    <TableCell>
                        <StatusBadge :status="row.status" />
                    </TableCell>
                    <TableCell class="text-right">
                        <div class="flex items-center justify-end gap-1">
                            <Button variant="ghost" size="icon-sm" as-child>
                                <Link :href="`/students/${row.id}`" prefetch>
                                    <Eye class="size-4" />
                                </Link>
                            </Button>
                            <Button variant="ghost" size="icon-sm" as-child>
                                <Link :href="`/students/${row.id}/edit`" prefetch>
                                    <Pencil class="size-4" />
                                </Link>
                            </Button>
                        </div>
                    </TableCell>
                </template>
            </DataTable>

            <!-- Pagination -->
            <div
                v-if="students.last_page > 1"
                class="flex items-center justify-between"
            >
                <p class="text-sm text-muted-foreground">
                    Showing {{ students.from }} to {{ students.to }} of
                    {{ students.total }} results
                </p>
                <div class="flex items-center gap-2">
                    <Button
                        v-for="link in students.links"
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
