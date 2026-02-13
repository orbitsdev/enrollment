<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import ConfirmDialog from '@/components/App/ConfirmDialog.vue';
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
    DialogDescription,
    DialogHeader,
    DialogScrollContent,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Dialog as TrainingDialog,
    DialogContent as TrainingDialogContent,
    DialogFooter as TrainingDialogFooter,
    DialogHeader as TrainingDialogHeader,
    DialogTitle as TrainingDialogTitle,
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
import type { TeacherProfile, TeacherTraining } from '@/types';
import type { User } from '@/types/auth';

type TeacherUser = User & {
    is_active: boolean;
    teacher_profile: TeacherProfile | null;
};

const props = defineProps<{
    open: boolean;
    teacher: TeacherUser | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

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
    if (!props.teacher) return;
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
    if (!deletingTraining.value || !props.teacher) return;
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
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogScrollContent class="sm:max-w-4xl">
            <DialogHeader>
                <DialogTitle>{{ teacher?.name }}</DialogTitle>
                <DialogDescription>
                    Employee ID: {{ teacher?.teacher_profile?.employee_id ?? 'Not set' }}
                    <Badge v-if="teacher?.teacher_profile?.appointment_status" variant="outline" class="ml-2">
                        {{ teacher.teacher_profile.appointment_status }}
                    </Badge>
                </DialogDescription>
            </DialogHeader>

            <Tabs v-if="teacher" default-value="personal" class="w-full">
                <TabsList>
                    <TabsTrigger value="personal">Personal Info</TabsTrigger>
                    <TabsTrigger value="employment">Employment</TabsTrigger>
                    <TabsTrigger value="trainings">Trainings</TabsTrigger>
                </TabsList>

                <!-- Personal Info Tab -->
                <TabsContent value="personal" class="mt-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <Card>
                            <CardHeader class="pb-3">
                                <CardTitle class="text-base">Personal Details</CardTitle>
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
                                        <dd class="text-sm font-mono">{{ teacher.teacher_profile?.employee_id ?? '--' }}</dd>
                                    </div>
                                    <Separator />
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-muted-foreground">Sex</dt>
                                        <dd class="text-sm capitalize">{{ teacher.teacher_profile?.sex ?? '--' }}</dd>
                                    </div>
                                    <Separator />
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-muted-foreground">Birthdate</dt>
                                        <dd class="text-sm">{{ teacher.teacher_profile?.birthdate ?? '--' }}</dd>
                                    </div>
                                </dl>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader class="pb-3">
                                <CardTitle class="text-base">Contact Information</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <dl class="space-y-3">
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-muted-foreground">Contact Number</dt>
                                        <dd class="text-sm">{{ teacher.teacher_profile?.contact_number ?? '--' }}</dd>
                                    </div>
                                    <Separator />
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-muted-foreground">Address</dt>
                                        <dd class="text-sm text-right max-w-[200px]">{{ teacher.teacher_profile?.address ?? '--' }}</dd>
                                    </div>
                                </dl>
                            </CardContent>
                        </Card>
                    </div>
                </TabsContent>

                <!-- Employment Tab -->
                <TabsContent value="employment" class="mt-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <Card>
                            <CardHeader class="pb-3">
                                <CardTitle class="text-base">Employment Details</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <dl class="space-y-3">
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-muted-foreground">Position</dt>
                                        <dd class="text-sm">{{ teacher.teacher_profile?.position_title ?? '--' }}</dd>
                                    </div>
                                    <Separator />
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-muted-foreground">Appointment Status</dt>
                                        <dd class="text-sm">
                                            <Badge v-if="teacher.teacher_profile?.appointment_status" variant="outline">
                                                {{ teacher.teacher_profile.appointment_status }}
                                            </Badge>
                                            <span v-else>--</span>
                                        </dd>
                                    </div>
                                    <Separator />
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-muted-foreground">Specialization</dt>
                                        <dd class="text-sm">{{ teacher.teacher_profile?.specialization ?? '--' }}</dd>
                                    </div>
                                    <Separator />
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-muted-foreground">Date Hired</dt>
                                        <dd class="text-sm">{{ teacher.teacher_profile?.date_hired ?? '--' }}</dd>
                                    </div>
                                    <Separator />
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-muted-foreground">Teaching Hours/Week</dt>
                                        <dd class="text-sm">{{ teacher.teacher_profile?.teaching_hours_per_week ?? '--' }}</dd>
                                    </div>
                                </dl>
                            </CardContent>
                        </Card>

                        <div class="space-y-4">
                            <Card>
                                <CardHeader class="pb-3">
                                    <CardTitle class="text-base">Education</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <dl class="space-y-3">
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-muted-foreground">Highest Degree</dt>
                                            <dd class="text-sm">{{ teacher.teacher_profile?.highest_degree ?? '--' }}</dd>
                                        </div>
                                        <Separator />
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-muted-foreground">Course</dt>
                                            <dd class="text-sm">{{ teacher.teacher_profile?.degree_course ?? '--' }}</dd>
                                        </div>
                                        <Separator />
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-muted-foreground">Major</dt>
                                            <dd class="text-sm">{{ teacher.teacher_profile?.degree_major ?? '--' }}</dd>
                                        </div>
                                        <Separator />
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-muted-foreground">School</dt>
                                            <dd class="text-sm text-right max-w-[180px]">{{ teacher.teacher_profile?.school_graduated ?? '--' }}</dd>
                                        </div>
                                        <Separator />
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-muted-foreground">Year Graduated</dt>
                                            <dd class="text-sm">{{ teacher.teacher_profile?.year_graduated ?? '--' }}</dd>
                                        </div>
                                    </dl>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader class="pb-3">
                                    <CardTitle class="text-base">Credentials</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <dl class="space-y-3">
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-muted-foreground">PRC License</dt>
                                            <dd class="text-sm font-mono">{{ teacher.teacher_profile?.prc_license_number ?? '--' }}</dd>
                                        </div>
                                        <Separator />
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-muted-foreground">PRC Validity</dt>
                                            <dd class="text-sm">{{ teacher.teacher_profile?.prc_validity ?? '--' }}</dd>
                                        </div>
                                        <Separator />
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-muted-foreground">Eligibility</dt>
                                            <dd class="text-sm">{{ teacher.teacher_profile?.eligibility ?? '--' }}</dd>
                                        </div>
                                    </dl>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </TabsContent>

                <!-- Trainings Tab -->
                <TabsContent value="trainings" class="mt-4">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-sm font-medium">Trainings & Seminars</h3>
                        <Button size="sm" @click="showTrainingDialog = true">
                            <Plus class="size-4" />
                            Add Training
                        </Button>
                    </div>

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
                                <template v-if="teacher.teacher_profile?.trainings && teacher.teacher_profile.trainings.length > 0">
                                    <TableRow v-for="t in teacher.teacher_profile.trainings" :key="t.id">
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
                </TabsContent>
            </Tabs>
        </DialogScrollContent>
    </Dialog>

    <!-- Add Training Dialog -->
    <TrainingDialog v-model:open="showTrainingDialog">
        <TrainingDialogContent>
            <TrainingDialogHeader>
                <TrainingDialogTitle>Add Training Record</TrainingDialogTitle>
            </TrainingDialogHeader>
            <form class="space-y-4" @submit.prevent="submitTraining">
                <div class="space-y-2">
                    <Label for="show_t_title">Title</Label>
                    <Input id="show_t_title" v-model="trainingForm.title" placeholder="Training title" />
                    <InputError :message="trainingForm.errors.title" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="show_t_type">Type</Label>
                        <Select v-model="trainingForm.type">
                            <SelectTrigger id="show_t_type">
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
                        <Label for="show_t_hours">Hours</Label>
                        <Input id="show_t_hours" v-model="trainingForm.hours" type="number" step="0.5" placeholder="0" />
                        <InputError :message="trainingForm.errors.hours" />
                    </div>
                </div>
                <div class="space-y-2">
                    <Label for="show_t_sponsor">Sponsor</Label>
                    <Input id="show_t_sponsor" v-model="trainingForm.sponsor" placeholder="Sponsoring organization" />
                    <InputError :message="trainingForm.errors.sponsor" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="show_t_date_from">Date From</Label>
                        <Input id="show_t_date_from" v-model="trainingForm.date_from" type="date" />
                        <InputError :message="trainingForm.errors.date_from" />
                    </div>
                    <div class="space-y-2">
                        <Label for="show_t_date_to">Date To</Label>
                        <Input id="show_t_date_to" v-model="trainingForm.date_to" type="date" />
                        <InputError :message="trainingForm.errors.date_to" />
                    </div>
                </div>
                <TrainingDialogFooter>
                    <Button type="button" variant="outline" @click="showTrainingDialog = false">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="trainingForm.processing">
                        Add Training
                    </Button>
                </TrainingDialogFooter>
            </form>
        </TrainingDialogContent>
    </TrainingDialog>

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
</template>
