<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Printer } from 'lucide-vue-next';
import { ref } from 'vue';
import ConfirmDialog from '@/components/App/ConfirmDialog.vue';
import PageHeader from '@/components/App/PageHeader.vue';
import StatusBadge from '@/components/App/StatusBadge.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type {
    BreadcrumbItem,
    Enrollment,
    Grade,
    Section,
    Strand,
    Student,
    Subject,
} from '@/types';

type EnrollmentWithRelations = Enrollment & {
    student: Student;
    section: Section & { strand: Strand };
    subjects: Subject[];
    grades: Grade[];
};

const props = defineProps<{
    enrollment: EnrollmentWithRelations;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Enrollment', href: '/enrollment' },
    { title: `Enrollment #${props.enrollment.id}` },
];

const studentName = props.enrollment.student.full_name
    ?? `${props.enrollment.student.last_name}, ${props.enrollment.student.first_name}`;

// Status update
const showConfirm = ref(false);
const confirmAction = ref<'enrolled' | 'dropped' | 'transferred'>('enrolled');
const statusProcessing = ref(false);

const actionLabels: Record<string, { title: string; description: string; variant: 'default' | 'destructive' }> = {
    enrolled: {
        title: 'Confirm Enrollment',
        description: 'Are you sure you want to confirm this enrollment? The student will be officially enrolled.',
        variant: 'default',
    },
    dropped: {
        title: 'Drop Student',
        description: 'Are you sure you want to drop this student? This will change their enrollment status to dropped.',
        variant: 'destructive',
    },
    transferred: {
        title: 'Transfer Student',
        description: 'Are you sure you want to mark this student as transferred?',
        variant: 'destructive',
    },
};

function promptStatusChange(action: 'enrolled' | 'dropped' | 'transferred') {
    confirmAction.value = action;
    showConfirm.value = true;
}

function executeStatusChange() {
    statusProcessing.value = true;
    router.put(
        `/enrollment/${props.enrollment.id}/status`,
        { status: confirmAction.value },
        {
            preserveScroll: true,
            onFinish: () => {
                statusProcessing.value = false;
                showConfirm.value = false;
            },
        },
    );
}

function getGradeForSubject(subjectId: number): Grade | undefined {
    return props.enrollment.grades?.find((g) => g.subject_id === subjectId);
}

function formatDate(dateStr: string | null): string {
    if (!dateStr) return '--';
    return new Date(dateStr).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}
</script>

<template>
    <Head :title="`Enrollment: ${studentName}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <PageHeader
                :title="`Enrollment: ${studentName}`"
                description="View enrollment details and manage status."
            >
                <template #actions>
                    <Button variant="outline" as-child>
                        <a :href="`/enrollment/${enrollment.id}/print`" target="_blank">
                            <Printer class="size-4" />
                            Print Enrollment Slip
                        </a>
                    </Button>
                </template>
            </PageHeader>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Student Info -->
                <Card>
                    <CardHeader>
                        <CardTitle>Student Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-muted-foreground">LRN</dt>
                                <dd class="text-sm font-mono">{{ enrollment.student.lrn }}</dd>
                            </div>
                            <Separator />
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-muted-foreground">Name</dt>
                                <dd class="text-sm">
                                    <Link
                                        :href="`/students/${enrollment.student.id}`"
                                        class="font-medium hover:underline"
                                    >
                                        {{ studentName }}
                                    </Link>
                                </dd>
                            </div>
                            <Separator />
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-muted-foreground">Gender</dt>
                                <dd class="text-sm capitalize">{{ enrollment.student.gender }}</dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>

                <!-- Enrollment Info -->
                <Card>
                    <CardHeader>
                        <CardTitle>Enrollment Details</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-muted-foreground">Section</dt>
                                <dd class="text-sm">
                                    <Link
                                        :href="`/sections/${enrollment.section.id}`"
                                        class="font-medium hover:underline"
                                    >
                                        {{ enrollment.section.name }}
                                    </Link>
                                </dd>
                            </div>
                            <Separator />
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-muted-foreground">Strand</dt>
                                <dd>
                                    <Badge variant="outline">
                                        {{ enrollment.section.strand?.code ?? '--' }}
                                    </Badge>
                                </dd>
                            </div>
                            <Separator />
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-muted-foreground">Grade Level</dt>
                                <dd class="text-sm">Grade {{ enrollment.section.grade_level }}</dd>
                            </div>
                            <Separator />
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-muted-foreground">Status</dt>
                                <dd><StatusBadge :status="enrollment.status" /></dd>
                            </div>
                            <Separator />
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-muted-foreground">Date Enrolled</dt>
                                <dd class="text-sm">{{ formatDate(enrollment.enrolled_at) }}</dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>
            </div>

            <!-- Status Actions -->
            <Card>
                <CardHeader>
                    <CardTitle>Status Actions</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-wrap gap-3">
                        <Button
                            v-if="enrollment.status === 'pending'"
                            @click="promptStatusChange('enrolled')"
                        >
                            Confirm Enrollment
                        </Button>
                        <Button
                            v-if="enrollment.status === 'enrolled' || enrollment.status === 'pending'"
                            variant="destructive"
                            @click="promptStatusChange('dropped')"
                        >
                            Drop Student
                        </Button>
                        <Button
                            v-if="enrollment.status === 'enrolled' || enrollment.status === 'pending'"
                            variant="outline"
                            @click="promptStatusChange('transferred')"
                        >
                            Transfer Student
                        </Button>
                        <p
                            v-if="enrollment.status === 'dropped' || enrollment.status === 'transferred'"
                            class="text-sm text-muted-foreground"
                        >
                            This enrollment is {{ enrollment.status }}. No further status changes available.
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Subject List -->
            <Card>
                <CardHeader>
                    <CardTitle>Subjects</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Code</TableHead>
                                    <TableHead>Subject</TableHead>
                                    <TableHead class="w-[100px]">Type</TableHead>
                                    <TableHead class="w-[100px] text-center">Final Grade</TableHead>
                                    <TableHead class="w-[100px] text-center">Remarks</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <template v-if="enrollment.subjects && enrollment.subjects.length > 0">
                                    <TableRow
                                        v-for="subject in enrollment.subjects"
                                        :key="subject.id"
                                    >
                                        <TableCell class="font-mono text-sm">
                                            {{ subject.code }}
                                        </TableCell>
                                        <TableCell class="font-medium">
                                            {{ subject.name }}
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="outline" class="capitalize">
                                                {{ subject.type }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell class="text-center">
                                            {{ getGradeForSubject(subject.id)?.final_grade ?? '--' }}
                                        </TableCell>
                                        <TableCell class="text-center">
                                            <StatusBadge
                                                v-if="getGradeForSubject(subject.id)?.remarks"
                                                :status="getGradeForSubject(subject.id)!.remarks!"
                                            />
                                            <span v-else class="text-muted-foreground">--</span>
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <template v-else>
                                    <TableRow>
                                        <TableCell
                                            :colspan="5"
                                            class="h-24 text-center text-muted-foreground"
                                        >
                                            No subjects assigned.
                                        </TableCell>
                                    </TableRow>
                                </template>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>

        <ConfirmDialog
            :open="showConfirm"
            :title="actionLabels[confirmAction]?.title ?? 'Confirm'"
            :description="actionLabels[confirmAction]?.description ?? ''"
            :confirm-text="actionLabels[confirmAction]?.title ?? 'Confirm'"
            :variant="actionLabels[confirmAction]?.variant ?? 'default'"
            :processing="statusProcessing"
            @confirm="executeStatusChange"
            @cancel="showConfirm = false"
        />
    </AppLayout>
</template>
