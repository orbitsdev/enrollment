<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import PageHeader from '@/components/App/PageHeader.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
import type { BreadcrumbItem, Student } from '@/types';

const props = defineProps<{
    student: Student;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Students', href: '/students' },
    { title: `Edit ${props.student.first_name} ${props.student.last_name}` },
];

const form = useForm({
    lrn: props.student.lrn,
    last_name: props.student.last_name,
    first_name: props.student.first_name,
    middle_name: props.student.middle_name ?? '',
    suffix: props.student.suffix ?? '',
    birthdate: props.student.birthdate,
    gender: props.student.gender,
    address: props.student.address ?? '',
    contact_number: props.student.contact_number ?? '',
    guardian_name: props.student.guardian_name ?? '',
    guardian_contact: props.student.guardian_contact ?? '',
    guardian_relationship: props.student.guardian_relationship ?? '',
    previous_school: props.student.previous_school ?? '',
    status: props.student.status,
});

useUnsavedChangesGuard(form);

function submit() {
    form.put(`/students/${props.student.id}`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="`Edit ${student.first_name} ${student.last_name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <PageHeader
                :title="`Edit ${student.first_name} ${student.last_name}`"
                description="Update student information."
            >
                <template #actions>
                    <Button variant="outline" as-child>
                        <Link :href="`/students/${student.id}`">
                            View Profile
                        </Link>
                    </Button>
                </template>
            </PageHeader>

            <form @submit.prevent="submit">
                <div class="mx-auto grid w-full max-w-4xl gap-6 md:grid-cols-2">
                    <!-- Personal Info -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Personal Information</CardTitle>
                            <CardDescription>
                                Basic student identity and demographics.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label for="lrn">LRN (12-digit)</Label>
                                <Input
                                    id="lrn"
                                    v-model="form.lrn"
                                    type="text"
                                    maxlength="12"
                                    placeholder="Enter 12-digit LRN"
                                />
                                <InputError :message="form.errors.lrn" />
                            </div>

                            <div class="space-y-2">
                                <Label for="last_name">Last Name</Label>
                                <Input
                                    id="last_name"
                                    v-model="form.last_name"
                                    type="text"
                                    placeholder="Last name"
                                />
                                <InputError :message="form.errors.last_name" />
                            </div>

                            <div class="space-y-2">
                                <Label for="first_name">First Name</Label>
                                <Input
                                    id="first_name"
                                    v-model="form.first_name"
                                    type="text"
                                    placeholder="First name"
                                />
                                <InputError :message="form.errors.first_name" />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="middle_name">Middle Name</Label>
                                    <Input
                                        id="middle_name"
                                        v-model="form.middle_name"
                                        type="text"
                                        placeholder="Middle name"
                                    />
                                    <InputError :message="form.errors.middle_name" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="suffix">Suffix</Label>
                                    <Input
                                        id="suffix"
                                        v-model="form.suffix"
                                        type="text"
                                        placeholder="Jr., Sr., III"
                                    />
                                    <InputError :message="form.errors.suffix" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="birthdate">Birthdate</Label>
                                <Input
                                    id="birthdate"
                                    v-model="form.birthdate"
                                    type="date"
                                />
                                <InputError :message="form.errors.birthdate" />
                            </div>

                            <div class="space-y-2">
                                <Label for="gender">Gender</Label>
                                <Select v-model="form.gender">
                                    <SelectTrigger id="gender">
                                        <SelectValue placeholder="Select gender" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="male">Male</SelectItem>
                                        <SelectItem value="female">Female</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.gender" />
                            </div>

                            <div class="space-y-2">
                                <Label for="status">Status</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger id="status">
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="active">Active</SelectItem>
                                        <SelectItem value="transferred">Transferred</SelectItem>
                                        <SelectItem value="dropped">Dropped</SelectItem>
                                        <SelectItem value="graduated">Graduated</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.status" />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Right column -->
                    <div class="space-y-6">
                        <!-- Contact Info -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Contact Information</CardTitle>
                                <CardDescription>
                                    Address and contact details.
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="address">Address</Label>
                                    <Textarea
                                        id="address"
                                        v-model="form.address"
                                        placeholder="Full address"
                                        rows="3"
                                    />
                                    <InputError :message="form.errors.address" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="contact_number">Contact Number</Label>
                                    <Input
                                        id="contact_number"
                                        v-model="form.contact_number"
                                        type="text"
                                        placeholder="Contact number"
                                    />
                                    <InputError :message="form.errors.contact_number" />
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Guardian Info -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Guardian Information</CardTitle>
                                <CardDescription>
                                    Parent or guardian details.
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="guardian_name">Guardian Name</Label>
                                    <Input
                                        id="guardian_name"
                                        v-model="form.guardian_name"
                                        type="text"
                                        placeholder="Full name of guardian"
                                    />
                                    <InputError :message="form.errors.guardian_name" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="guardian_contact">Guardian Contact</Label>
                                    <Input
                                        id="guardian_contact"
                                        v-model="form.guardian_contact"
                                        type="text"
                                        placeholder="Guardian contact number"
                                    />
                                    <InputError :message="form.errors.guardian_contact" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="guardian_relationship">Relationship</Label>
                                    <Input
                                        id="guardian_relationship"
                                        v-model="form.guardian_relationship"
                                        type="text"
                                        placeholder="e.g., Mother, Father, Guardian"
                                    />
                                    <InputError :message="form.errors.guardian_relationship" />
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Previous School -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Previous School</CardTitle>
                                <CardDescription>
                                    School previously attended.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-2">
                                    <Label for="previous_school">School Name</Label>
                                    <Input
                                        id="previous_school"
                                        v-model="form.previous_school"
                                        type="text"
                                        placeholder="Name of previous school"
                                    />
                                    <InputError :message="form.errors.previous_school" />
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- Submit -->
                <div class="mx-auto mt-6 flex w-full max-w-4xl justify-end gap-4">
                    <Button variant="outline" type="button" as-child>
                        <Link href="/students">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        Update Student
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
