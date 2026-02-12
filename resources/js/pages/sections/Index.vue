<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, Pencil, Plus, Users } from 'lucide-vue-next';
import { ref } from 'vue';
import CapacityBar from '@/components/App/CapacityBar.vue';
import PageHeader from '@/components/App/PageHeader.vue';
import SearchInput from '@/components/App/SearchInput.vue';
import SectionFormDialog from '@/components/App/SectionFormDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Section, Semester, Strand, User } from '@/types';

type SectionWithRelations = Section & {
    strand?: Strand;
    semester?: Semester & { school_year?: { name: string } };
    adviser?: { id: number; name: string };
    enrolled_count?: number;
};

const props = defineProps<{
    sections: SectionWithRelations[];
    filters: { search: string; semester_id: string; strand_id: string; grade_level: string };
    semesters: Semester[];
    strands: Strand[];
    teachers: User[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Sections' },
];

const search = ref(props.filters.search ?? '');
const semesterFilter = ref(props.filters.semester_id ?? '');
const strandFilter = ref(props.filters.strand_id ?? '');
const gradeLevelFilter = ref(props.filters.grade_level ?? '');

// Dialog state
const dialogOpen = ref(false);
const editingSection = ref<SectionWithRelations | null>(null);

function openCreateDialog() {
    editingSection.value = null;
    dialogOpen.value = true;
}

function openEditDialog(section: SectionWithRelations) {
    editingSection.value = section;
    dialogOpen.value = true;
}

function applyFilters() {
    router.get(
        '/sections',
        {
            semester_id: semesterFilter.value || undefined,
            strand_id: strandFilter.value || undefined,
            grade_level: gradeLevelFilter.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['sections'],
        },
    );
}

function onSemesterChange(value: string) {
    semesterFilter.value = value === 'all' ? '' : value;
    applyFilters();
}

function onStrandChange(value: string) {
    strandFilter.value = value === 'all' ? '' : value;
    applyFilters();
}

function onGradeLevelChange(value: string) {
    gradeLevelFilter.value = value === 'all' ? '' : value;
    applyFilters();
}
</script>

<template>
    <Head title="Sections" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <PageHeader
                title="Sections"
                description="Manage class sections and rosters."
            >
                <template #actions>
                    <Button @click="openCreateDialog">
                        <Plus class="size-4" />
                        Add Section
                    </Button>
                </template>
            </PageHeader>

            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-4">
                <SearchInput
                    v-model="search"
                    placeholder="Search sections..."
                    :only="['sections']"
                />
                <Select
                    :model-value="semesterFilter || 'all'"
                    @update:model-value="onSemesterChange"
                >
                    <SelectTrigger class="w-[220px]">
                        <SelectValue placeholder="Filter by semester" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Semesters</SelectItem>
                        <SelectItem
                            v-for="sem in semesters"
                            :key="sem.id"
                            :value="String(sem.id)"
                        >
                            {{ sem.label ?? `${sem.school_year?.name} - Sem ${sem.number}` }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <Select
                    :model-value="strandFilter || 'all'"
                    @update:model-value="onStrandChange"
                >
                    <SelectTrigger class="w-[200px]">
                        <SelectValue placeholder="Filter by strand" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Strands</SelectItem>
                        <SelectItem
                            v-for="strand in strands"
                            :key="strand.id"
                            :value="String(strand.id)"
                        >
                            {{ strand.code }} - {{ strand.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <Select
                    :model-value="gradeLevelFilter || 'all'"
                    @update:model-value="onGradeLevelChange"
                >
                    <SelectTrigger class="w-[160px]">
                        <SelectValue placeholder="Grade level" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Grades</SelectItem>
                        <SelectItem value="11">Grade 11</SelectItem>
                        <SelectItem value="12">Grade 12</SelectItem>
                    </SelectContent>
                </Select>
            </div>

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
                            <span>{{ section.semester?.label ?? `Sem ${section.semester?.number}` }}</span>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex items-center gap-2 text-sm">
                            <Users class="size-4 text-muted-foreground" />
                            <span>Adviser: {{ section.adviser?.name ?? 'Not assigned' }}</span>
                        </div>
                        <CapacityBar
                            :current="section.enrolled_count ?? 0"
                            :max="section.max_capacity"
                        />
                    </CardContent>
                    <CardFooter class="flex gap-2 border-t pt-4">
                        <Button variant="outline" size="sm" class="flex-1" as-child>
                            <Link :href="`/sections/${section.id}`" prefetch>
                                <Eye class="size-4" />
                                View Roster
                            </Link>
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            class="flex-1"
                            @click="openEditDialog(section)"
                        >
                            <Pencil class="size-4" />
                            Edit
                        </Button>
                    </CardFooter>
                </Card>
            </div>

            <!-- Empty state -->
            <div v-else class="flex flex-col items-center justify-center rounded-lg border border-dashed p-12">
                <Users class="mb-4 size-12 text-muted-foreground/50" />
                <p class="text-lg font-medium text-muted-foreground">No sections found</p>
                <p class="text-sm text-muted-foreground">
                    Try adjusting your filters or create a new section.
                </p>
            </div>
        </div>

        <SectionFormDialog
            v-model:open="dialogOpen"
            :section="editingSection"
            :strands="strands"
            :semesters="semesters"
            :teachers="teachers"
        />
    </AppLayout>
</template>
