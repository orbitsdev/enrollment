<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { type BreadcrumbItem, type Strand, type Student, type PaginatedData } from '@/types';
import { Download } from 'lucide-vue-next';

const props = defineProps<{
    students: PaginatedData<Student>;
    strands: Strand[];
    filters: { strand_id?: string; status?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Reports', href: '/reports' },
    { title: 'Student Masterlist', href: '/reports/masterlist' },
];

const strandId = ref(props.filters.strand_id ?? '');
const status = ref(props.filters.status ?? '');

watch([strandId, status], () => {
    router.get('/reports/masterlist', {
        strand_id: strandId.value || undefined,
        status: status.value || undefined,
        page: 1,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
});

const statuses = [
    { value: '', label: 'All Statuses' },
    { value: 'active', label: 'Active' },
    { value: 'transferred', label: 'Transferred' },
    { value: 'dropped', label: 'Dropped' },
    { value: 'graduated', label: 'Graduated' },
];

function applyFilter() {
    router.get('/reports/masterlist', {
        strand_id: strandId.value || undefined,
        status: status.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function exportMasterlist() {
    const params = new URLSearchParams();
    if (strandId.value) params.set('strand_id', strandId.value);
    if (status.value) params.set('status', status.value);
    window.location.href = `/reports/export/masterlist?${params.toString()}`;
}

function goToPage(url: string | null) {
    if (url) {
        router.get(url, {}, { preserveState: true, preserveScroll: true });
    }
}

function statusColor(s: string): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (s) {
        case 'active': return 'default';
        case 'graduated': return 'secondary';
        case 'dropped': return 'destructive';
        default: return 'outline';
    }
}
</script>

<template>
    <Head title="Student Masterlist" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-6">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Student Masterlist</h1>
                    <p class="text-muted-foreground">Complete list of all students.</p>
                </div>
                <Button @click="exportMasterlist" variant="outline">
                    <Download class="mr-2 h-4 w-4" />
                    Export Excel
                </Button>
            </div>

            <!-- Filters -->
            <Card class="mb-6">
                <CardContent class="flex flex-wrap items-end gap-4 p-4">
                    <div class="min-w-[200px]">
                        <label class="mb-1 block text-sm font-medium">Strand</label>
                        <select
                            v-model="strandId"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        >
                            <option value="">All Strands</option>
                            <option v-for="strand in strands" :key="strand.id" :value="strand.id.toString()">
                                {{ strand.track?.name }} - {{ strand.name }}
                            </option>
                        </select>
                    </div>
                    <div class="min-w-[160px]">
                        <label class="mb-1 block text-sm font-medium">Status</label>
                        <select
                            v-model="status"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        >
                            <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                        </select>
                    </div>
                </CardContent>
            </Card>

            <!-- Student Table -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">
                        Students ({{ students.total }} total)
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b text-left">
                                    <th class="pb-2 pr-4 font-medium">LRN</th>
                                    <th class="pb-2 pr-4 font-medium">Name</th>
                                    <th class="pb-2 pr-4 font-medium">Gender</th>
                                    <th class="pb-2 pr-4 font-medium">Contact</th>
                                    <th class="pb-2 pr-4 font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="student in students.data"
                                    :key="student.id"
                                    class="border-b last:border-0"
                                >
                                    <td class="py-2 pr-4 font-mono text-xs">{{ student.lrn }}</td>
                                    <td class="py-2 pr-4">{{ student.full_name }}</td>
                                    <td class="py-2 pr-4 capitalize">{{ student.gender }}</td>
                                    <td class="py-2 pr-4">{{ student.contact_number ?? '-' }}</td>
                                    <td class="py-2 pr-4">
                                        <Badge :variant="statusColor(student.status)">
                                            {{ student.status }}
                                        </Badge>
                                    </td>
                                </tr>
                                <tr v-if="students.data.length === 0">
                                    <td colspan="5" class="py-8 text-center text-muted-foreground">
                                        No students found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="students.last_page > 1" class="mt-4 flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">
                            Showing {{ students.from }} to {{ students.to }} of {{ students.total }}
                        </p>
                        <div class="flex gap-1">
                            <Button
                                v-for="link in students.links"
                                :key="link.label"
                                variant="outline"
                                size="sm"
                                :disabled="!link.url || link.active"
                                @click="goToPage(link.url)"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
