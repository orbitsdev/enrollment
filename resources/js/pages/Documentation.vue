<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
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
    Menu,
    X,
    ArrowLeft,
    ChevronRight,
} from 'lucide-vue-next';

const sections = [
    { id: 'overview', title: 'System Overview', icon: BookOpen },
    { id: 'getting-started', title: 'Getting Started', icon: Rocket },
    { id: 'role-admin', title: 'Admin Guide', icon: ShieldCheck },
    { id: 'role-registrar', title: 'Registrar Guide', icon: ClipboardList },
    { id: 'role-teacher', title: 'Teacher Guide', icon: GraduationCap },
    { id: 'role-student', title: 'Student Guide', icon: UserCheck },
    { id: 'journey-enrollment', title: 'Enrollment Day', icon: CalendarDays },
    { id: 'journey-grading', title: 'Grade Encoding', icon: PenLine },
    { id: 'journey-end-semester', title: 'End of Semester', icon: FileBarChart },
    { id: 'journey-new-year', title: 'New School Year', icon: Star },
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

onMounted(() => window.addEventListener('scroll', handleScroll));
onUnmounted(() => window.removeEventListener('scroll', handleScroll));
</script>

<template>
    <Head title="Documentation" />

    <div class="min-h-screen bg-background">
        <!-- Header -->
        <header class="sticky top-0 z-30 border-b bg-background/80 backdrop-blur-sm">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary">
                        <GraduationCap class="h-6 w-6 text-primary-foreground" />
                    </div>
                    <div class="hidden sm:block">
                        <p class="text-sm font-semibold leading-tight">Lake Sebu National</p>
                        <p class="text-xs leading-tight text-muted-foreground">High School &mdash; Documentation</p>
                    </div>
                </div>
                <Link
                    href="/"
                    class="inline-flex items-center gap-2 rounded-md border border-input bg-background px-4 py-2 text-sm font-medium shadow-sm transition-colors hover:bg-accent hover:text-accent-foreground"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Back to Home
                </Link>
            </div>
        </header>

        <!-- Layout -->
        <div class="mx-auto flex max-w-7xl gap-8 px-4 py-6 sm:px-6 lg:px-8">
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
                    'shrink-0 lg:sticky lg:top-20 lg:block lg:h-[calc(100vh-6rem)] lg:w-56 lg:overflow-y-auto',
                    mobileMenuOpen
                        ? 'fixed inset-y-0 left-0 z-50 block w-64 overflow-y-auto border-r bg-background p-4 shadow-xl'
                        : 'hidden',
                ]"
            >
                <nav class="space-y-0.5">
                    <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                        On this page
                    </p>
                    <button
                        v-for="section in sections"
                        :key="section.id"
                        class="flex w-full items-center gap-2.5 rounded-md px-3 py-1.5 text-left text-sm transition-colors"
                        :class="
                            activeSection === section.id
                                ? 'bg-accent text-accent-foreground font-medium'
                                : 'text-muted-foreground hover:bg-accent/50 hover:text-foreground'
                        "
                        @click="scrollToSection(section.id)"
                    >
                        <component :is="section.icon" class="h-3.5 w-3.5 shrink-0" />
                        <span>{{ section.title }}</span>
                    </button>
                </nav>
            </aside>

            <!-- Content -->
            <div class="min-w-0 flex-1 space-y-6">

                <!-- System Overview -->
                <Card id="overview" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle>System Overview</CardTitle>
                        <CardDescription>What the system is and who it's for.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <p class="text-sm leading-relaxed text-muted-foreground">
                            The <span class="font-medium text-foreground">Senior High School Enrollment System</span> is a web-based platform that streamlines the entire enrollment workflow for DepEd Senior High Schools &mdash; from student registration and section assignment to grade management and official report generation.
                        </p>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">User Roles</p>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="flex items-start gap-3 rounded-lg border p-3">
                                    <Badge variant="default" class="mt-0.5 shrink-0">Admin</Badge>
                                    <p class="text-sm text-muted-foreground">Manages users, school settings, school years, semesters, and curriculum.</p>
                                </div>
                                <div class="flex items-start gap-3 rounded-lg border p-3">
                                    <Badge variant="secondary" class="mt-0.5 shrink-0">Registrar</Badge>
                                    <p class="text-sm text-muted-foreground">Handles student records, enrollment, sections, and report generation.</p>
                                </div>
                                <div class="flex items-start gap-3 rounded-lg border p-3">
                                    <Badge variant="outline" class="mt-0.5 shrink-0">Teacher</Badge>
                                    <p class="text-sm text-muted-foreground">Enters and locks grades for assigned sections and subjects.</p>
                                </div>
                                <div class="flex items-start gap-3 rounded-lg border p-3">
                                    <Badge variant="outline" class="mt-0.5 shrink-0">Student</Badge>
                                    <p class="text-sm text-muted-foreground">Views profile, enrolled subjects, and grades through a personal portal.</p>
                                </div>
                            </div>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Key Features</p>
                            <ul class="grid gap-1.5 text-sm text-muted-foreground sm:grid-cols-2">
                                <li class="flex items-center gap-2"><ChevronRight class="h-3 w-3 shrink-0 text-primary" />Track &amp; strand-based curriculum</li>
                                <li class="flex items-center gap-2"><ChevronRight class="h-3 w-3 shrink-0 text-primary" />Semester-based enrollment</li>
                                <li class="flex items-center gap-2"><ChevronRight class="h-3 w-3 shrink-0 text-primary" />Midterm/finals grade entry &amp; locking</li>
                                <li class="flex items-center gap-2"><ChevronRight class="h-3 w-3 shrink-0 text-primary" />DepEd school forms (SF1, SF5, SF9, SF10)</li>
                                <li class="flex items-center gap-2"><ChevronRight class="h-3 w-3 shrink-0 text-primary" />CSV import for bulk data</li>
                                <li class="flex items-center gap-2"><ChevronRight class="h-3 w-3 shrink-0 text-primary" />Role-based access control</li>
                            </ul>
                        </div>
                    </CardContent>
                </Card>

                <!-- Getting Started -->
                <Card id="getting-started" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle>Getting Started</CardTitle>
                        <CardDescription>First login, password change, and navigation basics.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <p class="text-sm font-medium">1. First Login</p>
                            <p class="text-sm text-muted-foreground">Your account is created by the school administrator. Visit the login page and enter the email and password provided to you. After logging in, you'll be redirected to the Dashboard.</p>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">2. Change Your Password</p>
                            <div class="space-y-1.5 text-sm text-muted-foreground">
                                <p>For security, change your password after your first login:</p>
                                <ol class="list-decimal space-y-1 pl-5">
                                    <li>Click your name/avatar at the top-right corner.</li>
                                    <li>Select <span class="font-medium text-foreground">Settings</span>.</li>
                                    <li>Go to the <span class="font-medium text-foreground">Password</span> tab.</li>
                                    <li>Enter your current password, then your new password, and click Save.</li>
                                </ol>
                            </div>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">3. Navigating the System</p>
                            <div class="space-y-1.5 text-sm text-muted-foreground">
                                <p>Use the sidebar on the left to access different modules. The menu items shown depend on your role.</p>
                                <ul class="list-disc space-y-1 pl-5">
                                    <li><span class="font-medium text-foreground">Breadcrumbs</span> at the top of each page show your current location.</li>
                                    <li><span class="font-medium text-foreground">Search and filter</span> controls appear on list pages to help you find records quickly.</li>
                                </ul>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Role Guides divider -->
                <div class="flex items-center gap-3 pt-2">
                    <Separator class="flex-1" />
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Role Guides</span>
                    <Separator class="flex-1" />
                </div>

                <!-- Admin Guide -->
                <Card id="role-admin" class="scroll-mt-20">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <CardTitle>Admin Guide</CardTitle>
                                <CardDescription>Users, settings, school years, curriculum, and grade unlocking.</CardDescription>
                            </div>
                            <Badge variant="default">Admin</Badge>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <p class="text-sm font-medium">Managing Users</p>
                            <p class="text-sm text-muted-foreground">Navigate to <span class="font-medium text-foreground">Users</span> in the sidebar to manage system accounts.</p>
                            <ul class="list-disc space-y-1 pl-5 text-sm text-muted-foreground">
                                <li><span class="font-medium text-foreground">Create a user</span> &mdash; Click "Add User", fill in name, email, password, and assign a role.</li>
                                <li><span class="font-medium text-foreground">Deactivate a user</span> &mdash; Click the toggle button on the user row. Deactivated users cannot log in.</li>
                                <li><span class="font-medium text-foreground">Edit a user</span> &mdash; Click the user row to update their details or change their role.</li>
                            </ul>
                            <div class="rounded-lg bg-muted px-4 py-3 text-sm text-muted-foreground">
                                <span class="font-medium text-foreground">Scenario:</span> A new teacher joins mid-semester. Go to Users &rarr; Add User &rarr; select role "Teacher" &rarr; the teacher can now log in and access Grade Entry once assigned to sections.
                            </div>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">School Settings</p>
                            <p class="text-sm text-muted-foreground">Configure your school's basic information: school name, School ID, address, region, division, school head name, and contact information.</p>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">School Years &amp; Semesters</p>
                            <ol class="list-decimal space-y-1 pl-5 text-sm text-muted-foreground">
                                <li>Create a school year (e.g., 2025-2026). Two semesters are generated automatically.</li>
                                <li>Activate the school year to make it current.</li>
                                <li>Activate a semester and toggle enrollment open/closed to control when registrars can enroll students.</li>
                            </ol>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Curriculum Management</p>
                            <p class="text-sm text-muted-foreground">Navigate to <span class="font-medium text-foreground">Curriculum</span> to manage tracks, strands, and subjects.</p>
                            <ul class="list-disc space-y-1 pl-5 text-sm text-muted-foreground">
                                <li><span class="font-medium text-foreground">Tracks</span> &mdash; Academic, TVL, Sports, Arts &amp; Design (create / edit / toggle active).</li>
                                <li><span class="font-medium text-foreground">Strands</span> &mdash; Under each track (e.g., STEM, ABM, HUMSS).</li>
                                <li><span class="font-medium text-foreground">Subjects</span> &mdash; Create subjects and assign them to grade levels and semesters.</li>
                            </ul>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Unlocking Grades</p>
                            <p class="text-sm text-muted-foreground">When a teacher locks grades, only an admin can unlock them. Go to Grade Entry &rarr; select the section and subject &rarr; click "Unlock".</p>
                            <div class="rounded-lg bg-muted px-4 py-3 text-sm text-muted-foreground">
                                <span class="font-medium text-foreground">Scenario:</span> A teacher locked grades but one student's midterm score is wrong. Admin goes to Grade Entry &rarr; selects the section &amp; subject &rarr; clicks Unlock &rarr; the teacher corrects the score and re-locks.
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Registrar Guide -->
                <Card id="role-registrar" class="scroll-mt-20">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <CardTitle>Registrar Guide</CardTitle>
                                <CardDescription>Enrollment, student records, sections, reports, and data import.</CardDescription>
                            </div>
                            <Badge variant="secondary">Registrar</Badge>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <p class="text-sm font-medium">Enrolling Students</p>
                            <ol class="list-decimal space-y-1 pl-5 text-sm text-muted-foreground">
                                <li>Go to <span class="font-medium text-foreground">Enrollment</span> &rarr; click "Enroll Student".</li>
                                <li>Search for an existing student or create a new student record.</li>
                                <li>Select the student's grade level and strand.</li>
                                <li>The system automatically loads the subject list based on the curriculum.</li>
                                <li>Choose an available section.</li>
                                <li>Review and confirm the enrollment.</li>
                            </ol>
                            <div class="rounded-lg bg-muted px-4 py-3 text-sm text-muted-foreground">
                                <span class="font-medium text-foreground">Scenario:</span> A Grade 12 STEM student arrives. Search their name &rarr; select Grade 12 &rarr; STEM strand &rarr; subjects auto-load &rarr; pick a section &rarr; confirm.
                            </div>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Managing Student Records</p>
                            <ul class="list-disc space-y-1 pl-5 text-sm text-muted-foreground">
                                <li><span class="font-medium text-foreground">Add</span> new student records with personal info, LRN, address, and guardian details.</li>
                                <li><span class="font-medium text-foreground">Edit</span> existing student information.</li>
                                <li><span class="font-medium text-foreground">Search</span> by name, LRN, or other fields. Duplicate LRNs are detected automatically.</li>
                            </ul>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Managing Sections</p>
                            <p class="text-sm text-muted-foreground">Create sections with a name, grade level, strand, and maximum capacity. Assign an adviser (teacher) to each section.</p>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Generating Reports</p>
                            <dl class="space-y-2 text-sm">
                                <div class="flex gap-2">
                                    <dt class="shrink-0 font-medium">SF1</dt>
                                    <dd class="text-muted-foreground">School Register &mdash; per section, lists all enrolled students.</dd>
                                </div>
                                <div class="flex gap-2">
                                    <dt class="shrink-0 font-medium">SF5</dt>
                                    <dd class="text-muted-foreground">Report on Promotion &mdash; per section, summarizes grade outcomes.</dd>
                                </div>
                                <div class="flex gap-2">
                                    <dt class="shrink-0 font-medium">SF9</dt>
                                    <dd class="text-muted-foreground">Report Card &mdash; per student, shows grades for the semester.</dd>
                                </div>
                                <div class="flex gap-2">
                                    <dt class="shrink-0 font-medium">SF10</dt>
                                    <dd class="text-muted-foreground">Learner's Permanent Record &mdash; per student, full academic history.</dd>
                                </div>
                            </dl>
                            <p class="text-sm text-muted-foreground">Also available: Enrollment Summary, Class List, and Masterlist (exportable to CSV).</p>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Importing Data</p>
                            <ol class="list-decimal space-y-1 pl-5 text-sm text-muted-foreground">
                                <li>Download the CSV template for students or grades.</li>
                                <li>Fill in the template with your data.</li>
                                <li>Upload the CSV file and review the preview for validation errors.</li>
                                <li>Confirm the import to save records.</li>
                            </ol>
                        </div>
                    </CardContent>
                </Card>

                <!-- Teacher Guide -->
                <Card id="role-teacher" class="scroll-mt-20">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <CardTitle>Teacher Guide</CardTitle>
                                <CardDescription>Grade entry, locking, and viewing assigned sections.</CardDescription>
                            </div>
                            <Badge variant="outline">Teacher</Badge>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <p class="text-sm font-medium">Entering Grades</p>
                            <ol class="list-decimal space-y-1 pl-5 text-sm text-muted-foreground">
                                <li>Go to <span class="font-medium text-foreground">Grade Entry</span> from the sidebar.</li>
                                <li>You will see a list of sections and subjects assigned to you.</li>
                                <li>Click on a section/subject to open the grade entry form.</li>
                                <li>Enter midterm and finals grades for each student.</li>
                                <li>The system automatically calculates the final grade.</li>
                                <li>Click Save to save your progress (you can return to edit later).</li>
                            </ol>
                            <div class="rounded-lg bg-muted px-4 py-3 text-sm text-muted-foreground">
                                <span class="font-medium text-foreground">Scenario:</span> It's midterm week. Go to Grade Entry &rarr; select your section (e.g., 11-STEM-A) and subject (e.g., General Mathematics) &rarr; enter midterm scores &rarr; Save. Come back after finals to enter finals scores.
                            </div>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Locking Grades</p>
                            <p class="text-sm text-muted-foreground">When all grades are finalized, click <span class="font-medium text-foreground">"Lock Grades"</span>. Locked grades cannot be edited unless an admin unlocks them. Make sure all grades are correct before locking.</p>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Viewing Assigned Sections</p>
                            <p class="text-sm text-muted-foreground">The Grade Entry page shows all sections and subjects assigned to you for the active semester, including the grade entry status and lock status.</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Student Guide -->
                <Card id="role-student" class="scroll-mt-20">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <CardTitle>Student Guide</CardTitle>
                                <CardDescription>Viewing your profile, subjects, and grades.</CardDescription>
                            </div>
                            <Badge variant="outline">Student</Badge>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <p class="text-sm text-muted-foreground">As a student, you have a personal portal with three sections:</p>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-lg border p-3">
                                <p class="text-sm font-medium">My Profile</p>
                                <p class="mt-1 text-sm text-muted-foreground">View your personal information, LRN, address, and guardian details.</p>
                            </div>
                            <div class="rounded-lg border p-3">
                                <p class="text-sm font-medium">My Subjects</p>
                                <p class="mt-1 text-sm text-muted-foreground">See your enrolled subjects, section, and assigned teacher for the current semester.</p>
                            </div>
                            <div class="rounded-lg border p-3">
                                <p class="text-sm font-medium">My Grades</p>
                                <p class="mt-1 text-sm text-muted-foreground">View your midterm and finals grades once entered by your teachers.</p>
                            </div>
                        </div>
                        <div class="rounded-lg bg-muted px-4 py-3 text-sm text-muted-foreground">
                            <span class="font-medium text-foreground">Note:</span> If you believe there's an error in your grades, contact your teacher or the registrar's office.
                        </div>
                    </CardContent>
                </Card>

                <!-- User Journeys divider -->
                <div class="flex items-center gap-3 pt-2">
                    <Separator class="flex-1" />
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">User Journeys</span>
                    <Separator class="flex-1" />
                </div>

                <!-- Enrollment Day -->
                <Card id="journey-enrollment" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle>Enrollment Day</CardTitle>
                        <CardDescription>Complete flow from student arrival to enrollment confirmation.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Before Enrollment Opens</p>
                        <div class="space-y-2">
                            <div class="flex items-start gap-3 text-sm">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">1</span>
                                <div>
                                    <p><Badge variant="default" class="mr-1.5">Admin</Badge>Ensure the school year and semester are active.</p>
                                    <p class="text-muted-foreground">Go to School Settings &rarr; verify the correct school year is active.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 text-sm">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">2</span>
                                <div>
                                    <p><Badge variant="default" class="mr-1.5">Admin</Badge>Open enrollment for the semester.</p>
                                    <p class="text-muted-foreground">Toggle "Enrollment Open" for the active semester.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 text-sm">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">3</span>
                                <div>
                                    <p><Badge variant="secondary" class="mr-1.5">Registrar</Badge>Verify sections are set up.</p>
                                    <p class="text-muted-foreground">Check that sections exist for all grade levels and strands with correct capacity.</p>
                                </div>
                            </div>
                        </div>

                        <Separator />

                        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">During Enrollment</p>
                        <div class="space-y-2">
                            <div class="flex items-start gap-3 text-sm">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">4</span>
                                <div>
                                    <p><Badge variant="secondary" class="mr-1.5">Registrar</Badge>Student arrives &mdash; search or create record.</p>
                                    <p class="text-muted-foreground">Go to Enrollment &rarr; Enroll Student. Search by name or LRN.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 text-sm">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">5</span>
                                <div>
                                    <p><Badge variant="secondary" class="mr-1.5">Registrar</Badge>Select grade level and strand.</p>
                                    <p class="text-muted-foreground">Choose grade level (11 or 12) and strand (e.g., STEM, ABM, HUMSS).</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 text-sm">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">6</span>
                                <div>
                                    <p><Badge variant="secondary" class="mr-1.5">Registrar</Badge>Review subjects and assign section.</p>
                                    <p class="text-muted-foreground">Subjects auto-load from the curriculum. Select an available section.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 text-sm">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">7</span>
                                <div>
                                    <p><Badge variant="secondary" class="mr-1.5">Registrar</Badge>Confirm enrollment.</p>
                                    <p class="text-muted-foreground">Review all details and submit.</p>
                                </div>
                            </div>
                        </div>

                        <Separator />

                        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">After Enrollment</p>
                        <div class="space-y-2">
                            <div class="flex items-start gap-3 text-sm">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">8</span>
                                <div>
                                    <p><Badge variant="secondary" class="mr-1.5">Registrar</Badge>Print enrollment slip (optional).</p>
                                    <p class="text-muted-foreground">From the enrollment record, click "Print" to generate a confirmation slip.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 text-sm">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">9</span>
                                <div>
                                    <p><Badge variant="default" class="mr-1.5">Admin</Badge>Close enrollment when done.</p>
                                    <p class="text-muted-foreground">Toggle "Enrollment Open" off to prevent further enrollments.</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Grade Encoding Period -->
                <Card id="journey-grading" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle>Grade Encoding Period</CardTitle>
                        <CardDescription>Teacher enters midterm/finals, locks grades, admin reviews.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="space-y-2">
                            <div class="flex items-start gap-3 text-sm">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">1</span>
                                <div>
                                    <p><Badge variant="outline" class="mr-1.5">Teacher</Badge>Enter midterm grades.</p>
                                    <p class="text-muted-foreground">Grade Entry &rarr; select section and subject &rarr; enter midterm scores &rarr; Save.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 text-sm">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">2</span>
                                <div>
                                    <p><Badge variant="outline" class="mr-1.5">Teacher</Badge>Enter finals grades.</p>
                                    <p class="text-muted-foreground">Return to Grade Entry &rarr; enter finals scores &rarr; final grades are calculated automatically &rarr; Save.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 text-sm">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">3</span>
                                <div>
                                    <p><Badge variant="outline" class="mr-1.5">Teacher</Badge>Review and lock grades.</p>
                                    <p class="text-muted-foreground">Double-check all entries. Click "Lock Grades" to finalize.</p>
                                </div>
                            </div>
                        </div>

                        <Separator />

                        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">If Corrections Are Needed</p>
                        <div class="space-y-2">
                            <div class="flex items-start gap-3 text-sm">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-muted text-[10px] font-bold text-muted-foreground">4</span>
                                <div>
                                    <p><Badge variant="outline" class="mr-1.5">Teacher</Badge>Request unlock from admin.</p>
                                    <p class="text-muted-foreground">Specify which section and subject need corrections.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 text-sm">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-muted text-[10px] font-bold text-muted-foreground">5</span>
                                <div>
                                    <p><Badge variant="default" class="mr-1.5">Admin</Badge>Admin unlocks the grades.</p>
                                    <p class="text-muted-foreground">Grade Entry &rarr; select section/subject &rarr; click Unlock.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 text-sm">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-muted text-[10px] font-bold text-muted-foreground">6</span>
                                <div>
                                    <p><Badge variant="outline" class="mr-1.5">Teacher</Badge>Teacher corrects and re-locks.</p>
                                    <p class="text-muted-foreground">Make corrections &rarr; Save &rarr; Lock Grades again.</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- End of Semester -->
                <Card id="journey-end-semester" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle>End of Semester</CardTitle>
                        <CardDescription>Reports generation, SF9/SF10, and transitioning to next semester.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">1</span>
                            <div>
                                <p><Badge variant="default" class="mr-1.5">Admin</Badge>Verify all grades are locked.</p>
                                <p class="text-muted-foreground">Check Grade Entry for any unlocked sections. Follow up with teachers.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">2</span>
                            <div>
                                <p><Badge variant="secondary" class="mr-1.5">Registrar</Badge>Generate SF9 (Report Cards).</p>
                                <p class="text-muted-foreground">Reports &rarr; School Forms &rarr; generate SF9 for each student.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">3</span>
                            <div>
                                <p><Badge variant="secondary" class="mr-1.5">Registrar</Badge>Generate SF10 (Permanent Records).</p>
                                <p class="text-muted-foreground">Reports &rarr; School Forms &rarr; generate SF10 for students who need updated records.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">4</span>
                            <div>
                                <p><Badge variant="secondary" class="mr-1.5">Registrar</Badge>Generate SF1 and SF5.</p>
                                <p class="text-muted-foreground">Generate per section for DepEd submission.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">5</span>
                            <div>
                                <p><Badge variant="secondary" class="mr-1.5">Registrar</Badge>Export enrollment summary and masterlists.</p>
                                <p class="text-muted-foreground">Reports &rarr; export as CSV files for records.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">6</span>
                            <div>
                                <p><Badge variant="default" class="mr-1.5">Admin</Badge>Transition to next semester.</p>
                                <p class="text-muted-foreground">Activate the next semester under School Settings.</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- New School Year Setup -->
                <Card id="journey-new-year" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle>New School Year Setup</CardTitle>
                        <CardDescription>Admin creates school year, semesters, sections, and opens enrollment.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">1</span>
                            <div>
                                <p><Badge variant="default" class="mr-1.5">Admin</Badge>Create the new school year.</p>
                                <p class="text-muted-foreground">School Settings &rarr; Add School Year &rarr; enter the year range (e.g., 2026-2027).</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">2</span>
                            <div>
                                <p><Badge variant="default" class="mr-1.5">Admin</Badge>Activate the school year.</p>
                                <p class="text-muted-foreground">Click "Activate" to make it the current year across the system.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">3</span>
                            <div>
                                <p><Badge variant="default" class="mr-1.5">Admin</Badge>Activate the first semester.</p>
                                <p class="text-muted-foreground">Activate Semester 1 of the new school year.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">4</span>
                            <div>
                                <p><Badge variant="default" class="mr-1.5">Admin</Badge>Review curriculum.</p>
                                <p class="text-muted-foreground">Check tracks, strands, and subjects are up to date.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">5</span>
                            <div>
                                <p><Badge variant="secondary" class="mr-1.5">Registrar</Badge>Create new sections.</p>
                                <p class="text-muted-foreground">Set up sections for all grade levels and strands. Assign advisers.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">6</span>
                            <div>
                                <p><Badge variant="default" class="mr-1.5">Admin</Badge>Review user accounts.</p>
                                <p class="text-muted-foreground">Deactivate departed staff. Create accounts for new teachers.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">7</span>
                            <div>
                                <p><Badge variant="default" class="mr-1.5">Admin</Badge>Open enrollment.</p>
                                <p class="text-muted-foreground">Toggle "Enrollment Open" for Semester 1.</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- FAQ divider -->
                <div class="flex items-center gap-3 pt-2">
                    <Separator class="flex-1" />
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">FAQ</span>
                    <Separator class="flex-1" />
                </div>

                <!-- FAQ -->
                <Card id="faq" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle>Frequently Asked Questions</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-1.5">
                            <p class="text-sm font-medium">How do I reset a user's password?</p>
                            <p class="text-sm text-muted-foreground">As an admin, go to Users &rarr; click on the user &rarr; Edit &rarr; set a new password. Inform the user of their new password.</p>
                        </div>
                        <Separator />
                        <div class="space-y-1.5">
                            <p class="text-sm font-medium">A student is enrolled in the wrong section. How do I fix this?</p>
                            <p class="text-sm text-muted-foreground">Go to the student's enrollment record and update the section. If grades have been entered, coordinate with the teachers of both sections.</p>
                        </div>
                        <Separator />
                        <div class="space-y-1.5">
                            <p class="text-sm font-medium">Can I enroll students when enrollment is closed?</p>
                            <p class="text-sm text-muted-foreground">No. The admin must open enrollment for the active semester first. Contact your admin.</p>
                        </div>
                        <Separator />
                        <div class="space-y-1.5">
                            <p class="text-sm font-medium">How do I import student data from a spreadsheet?</p>
                            <p class="text-sm text-muted-foreground">Import Data &rarr; download CSV template &rarr; fill in your data &rarr; upload &rarr; review preview &rarr; confirm.</p>
                        </div>
                        <Separator />
                        <div class="space-y-1.5">
                            <p class="text-sm font-medium">Why can't I see the Grade Entry page?</p>
                            <p class="text-sm text-muted-foreground">Grade Entry is for admins, registrars, and teachers. If you're a teacher with no sections listed, you haven't been assigned yet. Contact the registrar.</p>
                        </div>
                        <Separator />
                        <div class="space-y-1.5">
                            <p class="text-sm font-medium">How are final grades calculated?</p>
                            <p class="text-sm text-muted-foreground">The final grade is the average of midterm and finals grades. Both must be entered before the final grade is computed.</p>
                        </div>
                        <Separator />
                        <div class="space-y-1.5">
                            <p class="text-sm font-medium">Can a student see their grades immediately?</p>
                            <p class="text-sm text-muted-foreground">Yes, grades appear in the "My Grades" portal once the teacher has entered them, regardless of lock status.</p>
                        </div>
                        <Separator />
                        <div class="space-y-1.5">
                            <p class="text-sm font-medium">What happens if I enroll a student with a duplicate LRN?</p>
                            <p class="text-sm text-muted-foreground">The system detects duplicates automatically and alerts you. You can use the existing record instead of creating a duplicate.</p>
                        </div>
                        <Separator />
                        <div class="space-y-1.5">
                            <p class="text-sm font-medium">How do I generate school forms (SF1, SF5, SF9, SF10)?</p>
                            <p class="text-sm text-muted-foreground">Reports &rarr; School Forms &rarr; select the form type and section/student &rarr; click Generate. Forms are downloadable as PDF.</p>
                        </div>
                        <Separator />
                        <div class="space-y-1.5">
                            <p class="text-sm font-medium">Who do I contact for technical issues?</p>
                            <p class="text-sm text-muted-foreground">For system issues, contact the school's system administrator. For account issues (login, role changes), contact the admin user.</p>
                        </div>
                    </CardContent>
                </Card>

            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-6 border-t bg-muted/30 py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                        <GraduationCap class="h-4 w-4" />
                        <span>Lake Sebu National High School</span>
                    </div>
                    <p class="text-xs text-muted-foreground">Lake Sebu, South Cotabato</p>
                </div>
            </div>
        </footer>
    </div>
</template>
