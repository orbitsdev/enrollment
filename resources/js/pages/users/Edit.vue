<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import PageHeader from '@/components/App/PageHeader.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { useUnsavedChangesGuard } from '@/composables/useUnsavedChangesGuard';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, User } from '@/types';

type UserWithRole = User & { role: string; is_active: boolean };

const props = defineProps<{
    user: UserWithRole;
    roles: Array<{ value: string; label: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Users', href: '/users' },
    { title: `Edit ${props.user.name}` },
];

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    role: props.user.role,
    is_active: props.user.is_active,
});

useUnsavedChangesGuard(form);

function submit() {
    form.put(`/users/${props.user.id}`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="`Edit ${user.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <PageHeader
                :title="`Edit ${user.name}`"
                description="Update user information and role."
            >
                <template #actions>
                    <Button variant="outline" as-child>
                        <Link href="/users">Back to Users</Link>
                    </Button>
                </template>
            </PageHeader>

            <form
                class="mx-auto w-full max-w-2xl space-y-6"
                @submit.prevent="submit"
            >
                <div class="space-y-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        type="text"
                        placeholder="Full name"
                        autocomplete="name"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="space-y-2">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="Email address"
                        autocomplete="email"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="space-y-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        v-model="form.password"
                        type="password"
                        placeholder="Leave blank to keep current"
                        autocomplete="new-password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="space-y-2">
                    <Label for="password_confirmation">
                        Confirm Password
                    </Label>
                    <Input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        placeholder="Confirm new password"
                        autocomplete="new-password"
                    />
                    <InputError
                        :message="form.errors.password_confirmation"
                    />
                </div>

                <div class="space-y-2">
                    <Label for="role">Role</Label>
                    <Select v-model="form.role">
                        <SelectTrigger id="role">
                            <SelectValue placeholder="Select a role" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="role in roles"
                                :key="role.value"
                                :value="role.value"
                            >
                                {{ role.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.role" />
                </div>

                <div class="flex items-center space-x-3">
                    <Switch
                        id="is_active"
                        :checked="form.is_active"
                        @update:checked="(val: boolean) => (form.is_active = val)"
                    />
                    <Label for="is_active">Active</Label>
                    <InputError :message="form.errors.is_active" />
                </div>

                <div class="flex justify-end gap-4">
                    <Button
                        variant="outline"
                        type="button"
                        as-child
                    >
                        <Link href="/users">Cancel</Link>
                    </Button>
                    <Button
                        type="submit"
                        :disabled="form.processing"
                    >
                        Update User
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
