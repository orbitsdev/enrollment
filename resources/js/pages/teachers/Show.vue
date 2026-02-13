<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import ConfirmDialog from '@/components/App/ConfirmDialog.vue';
import PageHeader from '@/components/App/PageHeader.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
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
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, TeacherProfile, TeacherTraining } from '@/types';
import type { User } from '@/types/auth';

const props = defineProps<{
    teacher: User;
    profile: TeacherProfile | null;
    trainings: TeacherTraining[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Teachers', href: '/teachers' },
    { title: props.teacher.name },
];

// Training dialog
const showTrainingDialog = ref(false);
const trainingForm = useForm({
    title: '',
    type: '',
    sponsor: '',
    date_from: '',
    date_to: '',
    hours: '',
});

function submitTraining() {
    trainingForm.post(`/teachers/${props.teacher.id}/trainings`, {
        preserveScroll: true,
        onSuccess: () => {
            showTrainingDialog.value = false;
            trainingForm.reset();
        },
    });
}

// Delete training
const confirmingDelete = ref(false);
const deletingTraining = ref<TeacherTraining | null>(null);
const deleteProcessing = ref(false);

function promptDeleteTraining(training: TeacherTraining) {
    deletingTraining.value = training;
    confirmingDelete.value = true;
}

function executeDelete() {
    if (!deletingTraining.value) return;
    deleteProcessing.value = true;
    router.delete(`/teachers/${props.teacher.id}/trainings/${deletingTraining.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleteProcessing.value = false;
            confirmingDelete.value = false;
            deletingTraining.value = null;
        },
    });
}

function infoItem(label: string, value: string | number | null | undefined) {
    return { label, value: value ?? '---' };
}
</script>

<template>
    <Head :title="teacher.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <PageHeader
                :title="teacher.name"
                description="Teacher profile and training records."
            >
                <template #actions>
                    <Button as-child>
                        <Link :href="`/teachers/${teacher.id}/edit`">
                            <Pencil class="size-4" />
                            Edit Profile
                        </Link>
                    </Button>
                </template>
            </PageHeader>

            <!-- Personal & Employment Info -->
            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardContent class="space-y-3 pt-6">
                        <h3 class="text-lg font-semibold">Personal Information</h3>
                        <dl class="grid gap-2 text-sm">
                            <div v-for="item in [
                                infoItem('Name', teacher.name),
                                infoItem('Email', teacher.email),
                                infoItem('Employee ID', profile?.employee_id),
                                infoItem('Sex', profile?.sex),
                                infoItem('Birthdate', profile?.birthdate),
                                infoItem('Contact', profile?.contact_number),
                                infoItem('Address', profile?.address),
                            ]" :key="item.label">
                                <dt class="text-muted-foreground">{{ item.label }}</dt>
                                <dd class="font-medium">{{ item.value }}</dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="space-y-3 pt-6">
                        <h3 class="text-lg font-semibold">Employment & Qualifications</h3>
                        <dl class="grid gap-2 text-sm">
                            <div v-for="item in [
                                infoItem('Position', profile?.position_title),
                                infoItem('Appointment', profile?.appointment_status),
                                infoItem('Specialization', profile?.specialization),
                                infoItem('Date Hired', profile?.date_hired),
                                infoItem('Teaching Hours/Week', profile?.teaching_hours_per_week),
                                infoItem('Highest Degree', profile?.highest_degree),
                                infoItem('Course', profile?.degree_course),
                                infoItem('Major', profile?.degree_major),
                                infoItem('School Graduated', profile?.school_graduated),
                                infoItem('Year Graduated', profile?.year_graduated),
                                infoItem('PRC License', profile?.prc_license_number),
                                infoItem('PRC Validity', profile?.prc_validity),
                                infoItem('Eligibility', profile?.eligibility),
                            ]" :key="item.label">
                                <dt class="text-muted-foreground">{{ item.label }}</dt>
                                <dd class="font-medium">{{ item.value }}</dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>
            </div>

            <!-- Trainings -->
            <Card>
                <CardContent class="pt-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Trainings & Seminars</h3>
                        <Button size="sm" @click="showTrainingDialog = true">
                            <Plus class="size-4" />
                            Add Training
                        </Button>
                    </div>

                    <Table v-if="trainings.length > 0">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Title</TableHead>
                                <TableHead class="w-[100px]">Type</TableHead>
                                <TableHead>Sponsor</TableHead>
                                <TableHead class="w-[120px]">Date</TableHead>
                                <TableHead class="w-[80px]">Hours</TableHead>
                                <TableHead class="w-[60px]" />
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="t in trainings" :key="t.id">
                                <TableCell class="font-medium">{{ t.title }}</TableCell>
                                <TableCell>
                                    <Badge v-if="t.type" variant="outline">{{ t.type }}</Badge>
                                </TableCell>
                                <TableCell>{{ t.sponsor || '---' }}</TableCell>
                                <TableCell>
                                    {{ t.date_from || '---' }}
                                    <span v-if="t.date_to"> - {{ t.date_to }}</span>
                                </TableCell>
                                <TableCell>{{ t.hours ?? '---' }}</TableCell>
                                <TableCell>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="promptDeleteTraining(t)"
                                    >
                                        <Trash2 class="size-4 text-destructive" />
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <p v-else class="text-sm text-muted-foreground">
                        No training records yet.
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- Add Training Dialog -->
        <Dialog v-model:open="showTrainingDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Add Training Record</DialogTitle>
                </DialogHeader>
                <form class="space-y-4" @submit.prevent="submitTraining">
                    <div class="space-y-2">
                        <Label for="t_title">Title</Label>
                        <Input id="t_title" v-model="trainingForm.title" placeholder="Training title" />
                        <InputError :message="trainingForm.errors.title" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="t_type">Type</Label>
                            <Select v-model="trainingForm.type">
                                <SelectTrigger id="t_type">
                                    <SelectValue placeholder="Select type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="seminar">Seminar</SelectItem>
                                    <SelectItem value="workshop">Workshop</SelectItem>
                                    <SelectItem value="training">Training</SelectItem>
                                    <SelectItem value="conference">Conference</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="trainingForm.errors.type" />
                        </div>
                        <div class="space-y-2">
                            <Label for="t_hours">Hours</Label>
                            <Input id="t_hours" v-model="trainingForm.hours" type="number" step="0.5" placeholder="0" />
                            <InputError :message="trainingForm.errors.hours" />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="t_sponsor">Sponsor</Label>
                        <Input id="t_sponsor" v-model="trainingForm.sponsor" placeholder="Sponsoring organization" />
                        <InputError :message="trainingForm.errors.sponsor" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="t_date_from">Date From</Label>
                            <Input id="t_date_from" v-model="trainingForm.date_from" type="date" />
                            <InputError :message="trainingForm.errors.date_from" />
                        </div>
                        <div class="space-y-2">
                            <Label for="t_date_to">Date To</Label>
                            <Input id="t_date_to" v-model="trainingForm.date_to" type="date" />
                            <InputError :message="trainingForm.errors.date_to" />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showTrainingDialog = false">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="trainingForm.processing">
                            Add Training
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <ConfirmDialog
            :open="confirmingDelete"
            title="Remove Training"
            :description="`Are you sure you want to remove '${deletingTraining?.title}'?`"
            confirm-text="Remove"
            variant="destructive"
            :processing="deleteProcessing"
            @confirm="executeDelete"
            @cancel="confirmingDelete = false; deletingTraining = null"
        />
    </AppLayout>
</template>
