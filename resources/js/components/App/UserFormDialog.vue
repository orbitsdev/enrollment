<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogScrollContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
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
import type { User } from '@/types';

type UserWithRole = User & { role: string; is_active: boolean };

const props = defineProps<{
    open: boolean;
    user?: UserWithRole | null;
    roles: Array<{ value: string; label: string }>;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '',
    is_active: true,
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            if (props.user) {
                form.name = props.user.name;
                form.email = props.user.email;
                form.password = '';
                form.password_confirmation = '';
                form.role = props.user.role;
                form.is_active = props.user.is_active;
            } else {
                form.reset();
                form.is_active = true;
            }
            form.clearErrors();
        }
    },
);

function submit() {
    if (props.user) {
        form.put(`/users/${props.user.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                emit('update:open', false);
            },
        });
    } else {
        form.post('/users', {
            preserveScroll: true,
            onSuccess: () => {
                emit('update:open', false);
            },
        });
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogScrollContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>{{ user ? 'Edit User' : 'Add User' }}</DialogTitle>
                <DialogDescription>
                    {{ user ? 'Update user information and role.' : 'Add a new user to the system.' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-2">
                    <Label for="dlg_user_name">Name</Label>
                    <Input
                        id="dlg_user_name"
                        v-model="form.name"
                        type="text"
                        placeholder="Full name"
                        autocomplete="name"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="space-y-2">
                    <Label for="dlg_user_email">Email</Label>
                    <Input
                        id="dlg_user_email"
                        v-model="form.email"
                        type="email"
                        placeholder="Email address"
                        autocomplete="email"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="space-y-2">
                    <Label for="dlg_user_password">Password</Label>
                    <Input
                        id="dlg_user_password"
                        v-model="form.password"
                        type="password"
                        :placeholder="user ? 'Leave blank to keep current' : 'Password'"
                        autocomplete="new-password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="space-y-2">
                    <Label for="dlg_user_password_confirmation">Confirm Password</Label>
                    <Input
                        id="dlg_user_password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        :placeholder="user ? 'Confirm new password' : 'Confirm password'"
                        autocomplete="new-password"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <div class="space-y-2">
                    <Label for="dlg_user_role">Role</Label>
                    <Select v-model="form.role">
                        <SelectTrigger id="dlg_user_role">
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

                <div v-if="user" class="flex items-center space-x-3">
                    <Switch
                        id="dlg_user_is_active"
                        :model-value="form.is_active"
                        @update:model-value="(val: boolean) => (form.is_active = val)"
                    />
                    <Label for="dlg_user_is_active">Active</Label>
                    <InputError :message="form.errors.is_active" />
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="emit('update:open', false)"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ user ? 'Update User' : 'Create User' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogScrollContent>
    </Dialog>
</template>
