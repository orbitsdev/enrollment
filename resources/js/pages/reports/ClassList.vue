<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem, type Section, type Student } from '@/types';
import { Download, Printer } from 'lucide-vue-next';

const props = defineProps<{
    sections: Section[];
    students: Student[];
    selected_section_id: number | null;
    filters: { section_id?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Reports', href: '/reports' },
    { title: 'Class List', href: '/reports/class-list' },
];

const selectedSectionId = ref(props.filters.section_id ?? '');

function applyFilter() {
    router.get('/reports/class-list', {
        section_id: selectedSectionId.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function exportClassList() {
    if (selectedSectionId.value) {
        window.location.href = `/reports/export/class-list/${selectedSectionId.value}`;
    }
}

function printList() {
    window.print();
}
</script>

<template>
    <Head title="Class List" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-6">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Class List</h1>
                    <p class="text-muted-foreground">View and export the student roster for a section.</p>
                </div>
                <div class="flex gap-2">
                    <Button @click="printList" variant="outline" :disabled="students.length === 0">
                        <Printer class="mr-2 h-4 w-4" />
                        Print
                    </Button>
                    <Button @click="exportClassList" variant="outline" :disabled="!selectedSectionId">
                        <Download class="mr-2 h-4 w-4" />
                        Export Excel
                    </Button>
                </div>
            </div>

            <!-- Section Selector -->
            <Card class="mb-6">
                <CardContent class="flex flex-wrap items-end gap-4 p-4">
                    <div class="min-w-[300px]">
                        <label class="mb-1 block text-sm font-medium">Section</label>
                        <select
                            v-model="selectedSectionId"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        >
                            <option value="">Select section...</option>
                            <option
                                v-for="section in sections"
                                :key="section.id"
                                :value="section.id.toString()"
                            >
                                {{ section.name }} ({{ section.strand?.name }})
                            </option>
                        </select>
                    </div>
                    <Button @click="applyFilter" size="sm">View Class List</Button>
                </CardContent>
            </Card>

            <!-- Student Roster -->
            <Card v-if="students.length > 0">
                <CardHeader>
                    <CardTitle class="text-base">
                        Student Roster ({{ students.length }} students)
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b text-left">
                                    <th class="pb-2 pr-4 font-medium">#</th>
                                    <th class="pb-2 pr-4 font-medium">LRN</th>
                                    <th class="pb-2 pr-4 font-medium">Last Name</th>
                                    <th class="pb-2 pr-4 font-medium">First Name</th>
                                    <th class="pb-2 pr-4 font-medium">Middle Name</th>
                                    <th class="pb-2 pr-4 font-medium">Gender</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(student, index) in students"
                                    :key="student.id"
                                    class="border-b last:border-0"
                                >
                                    <td class="py-2 pr-4">{{ index + 1 }}</td>
                                    <td class="py-2 pr-4 font-mono text-xs">{{ student.lrn }}</td>
                                    <td class="py-2 pr-4">{{ student.last_name }}</td>
                                    <td class="py-2 pr-4">{{ student.first_name }}</td>
                                    <td class="py-2 pr-4">{{ student.middle_name ?? '-' }}</td>
                                    <td class="py-2 pr-4 capitalize">{{ student.gender }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <div v-else-if="selected_section_id" class="py-12 text-center text-muted-foreground">
                No students enrolled in this section.
            </div>

            <div v-else class="py-12 text-center text-muted-foreground">
                Select a section to view the class list.
            </div>
        </div>
    </AppLayout>
</template>
