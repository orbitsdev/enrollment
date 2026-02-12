<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ConfirmDialog from '@/components/App/ConfirmDialog.vue';
import DataTable from '@/components/App/DataTable.vue';
import PageHeader from '@/components/App/PageHeader.vue';
import SearchInput from '@/components/App/SearchInput.vue';
import StatusBadge from '@/components/App/StatusBadge.vue';
import UserFormDialog from '@/components/App/UserFormDialog.vue';
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
import type { BreadcrumbItem, PaginatedData, User } from '@/types';

type UserWithRole = User & { role: string; is_active: boolean };

const props = defineProps<{
    users: PaginatedData<UserWithRole>;
    filters: { search: string; role: string };
    roles: Array<{ value: string; label: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Users' },
];

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'email', label: 'Email' },
    { key: 'role', label: 'Role', class: 'w-[120px]' },
    { key: 'status', label: 'Status', class: 'w-[120px]' },
    { key: 'actions', label: '', class: 'w-[100px] text-right' },
];

const search = ref(props.filters.search ?? '');
const roleFilter = ref(props.filters.role ?? '');

const searchExtraData = computed(() => ({
    role: roleFilter.value || undefined,
}));

function onRoleChange(value: string) {
    roleFilter.value = value === 'all' ? '' : value;
    router.reload({
        data: {
            search: search.value || undefined,
            role: roleFilter.value || undefined,
        },
        only: ['users', 'filters'],
        preserveState: true,
        preserveScroll: true,
    });
}

// Dialog state
const dialogOpen = ref(false);
const editingUser = ref<UserWithRole | null>(null);

function openCreateDialog() {
    editingUser.value = null;
    dialogOpen.value = true;
}

function openEditDialog(user: UserWithRole) {
    editingUser.value = user;
    dialogOpen.value = true;
}

const confirmingToggle = ref(false);
const toggleUser = ref<UserWithRole | null>(null);
const toggleProcessing = ref(false);

function promptToggle(user: UserWithRole) {
    toggleUser.value = user;
    confirmingToggle.value = true;
}

function executeToggle() {
    if (!toggleUser.value) return;

    toggleProcessing.value = true;
    router.put(
        `/users/${toggleUser.value.id}/toggle-active`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                toggleProcessing.value = false;
                confirmingToggle.value = false;
                toggleUser.value = null;
            },
        },
    );
}
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <PageHeader
                title="Users"
                description="Manage system users and their roles."
            >
                <template #actions>
                    <Button @click="openCreateDialog">
                        <Plus class="size-4" />
                        Add User
                    </Button>
                </template>
            </PageHeader>

            <div class="flex items-center gap-4">
                <SearchInput
                    v-model="search"
                    placeholder="Search users..."
                    :only="['users', 'filters']"
                    :extra-data="searchExtraData"
                />
                <Select
                    :model-value="roleFilter || 'all'"
                    @update:model-value="onRoleChange"
                >
                    <SelectTrigger class="w-[180px]">
                        <SelectValue placeholder="Filter by role" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Roles</SelectItem>
                        <SelectItem
                            v-for="role in roles"
                            :key="role.value"
                            :value="role.value"
                        >
                            {{ role.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <DataTable :columns="columns" :data="users.data">
                <template #row="{ row }">
                    <TableCell>
                        <div class="font-medium">{{ row.name }}</div>
                    </TableCell>
                    <TableCell>{{ row.email }}</TableCell>
                    <TableCell>
                        <Badge variant="outline">{{ row.role }}</Badge>
                    </TableCell>
                    <TableCell>
                        <StatusBadge
                            :status="row.is_active ? 'active' : 'inactive'"
                        />
                    </TableCell>
                    <TableCell class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="openEditDialog(row)"
                            >
                                Edit
                            </Button>
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="promptToggle(row)"
                            >
                                {{ row.is_active ? 'Deactivate' : 'Activate' }}
                            </Button>
                        </div>
                    </TableCell>
                </template>
            </DataTable>

            <!-- Pagination -->
            <div
                v-if="users.last_page > 1"
                class="flex items-center justify-between"
            >
                <p class="text-sm text-muted-foreground">
                    Showing {{ users.from }} to {{ users.to }} of
                    {{ users.total }} results
                </p>
                <div class="flex items-center gap-2">
                    <Button
                        v-for="link in users.links"
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

        <UserFormDialog
            v-model:open="dialogOpen"
            :user="editingUser"
            :roles="roles"
        />

        <ConfirmDialog
            :open="confirmingToggle"
            :title="
                toggleUser?.is_active ? 'Deactivate User' : 'Activate User'
            "
            :description="
                toggleUser?.is_active
                    ? `Are you sure you want to deactivate ${toggleUser?.name}? They will no longer be able to log in.`
                    : `Are you sure you want to activate ${toggleUser?.name}? They will be able to log in again.`
            "
            :confirm-text="toggleUser?.is_active ? 'Deactivate' : 'Activate'"
            :variant="toggleUser?.is_active ? 'destructive' : 'default'"
            :processing="toggleProcessing"
            @confirm="executeToggle"
            @cancel="
                confirmingToggle = false;
                toggleUser = null;
            "
        />
    </AppLayout>
</template>
