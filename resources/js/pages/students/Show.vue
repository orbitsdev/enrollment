<script setup lang="ts">
import { Deferred, Head, Link } from '@inertiajs/vue3';
import { Pencil } from 'lucide-vue-next';
import PageHeader from '@/components/App/PageHeader.vue';
import StatusBadge from '@/components/App/StatusBadge.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Skeleton } from '@/components/ui/skeleton';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from '@/components/ui/tabs';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Enrollment, Grade, Student } from '@/types';

const props = defineProps<{
    student: Student;
    enrollments?: Array<Enrollment & {
        section?: { name: string; strand?: { code: string } };
        semester?: { label?: string; school_year?: { name: string }; number: number };
    }>;
    grades?: Array<Grade & {
        subject?: { code: string; name: string };
        enrollment?: Enrollment & {
            semester?: { label?: string; school_year?: { name: string }; number: number };
        };
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Students', href: '/students' },
    { title: props.student.full_name ?? `${props.student.last_name}, ${props.student.first_name}` },
];

const studentName = props.student.full_name
    ?? `${props.student.last_name}, ${props.student.first_name} ${props.student.middle_name ?? ''}`.trim();
</script>

<template>
    <Head :title="studentName" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Student header -->
            <PageHeader :title="studentName" :description="`LRN: ${student.lrn}`">
                <template #actions>
                    <StatusBadge :status="student.status" />
                    <Button as-child>
                        <Link :href="`/students/${student.id}/edit`">
                            <Pencil class="size-4" />
                            Edit
                        </Link>
                    </Button>
                </template>
            </PageHeader>

            <!-- Tabs -->
            <Tabs default-value="personal" class="w-full">
                <TabsList>
                    <TabsTrigger value="personal">Personal Info</TabsTrigger>
                    <TabsTrigger value="enrollments">Enrollment History</TabsTrigger>
                    <TabsTrigger value="grades">Grades</TabsTrigger>
                </TabsList>

                <!-- Personal Info Tab -->
                <TabsContent value="personal" class="mt-4">
                    <div class="grid gap-6 md:grid-cols-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>Personal Details</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <dl class="space-y-3">
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-muted-foreground">LRN</dt>
                                        <dd class="text-sm font-mono">{{ student.lrn }}</dd>
                                    </div>
                                    <Separator />
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-muted-foreground">Last Name</dt>
                                        <dd class="text-sm">{{ student.last_name }}</dd>
                                    </div>
                                    <Separator />
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-muted-foreground">First Name</dt>
                                        <dd class="text-sm">{{ student.first_name }}</dd>
                                    </div>
                                    <Separator />
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-muted-foreground">Middle Name</dt>
                                        <dd class="text-sm">{{ student.middle_name ?? '--' }}</dd>
                                    </div>
                                    <Separator />
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-muted-foreground">Suffix</dt>
                                        <dd class="text-sm">{{ student.suffix ?? '--' }}</dd>
                                    </div>
                                    <Separator />
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-muted-foreground">Birthdate</dt>
                                        <dd class="text-sm">{{ student.birthdate }}</dd>
                                    </div>
                                    <Separator />
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-muted-foreground">Gender</dt>
                                        <dd class="text-sm capitalize">{{ student.gender }}</dd>
                                    </div>
                                    <Separator />
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-muted-foreground">Status</dt>
                                        <dd><StatusBadge :status="student.status" /></dd>
                                    </div>
                                </dl>
                            </CardContent>
                        </Card>

                        <div class="space-y-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Contact Information</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <dl class="space-y-3">
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-muted-foreground">Address</dt>
                                            <dd class="text-sm text-right max-w-[200px]">{{ student.address ?? '--' }}</dd>
                                        </div>
                                        <Separator />
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-muted-foreground">Contact Number</dt>
                                            <dd class="text-sm">{{ student.contact_number ?? '--' }}</dd>
                                        </div>
                                    </dl>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>Guardian Information</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <dl class="space-y-3">
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-muted-foreground">Guardian Name</dt>
                                            <dd class="text-sm">{{ student.guardian_name ?? '--' }}</dd>
                                        </div>
                                        <Separator />
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-muted-foreground">Guardian Contact</dt>
                                            <dd class="text-sm">{{ student.guardian_contact ?? '--' }}</dd>
                                        </div>
                                        <Separator />
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-muted-foreground">Relationship</dt>
                                            <dd class="text-sm">{{ student.guardian_relationship ?? '--' }}</dd>
                                        </div>
                                    </dl>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>Previous School</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p class="text-sm">{{ student.previous_school ?? 'Not specified' }}</p>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </TabsContent>

                <!-- Enrollment History Tab -->
                <TabsContent value="enrollments" class="mt-4">
                    <Deferred data="enrollments">
                        <template #fallback>
                            <Card>
                                <CardContent class="p-6">
                                    <div class="space-y-3">
                                        <Skeleton class="h-10 w-full" />
                                        <Skeleton class="h-10 w-full" />
                                        <Skeleton class="h-10 w-full" />
                                        <Skeleton class="h-10 w-full" />
                                    </div>
                                </CardContent>
                            </Card>
                        </template>

                        <Card>
                            <CardHeader>
                                <CardTitle>Enrollment History</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="rounded-md border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>School Year</TableHead>
                                                <TableHead>Semester</TableHead>
                                                <TableHead>Section</TableHead>
                                                <TableHead>Strand</TableHead>
                                                <TableHead>Grade Level</TableHead>
                                                <TableHead>Status</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <template v-if="enrollments && enrollments.length > 0">
                                                <TableRow v-for="enrollment in enrollments" :key="enrollment.id">
                                                    <TableCell>
                                                        {{ enrollment.semester?.school_year?.name ?? '--' }}
                                                    </TableCell>
                                                    <TableCell>
                                                        {{ enrollment.semester?.label ?? `Semester ${enrollment.semester?.number}` }}
                                                    </TableCell>
                                                    <TableCell>
                                                        {{ enrollment.section?.name ?? '--' }}
                                                    </TableCell>
                                                    <TableCell>
                                                        <Badge v-if="enrollment.section?.strand" variant="outline">
                                                            {{ enrollment.section.strand.code }}
                                                        </Badge>
                                                        <span v-else class="text-muted-foreground">--</span>
                                                    </TableCell>
                                                    <TableCell>
                                                        Grade {{ enrollment.section?.grade_level ?? '--' }}
                                                    </TableCell>
                                                    <TableCell>
                                                        <StatusBadge :status="enrollment.status" />
                                                    </TableCell>
                                                </TableRow>
                                            </template>
                                            <template v-else>
                                                <TableRow>
                                                    <TableCell :colspan="6" class="h-24 text-center text-muted-foreground">
                                                        No enrollment history found.
                                                    </TableCell>
                                                </TableRow>
                                            </template>
                                        </TableBody>
                                    </Table>
                                </div>
                            </CardContent>
                        </Card>
                    </Deferred>
                </TabsContent>

                <!-- Grades Tab -->
                <TabsContent value="grades" class="mt-4">
                    <Deferred data="grades">
                        <template #fallback>
                            <Card>
                                <CardContent class="p-6">
                                    <div class="space-y-3">
                                        <Skeleton class="h-10 w-full" />
                                        <Skeleton class="h-10 w-full" />
                                        <Skeleton class="h-10 w-full" />
                                        <Skeleton class="h-10 w-full" />
                                    </div>
                                </CardContent>
                            </Card>
                        </template>

                        <Card>
                            <CardHeader>
                                <CardTitle>Academic Grades</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <template v-if="grades && grades.length > 0">
                                    <div class="rounded-md border">
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead>Subject</TableHead>
                                                    <TableHead>Semester</TableHead>
                                                    <TableHead class="text-center">Midterm</TableHead>
                                                    <TableHead class="text-center">Finals</TableHead>
                                                    <TableHead class="text-center">Final Grade</TableHead>
                                                    <TableHead class="text-center">Remarks</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                <TableRow v-for="grade in grades" :key="grade.id">
                                                    <TableCell>
                                                        <div>
                                                            <span class="font-mono text-xs text-muted-foreground">
                                                                {{ grade.subject?.code }}
                                                            </span>
                                                            <div class="font-medium">{{ grade.subject?.name }}</div>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell>
                                                        {{ grade.enrollment?.semester?.label ?? `Sem ${grade.enrollment?.semester?.number}` }}
                                                    </TableCell>
                                                    <TableCell class="text-center">
                                                        {{ grade.midterm ?? '--' }}
                                                    </TableCell>
                                                    <TableCell class="text-center">
                                                        {{ grade.finals ?? '--' }}
                                                    </TableCell>
                                                    <TableCell class="text-center font-medium">
                                                        {{ grade.final_grade ?? '--' }}
                                                    </TableCell>
                                                    <TableCell class="text-center">
                                                        <StatusBadge v-if="grade.remarks" :status="grade.remarks" />
                                                        <span v-else class="text-muted-foreground">--</span>
                                                    </TableCell>
                                                </TableRow>
                                            </TableBody>
                                        </Table>
                                    </div>
                                </template>
                                <template v-else>
                                    <p class="py-8 text-center text-muted-foreground">
                                        No grades recorded yet.
                                    </p>
                                </template>
                            </CardContent>
                        </Card>
                    </Deferred>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>
