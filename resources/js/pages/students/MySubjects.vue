<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { type BreadcrumbItem, type Student, type Semester } from '@/types';

interface SubjectItem {
    id: number;
    code: string;
    name: string;
    type: string;
    hours: number;
}

const props = defineProps<{
    student: Student | null;
    semester: Semester | null;
    subjects: SubjectItem[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'My Subjects', href: '/my/subjects' },
];
</script>

<template>
    <Head title="My Subjects" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold tracking-tight">My Subjects</h1>
                <p v-if="semester" class="text-muted-foreground">
                    Subjects for the current semester.
                </p>
            </div>

            <template v-if="subjects.length > 0">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">
                            Enrolled Subjects ({{ subjects.length }})
                        </CardTitle>
                        <CardDescription v-if="student">
                            Student: {{ student.full_name }} | LRN: {{ student.lrn }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b text-left">
                                        <th class="pb-2 pr-4 font-medium">#</th>
                                        <th class="pb-2 pr-4 font-medium">Code</th>
                                        <th class="pb-2 pr-4 font-medium">Subject Name</th>
                                        <th class="pb-2 pr-4 font-medium">Type</th>
                                        <th class="pb-2 font-medium">Hours</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(subject, index) in subjects"
                                        :key="subject.id"
                                        class="border-b last:border-0"
                                    >
                                        <td class="py-2 pr-4">{{ index + 1 }}</td>
                                        <td class="py-2 pr-4 font-mono text-xs">{{ subject.code }}</td>
                                        <td class="py-2 pr-4">{{ subject.name }}</td>
                                        <td class="py-2 pr-4">
                                            <Badge variant="outline">{{ subject.type }}</Badge>
                                        </td>
                                        <td class="py-2">{{ subject.hours }}h</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </template>

            <Card v-else>
                <CardContent class="py-12 text-center text-muted-foreground">
                    <template v-if="!student">
                        No student profile found linked to your account.
                    </template>
                    <template v-else>
                        No subjects found for the current semester.
                    </template>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
