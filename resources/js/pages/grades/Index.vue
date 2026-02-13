<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { BookOpen, CalendarDays, CheckCircle, ChevronDown, ChevronRight, Info, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import PageHeader from '@/components/App/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Progress } from '@/components/ui/progress';
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
import type { BreadcrumbItem, Section, Strand, Subject } from '@/types';

type SubjectWithGradeCount = Subject & {
    grades_entered: number;
    grades_total: number;
};

type SectionWithGradeInfo = Section & {
    strand: Strand;
    enrollments_count: number;
    subjects: SubjectWithGradeCount[];
};

const props = defineProps<{
    sections: SectionWithGradeInfo[];
}>();

const page = usePage();
const activeSemester = computed(() => page.props.activeSemester as { full_label: string } | null);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Grades' },
];

// Filter state
const searchQuery = ref('');
const strandFilter = ref('all');
const gradeLevelFilter = ref('all');
const expandedRows = ref<Set<number>>(new Set());

// Derived filter options
const uniqueStrands = computed(() => {
    const strands = new Map<string, string>();
    for (const section of props.sections) {
        if (section.strand?.code) {
            strands.set(section.strand.code, section.strand.code);
        }
    }
    return Array.from(strands.values()).sort();
});

const uniqueGradeLevels = computed(() => {
    const levels = new Set<number>();
    for (const section of props.sections) {
        if (section.grade_level) {
            levels.add(section.grade_level);
        }
    }
    return Array.from(levels).sort((a, b) => a - b);
});

// Filtered sections
const filteredSections = computed(() => {
    return props.sections.filter((section) => {
        const matchesSearch = !searchQuery.value ||
            section.name.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesStrand = strandFilter.value === 'all' ||
            section.strand?.code === strandFilter.value;
        const matchesGradeLevel = gradeLevelFilter.value === 'all' ||
            section.grade_level?.toString() === gradeLevelFilter.value;
        return matchesSearch && matchesStrand && matchesGradeLevel;
    });
});

function toggleRow(sectionId: number) {
    const newSet = new Set(expandedRows.value);
    if (newSet.has(sectionId)) {
        newSet.delete(sectionId);
    } else {
        newSet.add(sectionId);
    }
    expandedRows.value = newSet;
}

function subjectCompletionPercent(subject: SubjectWithGradeCount): number {
    if (subject.grades_total <= 0) return 0;
    return Math.round((subject.grades_entered / subject.grades_total) * 100);
}

function sectionOverallCompletion(section: SectionWithGradeInfo): number {
    if (!section.subjects || section.subjects.length === 0) return 0;
    const totalGrades = section.subjects.reduce((sum, s) => sum + s.grades_total, 0);
    const enteredGrades = section.subjects.reduce((sum, s) => sum + s.grades_entered, 0);
    if (totalGrades <= 0) return 0;
    return Math.round((enteredGrades / totalGrades) * 100);
}
</script>

<template>
    <Head title="Grades" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <PageHeader
                title="Grades"
                description="Manage and enter student grades by section and subject."
            />

            <!-- Active Semester Context -->
            <div class="rounded-lg border bg-muted/50 px-4 py-3">
                <div class="flex items-center gap-2 text-sm">
                    <CalendarDays class="size-4 text-muted-foreground" />
                    <span class="font-medium">
                        {{ activeSemester?.full_label ?? 'No active semester' }}
                    </span>
                    <span class="text-muted-foreground">
                        — Showing sections and grades for this semester.
                    </span>
                </div>
            </div>

            <!-- Data Context Banner -->
            <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 dark:border-blue-900 dark:bg-blue-950/50">
                <div class="flex items-start gap-2 text-sm text-blue-800 dark:text-blue-300">
                    <Info class="mt-0.5 size-4 shrink-0" />
                    <span>
                        Grades are created automatically when students are enrolled. Teachers enter midterm and finals scores here.
                    </span>
                </div>
            </div>

            <template v-if="sections.length > 0">
                <!-- Filter Bar -->
                <div class="flex flex-wrap items-center gap-3">
                    <div class="relative min-w-[220px] flex-1 max-w-sm">
                        <Search class="absolute left-2.5 top-2.5 size-4 text-muted-foreground" />
                        <Input
                            v-model="searchQuery"
                            placeholder="Search sections..."
                            class="pl-9"
                        />
                    </div>
                    <Select v-model="strandFilter">
                        <SelectTrigger class="w-[160px]">
                            <SelectValue placeholder="All Strands" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Strands</SelectItem>
                            <SelectItem v-for="strand in uniqueStrands" :key="strand" :value="strand">
                                {{ strand }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="gradeLevelFilter">
                        <SelectTrigger class="w-[170px]">
                            <SelectValue placeholder="All Grade Levels" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Grade Levels</SelectItem>
                            <SelectItem v-for="level in uniqueGradeLevels" :key="level" :value="level.toString()">
                                Grade {{ level }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Sections Table -->
                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[40px]"></TableHead>
                                <TableHead>Section Name</TableHead>
                                <TableHead>Strand</TableHead>
                                <TableHead>Grade Level</TableHead>
                                <TableHead class="text-center">Students</TableHead>
                                <TableHead class="min-w-[180px]">Completion</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <template v-if="filteredSections.length > 0">
                                <template v-for="section in filteredSections" :key="section.id">
                                    <!-- Section Row -->
                                    <TableRow
                                        class="cursor-pointer"
                                        @click="toggleRow(section.id)"
                                    >
                                        <TableCell class="px-3">
                                            <ChevronDown v-if="expandedRows.has(section.id)" class="size-4 text-muted-foreground" />
                                            <ChevronRight v-else class="size-4 text-muted-foreground" />
                                        </TableCell>
                                        <TableCell class="font-medium">{{ section.name }}</TableCell>
                                        <TableCell>
                                            <Badge variant="outline">{{ section.strand?.code ?? '--' }}</Badge>
                                        </TableCell>
                                        <TableCell>Grade {{ section.grade_level }}</TableCell>
                                        <TableCell class="text-center">{{ section.enrollments_count }}</TableCell>
                                        <TableCell>
                                            <div class="flex items-center gap-2">
                                                <Progress :model-value="sectionOverallCompletion(section)" class="h-1.5 flex-1" />
                                                <span class="text-xs text-muted-foreground w-[36px] text-right">{{ sectionOverallCompletion(section) }}%</span>
                                            </div>
                                        </TableCell>
                                    </TableRow>

                                    <!-- Expanded Subject Rows -->
                                    <TableRow
                                        v-if="expandedRows.has(section.id)"
                                        v-for="subject in section.subjects"
                                        :key="`${section.id}-${subject.id}`"
                                        class="bg-muted/30"
                                    >
                                        <TableCell></TableCell>
                                        <TableCell colspan="3" class="pl-8">
                                            <div class="flex items-center gap-2">
                                                <Link
                                                    :href="`/grades/${section.id}/${subject.id}`"
                                                    prefetch
                                                    class="text-sm font-medium hover:underline"
                                                >
                                                    {{ subject.code }}
                                                </Link>
                                                <span class="text-xs text-muted-foreground">{{ subject.name }}</span>
                                            </div>
                                        </TableCell>
                                        <TableCell class="text-center">
                                            <span
                                                class="text-xs font-medium"
                                                :class="{
                                                    'text-green-600 dark:text-green-400': subjectCompletionPercent(subject) === 100,
                                                    'text-yellow-600 dark:text-yellow-400': subjectCompletionPercent(subject) > 0 && subjectCompletionPercent(subject) < 100,
                                                    'text-muted-foreground': subjectCompletionPercent(subject) === 0,
                                                }"
                                            >
                                                {{ subject.grades_entered }}/{{ subject.grades_total }}
                                            </span>
                                        </TableCell>
                                        <TableCell>
                                            <div class="flex items-center gap-2">
                                                <CheckCircle
                                                    v-if="subjectCompletionPercent(subject) === 100"
                                                    class="size-4 text-green-500"
                                                />
                                                <Button variant="ghost" size="sm" as-child>
                                                    <Link :href="`/grades/${section.id}/${subject.id}`" prefetch>
                                                        <BookOpen class="size-4" />
                                                    </Link>
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>

                                    <!-- No subjects message -->
                                    <TableRow
                                        v-if="expandedRows.has(section.id) && (!section.subjects || section.subjects.length === 0)"
                                    >
                                        <TableCell></TableCell>
                                        <TableCell colspan="5" class="text-center text-sm text-muted-foreground py-4">
                                            No subjects assigned.
                                        </TableCell>
                                    </TableRow>
                                </template>
                            </template>

                            <!-- No matching sections after filtering -->
                            <TableRow v-else>
                                <TableCell colspan="6" class="text-center py-8">
                                    <p class="text-muted-foreground">No matching sections. Try adjusting your search or filters.</p>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </template>

            <!-- Empty state (no sections at all) -->
            <div v-else class="flex flex-col items-center justify-center rounded-lg border border-dashed p-12">
                <BookOpen class="mb-4 size-12 text-muted-foreground/50" />
                <p class="text-lg font-medium text-muted-foreground">No sections found</p>
                <p class="text-sm text-muted-foreground">
                    You have no sections assigned for grade entry.
                </p>
            </div>
        </div>
    </AppLayout>
</template>
