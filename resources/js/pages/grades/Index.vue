<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { BookOpen, CheckCircle } from 'lucide-vue-next';
import { computed } from 'vue';
import PageHeader from '@/components/App/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
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

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Grades' },
];

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

            <!-- Section Cards Grid -->
            <div v-if="sections.length > 0" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="section in sections" :key="section.id">
                    <CardHeader class="pb-3">
                        <div class="flex items-start justify-between">
                            <CardTitle class="text-lg">{{ section.name }}</CardTitle>
                            <Badge variant="outline">
                                {{ section.strand?.code ?? '--' }}
                            </Badge>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                            <span>Grade {{ section.grade_level }}</span>
                            <span class="text-muted-foreground/50">|</span>
                            <span>{{ section.enrollments_count }} students</span>
                        </div>
                        <div class="mt-2">
                            <div class="flex items-center justify-between text-xs text-muted-foreground mb-1">
                                <span>Overall Completion</span>
                                <span>{{ sectionOverallCompletion(section) }}%</span>
                            </div>
                            <Progress :model-value="sectionOverallCompletion(section)" class="h-1.5" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <div
                                v-for="subject in section.subjects"
                                :key="subject.id"
                                class="flex items-center justify-between rounded-md border px-3 py-2"
                            >
                                <div class="flex-1 min-w-0">
                                    <Link
                                        :href="`/grades/${section.id}/${subject.id}`"
                                        class="text-sm font-medium hover:underline truncate block"
                                    >
                                        {{ subject.code }}
                                    </Link>
                                    <p class="text-xs text-muted-foreground truncate">
                                        {{ subject.name }}
                                    </p>
                                </div>
                                <div class="ml-3 flex items-center gap-2">
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
                                    <CheckCircle
                                        v-if="subjectCompletionPercent(subject) === 100"
                                        class="size-4 text-green-500"
                                    />
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        as-child
                                    >
                                        <Link :href="`/grades/${section.id}/${subject.id}`">
                                            <BookOpen class="size-4" />
                                        </Link>
                                    </Button>
                                </div>
                            </div>

                            <p
                                v-if="!section.subjects || section.subjects.length === 0"
                                class="text-sm text-muted-foreground text-center py-4"
                            >
                                No subjects assigned.
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty state -->
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
