<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Lock, Loader2, Save, Unlock } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ConfirmDialog from '@/components/App/ConfirmDialog.vue';
import PageHeader from '@/components/App/PageHeader.vue';
import StatusBadge from '@/components/App/StatusBadge.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Enrollment, Grade, Section, Student, Subject } from '@/types';

type GradeWithEnrollment = Grade & {
    enrollment: Enrollment & { student: Student };
};

const props = defineProps<{
    section: Section;
    subject: Subject;
    grades: GradeWithEnrollment[];
    settings: {
        midterm_weight: number;
        finals_weight: number;
        passing_grade: number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Grades', href: '/grades' },
    { title: props.section.name },
    { title: props.subject.code },
];

// Form with all grade entries
const form = useForm({
    grades: props.grades.map((g) => ({
        id: g.id,
        midterm: g.midterm,
        finals: g.finals,
    })),
});

// Computed final grades for each entry (client-side)
const computedGrades = computed(() => {
    return form.grades.map((entry) => {
        const midterm = entry.midterm;
        const finals = entry.finals;

        if (midterm === null || finals === null || midterm === undefined || finals === undefined) {
            return { final_grade: null, remarks: null };
        }

        const midtermNum = Number(midterm);
        const finalsNum = Number(finals);

        if (isNaN(midtermNum) || isNaN(finalsNum)) {
            return { final_grade: null, remarks: null };
        }

        const midtermWeight = props.settings.midterm_weight / 100;
        const finalsWeight = props.settings.finals_weight / 100;
        const finalGrade = Math.round((midtermNum * midtermWeight + finalsNum * finalsWeight) * 100) / 100;
        const remarks = finalGrade >= props.settings.passing_grade ? 'passed' : 'failed';

        return { final_grade: finalGrade, remarks };
    });
});

// Check if any grades are locked
const hasLockedGrades = computed(() => props.grades.some((g) => g.is_locked));
const allLocked = computed(() => props.grades.length > 0 && props.grades.every((g) => g.is_locked));

function getOriginalGrade(index: number): GradeWithEnrollment {
    return props.grades[index];
}

function studentName(student: Student): string {
    return student.full_name
        ?? `${student.last_name}, ${student.first_name} ${student.middle_name ?? ''}`.trim();
}

// Save grades
function saveGrades() {
    form.put(`/grades/${props.section.id}/${props.subject.id}`, {
        preserveScroll: true,
    });
}

// Lock/Unlock grades
const showLockConfirm = ref(false);
const showUnlockConfirm = ref(false);
const lockProcessing = ref(false);

function lockGrades() {
    lockProcessing.value = true;
    router.put(
        `/grades/${props.section.id}/${props.subject.id}/lock`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                lockProcessing.value = false;
                showLockConfirm.value = false;
            },
        },
    );
}

function unlockGrades() {
    lockProcessing.value = true;
    router.put(
        `/grades/${props.section.id}/${props.subject.id}/unlock`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                lockProcessing.value = false;
                showUnlockConfirm.value = false;
            },
        },
    );
}

// Stats
const totalStudents = computed(() => props.grades.length);
const gradesCompleted = computed(() =>
    computedGrades.value.filter((g) => g.final_grade !== null).length,
);
const passingCount = computed(() =>
    computedGrades.value.filter((g) => g.remarks === 'passed').length,
);
const failingCount = computed(() =>
    computedGrades.value.filter((g) => g.remarks === 'failed').length,
);
</script>

<template>
    <Head :title="`Grades: ${section.name} - ${subject.code}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <PageHeader
                :title="`${subject.code} - ${subject.name}`"
                :description="`Section: ${section.name} | Grade ${section.grade_level}`"
            >
                <template #actions>
                    <Button variant="outline" as-child>
                        <Link href="/grades">
                            <ArrowLeft class="size-4" />
                            Back to Grades
                        </Link>
                    </Button>
                </template>
            </PageHeader>

            <!-- Stats Bar -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardContent class="pt-6">
                        <div class="text-2xl font-bold">{{ totalStudents }}</div>
                        <p class="text-xs text-muted-foreground">Total Students</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-6">
                        <div class="text-2xl font-bold">{{ gradesCompleted }}/{{ totalStudents }}</div>
                        <p class="text-xs text-muted-foreground">Grades Completed</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-6">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ passingCount }}</div>
                        <p class="text-xs text-muted-foreground">Passing</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-6">
                        <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ failingCount }}</div>
                        <p class="text-xs text-muted-foreground">Failing</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Grade Weights Info -->
            <div class="flex items-center gap-4 text-sm text-muted-foreground">
                <span>Weights: Midterm {{ settings.midterm_weight }}% | Finals {{ settings.finals_weight }}%</span>
                <span class="text-muted-foreground/50">|</span>
                <span>Passing Grade: {{ settings.passing_grade }}</span>
                <span v-if="allLocked" class="ml-auto flex items-center gap-1 text-yellow-600 dark:text-yellow-400">
                    <Lock class="size-4" />
                    All grades are locked
                </span>
            </div>

            <!-- Grade Entry Table -->
            <form @submit.prevent="saveGrades">
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle>Grade Entry</CardTitle>
                            <div class="flex gap-2">
                                <Button
                                    v-if="!allLocked"
                                    type="submit"
                                    :disabled="form.processing"
                                >
                                    <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                                    <Save v-else class="size-4" />
                                    Save Grades
                                </Button>
                                <Button
                                    v-if="!allLocked && gradesCompleted > 0"
                                    type="button"
                                    variant="outline"
                                    @click="showLockConfirm = true"
                                >
                                    <Lock class="size-4" />
                                    Lock Grades
                                </Button>
                                <Button
                                    v-if="allLocked"
                                    type="button"
                                    variant="outline"
                                    @click="showUnlockConfirm = true"
                                >
                                    <Unlock class="size-4" />
                                    Unlock Grades
                                </Button>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <!-- General form error -->
                        <InputError :message="form.errors.grades" class="mb-4" />

                        <div class="rounded-md border overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead class="w-[50px]">#</TableHead>
                                        <TableHead class="w-[130px]">LRN</TableHead>
                                        <TableHead>Student Name</TableHead>
                                        <TableHead class="w-[120px] text-center">Midterm</TableHead>
                                        <TableHead class="w-[120px] text-center">Finals</TableHead>
                                        <TableHead class="w-[120px] text-center">Final Grade</TableHead>
                                        <TableHead class="w-[100px] text-center">Remarks</TableHead>
                                        <TableHead class="w-[60px] text-center">Status</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <template v-if="grades.length > 0">
                                        <TableRow
                                            v-for="(gradeEntry, index) in form.grades"
                                            :key="getOriginalGrade(index).id"
                                        >
                                            <TableCell class="font-medium">
                                                {{ index + 1 }}
                                            </TableCell>
                                            <TableCell class="font-mono text-sm">
                                                {{ getOriginalGrade(index).enrollment.student.lrn }}
                                            </TableCell>
                                            <TableCell class="font-medium">
                                                {{ studentName(getOriginalGrade(index).enrollment.student) }}
                                            </TableCell>
                                            <TableCell class="text-center">
                                                <Input
                                                    v-model.number="gradeEntry.midterm"
                                                    type="number"
                                                    min="0"
                                                    max="100"
                                                    step="0.01"
                                                    class="h-8 w-20 text-center mx-auto"
                                                    :disabled="getOriginalGrade(index).is_locked"
                                                    placeholder="--"
                                                />
                                            </TableCell>
                                            <TableCell class="text-center">
                                                <Input
                                                    v-model.number="gradeEntry.finals"
                                                    type="number"
                                                    min="0"
                                                    max="100"
                                                    step="0.01"
                                                    class="h-8 w-20 text-center mx-auto"
                                                    :disabled="getOriginalGrade(index).is_locked"
                                                    placeholder="--"
                                                />
                                            </TableCell>
                                            <TableCell class="text-center">
                                                <span
                                                    class="font-semibold"
                                                    :class="{
                                                        'text-green-600 dark:text-green-400': computedGrades[index]?.remarks === 'passed',
                                                        'text-red-600 dark:text-red-400': computedGrades[index]?.remarks === 'failed',
                                                    }"
                                                >
                                                    {{ computedGrades[index]?.final_grade ?? '--' }}
                                                </span>
                                            </TableCell>
                                            <TableCell class="text-center">
                                                <StatusBadge
                                                    v-if="computedGrades[index]?.remarks"
                                                    :status="computedGrades[index].remarks!"
                                                />
                                                <span v-else class="text-muted-foreground">--</span>
                                            </TableCell>
                                            <TableCell class="text-center">
                                                <Lock
                                                    v-if="getOriginalGrade(index).is_locked"
                                                    class="size-4 text-yellow-500 mx-auto"
                                                />
                                            </TableCell>
                                        </TableRow>
                                    </template>
                                    <template v-else>
                                        <TableRow>
                                            <TableCell
                                                :colspan="8"
                                                class="h-24 text-center text-muted-foreground"
                                            >
                                                No students enrolled in this section.
                                            </TableCell>
                                        </TableRow>
                                    </template>
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>
            </form>
        </div>

        <!-- Lock Confirmation Dialog -->
        <ConfirmDialog
            :open="showLockConfirm"
            title="Lock Grades"
            description="Are you sure you want to lock all grades for this subject? Once locked, grades cannot be modified without admin approval."
            confirm-text="Lock Grades"
            variant="default"
            :processing="lockProcessing"
            @confirm="lockGrades"
            @cancel="showLockConfirm = false"
        />

        <!-- Unlock Confirmation Dialog -->
        <ConfirmDialog
            :open="showUnlockConfirm"
            title="Unlock Grades"
            description="Are you sure you want to unlock grades? This action is typically restricted to administrators."
            confirm-text="Unlock Grades"
            variant="destructive"
            :processing="lockProcessing"
            @confirm="unlockGrades"
            @cancel="showUnlockConfirm = false"
        />
    </AppLayout>
</template>
