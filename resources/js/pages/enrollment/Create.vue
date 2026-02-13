<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Check,
    ChevronLeft,
    ChevronRight,
    CircleCheck,
    CircleX,
    ExternalLink,
    Loader2,
    Search,
    UserPlus,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import CapacityBar from '@/components/App/CapacityBar.vue';
import PageHeader from '@/components/App/PageHeader.vue';
import QuickStudentFormDialog from '@/components/App/QuickStudentFormDialog.vue';
import StepIndicator from '@/components/App/StepIndicator.vue';
import InputError from '@/components/InputError.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import type {
    BreadcrumbItem,
    Section,
    Semester,
    Strand,
    Student,
    Subject,
    Track,
} from '@/types';

interface SetupChecklist {
    school_year: boolean;
    semester: boolean;
    tracks: boolean;
    strands: boolean;
    subjects: boolean;
    subject_mappings: boolean;
    sections: boolean;
    students: boolean;
}

const props = defineProps<{
    tracks: Track[];
    activeSemester: Semester | null;
    setupChecklist: SetupChecklist;
}>();

const checklistItems = computed(() => [
    { key: 'school_year', label: 'Active School Year', link: '/school-years', required: true },
    { key: 'semester', label: 'Active Semester', link: '/school-years', required: true },
    { key: 'tracks', label: 'At least one Track', link: '/curriculum', required: true },
    { key: 'strands', label: 'At least one Strand', link: '/curriculum', required: true },
    { key: 'subjects', label: 'At least one Subject', link: '/subjects', required: true },
    { key: 'subject_mappings', label: 'Subjects mapped to Strands', link: '/subjects', required: true },
    { key: 'sections', label: 'Sections for active semester', link: '/sections', required: true },
    { key: 'students', label: 'At least one Student', link: '/students', required: false },
] as const);

const requiredSetupReady = computed(() =>
    checklistItems.value
        .filter((item) => item.required)
        .every((item) => props.setupChecklist[item.key]),
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Enrollment', href: '/enrollment' },
    { title: 'New Enrollment' },
];

const steps = [
    { label: 'Find Student' },
    { label: 'Track & Strand' },
    { label: 'Subjects' },
    { label: 'Section' },
    { label: 'Confirm' },
];

const currentStep = ref(0);

const form = useForm('EnrollmentWizard', {
    student_id: null as number | null,
    strand_id: null as number | null,
    grade_level: null as number | null,
    semester_id: props.activeSemester?.id ?? null,
    section_id: null as number | null,
    subject_ids: [] as number[],
});

// ===== Step 1: Find Student =====
const studentSearch = ref('');
const studentResults = ref<Student[]>([]);
const selectedStudent = ref<Student | null>(null);
const searchingStudents = ref(false);
const showQuickStudentDialog = ref(false);

let studentSearchTimer: ReturnType<typeof setTimeout> | null = null;

watch(studentSearch, (val) => {
    if (studentSearchTimer) clearTimeout(studentSearchTimer);
    if (!val || val.length < 2) {
        studentResults.value = [];
        return;
    }
    studentSearchTimer = setTimeout(async () => {
        searchingStudents.value = true;
        try {
            const response = await fetch(`/api/students/search?q=${encodeURIComponent(val)}`, {
                headers: { 'Accept': 'application/json' },
            });
            const data = await response.json();
            studentResults.value = data.students ?? data.data ?? [];
        } catch {
            studentResults.value = [];
        } finally {
            searchingStudents.value = false;
        }
    }, 300);
});

function selectStudent(student: Student) {
    selectedStudent.value = student;
    form.student_id = student.id;
    studentResults.value = [];
    studentSearch.value = '';
}

function clearStudent() {
    selectedStudent.value = null;
    form.student_id = null;
}

function onStudentCreated(student: Student) {
    selectStudent(student);
}

// ===== Step 2: Track & Strand =====
const selectedTrackId = ref<number | null>(null);

const filteredStrands = computed(() => {
    if (!selectedTrackId.value) return [];
    const track = props.tracks.find((t) => t.id === selectedTrackId.value);
    return track?.strands?.filter((s) => s.is_active) ?? [];
});

function onTrackChange(value: string) {
    selectedTrackId.value = value ? Number(value) : null;
    form.strand_id = null;
}

function onStrandChange(value: string) {
    form.strand_id = value ? Number(value) : null;
}

function onGradeLevelChange(value: string) {
    form.grade_level = value ? Number(value) : null;
}

// ===== Step 3: Subjects =====
const availableSubjects = ref<Array<Subject & { has_prerequisite_issue?: boolean; prerequisite_message?: string }>>([]);
const loadingSubjects = ref(false);

async function loadSubjects() {
    if (!form.strand_id || !form.grade_level || !form.semester_id) return;

    loadingSubjects.value = true;
    try {
        const params = new URLSearchParams({
            strand_id: String(form.strand_id),
            grade_level: String(form.grade_level),
            semester_id: String(form.semester_id),
        });
        const response = await fetch(`/api/enrollment/subjects?${params}`, {
            headers: { 'Accept': 'application/json' },
        });
        const data = await response.json();
        availableSubjects.value = data.subjects ?? data.data ?? [];

        // Pre-check all core subjects
        form.subject_ids = availableSubjects.value
            .filter((s) => s.type === 'core')
            .map((s) => s.id);

        // Check prerequisites
        if (form.student_id) {
            await checkPrerequisites();
        }
    } catch {
        availableSubjects.value = [];
    } finally {
        loadingSubjects.value = false;
    }
}

async function checkPrerequisites() {
    try {
        const params = new URLSearchParams({
            student_id: String(form.student_id),
            subject_ids: form.subject_ids.join(','),
        });
        const response = await fetch(`/api/enrollment/prerequisites?${params}`, {
            headers: { 'Accept': 'application/json' },
        });
        const data = await response.json();
        const issues = data.issues ?? {};

        availableSubjects.value = availableSubjects.value.map((s) => ({
            ...s,
            has_prerequisite_issue: !!issues[s.id],
            prerequisite_message: issues[s.id] ?? null,
        }));
    } catch {
        // silently ignore
    }
}

const prerequisiteOverride = ref(false);

function toggleSubject(subjectId: number, checked: boolean) {
    if (checked) {
        if (!form.subject_ids.includes(subjectId)) {
            form.subject_ids.push(subjectId);
        }
    } else {
        form.subject_ids = form.subject_ids.filter((id) => id !== subjectId);
    }
}

// ===== Step 4: Section =====
const availableSections = ref<Array<Section & { adviser?: { name: string }; enrolled_count?: number }>>([]);
const loadingSections = ref(false);

async function loadSections() {
    if (!form.strand_id || !form.grade_level || !form.semester_id) return;

    loadingSections.value = true;
    try {
        const params = new URLSearchParams({
            strand_id: String(form.strand_id),
            grade_level: String(form.grade_level),
            semester_id: String(form.semester_id),
        });
        const response = await fetch(`/api/enrollment/sections?${params}`, {
            headers: { 'Accept': 'application/json' },
        });
        const data = await response.json();
        availableSections.value = data.sections ?? data.data ?? [];

        // Auto-select if only one available
        if (availableSections.value.length === 1) {
            form.section_id = availableSections.value[0].id;
        }
    } catch {
        availableSections.value = [];
    } finally {
        loadingSections.value = false;
    }
}

function onSectionChange(value: string) {
    form.section_id = value ? Number(value) : null;
}

// ===== Step 5: Confirm =====
const selectedStrand = computed(() => {
    for (const track of props.tracks) {
        const strand = track.strands?.find((s) => s.id === form.strand_id);
        if (strand) return strand;
    }
    return null;
});

const selectedSection = computed(() =>
    availableSections.value.find((s) => s.id === form.section_id) ?? null,
);

const selectedSubjects = computed(() =>
    availableSubjects.value.filter((s) => form.subject_ids.includes(s.id)),
);

const enrollmentSuccess = ref(false);
const enrollmentId = ref<number | null>(null);

function submitEnrollment() {
    form.post('/enrollment', {
        preserveScroll: true,
        onSuccess: (page: any) => {
            enrollmentSuccess.value = true;
            enrollmentId.value = page.props?.enrollment?.id ?? null;
        },
    });
}

// ===== Navigation =====
const canProceed = computed(() => {
    switch (currentStep.value) {
        case 0:
            return form.student_id !== null;
        case 1:
            return form.strand_id !== null && form.grade_level !== null && form.semester_id !== null;
        case 2:
            return form.subject_ids.length > 0;
        case 3:
            return form.section_id !== null;
        case 4:
            return true;
        default:
            return false;
    }
});

async function nextStep() {
    if (!canProceed.value) return;

    if (currentStep.value === 1) {
        // Load subjects when moving from step 2 to step 3
        await loadSubjects();
    }

    if (currentStep.value === 2) {
        // Load sections when moving from step 3 to step 4
        await loadSections();
    }

    if (currentStep.value < steps.length - 1) {
        currentStep.value++;
    }
}

function prevStep() {
    if (currentStep.value > 0) {
        currentStep.value--;
    }
}
</script>

<template>
    <Head title="New Enrollment" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <PageHeader
                title="New Enrollment"
                description="Enroll a student step by step."
            >
                <template #actions>
                    <Button variant="outline" as-child>
                        <Link href="/enrollment">Cancel</Link>
                    </Button>
                </template>
            </PageHeader>

            <!-- Setup Checklist -->
            <div v-if="!requiredSetupReady" class="w-full">
                <Alert class="border-amber-300 bg-amber-50 dark:border-amber-700 dark:bg-amber-950">
                    <AlertTriangle class="size-4 text-amber-600 dark:text-amber-400" />
                    <AlertTitle class="text-amber-800 dark:text-amber-200">Setup Required</AlertTitle>
                    <AlertDescription>
                        <p class="mb-3 text-sm text-amber-700 dark:text-amber-300">
                            The following items must be configured before enrollment can proceed:
                        </p>
                        <ul class="space-y-2">
                            <li
                                v-for="item in checklistItems"
                                :key="item.key"
                                class="flex items-center gap-2 text-sm"
                            >
                                <CircleCheck
                                    v-if="setupChecklist[item.key]"
                                    class="size-4 shrink-0 text-green-600 dark:text-green-400"
                                />
                                <CircleX
                                    v-else
                                    class="size-4 shrink-0 text-red-500 dark:text-red-400"
                                />
                                <span :class="setupChecklist[item.key] ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300'">
                                    {{ item.label }}
                                </span>
                                <Badge v-if="!item.required" variant="outline" class="text-xs">Optional</Badge>
                                <Link
                                    v-if="!setupChecklist[item.key]"
                                    :href="item.link"
                                    class="ml-auto inline-flex items-center gap-1 text-xs font-medium text-amber-700 underline hover:text-amber-900 dark:text-amber-300 dark:hover:text-amber-100"
                                >
                                    Set up
                                    <ExternalLink class="size-3" />
                                </Link>
                            </li>
                        </ul>
                    </AlertDescription>
                </Alert>
            </div>

            <!-- Step Indicator -->
            <div v-if="requiredSetupReady" class="w-full px-4">
                <StepIndicator :steps="steps" :current-step="currentStep" />
            </div>

            <div v-if="requiredSetupReady" class="w-full">
                <!-- ==================== STEP 1: FIND STUDENT ==================== -->
                <div v-show="currentStep === 0">
                    <Card>
                        <CardHeader>
                            <CardTitle>Find Student</CardTitle>
                            <CardDescription>
                                Search for an existing student or create a new one.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <!-- Selected Student Card -->
                            <div v-if="selectedStudent" class="rounded-lg border bg-muted/50 p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium">
                                            {{ selectedStudent.full_name ?? `${selectedStudent.last_name}, ${selectedStudent.first_name}` }}
                                        </p>
                                        <p class="text-sm text-muted-foreground">
                                            LRN: {{ selectedStudent.lrn }}
                                        </p>
                                    </div>
                                    <Button variant="ghost" size="sm" @click="clearStudent">
                                        Change
                                    </Button>
                                </div>
                            </div>

                            <!-- Search -->
                            <div v-else>
                                <div class="relative">
                                    <Search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                                    <Input
                                        v-model="studentSearch"
                                        placeholder="Search by LRN or student name..."
                                        class="pl-9"
                                    />
                                    <Loader2
                                        v-if="searchingStudents"
                                        class="absolute right-3 top-1/2 size-4 -translate-y-1/2 animate-spin text-muted-foreground"
                                    />
                                </div>

                                <!-- Search Results -->
                                <div
                                    v-if="studentResults.length > 0"
                                    class="mt-2 max-h-60 overflow-y-auto rounded-md border"
                                >
                                    <button
                                        v-for="result in studentResults"
                                        :key="result.id"
                                        type="button"
                                        class="flex w-full items-center justify-between px-4 py-3 text-left hover:bg-muted/50 transition-colors"
                                        @click="selectStudent(result)"
                                    >
                                        <div>
                                            <p class="font-medium text-sm">
                                                {{ result.full_name ?? `${result.last_name}, ${result.first_name}` }}
                                            </p>
                                            <p class="text-xs text-muted-foreground">
                                                LRN: {{ result.lrn }}
                                            </p>
                                        </div>
                                        <Badge variant="outline" class="capitalize">
                                            {{ result.status }}
                                        </Badge>
                                    </button>
                                </div>

                                <!-- No results -->
                                <p
                                    v-if="studentSearch.length >= 2 && !searchingStudents && studentResults.length === 0"
                                    class="mt-2 text-sm text-muted-foreground"
                                >
                                    No students found matching "{{ studentSearch }}".
                                </p>

                                <Separator class="my-4" />

                                <!-- Add New Student -->
                                <Button
                                    variant="outline"
                                    class="w-full"
                                    @click="showQuickStudentDialog = true"
                                >
                                    <UserPlus class="size-4" />
                                    Add New Student
                                </Button>
                            </div>

                            <InputError :message="form.errors.student_id" />
                        </CardContent>
                    </Card>
                </div>

                <!-- ==================== STEP 2: TRACK & STRAND ==================== -->
                <div v-show="currentStep === 1">
                    <Card>
                        <CardHeader>
                            <CardTitle>Track & Strand</CardTitle>
                            <CardDescription>
                                Select the academic track, strand, and grade level.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label for="track">Track</Label>
                                <Select
                                    :model-value="selectedTrackId ? String(selectedTrackId) : ''"
                                    @update:model-value="onTrackChange"
                                >
                                    <SelectTrigger id="track">
                                        <SelectValue placeholder="Select a track" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="track in tracks.filter((t) => t.is_active)"
                                            :key="track.id"
                                            :value="String(track.id)"
                                        >
                                            {{ track.name }} ({{ track.code }})
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="space-y-2">
                                <Label for="strand">Strand</Label>
                                <Select
                                    :model-value="form.strand_id ? String(form.strand_id) : ''"
                                    :disabled="!selectedTrackId"
                                    @update:model-value="onStrandChange"
                                >
                                    <SelectTrigger id="strand">
                                        <SelectValue placeholder="Select a strand" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="strand in filteredStrands"
                                            :key="strand.id"
                                            :value="String(strand.id)"
                                        >
                                            {{ strand.code }} - {{ strand.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.strand_id" />
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="grade_level">Grade Level</Label>
                                    <Select
                                        :model-value="form.grade_level ? String(form.grade_level) : ''"
                                        @update:model-value="onGradeLevelChange"
                                    >
                                        <SelectTrigger id="grade_level">
                                            <SelectValue placeholder="Select grade level" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="11">Grade 11</SelectItem>
                                            <SelectItem value="12">Grade 12</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.grade_level" />
                                </div>

                                <div class="space-y-2">
                                    <Label>Semester</Label>
                                    <div
                                        v-if="activeSemester"
                                        class="flex h-9 items-center rounded-md border bg-muted/50 px-3 text-sm font-medium"
                                    >
                                        {{ activeSemester.full_label || activeSemester.label || `Semester ${activeSemester.number}` }}
                                    </div>
                                    <p v-if="activeSemester" class="text-xs text-muted-foreground">
                                        Auto-selected from active semester. Managed in
                                        <Link href="/school-years" class="font-medium text-primary underline">School Year settings</Link>.
                                    </p>
                                    <Alert v-else class="border-amber-300 bg-amber-50 dark:border-amber-700 dark:bg-amber-950">
                                        <AlertTriangle class="size-4 text-amber-600" />
                                        <AlertDescription class="text-sm text-amber-700 dark:text-amber-300">
                                            No active semester found. An admin must activate a school year and semester in
                                            <Link href="/school-years" class="font-medium underline">School Year settings</Link>
                                            before enrollment can proceed.
                                        </AlertDescription>
                                    </Alert>
                                    <InputError :message="form.errors.semester_id" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- ==================== STEP 3: SUBJECTS ==================== -->
                <div v-show="currentStep === 2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Subjects</CardTitle>
                            <CardDescription>
                                Select the subjects for this enrollment. Core subjects are pre-selected.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="loadingSubjects" class="flex items-center justify-center py-8">
                                <Loader2 class="size-6 animate-spin text-muted-foreground" />
                                <span class="ml-2 text-sm text-muted-foreground">Loading subjects...</span>
                            </div>

                            <div v-else-if="availableSubjects.length > 0" class="space-y-3">
                                <div
                                    v-for="subject in availableSubjects"
                                    :key="subject.id"
                                    class="flex items-start space-x-3 rounded-lg border p-3"
                                    :class="{
                                        'border-yellow-300 bg-yellow-50 dark:border-yellow-700 dark:bg-yellow-950':
                                            subject.has_prerequisite_issue,
                                    }"
                                >
                                    <Checkbox
                                        :id="`subject-${subject.id}`"
                                        :model-value="form.subject_ids.includes(subject.id)"
                                        @update:model-value="(val: boolean) => toggleSubject(subject.id, val)"
                                    />
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <label
                                                :for="`subject-${subject.id}`"
                                                class="text-sm font-medium cursor-pointer"
                                            >
                                                {{ subject.code }} - {{ subject.name }}
                                            </label>
                                            <Badge variant="outline" class="text-xs capitalize">
                                                {{ subject.type }}
                                            </Badge>
                                        </div>
                                        <p class="text-xs text-muted-foreground">
                                            {{ subject.hours }} hours
                                        </p>
                                        <div v-if="subject.has_prerequisite_issue" class="mt-1 flex items-center gap-1 text-xs text-yellow-700 dark:text-yellow-400">
                                            <AlertTriangle class="size-3" />
                                            {{ subject.prerequisite_message }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Prerequisite override -->
                                <div v-if="availableSubjects.some((s) => s.has_prerequisite_issue)" class="mt-4">
                                    <Alert>
                                        <AlertTriangle class="size-4" />
                                        <AlertDescription>
                                            <div class="flex items-center gap-2">
                                                <Checkbox
                                                    id="prereq-override"
                                                    :model-value="prerequisiteOverride"
                                                    @update:model-value="(val: boolean) => (prerequisiteOverride = val)"
                                                />
                                                <label for="prereq-override" class="text-sm cursor-pointer">
                                                    Override prerequisite requirements (admin/registrar only)
                                                </label>
                                            </div>
                                        </AlertDescription>
                                    </Alert>
                                </div>
                            </div>

                            <div v-else class="py-8 text-center">
                                <p class="text-muted-foreground">
                                    No subjects found for this strand/grade/semester combination.
                                </p>
                                <p class="mt-2 text-sm text-muted-foreground">
                                    Make sure subjects are mapped to strands in the
                                    <Link href="/subjects" class="font-medium text-primary underline">Subjects page</Link>.
                                </p>
                            </div>

                            <InputError :message="form.errors.subject_ids" class="mt-2" />
                        </CardContent>
                    </Card>
                </div>

                <!-- ==================== STEP 4: SECTION ==================== -->
                <div v-show="currentStep === 3">
                    <Card>
                        <CardHeader>
                            <CardTitle>Select Section</CardTitle>
                            <CardDescription>
                                Choose an available section for this enrollment.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="loadingSections" class="flex items-center justify-center py-8">
                                <Loader2 class="size-6 animate-spin text-muted-foreground" />
                                <span class="ml-2 text-sm text-muted-foreground">Loading sections...</span>
                            </div>

                            <RadioGroup
                                v-else-if="availableSections.length > 0"
                                :model-value="form.section_id ? String(form.section_id) : ''"
                                @update:model-value="onSectionChange"
                                class="space-y-3"
                            >
                                <div
                                    v-for="section in availableSections"
                                    :key="section.id"
                                    class="flex items-start space-x-3 rounded-lg border p-4 cursor-pointer hover:bg-muted/50 transition-colors"
                                    :class="{
                                        'border-primary bg-primary/5': form.section_id === section.id,
                                    }"
                                    @click="form.section_id = section.id"
                                >
                                    <RadioGroupItem
                                        :value="String(section.id)"
                                        :id="`section-${section.id}`"
                                    />
                                    <div class="flex-1 space-y-2">
                                        <div class="flex items-center justify-between">
                                            <label :for="`section-${section.id}`" class="font-medium cursor-pointer">
                                                {{ section.name }}
                                            </label>
                                        </div>
                                        <p class="text-sm text-muted-foreground">
                                            Adviser: {{ section.adviser?.name ?? section.adviser ?? 'Not assigned' }}
                                        </p>
                                        <CapacityBar
                                            :current="section.enrolled_count ?? 0"
                                            :max="section.max_capacity"
                                        />
                                    </div>
                                </div>
                            </RadioGroup>

                            <div v-else class="py-8">
                                <Alert class="border-amber-300 bg-amber-50 dark:border-amber-700 dark:bg-amber-950">
                                    <AlertTriangle class="size-4 text-amber-600 dark:text-amber-400" />
                                    <AlertTitle class="text-amber-800 dark:text-amber-200">No sections available</AlertTitle>
                                    <AlertDescription class="space-y-2">
                                        <p class="text-sm text-amber-700 dark:text-amber-300">
                                            No sections exist for
                                            <strong>{{ selectedStrand?.name ?? 'Unknown Strand' }}</strong> /
                                            <strong>Grade {{ form.grade_level }}</strong> /
                                            <strong>{{ activeSemester?.full_label ?? activeSemester?.label ?? 'Unknown Semester' }}</strong>.
                                        </p>
                                        <p class="text-sm text-amber-700 dark:text-amber-300">
                                            <strong>Why:</strong> A section must be created with this exact strand, grade level, and semester combination before a student can be enrolled.
                                        </p>
                                        <p class="text-sm text-amber-700 dark:text-amber-300">
                                            <strong>How to fix:</strong> Go to the
                                            <Link href="/sections" class="font-medium underline hover:text-amber-900 dark:hover:text-amber-100">Sections page</Link>
                                            and create a new section assigned to
                                            <strong>{{ selectedStrand?.name }}</strong>, <strong>Grade {{ form.grade_level }}</strong>,
                                            for the <strong>{{ activeSemester?.full_label ?? activeSemester?.label }}</strong> semester.
                                        </p>
                                    </AlertDescription>
                                </Alert>
                            </div>

                            <InputError :message="form.errors.section_id" class="mt-2" />
                        </CardContent>
                    </Card>
                </div>

                <!-- ==================== STEP 5: CONFIRM ==================== -->
                <div v-show="currentStep === 4">
                    <!-- Success State -->
                    <Card v-if="enrollmentSuccess">
                        <CardContent class="flex flex-col items-center justify-center py-12">
                            <div class="mb-4 flex size-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                                <Check class="size-8 text-green-600 dark:text-green-400" />
                            </div>
                            <h3 class="text-lg font-semibold">Enrollment Successful!</h3>
                            <p class="mt-1 text-sm text-muted-foreground">
                                The student has been successfully enrolled.
                            </p>
                            <div class="mt-6 flex gap-3">
                                <Button variant="outline" as-child>
                                    <Link href="/enrollment">
                                        Back to Enrollments
                                    </Link>
                                </Button>
                                <Button v-if="enrollmentId" as-child>
                                    <a :href="`/enrollment/${enrollmentId}/print`" target="_blank">
                                        Print Enrollment Slip
                                    </a>
                                </Button>
                                <Button variant="outline" as-child>
                                    <Link href="/enrollment/create">
                                        Enroll Another
                                    </Link>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Confirmation Summary -->
                    <Card v-else>
                        <CardHeader>
                            <CardTitle>Confirm Enrollment</CardTitle>
                            <CardDescription>
                                Review all details before submitting.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <!-- Student Summary -->
                            <div>
                                <h4 class="text-sm font-semibold text-muted-foreground uppercase tracking-wider">Student</h4>
                                <div class="mt-2 rounded-lg bg-muted/50 p-3">
                                    <p class="font-medium">
                                        {{ selectedStudent?.full_name ?? `${selectedStudent?.last_name}, ${selectedStudent?.first_name}` }}
                                    </p>
                                    <p class="text-sm text-muted-foreground">LRN: {{ selectedStudent?.lrn }}</p>
                                </div>
                            </div>

                            <Separator />

                            <!-- Track & Strand Summary -->
                            <div class="grid gap-4 md:grid-cols-3">
                                <div>
                                    <h4 class="text-sm font-semibold text-muted-foreground uppercase tracking-wider">Strand</h4>
                                    <p class="mt-1 font-medium">
                                        {{ selectedStrand?.code }} - {{ selectedStrand?.name }}
                                    </p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-muted-foreground uppercase tracking-wider">Grade Level</h4>
                                    <p class="mt-1 font-medium">Grade {{ form.grade_level }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-muted-foreground uppercase tracking-wider">Semester</h4>
                                    <p class="mt-1 font-medium">
                                        {{ activeSemester?.full_label ?? activeSemester?.label ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>

                            <Separator />

                            <!-- Section Summary -->
                            <div>
                                <h4 class="text-sm font-semibold text-muted-foreground uppercase tracking-wider">Section</h4>
                                <p class="mt-1 font-medium">
                                    {{ selectedSection?.name ?? '--' }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    Adviser: {{ selectedSection?.adviser?.name ?? selectedSection?.adviser ?? 'Not assigned' }}
                                </p>
                            </div>

                            <Separator />

                            <!-- Subjects Summary -->
                            <div>
                                <h4 class="text-sm font-semibold text-muted-foreground uppercase tracking-wider">
                                    Subjects ({{ selectedSubjects.length }})
                                </h4>
                                <ul class="mt-2 space-y-1">
                                    <li
                                        v-for="subject in selectedSubjects"
                                        :key="subject.id"
                                        class="flex items-center gap-2 text-sm"
                                    >
                                        <Check class="size-3 text-green-500" />
                                        <span class="font-mono text-xs text-muted-foreground">{{ subject.code }}</span>
                                        {{ subject.name }}
                                    </li>
                                </ul>
                            </div>

                            <!-- General form errors -->
                            <InputError :message="form.errors.student_id" />
                            <InputError :message="form.errors.strand_id" />
                            <InputError :message="form.errors.section_id" />
                            <InputError :message="form.errors.subject_ids" />
                        </CardContent>
                    </Card>
                </div>

                <!-- Navigation Buttons -->
                <div v-if="!enrollmentSuccess" class="mt-6 flex items-center justify-between">
                    <Button
                        v-if="currentStep > 0"
                        variant="outline"
                        @click="prevStep"
                    >
                        <ChevronLeft class="size-4" />
                        Previous
                    </Button>
                    <div v-else />

                    <Button
                        v-if="currentStep < steps.length - 1"
                        :disabled="!canProceed"
                        @click="nextStep"
                    >
                        Next
                        <ChevronRight class="size-4" />
                    </Button>
                    <Button
                        v-else
                        :disabled="form.processing || !canProceed"
                        @click="submitEnrollment"
                    >
                        <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                        Submit Enrollment
                    </Button>
                </div>
            </div>
        </div>

        <!-- Quick Student Registration Dialog -->
        <QuickStudentFormDialog
            :open="showQuickStudentDialog"
            @update:open="showQuickStudentDialog = $event"
            @created="onStudentCreated"
        />
    </AppLayout>
</template>
