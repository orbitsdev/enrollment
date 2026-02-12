<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
import {
    BarChart3,
    ClipboardList,
    Users,
    BookOpen,
    FileText,
} from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Reports', href: '/reports' },
];

const reports = [
    {
        title: 'Enrollment Summary',
        description: 'View enrollment statistics by track, strand, and grade level.',
        icon: BarChart3,
        href: '/reports/enrollment-summary',
        color: 'text-blue-600',
    },
    {
        title: 'Class List',
        description: 'View and export the student roster for each section.',
        icon: ClipboardList,
        href: '/reports/class-list',
        color: 'text-green-600',
    },
    {
        title: 'Student Masterlist',
        description: 'Complete list of all students with filtering options.',
        icon: Users,
        href: '/reports/masterlist',
        color: 'text-purple-600',
    },
    {
        title: 'Grade Summary',
        description: 'View grade reports by section and subject.',
        icon: BookOpen,
        href: '/reports/grade-summary',
        color: 'text-amber-600',
    },
    {
        title: 'School Forms',
        description: 'Generate official DepEd school forms (SF9, SF10).',
        icon: FileText,
        href: '/reports/school-forms',
        color: 'text-red-600',
    },
];
</script>

<template>
    <Head title="Reports" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold tracking-tight">Reports</h1>
                <p class="text-muted-foreground">Generate and export reports for school operations.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="report in reports"
                    :key="report.href"
                    :href="report.href"
                    class="block transition-transform hover:scale-[1.02]"
                >
                    <Card class="h-full cursor-pointer hover:border-primary/50 hover:shadow-md">
                        <CardHeader>
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-muted">
                                    <component :is="report.icon" class="h-5 w-5" :class="report.color" />
                                </div>
                                <CardTitle class="text-base">{{ report.title }}</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <CardDescription>{{ report.description }}</CardDescription>
                        </CardContent>
                    </Card>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
