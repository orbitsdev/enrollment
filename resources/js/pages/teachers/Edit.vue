<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import PageHeader from '@/components/App/PageHeader.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useUnsavedChangesGuard } from '@/composables/useUnsavedChangesGuard';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, TeacherProfile } from '@/types';
import type { User } from '@/types/auth';

const props = defineProps<{
    teacher: User;
    profile: TeacherProfile | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Teachers', href: '/teachers' },
    { title: props.teacher.name, href: `/teachers/${props.teacher.id}` },
    { title: 'Edit Profile' },
];

const form = useForm({
    employee_id: props.profile?.employee_id ?? '',
    position_title: props.profile?.position_title ?? '',
    appointment_status: props.profile?.appointment_status ?? '',
    sex: props.profile?.sex ?? '',
    birthdate: props.profile?.birthdate ?? '',
    contact_number: props.profile?.contact_number ?? '',
    address: props.profile?.address ?? '',
    highest_degree: props.profile?.highest_degree ?? '',
    degree_course: props.profile?.degree_course ?? '',
    degree_major: props.profile?.degree_major ?? '',
    school_graduated: props.profile?.school_graduated ?? '',
    year_graduated: props.profile?.year_graduated ?? '',
    prc_license_number: props.profile?.prc_license_number ?? '',
    prc_validity: props.profile?.prc_validity ?? '',
    eligibility: props.profile?.eligibility ?? '',
    specialization: props.profile?.specialization ?? '',
    date_hired: props.profile?.date_hired ?? '',
    teaching_hours_per_week: props.profile?.teaching_hours_per_week ?? '',
});

useUnsavedChangesGuard(form);

function submit() {
    form.put(`/teachers/${props.teacher.id}`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="`Edit ${teacher.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <PageHeader
                :title="`Edit ${teacher.name}`"
                description="Update teacher profile information for SF7."
            >
                <template #actions>
                    <Button variant="outline" as-child>
                        <Link :href="`/teachers/${teacher.id}`">Cancel</Link>
                    </Button>
                </template>
            </PageHeader>

            <form class="mx-auto w-full max-w-3xl space-y-6" @submit.prevent="submit">
                <!-- Personal Information -->
                <Card>
                    <CardContent class="space-y-4 pt-6">
                        <h3 class="text-lg font-semibold">Personal Information</h3>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="employee_id">Employee ID</Label>
                                <Input id="employee_id" v-model="form.employee_id" placeholder="DepEd Employee ID" />
                                <InputError :message="form.errors.employee_id" />
                            </div>
                            <div class="space-y-2">
                                <Label for="sex">Sex</Label>
                                <Select v-model="form.sex">
                                    <SelectTrigger id="sex">
                                        <SelectValue placeholder="Select sex" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Male">Male</SelectItem>
                                        <SelectItem value="Female">Female</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.sex" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="birthdate">Birthdate</Label>
                                <Input id="birthdate" v-model="form.birthdate" type="date" />
                                <InputError :message="form.errors.birthdate" />
                            </div>
                            <div class="space-y-2">
                                <Label for="contact_number">Contact Number</Label>
                                <Input id="contact_number" v-model="form.contact_number" placeholder="Contact number" />
                                <InputError :message="form.errors.contact_number" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="address">Address</Label>
                            <Textarea id="address" v-model="form.address" placeholder="Full address" rows="2" />
                            <InputError :message="form.errors.address" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Employment -->
                <Card>
                    <CardContent class="space-y-4 pt-6">
                        <h3 class="text-lg font-semibold">Employment</h3>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="position_title">Position Title</Label>
                                <Input id="position_title" v-model="form.position_title" placeholder="e.g., Teacher I" />
                                <InputError :message="form.errors.position_title" />
                            </div>
                            <div class="space-y-2">
                                <Label for="appointment_status">Appointment Status</Label>
                                <Select v-model="form.appointment_status">
                                    <SelectTrigger id="appointment_status">
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="permanent">Permanent</SelectItem>
                                        <SelectItem value="contractual">Contractual</SelectItem>
                                        <SelectItem value="part-time">Part-Time</SelectItem>
                                        <SelectItem value="job_order">Job Order</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.appointment_status" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="specialization">Specialization</Label>
                                <Input id="specialization" v-model="form.specialization" placeholder="Teaching specialization" />
                                <InputError :message="form.errors.specialization" />
                            </div>
                            <div class="space-y-2">
                                <Label for="date_hired">Date Hired</Label>
                                <Input id="date_hired" v-model="form.date_hired" type="date" />
                                <InputError :message="form.errors.date_hired" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="teaching_hours_per_week">Teaching Hours Per Week</Label>
                            <Input id="teaching_hours_per_week" v-model="form.teaching_hours_per_week" type="number" min="0" max="60" placeholder="0" />
                            <InputError :message="form.errors.teaching_hours_per_week" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Education & Credentials -->
                <Card>
                    <CardContent class="space-y-4 pt-6">
                        <h3 class="text-lg font-semibold">Education & Credentials</h3>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="highest_degree">Highest Degree</Label>
                                <Select v-model="form.highest_degree">
                                    <SelectTrigger id="highest_degree">
                                        <SelectValue placeholder="Select degree" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Bachelor's">Bachelor's</SelectItem>
                                        <SelectItem value="Master's">Master's</SelectItem>
                                        <SelectItem value="Doctorate">Doctorate</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.highest_degree" />
                            </div>
                            <div class="space-y-2">
                                <Label for="degree_course">Degree Course</Label>
                                <Input id="degree_course" v-model="form.degree_course" placeholder="e.g., BSEd Mathematics" />
                                <InputError :message="form.errors.degree_course" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="degree_major">Major</Label>
                                <Input id="degree_major" v-model="form.degree_major" placeholder="e.g., Mathematics" />
                                <InputError :message="form.errors.degree_major" />
                            </div>
                            <div class="space-y-2">
                                <Label for="school_graduated">School Graduated</Label>
                                <Input id="school_graduated" v-model="form.school_graduated" placeholder="University name" />
                                <InputError :message="form.errors.school_graduated" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="year_graduated">Year Graduated</Label>
                            <Input id="year_graduated" v-model="form.year_graduated" type="number" min="1950" max="2099" placeholder="Year" />
                            <InputError :message="form.errors.year_graduated" />
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="prc_license_number">PRC License Number</Label>
                                <Input id="prc_license_number" v-model="form.prc_license_number" placeholder="License number" />
                                <InputError :message="form.errors.prc_license_number" />
                            </div>
                            <div class="space-y-2">
                                <Label for="prc_validity">PRC Validity</Label>
                                <Input id="prc_validity" v-model="form.prc_validity" type="date" />
                                <InputError :message="form.errors.prc_validity" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="eligibility">Eligibility</Label>
                            <Input id="eligibility" v-model="form.eligibility" placeholder="e.g., LET Passer, CSC Professional" />
                            <InputError :message="form.errors.eligibility" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Submit -->
                <div class="flex justify-end gap-4">
                    <Button variant="outline" type="button" as-child>
                        <Link :href="`/teachers/${teacher.id}`">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        Save Profile
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
