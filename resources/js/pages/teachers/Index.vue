<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Eye } from 'lucide-vue-next';
import { ref } from 'vue';
import DataTable from '@/components/App/DataTable.vue';
import PageHeader from '@/components/App/PageHeader.vue';
import SearchInput from '@/components/App/SearchInput.vue';
import StatusBadge from '@/components/App/StatusBadge.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { TableCell } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, PaginatedData, TeacherProfile } from '@/types';
import type { User } from '@/types/auth';

type TeacherUser = User & {
    is_active: boolean;
    teacher_profile: TeacherProfile | null;
};

const props = defineProps<{
    teachers: PaginatedData<TeacherUser>;
    filters: { search: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Teachers' },
];

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'employee_id', label: 'Employee ID', class: 'w-[140px]' },
    { key: 'position', label: 'Position', class: 'w-[180px]' },
    { key: 'specialization', label: 'Specialization', class: 'w-[180px]' },
    { key: 'status', label: 'Profile', class: 'w-[100px]' },
    { key: 'actions', label: '', class: 'w-[80px] text-right' },
];

const search = ref(props.filters.search ?? '');
</script>

<template>
    <Head title="Teachers" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <PageHeader
                title="Teachers"
                description="View and manage teacher profiles."
            />

            <div class="flex items-center gap-4">
                <SearchInput
                    v-model="search"
                    placeholder="Search teachers..."
                    :only="['teachers', 'filters']"
                />
            </div>

            <DataTable :columns="columns" :data="teachers.data">
                <template #row="{ row }">
                    <TableCell>
                        <div class="font-medium">{{ row.name }}</div>
                        <div class="text-xs text-muted-foreground">{{ row.email }}</div>
                    </TableCell>
                    <TableCell>
                        {{ row.teacher_profile?.employee_id || '---' }}
                    </TableCell>
                    <TableCell>
                        {{ row.teacher_profile?.position_title || '---' }}
                    </TableCell>
                    <TableCell>
                        {{ row.teacher_profile?.specialization || '---' }}
                    </TableCell>
                    <TableCell>
                        <Badge :variant="row.teacher_profile ? 'default' : 'outline'">
                            {{ row.teacher_profile ? 'Complete' : 'Incomplete' }}
                        </Badge>
                    </TableCell>
                    <TableCell class="text-right">
                        <Button variant="ghost" size="sm" as-child>
                            <Link :href="`/teachers/${row.id}`">
                                <Eye class="size-4" />
                            </Link>
                        </Button>
                    </TableCell>
                </template>
            </DataTable>

            <!-- Pagination -->
            <div
                v-if="teachers.last_page > 1"
                class="flex items-center justify-between"
            >
                <p class="text-sm text-muted-foreground">
                    Showing {{ teachers.from }} to {{ teachers.to }} of
                    {{ teachers.total }} results
                </p>
                <div class="flex items-center gap-2">
                    <Button
                        v-for="link in teachers.links"
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
