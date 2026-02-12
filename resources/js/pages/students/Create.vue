<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { AlertTriangle } from 'lucide-vue-next';
import { ref } from 'vue';
import PageHeader from '@/components/App/PageHeader.vue';
import InputError from '@/components/InputError.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
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
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Students', href: '/students' },
    { title: 'Add Student' },
];

const form = useForm({
    lrn: '',
    last_name: '',
    first_name: '',
    middle_name: '',
    suffix: '',
    birthdate: '',
    gender: '',
    address: '',
    contact_number: '',
    guardian_name: '',
    guardian_contact: '',
    guardian_relationship: '',
    previous_school: '',
});

const duplicates = ref<Array<{ id: number; lrn: string; full_name: string }>>([]);
const checkingDuplicates = ref(false);

async function checkDuplicates() {
    if (!form.last_name || !form.first_name || !form.birthdate) return;

    checkingDuplicates.value = true;
    try {
        const response = await fetch('/api/students/duplicate-check', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(
                    document.cookie
                        .split('; ')
                        .find((row) => row.startsWith('XSRF-TOKEN='))
                        ?.split('=')[1] ?? '',
                ),
            },
            body: JSON.stringify({
                last_name: form.last_name,
                first_name: form.first_name,
                birthdate: form.birthdate,
            }),
        });
        const data = await response.json();
        duplicates.value = data.duplicates ?? [];
    } catch {
        duplicates.value = [];
    } finally {
        checkingDuplicates.value = false;
    }
}

function submit() {
    form.post('/students', {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Add Student" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <PageHeader
                title="Add Student"
                description="Register a new student in the system."
            >
                <template #actions>
                    <Button variant="outline" as-child>
                        <Link href="/students">Cancel</Link>
                    </Button>
                </template>
            </PageHeader>

            <!-- Duplicate Warning -->
            <Alert v-if="duplicates.length > 0" variant="destructive">
                <AlertTriangle class="size-4" />
                <AlertTitle>Possible Duplicate Found</AlertTitle>
                <AlertDescription>
                    The following student(s) match the name and birthdate:
                    <ul class="mt-2 list-inside list-disc">
                        <li v-for="dup in duplicates" :key="dup.id">
                            {{ dup.full_name }} (LRN: {{ dup.lrn }})
                        </li>
                    </ul>
                </AlertDescription>
            </Alert>

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
                                    @blur="checkDuplicates"
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
                                    @blur="checkDuplicates"
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
                                    @blur="checkDuplicates"
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
                        Create Student
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
