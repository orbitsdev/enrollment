<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogScrollContent,
    DialogTitle,
} from '@/components/ui/dialog';
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
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
import type { TeacherProfile } from '@/types';
import type { User } from '@/types/auth';

type TeacherUser = User & {
    is_active: boolean;
    teacher_profile: TeacherProfile | null;
};

const props = defineProps<{
    open: boolean;
    teacher: TeacherUser | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const form = useForm({
    employee_id: '',
    position_title: '',
    appointment_status: '',
    sex: '',
    birthdate: '',
    contact_number: '',
    address: '',
    highest_degree: '',
    degree_course: '',
    degree_major: '',
    school_graduated: '',
    year_graduated: '',
    prc_license_number: '',
    prc_validity: '',
    eligibility: '',
    specialization: '',
    date_hired: '',
    teaching_hours_per_week: '',
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen && props.teacher) {
            const p = props.teacher.teacher_profile;
            form.employee_id = p?.employee_id ?? '';
            form.position_title = p?.position_title ?? '';
            form.appointment_status = p?.appointment_status ?? '';
            form.sex = p?.sex ?? '';
            form.birthdate = p?.birthdate ?? '';
            form.contact_number = p?.contact_number ?? '';
            form.address = p?.address ?? '';
            form.highest_degree = p?.highest_degree ?? '';
            form.degree_course = p?.degree_course ?? '';
            form.degree_major = p?.degree_major ?? '';
            form.school_graduated = p?.school_graduated ?? '';
            form.year_graduated = p?.year_graduated?.toString() ?? '';
            form.prc_license_number = p?.prc_license_number ?? '';
            form.prc_validity = p?.prc_validity ?? '';
            form.eligibility = p?.eligibility ?? '';
            form.specialization = p?.specialization ?? '';
            form.date_hired = p?.date_hired ?? '';
            form.teaching_hours_per_week = p?.teaching_hours_per_week?.toString() ?? '';
            form.clearErrors();
        }
    },
);

function submit() {
    if (!props.teacher) return;
    form.put(`/teachers/${props.teacher.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            emit('update:open', false);
        },
    });
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogScrollContent class="sm:max-w-3xl">
            <DialogHeader>
                <DialogTitle>Edit {{ teacher?.name }}</DialogTitle>
                <DialogDescription>
                    Update teacher profile information.
                </DialogDescription>
            </DialogHeader>

            <form v-if="teacher" class="space-y-4" @submit.prevent="submit">
                <Tabs default-value="personal">
                    <TabsList>
                        <TabsTrigger value="personal">Personal</TabsTrigger>
                        <TabsTrigger value="employment">Employment</TabsTrigger>
                        <TabsTrigger value="education">Education</TabsTrigger>
                    </TabsList>

                    <!-- Personal Information -->
                    <TabsContent value="personal" class="mt-4 space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="dlg_employee_id">Employee ID</Label>
                                <Input id="dlg_employee_id" v-model="form.employee_id" placeholder="DepEd Employee ID" />
                                <InputError :message="form.errors.employee_id" />
                            </div>
                            <div class="space-y-2">
                                <Label for="dlg_sex">Sex</Label>
                                <Select v-model="form.sex">
                                    <SelectTrigger id="dlg_sex">
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
                                <Label for="dlg_birthdate">Birthdate</Label>
                                <Input id="dlg_birthdate" v-model="form.birthdate" type="date" />
                                <InputError :message="form.errors.birthdate" />
                            </div>
                            <div class="space-y-2">
                                <Label for="dlg_contact_number">Contact Number</Label>
                                <Input id="dlg_contact_number" v-model="form.contact_number" placeholder="Contact number" />
                                <InputError :message="form.errors.contact_number" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="dlg_address">Address</Label>
                            <Textarea id="dlg_address" v-model="form.address" placeholder="Full address" rows="2" />
                            <InputError :message="form.errors.address" />
                        </div>
                    </TabsContent>

                    <!-- Employment -->
                    <TabsContent value="employment" class="mt-4 space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="dlg_position_title">Position Title</Label>
                                <Input id="dlg_position_title" v-model="form.position_title" placeholder="e.g., Teacher I" />
                                <InputError :message="form.errors.position_title" />
                            </div>
                            <div class="space-y-2">
                                <Label for="dlg_appointment_status">Appointment Status</Label>
                                <Select v-model="form.appointment_status">
                                    <SelectTrigger id="dlg_appointment_status">
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
                                <Label for="dlg_specialization">Specialization</Label>
                                <Input id="dlg_specialization" v-model="form.specialization" placeholder="Teaching specialization" />
                                <InputError :message="form.errors.specialization" />
                            </div>
                            <div class="space-y-2">
                                <Label for="dlg_date_hired">Date Hired</Label>
                                <Input id="dlg_date_hired" v-model="form.date_hired" type="date" />
                                <InputError :message="form.errors.date_hired" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="dlg_teaching_hours">Teaching Hours Per Week</Label>
                            <Input id="dlg_teaching_hours" v-model="form.teaching_hours_per_week" type="number" min="0" max="60" placeholder="0" />
                            <InputError :message="form.errors.teaching_hours_per_week" />
                        </div>
                    </TabsContent>

                    <!-- Education & Credentials -->
                    <TabsContent value="education" class="mt-4 space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="dlg_highest_degree">Highest Degree</Label>
                                <Select v-model="form.highest_degree">
                                    <SelectTrigger id="dlg_highest_degree">
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
                                <Label for="dlg_degree_course">Degree Course</Label>
                                <Input id="dlg_degree_course" v-model="form.degree_course" placeholder="e.g., BSEd Mathematics" />
                                <InputError :message="form.errors.degree_course" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="dlg_degree_major">Major</Label>
                                <Input id="dlg_degree_major" v-model="form.degree_major" placeholder="e.g., Mathematics" />
                                <InputError :message="form.errors.degree_major" />
                            </div>
                            <div class="space-y-2">
                                <Label for="dlg_school_graduated">School Graduated</Label>
                                <Input id="dlg_school_graduated" v-model="form.school_graduated" placeholder="University name" />
                                <InputError :message="form.errors.school_graduated" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="dlg_year_graduated">Year Graduated</Label>
                            <Input id="dlg_year_graduated" v-model="form.year_graduated" type="number" min="1950" max="2099" placeholder="Year" />
                            <InputError :message="form.errors.year_graduated" />
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="dlg_prc_license">PRC License Number</Label>
                                <Input id="dlg_prc_license" v-model="form.prc_license_number" placeholder="License number" />
                                <InputError :message="form.errors.prc_license_number" />
                            </div>
                            <div class="space-y-2">
                                <Label for="dlg_prc_validity">PRC Validity</Label>
                                <Input id="dlg_prc_validity" v-model="form.prc_validity" type="date" />
                                <InputError :message="form.errors.prc_validity" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="dlg_eligibility">Eligibility</Label>
                            <Input id="dlg_eligibility" v-model="form.eligibility" placeholder="e.g., LET Passer, CSC Professional" />
                            <InputError :message="form.errors.eligibility" />
                        </div>
                    </TabsContent>
                </Tabs>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="emit('update:open', false)">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        Save Profile
                    </Button>
                </DialogFooter>
            </form>
        </DialogScrollContent>
    </Dialog>
</template>
