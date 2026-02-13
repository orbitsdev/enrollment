<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { CalendarDays, LayoutList } from 'lucide-vue-next';
import { computed } from 'vue';
import PageHeader from '@/components/App/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Section, Strand } from '@/types';

type SectionWithDetails = Section & {
    strand: Strand;
    enrolled_count: number;
};

const props = defineProps<{
    sections: SectionWithDetails[];
}>();

const page = usePage();
const activeSemester = computed(() => page.props.activeSemester as { full_label: string } | null);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'My Sections' },
];
</script>

<template>
    <Head title="My Sections" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <PageHeader
                title="My Sections"
                description="Sections assigned to you as adviser for the active semester."
            />

            <!-- Active Semester Context -->
            <div class="rounded-lg border bg-muted/50 px-4 py-3">
                <div class="flex items-center gap-2 text-sm">
                    <CalendarDays class="size-4 text-muted-foreground" />
                    <span class="font-medium">
                        {{ activeSemester?.full_label ?? 'No active semester' }}
                    </span>
                </div>
            </div>

            <template v-if="sections.length > 0">
                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Section Name</TableHead>
                                <TableHead>Strand</TableHead>
                                <TableHead>Grade Level</TableHead>
                                <TableHead class="text-center">Students Enrolled</TableHead>
                                <TableHead></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="section in sections" :key="section.id">
                                <TableCell class="font-medium">{{ section.name }}</TableCell>
                                <TableCell>
                                    <Badge variant="outline">{{ section.strand?.code ?? '--' }}</Badge>
                                </TableCell>
                                <TableCell>Grade {{ section.grade_level }}</TableCell>
                                <TableCell class="text-center">{{ section.enrolled_count }}</TableCell>
                                <TableCell class="text-right">
                                    <Link
                                        :href="`/grades`"
                                        class="text-sm font-medium text-primary hover:underline"
                                    >
                                        Grade Entry
                                    </Link>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </template>

            <!-- Empty state -->
            <div v-else class="flex flex-col items-center justify-center rounded-lg border border-dashed p-12">
                <LayoutList class="mb-4 size-12 text-muted-foreground/50" />
                <p class="text-lg font-medium text-muted-foreground">No sections assigned</p>
                <p class="text-sm text-muted-foreground">
                    You have no advisory sections for the active semester.
                </p>
            </div>
        </div>
    </AppLayout>
</template>
