<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { type BreadcrumbItem } from '@/types';
import { ref, onMounted, onUnmounted } from 'vue';
import {
    BookOpen,
    Rocket,
    ShieldCheck,
    ClipboardList,
    GraduationCap,
    UserCheck,
    CalendarDays,
    PenLine,
    FileBarChart,
    Star,
    HelpCircle,
    ChevronRight,
    Menu,
    X,
} from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Documentation', href: '/documentation' },
];

const sections = [
    { id: 'overview', title: 'System Overview', icon: BookOpen },
    { id: 'getting-started', title: 'Getting Started', icon: Rocket },
    { id: 'role-admin', title: 'Admin Guide', icon: ShieldCheck },
    { id: 'role-registrar', title: 'Registrar Guide', icon: ClipboardList },
    { id: 'role-teacher', title: 'Teacher Guide', icon: GraduationCap },
    { id: 'role-student', title: 'Student Guide', icon: UserCheck },
    { id: 'journey-enrollment', title: 'Enrollment Day', icon: CalendarDays },
    { id: 'journey-grading', title: 'Grade Encoding Period', icon: PenLine },
    { id: 'journey-end-semester', title: 'End of Semester', icon: FileBarChart },
    { id: 'journey-new-year', title: 'New School Year Setup', icon: Star },
    { id: 'faq', title: 'FAQ', icon: HelpCircle },
];

const activeSection = ref('overview');
const mobileMenuOpen = ref(false);

function scrollToSection(id: string) {
    const el = document.getElementById(id);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        activeSection.value = id;
        mobileMenuOpen.value = false;
    }
}

function handleScroll() {
    const scrollPosition = window.scrollY + 120;
    for (let i = sections.length - 1; i >= 0; i--) {
        const el = document.getElementById(sections[i].id);
        if (el && el.offsetTop <= scrollPosition) {
            activeSection.value = sections[i].id;
            break;
        }
    }
}

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <Head title="Documentation" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex gap-6 p-4 md:p-6">
            <!-- Mobile TOC Toggle -->
            <button
                class="fixed bottom-4 right-4 z-50 flex h-12 w-12 items-center justify-center rounded-full bg-primary text-primary-foreground shadow-lg lg:hidden"
                @click="mobileMenuOpen = !mobileMenuOpen"
            >
                <Menu v-if="!mobileMenuOpen" class="h-5 w-5" />
                <X v-else class="h-5 w-5" />
            </button>

            <!-- Mobile TOC Overlay -->
            <div
                v-if="mobileMenuOpen"
                class="fixed inset-0 z-40 bg-background/80 backdrop-blur-sm lg:hidden"
                @click="mobileMenuOpen = false"
            />

            <!-- Sidebar TOC -->
            <aside
                :class="[
                    'lg:sticky lg:top-20 lg:block lg:h-[calc(100vh-6rem)] lg:w-64 lg:shrink-0 lg:overflow-y-auto',
                    mobileMenuOpen
                        ? 'fixed inset-y-0 left-0 z-50 block w-72 overflow-y-auto bg-background p-4 shadow-xl'
                        : 'hidden',
                ]"
            >
                <nav class="space-y-1">
                    <h3 class="mb-3 text-sm font-semibold text-muted-foreground uppercase tracking-wider">
                        Table of Contents
                    </h3>
                    <button
                        v-for="section in sections"
                        :key="section.id"
                        class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-left text-sm transition-colors"
                        :class="
                            activeSection === section.id
                                ? 'bg-primary/10 text-primary font-medium'
                                : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                        "
                        @click="scrollToSection(section.id)"
                    >
                        <component :is="section.icon" class="h-4 w-4 shrink-0" />
                        <span>{{ section.title }}</span>
                    </button>
                </nav>
            </aside>

            <!-- Main Content -->
            <div class="min-w-0 flex-1 space-y-8">
                <!-- System Overview -->
                <Card id="overview" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-xl">
                            <BookOpen class="h-5 w-5 text-primary" />
                            System Overview
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="prose prose-sm max-w-none dark:prose-invert">
                        <p>
                            Welcome to the <strong>Senior High School Enrollment System</strong>. This web-based platform
                            streamlines the entire enrollment workflow for DepEd Senior High Schools &mdash; from student
                            registration and section assignment to grade management and official report generation.
                        </p>
                        <h4>Who is this system for?</h4>
                        <div class="not-prose flex flex-wrap gap-2 mb-4">
                            <Badge variant="default">Admin</Badge>
                            <Badge variant="secondary">Registrar</Badge>
                            <Badge variant="outline">Teacher</Badge>
                            <Badge variant="outline">Student</Badge>
                        </div>
                        <ul>
                            <li><strong>Admins</strong> manage the entire system: users, school settings, school years, semesters, and curriculum.</li>
                            <li><strong>Registrars</strong> handle student records, enrollment, sections, and report generation.</li>
                            <li><strong>Teachers</strong> enter and lock grades for their assigned sections and subjects.</li>
                            <li><strong>Students</strong> view their profile, enrolled subjects, and grades through a personal portal.</li>
                        </ul>
                        <h4>Key Features</h4>
                        <ul>
                            <li>Track &amp; strand-based curriculum management (Academic, TVL, Sports, Arts &amp; Design)</li>
                            <li>Semester-based enrollment with duplicate detection</li>
                            <li>Grade entry with midterm/finals and locking workflow</li>
                            <li>Official DepEd school forms: SF1, SF5, SF9, SF10</li>
                            <li>CSV import for bulk student and grade data</li>
                            <li>Role-based access control with per-feature permissions</li>
                        </ul>
                    </CardContent>
                </Card>

                <!-- Getting Started -->
                <Card id="getting-started" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-xl">
                            <Rocket class="h-5 w-5 text-primary" />
                            Getting Started
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="prose prose-sm max-w-none dark:prose-invert">
                        <h4>1. First Login</h4>
                        <p>
                            Your account is created by the school administrator. You will receive your email and temporary
                            password. Visit the login page and enter your credentials.
                        </p>
                        <ol>
                            <li>Go to the login page and enter your <strong>email</strong> and <strong>password</strong>.</li>
                            <li>You will be redirected to the <strong>Dashboard</strong> after a successful login.</li>
                            <li>The dashboard shows a summary of enrollment statistics, section capacity, and recent enrollments.</li>
                        </ol>

                        <h4>2. Change Your Password</h4>
                        <p>
                            For security, change your password after your first login:
                        </p>
                        <ol>
                            <li>Click your <strong>name/avatar</strong> at the top-right corner of the page.</li>
                            <li>Select <strong>Settings</strong>.</li>
                            <li>Go to the <strong>Password</strong> tab.</li>
                            <li>Enter your current password, then your new password (confirm it), and click <strong>Save</strong>.</li>
                        </ol>

                        <h4>3. Navigating the System</h4>
                        <p>
                            Use the <strong>sidebar</strong> on the left to access different modules. The available menu items
                            depend on your role. You can collapse the sidebar on desktop by clicking the toggle button.
                        </p>
                        <ul>
                            <li><strong>Breadcrumbs</strong> at the top of each page show your current location and allow quick navigation.</li>
                            <li><strong>Search and filter</strong> controls appear on list pages (students, sections, enrollments) to help you find records quickly.</li>
                        </ul>
                    </CardContent>
                </Card>

                <!-- Role Guides Header -->
                <div class="border-b pb-2">
                    <h2 class="text-lg font-semibold text-foreground">Role Guides</h2>
                    <p class="text-sm text-muted-foreground">Detailed instructions for each user role with real-world scenarios.</p>
                </div>

                <!-- Admin Guide -->
                <Card id="role-admin" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-xl">
                            <ShieldCheck class="h-5 w-5 text-primary" />
                            Admin Guide
                            <Badge variant="default" class="ml-auto">Admin</Badge>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="prose prose-sm max-w-none dark:prose-invert">
                        <h4>Managing Users</h4>
                        <p>Navigate to <strong>Users</strong> in the sidebar to manage system accounts.</p>
                        <ul>
                            <li><strong>Create a user:</strong> Click "Add User", fill in name, email, password, and assign a role (admin, registrar, teacher, or student).</li>
                            <li><strong>Deactivate a user:</strong> Click the toggle button on the user row. Deactivated users cannot log in.</li>
                            <li><strong>Edit a user:</strong> Click the user row to update their details or change their role.</li>
                        </ul>
                        <div class="not-prose rounded-md bg-muted p-3 text-sm">
                            <strong>Scenario:</strong> A new teacher joins mid-semester. Go to Users &rarr; Add User &rarr; select role "Teacher" &rarr; the teacher can now log in and access Grade Entry once assigned to sections.
                        </div>

                        <h4 class="mt-6">School Settings</h4>
                        <p>Configure your school's basic information under <strong>School Settings</strong>:</p>
                        <ul>
                            <li>School name, School ID, address, region, division</li>
                            <li>School head name and contact information</li>
                        </ul>

                        <h4>School Years &amp; Semesters</h4>
                        <p>Manage academic periods under <strong>School Settings</strong>:</p>
                        <ol>
                            <li><strong>Create a school year</strong> (e.g., 2025-2026).</li>
                            <li>Each school year automatically gets two semesters.</li>
                            <li><strong>Activate</strong> the school year to make it current.</li>
                            <li><strong>Activate a semester</strong> and toggle <strong>enrollment open/closed</strong> to control when registrars can enroll students.</li>
                        </ol>

                        <h4>Curriculum Management</h4>
                        <p>Navigate to <strong>Curriculum</strong> to manage tracks, strands, and subjects:</p>
                        <ul>
                            <li><strong>Tracks:</strong> Academic, TVL, Sports, Arts &amp; Design (create/edit/toggle active).</li>
                            <li><strong>Strands:</strong> Under each track (e.g., STEM, ABM, HUMSS under Academic).</li>
                            <li><strong>Subjects:</strong> Create subjects and assign them to grade levels and semesters.</li>
                        </ul>

                        <h4>Unlocking Grades</h4>
                        <p>
                            When a teacher locks grades for a section/subject, only an admin can <strong>unlock</strong> them.
                            Navigate to <strong>Grade Entry</strong>, select the section and subject, and click "Unlock" if corrections
                            are needed.
                        </p>
                        <div class="not-prose rounded-md bg-muted p-3 text-sm">
                            <strong>Scenario:</strong> A teacher locked grades but realizes one student's midterm score was entered incorrectly. The teacher asks the admin to unlock the grades. Admin goes to Grade Entry &rarr; selects the section &amp; subject &rarr; clicks Unlock &rarr; the teacher can now correct the score and re-lock.
                        </div>
                    </CardContent>
                </Card>

                <!-- Registrar Guide -->
                <Card id="role-registrar" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-xl">
                            <ClipboardList class="h-5 w-5 text-primary" />
                            Registrar Guide
                            <Badge variant="secondary" class="ml-auto">Registrar</Badge>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="prose prose-sm max-w-none dark:prose-invert">
                        <h4>Enrolling Students</h4>
                        <p>The enrollment process follows these steps:</p>
                        <ol>
                            <li>Go to <strong>Enrollment</strong> &rarr; click <strong>"Enroll Student"</strong>.</li>
                            <li>Search for an existing student or create a new student record.</li>
                            <li>Select the student's <strong>grade level</strong> and <strong>strand</strong>.</li>
                            <li>The system automatically loads the <strong>subject list</strong> based on the curriculum.</li>
                            <li>Choose an available <strong>section</strong>.</li>
                            <li>Review and confirm the enrollment.</li>
                        </ol>
                        <div class="not-prose rounded-md bg-muted p-3 text-sm">
                            <strong>Scenario:</strong> A Grade 12 STEM student arrives for enrollment. Search their name &rarr; select Grade 12 &rarr; STEM strand &rarr; subjects auto-load &rarr; pick an available section &rarr; confirm. The student appears in the enrollment list and class roster.
                        </div>

                        <h4 class="mt-6">Managing Student Records</h4>
                        <p>Under <strong>Students</strong>, you can:</p>
                        <ul>
                            <li><strong>Add</strong> new student records with personal info, LRN, address, guardian details.</li>
                            <li><strong>Edit</strong> existing student information.</li>
                            <li><strong>Search</strong> by name, LRN, or other fields.</li>
                            <li>The system checks for <strong>duplicate LRNs</strong> automatically.</li>
                        </ul>

                        <h4>Managing Sections</h4>
                        <p>Under <strong>Sections</strong>:</p>
                        <ul>
                            <li>Create sections with a name, grade level, strand, and maximum capacity.</li>
                            <li>Assign an <strong>adviser</strong> (teacher) to each section.</li>
                            <li>View the current enrollment count vs. capacity.</li>
                        </ul>

                        <h4>Generating Reports</h4>
                        <p>Navigate to <strong>Reports</strong> to generate official documents:</p>
                        <ul>
                            <li><strong>SF1 (School Register):</strong> Per section, lists all enrolled students.</li>
                            <li><strong>SF5 (Report on Promotion):</strong> Per section, summarizes grade outcomes.</li>
                            <li><strong>SF9 (Report Card):</strong> Per student, shows grades for the semester.</li>
                            <li><strong>SF10 (Learner's Permanent Record):</strong> Per student, full academic history.</li>
                            <li><strong>Enrollment Summary:</strong> Counts by track, strand, grade level.</li>
                            <li><strong>Class List &amp; Masterlist:</strong> Exportable to CSV.</li>
                        </ul>

                        <h4>Importing Data</h4>
                        <p>Under <strong>Import Data</strong>:</p>
                        <ol>
                            <li>Download the <strong>CSV template</strong> for students or grades.</li>
                            <li>Fill in the template with your data.</li>
                            <li>Upload the CSV file.</li>
                            <li>Review the preview table for any validation errors.</li>
                            <li>Confirm the import to save records.</li>
                        </ol>
                    </CardContent>
                </Card>

                <!-- Teacher Guide -->
                <Card id="role-teacher" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-xl">
                            <GraduationCap class="h-5 w-5 text-primary" />
                            Teacher Guide
                            <Badge variant="outline" class="ml-auto">Teacher</Badge>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="prose prose-sm max-w-none dark:prose-invert">
                        <h4>Entering Grades</h4>
                        <ol>
                            <li>Go to <strong>Grade Entry</strong> from the sidebar.</li>
                            <li>You will see a list of <strong>sections and subjects</strong> assigned to you.</li>
                            <li>Click on a section/subject to open the grade entry form.</li>
                            <li>Enter <strong>midterm</strong> and <strong>finals</strong> grades for each student.</li>
                            <li>The system automatically calculates the <strong>final grade</strong> (average of midterm and finals).</li>
                            <li>Click <strong>Save</strong> to save your progress (you can return to edit later).</li>
                        </ol>
                        <div class="not-prose rounded-md bg-muted p-3 text-sm">
                            <strong>Scenario:</strong> It's midterm week. Go to Grade Entry &rarr; select your section (e.g., 11-STEM-A) and subject (e.g., General Mathematics) &rarr; enter midterm scores for each student &rarr; Save. Come back after finals to enter finals scores.
                        </div>

                        <h4 class="mt-6">Locking Grades</h4>
                        <p>
                            When all grades for a section/subject are finalized, click <strong>"Lock Grades"</strong>.
                        </p>
                        <ul>
                            <li>Locked grades <strong>cannot be edited</strong> unless an admin unlocks them.</li>
                            <li>Locking signals to the registrar and admin that grades are final and ready for report generation.</li>
                            <li>Make sure all grades are correct before locking &mdash; contact the admin if you need to make corrections after locking.</li>
                        </ul>

                        <h4>Viewing Assigned Sections</h4>
                        <p>
                            The Grade Entry page shows all sections and subjects currently assigned to you for the active semester.
                            Each entry shows the section name, subject, and the grade entry status (e.g., number of students graded, lock status).
                        </p>
                    </CardContent>
                </Card>

                <!-- Student Guide -->
                <Card id="role-student" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-xl">
                            <UserCheck class="h-5 w-5 text-primary" />
                            Student Guide
                            <Badge variant="outline" class="ml-auto">Student</Badge>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="prose prose-sm max-w-none dark:prose-invert">
                        <h4>Your Portal</h4>
                        <p>As a student, you have a personal portal with three sections:</p>
                        <ul>
                            <li><strong>My Profile:</strong> View your personal information, LRN, address, and guardian details. Contact the registrar if any information needs correction.</li>
                            <li><strong>My Subjects:</strong> See the list of subjects you are enrolled in for the current semester, including the section and teacher assigned to each subject.</li>
                            <li><strong>My Grades:</strong> View your midterm and finals grades once they have been entered and finalized by your teachers.</li>
                        </ul>
                        <div class="not-prose rounded-md bg-muted p-3 text-sm">
                            <strong>Note:</strong> Grades will only appear after your teacher has entered them. If you believe there's an error in your grades, contact your teacher or the registrar's office.
                        </div>
                    </CardContent>
                </Card>

                <!-- User Journeys Header -->
                <div class="border-b pb-2">
                    <h2 class="text-lg font-semibold text-foreground">User Journeys</h2>
                    <p class="text-sm text-muted-foreground">Step-by-step walkthroughs for common school processes.</p>
                </div>

                <!-- Enrollment Day -->
                <Card id="journey-enrollment" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-xl">
                            <CalendarDays class="h-5 w-5 text-primary" />
                            Enrollment Day
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="prose prose-sm max-w-none dark:prose-invert">
                        <p>A complete walkthrough of the enrollment process from start to finish.</p>

                        <h4>Before Enrollment Opens</h4>
                        <div class="not-prose space-y-2">
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="default" class="mt-0.5 shrink-0">Admin</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">1. Ensure the school year and semester are active</p>
                                    <p class="text-muted-foreground">Go to School Settings &rarr; verify the correct school year is active and the semester is set.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="default" class="mt-0.5 shrink-0">Admin</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">2. Open enrollment for the semester</p>
                                    <p class="text-muted-foreground">Toggle "Enrollment Open" for the active semester. Registrars can now process enrollments.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="secondary" class="mt-0.5 shrink-0">Registrar</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">3. Verify sections are set up</p>
                                    <p class="text-muted-foreground">Check that sections exist for all grade levels and strands, with advisers assigned and correct capacity limits.</p>
                                </div>
                            </div>
                        </div>

                        <h4 class="mt-6">During Enrollment</h4>
                        <div class="not-prose space-y-2">
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="secondary" class="mt-0.5 shrink-0">Registrar</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">4. Student arrives &mdash; search or create record</p>
                                    <p class="text-muted-foreground">Go to Enrollment &rarr; Enroll Student. Search by name or LRN. If the student is new, create a new record with all required information.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="secondary" class="mt-0.5 shrink-0">Registrar</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">5. Select grade level and strand</p>
                                    <p class="text-muted-foreground">Choose the appropriate grade level (11 or 12) and strand (e.g., STEM, ABM, HUMSS, TVL).</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="secondary" class="mt-0.5 shrink-0">Registrar</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">6. Review subjects and assign section</p>
                                    <p class="text-muted-foreground">Subjects auto-load from the curriculum. Select an available section (capacity is checked automatically).</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="secondary" class="mt-0.5 shrink-0">Registrar</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">7. Confirm enrollment</p>
                                    <p class="text-muted-foreground">Review all details and submit. The student is now enrolled and will appear in class lists and the dashboard count.</p>
                                </div>
                            </div>
                        </div>

                        <h4 class="mt-6">After Enrollment</h4>
                        <div class="not-prose space-y-2">
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="secondary" class="mt-0.5 shrink-0">Registrar</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">8. Print enrollment slip (optional)</p>
                                    <p class="text-muted-foreground">From the enrollment record, click "Print" to generate an enrollment confirmation slip for the student.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="default" class="mt-0.5 shrink-0">Admin</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">9. Close enrollment when done</p>
                                    <p class="text-muted-foreground">Toggle "Enrollment Open" off to prevent further enrollments until the next period.</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Grade Encoding Period -->
                <Card id="journey-grading" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-xl">
                            <PenLine class="h-5 w-5 text-primary" />
                            Grade Encoding Period
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="prose prose-sm max-w-none dark:prose-invert">
                        <p>The complete workflow for entering, reviewing, and finalizing grades.</p>

                        <h4>Midterm Grades</h4>
                        <div class="not-prose space-y-2">
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="outline" class="mt-0.5 shrink-0">Teacher</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">1. Enter midterm grades</p>
                                    <p class="text-muted-foreground">Go to Grade Entry &rarr; select your section and subject &rarr; enter midterm scores for all students &rarr; Save.</p>
                                </div>
                            </div>
                        </div>

                        <h4 class="mt-6">Finals Grades</h4>
                        <div class="not-prose space-y-2">
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="outline" class="mt-0.5 shrink-0">Teacher</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">2. Enter finals grades</p>
                                    <p class="text-muted-foreground">Return to Grade Entry &rarr; enter finals scores &rarr; the system calculates final grades automatically &rarr; Save.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="outline" class="mt-0.5 shrink-0">Teacher</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">3. Review and lock grades</p>
                                    <p class="text-muted-foreground">Double-check all entries. Click "Lock Grades" to finalize. Locked grades cannot be edited without admin approval.</p>
                                </div>
                            </div>
                        </div>

                        <h4 class="mt-6">If Corrections Are Needed</h4>
                        <div class="not-prose space-y-2">
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="outline" class="mt-0.5 shrink-0">Teacher</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">4. Request unlock from admin</p>
                                    <p class="text-muted-foreground">Contact the admin and specify which section and subject need corrections.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="default" class="mt-0.5 shrink-0">Admin</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">5. Admin unlocks the grades</p>
                                    <p class="text-muted-foreground">Admin navigates to Grade Entry &rarr; selects the section/subject &rarr; clicks Unlock.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="outline" class="mt-0.5 shrink-0">Teacher</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">6. Teacher corrects and re-locks</p>
                                    <p class="text-muted-foreground">Make the corrections &rarr; Save &rarr; Lock Grades again.</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- End of Semester -->
                <Card id="journey-end-semester" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-xl">
                            <FileBarChart class="h-5 w-5 text-primary" />
                            End of Semester
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="prose prose-sm max-w-none dark:prose-invert">
                        <p>Tasks to complete at the end of each semester.</p>

                        <div class="not-prose space-y-2">
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="default" class="mt-0.5 shrink-0">Admin</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">1. Verify all grades are locked</p>
                                    <p class="text-muted-foreground">Check Grade Entry for any unlocked sections. Follow up with teachers who haven't submitted grades yet.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="secondary" class="mt-0.5 shrink-0">Registrar</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">2. Generate SF9 (Report Cards)</p>
                                    <p class="text-muted-foreground">Go to Reports &rarr; School Forms &rarr; select enrollments &rarr; generate SF9 for each student. These can be printed and distributed.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="secondary" class="mt-0.5 shrink-0">Registrar</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">3. Generate SF10 (Permanent Records)</p>
                                    <p class="text-muted-foreground">Go to Reports &rarr; School Forms &rarr; generate SF10 for students who need updated permanent records.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="secondary" class="mt-0.5 shrink-0">Registrar</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">4. Generate SF1 and SF5</p>
                                    <p class="text-muted-foreground">Generate the School Register (SF1) and Report on Promotion (SF5) per section for DepEd submission.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="secondary" class="mt-0.5 shrink-0">Registrar</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">5. Export enrollment summary and masterlists</p>
                                    <p class="text-muted-foreground">Go to Reports &rarr; export enrollment summary and masterlists as CSV files for records.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="default" class="mt-0.5 shrink-0">Admin</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">6. Transition to next semester</p>
                                    <p class="text-muted-foreground">Activate the next semester under School Settings. If moving to a new school year, see the "New School Year Setup" guide below.</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- New School Year Setup -->
                <Card id="journey-new-year" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-xl">
                            <Star class="h-5 w-5 text-primary" />
                            New School Year Setup
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="prose prose-sm max-w-none dark:prose-invert">
                        <p>Complete checklist for setting up a new school year.</p>

                        <div class="not-prose space-y-2">
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="default" class="mt-0.5 shrink-0">Admin</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">1. Create the new school year</p>
                                    <p class="text-muted-foreground">Go to School Settings &rarr; click "Add School Year" &rarr; enter the year range (e.g., 2026-2027). Two semesters are created automatically.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="default" class="mt-0.5 shrink-0">Admin</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">2. Activate the school year</p>
                                    <p class="text-muted-foreground">Click "Activate" on the new school year. This makes it the current year across the system.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="default" class="mt-0.5 shrink-0">Admin</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">3. Activate the first semester</p>
                                    <p class="text-muted-foreground">Activate Semester 1 of the new school year.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="default" class="mt-0.5 shrink-0">Admin</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">4. Review curriculum</p>
                                    <p class="text-muted-foreground">Check that all tracks, strands, and subjects are up to date. Add any new subjects or deactivate outdated ones.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="secondary" class="mt-0.5 shrink-0">Registrar</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">5. Create new sections</p>
                                    <p class="text-muted-foreground">Set up sections for all grade levels and strands for the new year. Assign advisers and set capacity limits.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="default" class="mt-0.5 shrink-0">Admin</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">6. Review user accounts</p>
                                    <p class="text-muted-foreground">Deactivate accounts for teachers/staff who are no longer at the school. Create accounts for new teachers.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 rounded-md border p-3">
                                <Badge variant="default" class="mt-0.5 shrink-0">Admin</Badge>
                                <div class="text-sm">
                                    <p class="font-medium">7. Open enrollment</p>
                                    <p class="text-muted-foreground">Toggle "Enrollment Open" for Semester 1. Registrars can now begin enrolling students.</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- FAQ -->
                <Card id="faq" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-xl">
                            <HelpCircle class="h-5 w-5 text-primary" />
                            Frequently Asked Questions
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div>
                            <h4 class="text-sm font-semibold">How do I reset a user's password?</h4>
                            <p class="mt-1 text-sm text-muted-foreground">
                                As an admin, go to Users &rarr; click on the user &rarr; Edit &rarr; set a new password. Inform the user of their new password so they can log in and change it.
                            </p>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold">A student is enrolled in the wrong section. How do I fix this?</h4>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Go to the student's enrollment record and update the section assignment. If grades have already been entered, coordinate with the teachers of both sections.
                            </p>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold">Can I enroll students when enrollment is closed?</h4>
                            <p class="mt-1 text-sm text-muted-foreground">
                                No. The admin must open enrollment for the active semester before registrars can process new enrollments. Contact your admin to open enrollment.
                            </p>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold">How do I import student data from a spreadsheet?</h4>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Go to Import Data &rarr; download the CSV template &rarr; fill in your data following the template format &rarr; upload the file &rarr; review the preview &rarr; confirm the import.
                            </p>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold">Why can't I see the Grade Entry page?</h4>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Grade Entry is available to admins, registrars, and teachers. If you are a teacher and don't see any sections, it means you haven't been assigned to any sections for the current semester. Contact the registrar.
                            </p>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold">How are final grades calculated?</h4>
                            <p class="mt-1 text-sm text-muted-foreground">
                                The final grade is calculated as the average of the midterm grade and the finals grade. Both must be entered before the final grade is computed.
                            </p>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold">Can a student see their grades immediately?</h4>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Students can see their grades in the "My Grades" section of their portal once the teacher has entered them. Grades appear regardless of lock status.
                            </p>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold">What happens if I try to enroll a student with a duplicate LRN?</h4>
                            <p class="mt-1 text-sm text-muted-foreground">
                                The system performs automatic duplicate detection. If a student with the same LRN already exists, you will be alerted and can choose to use the existing record instead of creating a duplicate.
                            </p>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold">How do I generate school forms (SF1, SF5, SF9, SF10)?</h4>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Go to Reports &rarr; School Forms. Select the form type, choose the section or student, and click Generate. The form will be generated as a downloadable PDF.
                            </p>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold">Who do I contact for technical issues?</h4>
                            <p class="mt-1 text-sm text-muted-foreground">
                                For system issues, contact your school's system administrator. For account-related issues (login problems, role changes), contact the admin user.
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
