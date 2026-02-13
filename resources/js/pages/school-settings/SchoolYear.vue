<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { CheckCircle, Plus, X } from 'lucide-vue-next';
import { ref } from 'vue';
import ConfirmDialog from '@/components/App/ConfirmDialog.vue';
import PageHeader from '@/components/App/PageHeader.vue';
import StatusBadge from '@/components/App/StatusBadge.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, SchoolYear } from '@/types';

defineProps<{
    schoolYears: SchoolYear[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'School Settings', href: '/school-settings' },
    { title: 'School Years' },
];

const showCreateForm = ref(false);

const form = useForm({
    name: '',
});

function createSchoolYear() {
    form.post('/school-years', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showCreateForm.value = false;
        },
    });
}

function setActiveSchoolYear(schoolYearId: number) {
    router.post(
        `/school-years/${schoolYearId}/activate`,
        {},
        { preserveScroll: true },
    );
}

function setActiveSemester(semesterId: number) {
    router.post(
        `/semesters/${semesterId}/activate`,
        {},
        { preserveScroll: true },
    );
}

function toggleEnrollment(semesterId: number, currentState: boolean) {
    router.post(
        `/semesters/${semesterId}/toggle-enrollment`,
        { enrollment_open: !currentState },
        { preserveScroll: true },
    );
}

// Confirm dialog for activating school year
const confirmingActivate = ref(false);
const activateTarget = ref<{ id: number; name: string } | null>(null);

function promptActivate(sy: SchoolYear) {
    activateTarget.value = { id: sy.id, name: sy.name };
    confirmingActivate.value = true;
}

function executeActivate() {
    if (!activateTarget.value) return;
    setActiveSchoolYear(activateTarget.value.id);
    confirmingActivate.value = false;
    activateTarget.value = null;
}
</script>

<template>
    <Head title="School Years" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <PageHeader
                title="School Years"
                description="Manage school years and semesters."
            >
                <template #actions>
                    <Button @click="showCreateForm = !showCreateForm">
                        <Plus class="mr-2 size-4" />
                        Add School Year
                    </Button>
                </template>
            </PageHeader>

            <!-- Inline Create Form -->
            <Card v-if="showCreateForm">
                <CardHeader>
                    <CardTitle>New School Year</CardTitle>
                </CardHeader>
                <CardContent>
                    <form
                        class="flex items-end gap-4"
                        @submit.prevent="createSchoolYear"
                    >
                        <div class="flex-1 space-y-2">
                            <Label for="sy_name">Name</Label>
                            <Input
                                id="sy_name"
                                v-model="form.name"
                                type="text"
                                placeholder="e.g. 2024-2025"
                            />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="flex gap-2">
                            <Button
                                type="submit"
                                :disabled="form.processing"
                            >
                                Create
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                @click="
                                    showCreateForm = false;
                                    form.reset();
                                    form.clearErrors();
                                "
                            >
                                <X class="size-4" />
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- School Years List -->
            <div v-if="schoolYears.length === 0" class="py-12 text-center text-muted-foreground">
                No school years found. Create one to get started.
            </div>

            <div class="space-y-4">
                <Card v-for="sy in schoolYears" :key="sy.id">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <CardTitle>{{ sy.name }}</CardTitle>
                                <Badge v-if="sy.is_active" variant="default">
                                    Active
                                </Badge>
                            </div>
                            <Button
                                v-if="!sy.is_active"
                                variant="outline"
                                size="sm"
                                @click="promptActivate(sy)"
                            >
                                <CheckCircle class="mr-2 size-4" />
                                Set Active
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div
                                v-for="semester in sy.semesters"
                                :key="semester.id"
                                class="flex items-center justify-between rounded-lg border p-4"
                            >
                                <div class="flex items-center gap-3">
                                    <span class="font-medium">
                                        {{ semester.number === 1 ? '1st' : '2nd' }} Semester
                                    </span>
                                    <Badge
                                        v-if="semester.is_active"
                                        variant="default"
                                    >
                                        Active
                                    </Badge>
                                    <StatusBadge
                                        :status="semester.enrollment_open ? 'open' : 'closed'"
                                    />
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center gap-2">
                                        <Label
                                            :for="`enrollment-${semester.id}`"
                                            class="text-sm text-muted-foreground"
                                        >
                                            Enrollment
                                        </Label>
                                        <Switch
                                            :id="`enrollment-${semester.id}`"
                                            :model-value="semester.enrollment_open"
                                            @update:model-value="toggleEnrollment(semester.id, semester.enrollment_open)"
                                        />
                                    </div>
                                    <Button
                                        v-if="!semester.is_active && sy.is_active"
                                        variant="outline"
                                        size="sm"
                                        @click="setActiveSemester(semester.id)"
                                    >
                                        Set Active Semester
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <ConfirmDialog
            :open="confirmingActivate"
            title="Set Active School Year"
            :description="`Are you sure you want to set '${activateTarget?.name}' as the active school year? This will deactivate the current active school year.`"
            confirm-text="Activate"
            @confirm="executeActivate"
            @cancel="
                confirmingActivate = false;
                activateTarget = null;
            "
        />
    </AppLayout>
</template>
