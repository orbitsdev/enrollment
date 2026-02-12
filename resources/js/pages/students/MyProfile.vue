<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { type BreadcrumbItem, type Student } from '@/types';

const props = defineProps<{
    student: Student | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'My Profile', href: '/my/profile' },
];

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
    <Head title="My Profile" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold tracking-tight">My Profile</h1>
                <p class="text-muted-foreground">Your student information.</p>
            </div>

            <template v-if="student">
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Personal Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base">Personal Information</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div class="grid grid-cols-2 gap-y-3 text-sm">
                                <span class="font-medium text-muted-foreground">LRN</span>
                                <span class="font-mono">{{ student.lrn }}</span>

                                <span class="font-medium text-muted-foreground">Full Name</span>
                                <span>{{ student.full_name }}</span>

                                <span class="font-medium text-muted-foreground">Gender</span>
                                <span class="capitalize">{{ student.gender }}</span>

                                <span class="font-medium text-muted-foreground">Birthdate</span>
                                <span>{{ student.birthdate }}</span>

                                <span class="font-medium text-muted-foreground">Address</span>
                                <span>{{ student.address ?? 'Not provided' }}</span>

                                <span class="font-medium text-muted-foreground">Contact Number</span>
                                <span>{{ student.contact_number ?? 'Not provided' }}</span>

                                <span class="font-medium text-muted-foreground">Status</span>
                                <span>
                                    <Badge :variant="statusColor(student.status)">{{ student.status }}</Badge>
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Guardian Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base">Guardian Information</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div class="grid grid-cols-2 gap-y-3 text-sm">
                                <span class="font-medium text-muted-foreground">Guardian Name</span>
                                <span>{{ student.guardian_name ?? 'Not provided' }}</span>

                                <span class="font-medium text-muted-foreground">Guardian Contact</span>
                                <span>{{ student.guardian_contact ?? 'Not provided' }}</span>

                                <span class="font-medium text-muted-foreground">Relationship</span>
                                <span>{{ student.guardian_relationship ?? 'Not provided' }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- School Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base">School Information</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-2 gap-y-3 text-sm">
                                <span class="font-medium text-muted-foreground">Previous School</span>
                                <span>{{ student.previous_school ?? 'Not provided' }}</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </template>

            <Card v-else>
                <CardContent class="py-12 text-center text-muted-foreground">
                    No student profile found linked to your account.
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
