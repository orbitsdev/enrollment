<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, PowerOff } from 'lucide-vue-next';
import { ref } from 'vue';
import DataTable from '@/components/App/DataTable.vue';
import PageHeader from '@/components/App/PageHeader.vue';
import SearchInput from '@/components/App/SearchInput.vue';
import { Badge } from '@/components/ui/badge';
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
import type { BreadcrumbItem, PaginatedData, Subject } from '@/types';

type SubjectWithCount = Subject & { strands_count: number };

const props = defineProps<{
    subjects: PaginatedData<SubjectWithCount>;
    filters: { search: string; type: string; strand_id: string };
    types: Array<{ value: string; label: string }>;
    strands: Array<{ id: number; code: string; full_name: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Curriculum' },
    { title: 'Subjects' },
];

const columns = [
    { key: 'code', label: 'Code', class: 'w-[120px]' },
    { key: 'name', label: 'Name' },
    { key: 'type', label: 'Type', class: 'w-[120px]' },
    { key: 'hours', label: 'Hours', class: 'w-[80px]' },
    { key: 'prerequisite', label: 'Prerequisite', class: 'w-[150px]' },
    { key: 'strands_count', label: 'Strands', class: 'w-[80px]' },
    { key: 'actions', label: '', class: 'w-[180px] text-right' },
];

const search = ref(props.filters.search ?? '');
const typeFilter = ref(props.filters.type ?? '');
const strandFilter = ref(props.filters.strand_id ?? '');

const typeVariantMap: Record<string, 'default' | 'secondary' | 'outline'> = {
    core: 'default',
    specialized: 'secondary',
    applied: 'outline',
};

function applyFilters() {
    router.get(
        '/subjects',
        {
            search: search.value || undefined,
            type: typeFilter.value || undefined,
            strand_id: strandFilter.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['subjects'],
        },
    );
}

function onTypeChange(value: string) {
    typeFilter.value = value === 'all' ? '' : value;
    applyFilters();
}

function onStrandChange(value: string) {
    strandFilter.value = value === 'all' ? '' : value;
    applyFilters();
}
</script>

<template>
    <Head title="Subjects" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <PageHeader
                title="Subjects"
                description="Manage curriculum subjects and their strand mappings."
            >
                <template #actions>
                    <Button as-child>
                        <Link href="/subjects/create">
                            <Plus class="size-4" />
                            Add Subject
                        </Link>
                    </Button>
                </template>
            </PageHeader>

            <!-- Filters -->
            <div class="flex items-center gap-4">
                <SearchInput
                    v-model="search"
                    placeholder="Search subjects..."
                    :only="['subjects']"
                />
                <Select
                    :model-value="typeFilter || 'all'"
                    @update:model-value="onTypeChange"
                >
                    <SelectTrigger class="w-[180px]">
                        <SelectValue placeholder="Filter by type" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Types</SelectItem>
                        <SelectItem
                            v-for="t in types"
                            :key="t.value"
                            :value="t.value"
                        >
                            {{ t.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Select
                    :model-value="strandFilter || 'all'"
                    @update:model-value="onStrandChange"
                >
                    <SelectTrigger class="w-[220px]">
                        <SelectValue placeholder="Filter by strand" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Strands</SelectItem>
                        <SelectItem
                            v-for="s in strands"
                            :key="s.id"
                            :value="String(s.id)"
                        >
                            {{ s.code }} - {{ s.full_name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- DataTable -->
            <DataTable
                :columns="columns"
                :data="subjects.data"
                empty-message="No subjects found."
            >
                <template #row="{ row }">
                    <TableCell class="font-mono text-sm">
                        {{ row.code }}
                    </TableCell>
                    <TableCell class="font-medium">
                        {{ row.name }}
                    </TableCell>
                    <TableCell>
                        <Badge :variant="typeVariantMap[row.type] ?? 'secondary'">
                            {{ row.type }}
                        </Badge>
                    </TableCell>
                    <TableCell>
                        {{ row.hours }}
                    </TableCell>
                    <TableCell>
                        <span v-if="row.prerequisite" class="text-sm">
                            {{ row.prerequisite.code }}
                        </span>
                        <span v-else class="text-sm text-muted-foreground">--</span>
                    </TableCell>
                    <TableCell>
                        {{ row.strands_count }}
                    </TableCell>
                    <TableCell class="text-right space-x-1">
                        <Button variant="ghost" size="sm" as-child>
                            <Link :href="`/subjects/${row.id}/edit`" prefetch>
                                <Pencil class="size-4" />
                                Edit
                            </Link>
                        </Button>
                        <Button
                            variant="ghost"
                            size="sm"
                            class="text-destructive hover:text-destructive"
                            @click="
                                if (confirm('Are you sure you want to deactivate this subject?')) {
                                    router.delete(`/subjects/${row.id}`, { preserveScroll: true });
                                }
                            "
                        >
                            <PowerOff class="size-4" />
                            Deactivate
                        </Button>
                    </TableCell>
                </template>
            </DataTable>

            <!-- Pagination -->
            <div
                v-if="subjects.last_page > 1"
                class="flex items-center justify-between"
            >
                <p class="text-sm text-muted-foreground">
                    Showing {{ subjects.from }} to {{ subjects.to }} of
                    {{ subjects.total }} results
                </p>
                <div class="flex items-center gap-2">
                    <Button
                        v-for="link in subjects.links"
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
