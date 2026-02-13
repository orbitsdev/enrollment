<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import ConfirmDialog from '@/components/App/ConfirmDialog.vue';
import PageHeader from '@/components/App/PageHeader.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
import { Separator } from '@/components/ui/separator';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from '@/components/ui/tabs';
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
</script>

<template>
    <Head :title="teacher.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <PageHeader
                :title="teacher.name"
                :description="`Employee ID: ${profile?.employee_id ?? 'Not set'}`"
            >
                <template #actions>
                    <Badge v-if="profile?.appointment_status" variant="outline">
                        {{ profile.appointment_status }}
                    </Badge>
                    <Button as-child>
                        <Link :href="`/teachers/${teacher.id}/edit`">
                            <Pencil class="size-4" />
                            Edit Profile
                        </Link>
                    </Button>
                </template>
            </PageHeader>

            <!-- Tabs -->
            <Tabs default-value="personal" class="w-full">
                <TabsList>
                    <TabsTrigger value="personal">Personal Info</TabsTrigger>
                    <TabsTrigger value="employment">Employment</TabsTrigger>
                    <TabsTrigger value="trainings">Trainings</TabsTrigger>
                </TabsList>

                <!-- Personal Info Tab -->
                <TabsContent value="personal" class="mt-4">
                    <div class="grid gap-6 md:grid-cols-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>Personal Details</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <dl class="space-y-3">
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-muted-foreground">Name</dt>
                                        <dd class="text-sm">{{ teacher.name }}</dd>
                                    </div>
                                    <Separator />
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-muted-foreground">Email</dt>
                                        <dd class="text-sm">{{ teacher.email }}</dd>
                                    </div>
                                    <Separator />
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-muted-foreground">Employee ID</dt>
                                        <dd class="text-sm font-mono">{{ profile?.employee_id ?? '--' }}</dd>
                                    </div>
                                    <Separator />
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-muted-foreground">Sex</dt>
                                        <dd class="text-sm capitalize">{{ profile?.sex ?? '--' }}</dd>
                                    </div>
                                    <Separator />
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-muted-foreground">Birthdate</dt>
                                        <dd class="text-sm">{{ profile?.birthdate ?? '--' }}</dd>
                                    </div>
                                </dl>
                            </CardContent>
                        </Card>

                        <div class="space-y-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Contact Information</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <dl class="space-y-3">
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-muted-foreground">Contact Number</dt>
                                            <dd class="text-sm">{{ profile?.contact_number ?? '--' }}</dd>
                                        </div>
                                        <Separator />
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-muted-foreground">Address</dt>
                                            <dd class="text-sm text-right max-w-[200px]">{{ profile?.address ?? '--' }}</dd>
                                        </div>
                                    </dl>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </TabsContent>

                <!-- Employment Tab -->
                <TabsContent value="employment" class="mt-4">
                    <div class="grid gap-6 md:grid-cols-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>Employment Details</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <dl class="space-y-3">
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-muted-foreground">Position</dt>
                                        <dd class="text-sm">{{ profile?.position_title ?? '--' }}</dd>
                                    </div>
                                    <Separator />
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-muted-foreground">Appointment Status</dt>
                                        <dd class="text-sm">
                                            <Badge v-if="profile?.appointment_status" variant="outline">
                                                {{ profile.appointment_status }}
                                            </Badge>
                                            <span v-else>--</span>
                                        </dd>
                                    </div>
                                    <Separator />
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-muted-foreground">Specialization</dt>
                                        <dd class="text-sm">{{ profile?.specialization ?? '--' }}</dd>
                                    </div>
                                    <Separator />
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-muted-foreground">Date Hired</dt>
                                        <dd class="text-sm">{{ profile?.date_hired ?? '--' }}</dd>
                                    </div>
                                    <Separator />
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-muted-foreground">Teaching Hours/Week</dt>
                                        <dd class="text-sm">{{ profile?.teaching_hours_per_week ?? '--' }}</dd>
                                    </div>
                                </dl>
                            </CardContent>
                        </Card>

                        <div class="space-y-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Education</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <dl class="space-y-3">
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-muted-foreground">Highest Degree</dt>
                                            <dd class="text-sm">{{ profile?.highest_degree ?? '--' }}</dd>
                                        </div>
                                        <Separator />
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-muted-foreground">Course</dt>
                                            <dd class="text-sm">{{ profile?.degree_course ?? '--' }}</dd>
                                        </div>
                                        <Separator />
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-muted-foreground">Major</dt>
                                            <dd class="text-sm">{{ profile?.degree_major ?? '--' }}</dd>
                                        </div>
                                        <Separator />
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-muted-foreground">School Graduated</dt>
                                            <dd class="text-sm text-right max-w-[200px]">{{ profile?.school_graduated ?? '--' }}</dd>
                                        </div>
                                        <Separator />
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-muted-foreground">Year Graduated</dt>
                                            <dd class="text-sm">{{ profile?.year_graduated ?? '--' }}</dd>
                                        </div>
                                    </dl>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>Credentials</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <dl class="space-y-3">
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-muted-foreground">PRC License</dt>
                                            <dd class="text-sm font-mono">{{ profile?.prc_license_number ?? '--' }}</dd>
                                        </div>
                                        <Separator />
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-muted-foreground">PRC Validity</dt>
                                            <dd class="text-sm">{{ profile?.prc_validity ?? '--' }}</dd>
                                        </div>
                                        <Separator />
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-muted-foreground">Eligibility</dt>
                                            <dd class="text-sm">{{ profile?.eligibility ?? '--' }}</dd>
                                        </div>
                                    </dl>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </TabsContent>

                <!-- Trainings Tab -->
                <TabsContent value="trainings" class="mt-4">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0">
                            <CardTitle>Trainings & Seminars</CardTitle>
                            <Button size="sm" @click="showTrainingDialog = true">
                                <Plus class="size-4" />
                                Add Training
                            </Button>
                        </CardHeader>
                        <CardContent>
                            <div class="rounded-md border">
                                <Table>
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
                                        <template v-if="trainings.length > 0">
                                            <TableRow v-for="t in trainings" :key="t.id">
                                                <TableCell class="font-medium">{{ t.title }}</TableCell>
                                                <TableCell>
                                                    <Badge v-if="t.type" variant="outline">{{ t.type }}</Badge>
                                                </TableCell>
                                                <TableCell>{{ t.sponsor || '--' }}</TableCell>
                                                <TableCell>
                                                    {{ t.date_from || '--' }}
                                                    <span v-if="t.date_to"> - {{ t.date_to }}</span>
                                                </TableCell>
                                                <TableCell>{{ t.hours ?? '--' }}</TableCell>
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
                                        </template>
                                        <template v-else>
                                            <TableRow>
                                                <TableCell :colspan="6" class="h-24 text-center text-muted-foreground">
                                                    No training records yet.
                                                </TableCell>
                                            </TableRow>
                                        </template>
                                    </TableBody>
                                </Table>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>
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
