<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { AlertTriangle, Check, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { ref } from 'vue';
import PageHeader from '@/components/App/PageHeader.vue';
import { useUnsavedChangesGuard } from '@/composables/useUnsavedChangesGuard';
import InputError from '@/components/InputError.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
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
import {
    Stepper,
    StepperDescription,
    StepperIndicator,
    StepperItem,
    StepperSeparator,
    StepperTitle,
    StepperTrigger,
} from '@/components/ui/stepper';
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

useUnsavedChangesGuard(form);

const currentStep = ref(1);

const steps = [
    { step: 1, title: 'Personal', description: 'Basic info' },
    { step: 2, title: 'Contact', description: 'Address & phone' },
    { step: 3, title: 'Guardian', description: 'Parent/guardian' },
    { step: 4, title: 'Review', description: 'Confirm & submit' },
];

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

const step1Errors = ref<string[]>([]);

function canAdvanceFromStep1(): boolean {
    const errors: string[] = [];
    if (!form.lrn) errors.push('LRN is required');
    if (!form.last_name) errors.push('Last name is required');
    if (!form.first_name) errors.push('First name is required');
    if (!form.birthdate) errors.push('Birthdate is required');
    if (!form.gender) errors.push('Gender is required');
    step1Errors.value = errors;
    return errors.length === 0;
}

function nextStep() {
    if (currentStep.value === 1 && !canAdvanceFromStep1()) return;
    if (currentStep.value < 4) currentStep.value++;
}

function prevStep() {
    if (currentStep.value > 1) currentStep.value--;
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

            <div class="mx-auto w-full max-w-3xl">
                <!-- Stepper Header -->
                <Stepper v-model="currentStep" class="mb-8 flex w-full items-start gap-2">
                    <StepperItem
                        v-for="s in steps"
                        :key="s.step"
                        v-slot="{ state }"
                        :step="s.step"
                        class="relative flex w-full flex-col items-center justify-center"
                    >
                        <StepperSeparator
                            v-if="s.step !== steps[0].step"
                            class="absolute left-[calc(-50%+20px)] right-[calc(50%+20px)] top-5 block h-0.5 shrink-0 rounded-full bg-muted group-data-[state=completed]:bg-primary"
                        />
                        <StepperTrigger as-child>
                            <Button
                                :variant="state === 'completed' || state === 'active' ? 'default' : 'outline'"
                                size="icon"
                                class="z-10 size-10 shrink-0 rounded-full"
                                :disabled="false"
                            >
                                <Check v-if="state === 'completed'" class="size-5" />
                                <span v-else>{{ s.step }}</span>
                            </Button>
                        </StepperTrigger>

                        <div class="mt-2 flex flex-col items-center text-center">
                            <StepperTitle
                                :class="[
                                    'text-sm font-semibold transition',
                                    state === 'active' ? 'text-primary' : 'text-muted-foreground',
                                ]"
                            >
                                {{ s.title }}
                            </StepperTitle>
                            <StepperDescription class="text-xs text-muted-foreground sr-only md:not-sr-only">
                                {{ s.description }}
                            </StepperDescription>
                        </div>
                    </StepperItem>
                </Stepper>

                <form @submit.prevent="submit">
                    <!-- Step 1: Personal Information -->
                    <Card v-show="currentStep === 1">
                        <CardContent class="space-y-4 pt-6">
                            <h3 class="text-lg font-semibold">Personal Information</h3>
                            <p class="text-sm text-muted-foreground">Basic student identity and demographics.</p>

                            <!-- Step 1 validation errors -->
                            <Alert v-if="step1Errors.length > 0" variant="destructive">
                                <AlertTriangle class="size-4" />
                                <AlertTitle>Required fields missing</AlertTitle>
                                <AlertDescription>
                                    <ul class="mt-1 list-inside list-disc">
                                        <li v-for="err in step1Errors" :key="err">{{ err }}</li>
                                    </ul>
                                </AlertDescription>
                            </Alert>

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

                            <div class="grid gap-4 md:grid-cols-2">
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

                    <!-- Step 2: Contact Information -->
                    <Card v-show="currentStep === 2">
                        <CardContent class="space-y-4 pt-6">
                            <h3 class="text-lg font-semibold">Contact Information</h3>
                            <p class="text-sm text-muted-foreground">Address and contact details.</p>

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

                    <!-- Step 3: Guardian Information -->
                    <Card v-show="currentStep === 3">
                        <CardContent class="space-y-4 pt-6">
                            <h3 class="text-lg font-semibold">Guardian Information</h3>
                            <p class="text-sm text-muted-foreground">Parent or guardian details.</p>

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

                    <!-- Step 4: Previous School + Review -->
                    <Card v-show="currentStep === 4">
                        <CardContent class="space-y-6 pt-6">
                            <div>
                                <h3 class="text-lg font-semibold">Previous School</h3>
                                <p class="text-sm text-muted-foreground">School previously attended.</p>

                                <div class="mt-4 space-y-2">
                                    <Label for="previous_school">School Name</Label>
                                    <Input
                                        id="previous_school"
                                        v-model="form.previous_school"
                                        type="text"
                                        placeholder="Name of previous school"
                                    />
                                    <InputError :message="form.errors.previous_school" />
                                </div>
                            </div>

                            <div class="rounded-lg border p-4">
                                <h4 class="mb-3 font-semibold">Review Summary</h4>
                                <dl class="grid gap-2 text-sm md:grid-cols-2">
                                    <div>
                                        <dt class="text-muted-foreground">LRN</dt>
                                        <dd class="font-medium">{{ form.lrn || '—' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-muted-foreground">Name</dt>
                                        <dd class="font-medium">
                                            {{ form.last_name }}, {{ form.first_name }}
                                            {{ form.middle_name ? form.middle_name + ' ' : '' }}{{ form.suffix }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-muted-foreground">Birthdate</dt>
                                        <dd class="font-medium">{{ form.birthdate || '—' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-muted-foreground">Gender</dt>
                                        <dd class="font-medium capitalize">{{ form.gender || '—' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-muted-foreground">Address</dt>
                                        <dd class="font-medium">{{ form.address || '—' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-muted-foreground">Contact</dt>
                                        <dd class="font-medium">{{ form.contact_number || '—' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-muted-foreground">Guardian</dt>
                                        <dd class="font-medium">
                                            {{ form.guardian_name || '—' }}
                                            <span v-if="form.guardian_relationship" class="text-muted-foreground">
                                                ({{ form.guardian_relationship }})
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-muted-foreground">Guardian Contact</dt>
                                        <dd class="font-medium">{{ form.guardian_contact || '—' }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Navigation -->
                    <div class="mt-6 flex justify-between">
                        <Button
                            v-if="currentStep > 1"
                            type="button"
                            variant="outline"
                            @click="prevStep"
                        >
                            <ChevronLeft class="mr-1 size-4" />
                            Previous
                        </Button>
                        <div v-else />

                        <div class="flex gap-4">
                            <Button variant="outline" type="button" as-child>
                                <Link href="/students">Cancel</Link>
                            </Button>
                            <Button
                                v-if="currentStep < 4"
                                type="button"
                                @click="nextStep"
                            >
                                Next
                                <ChevronRight class="ml-1 size-4" />
                            </Button>
                            <Button
                                v-else
                                type="submit"
                                :disabled="form.processing"
                            >
                                Create Student
                            </Button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
