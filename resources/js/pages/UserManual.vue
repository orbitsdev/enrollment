<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { ref, onMounted, onUnmounted } from 'vue';
import {
    BookOpen,
    KeyRound,
    ShieldCheck,
    ClipboardList,
    GraduationCap,
    UserCheck,
    FileText,
    PenLine,
    HelpCircle,
    Menu,
    X,
    ArrowLeft,
    ChevronRight,
    Cpu,
} from 'lucide-vue-next';

const sections = [
    { id: 'about', title: 'About the System', icon: BookOpen },
    { id: 'accounts', title: 'Test Accounts', icon: KeyRound },
    { id: 'admin', title: 'Admin Guide', icon: ShieldCheck },
    { id: 'registrar', title: 'Registrar Guide', icon: ClipboardList },
    { id: 'teacher', title: 'Teacher Guide', icon: GraduationCap },
    { id: 'student', title: 'Student Guide', icon: UserCheck },
    { id: 'enroll-steps', title: 'Paano Mag-Enroll?', icon: FileText },
    { id: 'grade-steps', title: 'Paano Mag-Encode?', icon: PenLine },
    { id: 'faq', title: 'Common Questions', icon: HelpCircle },
    { id: 'tech', title: 'Technologies', icon: Cpu },
];

const activeSection = ref('about');
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
    <Head title="User Manual" />

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
                        <p class="text-xs leading-tight text-muted-foreground">High School &mdash; User Manual</p>
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

                <!-- Friendly intro -->
                <div class="rounded-lg border border-primary/20 bg-primary/5 px-5 py-4 space-y-2">
                    <p class="text-sm leading-relaxed text-foreground">
                        <span class="font-semibold">Ma'am, sundon mo lang ni ha!</span> Step by step lang ni, from top to bottom, easy lang. Lets go!
                    </p>
                    <p class="text-sm leading-relaxed text-muted-foreground">
                        <span class="font-medium text-foreground">Tip:</span> Recommend ko Ma'am nga i-open mo ni nga user manual sa isa ka separate tab, para habang ga-practice ka sa system, ma-sundan mo diri ang steps.
                    </p>
                </div>

                <!-- 1. About the System -->
                <Card id="about" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle>About the System</CardTitle>
                        <CardDescription>Una, let me explain what this system is about.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <p class="text-sm leading-relaxed text-muted-foreground">
                            Ma'am, kabalo ka na ni, pero quick lang: ang system na ni handles enrollment, student records, grades, and DepEd school forms para sa <span class="font-medium text-foreground">Lake Sebu National High School</span>.
                        </p>
                        <p class="text-sm leading-relaxed text-muted-foreground">
                            May 4 ka user types: <span class="font-medium text-foreground">Admin</span>, <span class="font-medium text-foreground">Registrar</span>, <span class="font-medium text-foreground">Teacher</span>, and <span class="font-medium text-foreground">Student</span>. Each type lain-lain ang dashboard nila -- pag nag-login ka, automatic ka nga ma-redirect sa imo dashboard based sa imo role.
                        </p>
                    </CardContent>
                </Card>

                <!-- 2. Test Accounts -->
                <Card id="accounts" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle>Test Accounts</CardTitle>
                        <CardDescription>Ni ang mga accounts nga gamiton mo para ma-try ang system. Tanan password: <code class="rounded bg-muted px-1.5 py-0.5 text-xs font-mono">password</code></CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b text-left">
                                        <th class="pb-2 pr-4 font-medium">Role</th>
                                        <th class="pb-2 pr-4 font-medium">Email</th>
                                        <th class="pb-2 font-medium">Password</th>
                                    </tr>
                                </thead>
                                <tbody class="text-muted-foreground">
                                    <tr class="border-b">
                                        <td class="py-2 pr-4"><Badge variant="default">Admin</Badge></td>
                                        <td class="py-2 pr-4 font-mono text-xs">admin@lsnhs.edu.ph</td>
                                        <td class="py-2 font-mono text-xs">password</td>
                                    </tr>
                                    <tr class="border-b">
                                        <td class="py-2 pr-4"><Badge variant="secondary">Registrar</Badge></td>
                                        <td class="py-2 pr-4 font-mono text-xs">registrar@school.edu.ph</td>
                                        <td class="py-2 font-mono text-xs">password</td>
                                    </tr>
                                    <tr class="border-b">
                                        <td class="py-2 pr-4"><Badge variant="outline">Teacher</Badge></td>
                                        <td class="py-2 pr-4 font-mono text-xs">maria.santos@school.edu.ph</td>
                                        <td class="py-2 font-mono text-xs">password</td>
                                    </tr>
                                    <tr class="border-b">
                                        <td class="py-2 pr-4"><Badge variant="outline">Teacher</Badge></td>
                                        <td class="py-2 pr-4 font-mono text-xs">jose.reyes@school.edu.ph</td>
                                        <td class="py-2 font-mono text-xs">password</td>
                                    </tr>
                                    <tr class="border-b">
                                        <td class="py-2 pr-4"><Badge variant="outline">Student</Badge></td>
                                        <td class="py-2 pr-4 font-mono text-xs">student1@school.edu.ph</td>
                                        <td class="py-2 font-mono text-xs">password</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 pr-4"><Badge variant="outline">Student</Badge></td>
                                        <td class="py-2 pr-4 font-mono text-xs">student2@school.edu.ph</td>
                                        <td class="py-2 font-mono text-xs">password</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="mt-3 text-xs text-muted-foreground">May 5 ka teacher accounts and 3 ka student accounts sa demo data. Ang mga nasa taas ang pinaka-common nga gamiton for testing.</p>
                    </CardContent>
                </Card>

                <!-- Role Guides divider -->
                <div class="flex items-center gap-3 pt-2">
                    <Separator class="flex-1" />
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Role Guides</span>
                    <Separator class="flex-1" />
                </div>

                <!-- 3. Admin Guide -->
                <Card id="admin" class="scroll-mt-20">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <CardTitle>Admin Guide</CardTitle>
                                <CardDescription>Okay Ma'am, ni tanan ang makita and mahimo sang Admin.</CardDescription>
                            </div>
                            <Badge variant="default">Admin</Badge>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="rounded-lg bg-muted px-4 py-3 text-sm text-muted-foreground">
                            <span class="font-medium text-foreground">Login:</span> <code class="rounded bg-background px-1.5 py-0.5 text-xs font-mono">admin@lsnhs.edu.ph</code> / <code class="rounded bg-background px-1.5 py-0.5 text-xs font-mono">password</code>
                        </div>

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Dashboard</p>
                            <p class="text-sm text-muted-foreground">Pag nag-login ka na Ma'am as <span class="font-medium text-foreground">Admin</span>, ni ang una mo makita &mdash; ang Dashboard. May enrollment stats (pila na ang enrolled), section capacity chart, and recent enrollments.</p>
                            <p class="text-xs italic text-muted-foreground">Tip: Check ni regularly Ma'am para makita mo kung ano na ang status sang enrollment and kung diin nga section halos full na.</p>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Users</p>
                            <p class="text-sm text-muted-foreground">Next, try mo click ang <span class="font-medium text-foreground">Users</span> sa sidebar. Makita mo diri tanan nga accounts sa system. Diri ka man mag-add sang new user, mag-edit sang details, or mag-deactivate kung wala na active.</p>
                            <p class="text-xs italic text-muted-foreground">Example: Para mag-add sang teacher, click "Add User" &rarr; fill-up ang name and email (e.g., juan.cruz@school.edu.ph) &rarr; set ang role to "Teacher" &rarr; Save.</p>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">School Settings</p>
                            <p class="text-sm text-muted-foreground">Okay next, click mo ang <span class="font-medium text-foreground">School Settings</span>. Diri mo ma-update ang school info (name, ID, address) and ang grading config (passing grade, midterm/finals weight).</p>
                            <p class="text-xs italic text-muted-foreground">Example: Set passing grade to 75, midterm weight to 30%, finals weight to 70% &mdash; depende sa grading policy sang school.</p>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">School Years</p>
                            <p class="text-sm text-muted-foreground">Ni importante Ma'am. Click mo ang <span class="font-medium text-foreground">School Years</span>. Diri ka mag-create sang new school year (e.g., 2025-2026) &mdash; auto ni may 2 semesters. Then activate mo ang school year, then activate mo ang semester, then open mo ang enrollment.</p>
                            <p class="text-xs italic text-muted-foreground">Tip: Ang order Ma'am: Create School Year &rarr; Activate &rarr; Activate Semester 1 &rarr; Open Enrollment. Sundon lang ni ha, in order gid.</p>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Curriculum (Tracks &amp; Strands)</p>
                            <p class="text-sm text-muted-foreground">Next, check mo ang <span class="font-medium text-foreground">Curriculum</span> sa sidebar. Makita mo diri ang tracks (Academic, TVL) and ang strands (STEM, ABM, HUMSS, GAS, ICT, HE, AFA). Pwede mo ni i-add, i-edit, or i-toggle active/inactive.</p>
                            <p class="text-xs italic text-muted-foreground">Example: Academic Track may STEM, ABM, HUMSS, GAS. TVL Track may ICT, HE, AFA. Kung wala ang school naga-offer sang AFA, i-toggle mo lang to inactive.</p>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Subjects</p>
                            <p class="text-sm text-muted-foreground">Click mo ang <span class="font-medium text-foreground">Subjects</span>. Ang subjects diri naka-map per strand, grade level, and semester. May core subjects (same sa tanan nga strand) and specialized subjects (per strand). Pwede mo i-add or i-edit diri.</p>
                            <p class="text-xs italic text-muted-foreground">Example: "Oral Communication" is a core subject for all Grade 11 Sem 1. "General Biology 1" is specialized for STEM Grade 11 Sem 1 lang.</p>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Students, Teachers, Sections</p>
                            <p class="text-sm text-muted-foreground">Makita mo man sa sidebar ang <span class="font-medium text-foreground">Students</span>, <span class="font-medium text-foreground">Teachers</span>, and <span class="font-medium text-foreground">Sections</span>. Diri mo ma-manage ang student records (LRN, guardian info), teacher profiles (plus trainings), and sections (assign adviser, set capacity).</p>
                            <p class="text-xs italic text-muted-foreground">Tip: Pag mag-create ka sang section, set mo ang name (e.g., "STEM 11-A"), assign ang adviser, and set max capacity (e.g., 40 students).</p>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Enrollment</p>
                            <p class="text-sm text-muted-foreground">Okay ni ang main feature Ma'am. Click mo ang <span class="font-medium text-foreground">Enrollment</span>. May 5-step wizard ni: (1) search the student, (2) pick a track and strand, (3) subjects auto-load, (4) pick a section, (5) confirm. Amo lang na!</p>
                            <p class="text-xs italic text-muted-foreground">Tip: Make sure open na ang enrollment (School Years &rarr; Activate Semester &rarr; Open Enrollment) before ka mag-try mag-enroll.</p>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Grades</p>
                            <p class="text-sm text-muted-foreground">Click mo ang <span class="font-medium text-foreground">Grades</span>. Makita mo diri ang grades per section. Admin lang Ma'am ang pwede mag-<span class="font-medium text-foreground">unlock</span> sang grades &mdash; kung na-lock na sang teacher pero may correction pa.</p>
                            <p class="text-xs italic text-muted-foreground">Tip: Para mag-unlock, go to Grade Entry &rarr; pick ang section and subject &rarr; click "Unlock". Then pwede na mag-edit ang teacher and i-lock ulit.</p>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Reports</p>
                            <p class="text-sm text-muted-foreground">Click mo ang <span class="font-medium text-foreground">Reports</span>. Diri mo ma-generate ang enrollment summary, class list, masterlist, grade summary, and DepEd school forms &mdash; SF1 (School Register), SF5 (Report on Promotion), SF9 (Report Card), SF10 (Permanent Record).</p>
                            <p class="text-xs italic text-muted-foreground">Example: Para mag-generate sang SF9 (Report Card), go to Reports &rarr; School Forms &rarr; select SF9 &rarr; pick ang student &rarr; click Generate. Ma-download siya as PDF.</p>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Import</p>
                            <p class="text-sm text-muted-foreground">And last, ang <span class="font-medium text-foreground">Import</span>. Diri ka pwede mag-bulk upload sang students or grades via CSV file. Download mo lang ang template, fill-up, upload, review, then confirm.</p>
                            <p class="text-xs italic text-muted-foreground">Tip: Always download ang template first Ma'am &mdash; indi mag-create sang imo kaugalingon nga CSV format. Ang system naga-expect sang specific column headers para gana siya.</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- 4. Registrar Guide -->
                <Card id="registrar" class="scroll-mt-20">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <CardTitle>Registrar Guide</CardTitle>
                                <CardDescription>Ni para sa Registrar Ma'am &mdash; enrollment, records, and reports.</CardDescription>
                            </div>
                            <Badge variant="secondary">Registrar</Badge>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="rounded-lg bg-muted px-4 py-3 text-sm text-muted-foreground">
                            <span class="font-medium text-foreground">Login:</span> <code class="rounded bg-background px-1.5 py-0.5 text-xs font-mono">registrar@school.edu.ph</code> / <code class="rounded bg-background px-1.5 py-0.5 text-xs font-mono">password</code>
                        </div>

                        <p class="text-sm text-muted-foreground">
                            Okay Ma'am, ang Registrar halos same lang sa Admin pagdating sa enrollment &mdash; pero <span class="font-medium text-foreground">wala</span> siya access sa Users, School Settings, and School Years. So focused lang siya sa:
                        </p>

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Ang pwede niya himuon:</p>
                            <ul class="space-y-1.5 text-sm text-muted-foreground">
                                <li class="flex items-start gap-2"><ChevronRight class="mt-0.5 h-3 w-3 shrink-0 text-primary" /><span><span class="font-medium text-foreground">Enrollment</span> &mdash; ni ang main task niya. Gamiton ang 5-step wizard para i-enroll ang students.</span></li>
                                <li class="flex items-start gap-2"><ChevronRight class="mt-0.5 h-3 w-3 shrink-0 text-primary" /><span><span class="font-medium text-foreground">Students</span> &mdash; add/edit student records, LRN, guardian info.</span></li>
                                <li class="flex items-start gap-2"><ChevronRight class="mt-0.5 h-3 w-3 shrink-0 text-primary" /><span><span class="font-medium text-foreground">Sections</span> &mdash; create sections, assign adviser, set max capacity.</span></li>
                                <li class="flex items-start gap-2"><ChevronRight class="mt-0.5 h-3 w-3 shrink-0 text-primary" /><span><span class="font-medium text-foreground">Teachers</span> &mdash; view and update teacher profiles and trainings.</span></li>
                                <li class="flex items-start gap-2"><ChevronRight class="mt-0.5 h-3 w-3 shrink-0 text-primary" /><span><span class="font-medium text-foreground">Reports</span> &mdash; generate enrollment summary, class list, masterlist, grade summary, and school forms (SF1, SF5, SF9, SF10).</span></li>
                                <li class="flex items-start gap-2"><ChevronRight class="mt-0.5 h-3 w-3 shrink-0 text-primary" /><span><span class="font-medium text-foreground">Import</span> &mdash; bulk upload students/grades via CSV.</span></li>
                            </ul>
                        </div>
                    </CardContent>
                </Card>

                <!-- 5. Teacher Guide -->
                <Card id="teacher" class="scroll-mt-20">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <CardTitle>Teacher Guide</CardTitle>
                                <CardDescription>Ni para sa Teacher &mdash; grade entry and locking.</CardDescription>
                            </div>
                            <Badge variant="outline">Teacher</Badge>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="rounded-lg bg-muted px-4 py-3 text-sm text-muted-foreground">
                            <span class="font-medium text-foreground">Login:</span> <code class="rounded bg-background px-1.5 py-0.5 text-xs font-mono">maria.santos@school.edu.ph</code> / <code class="rounded bg-background px-1.5 py-0.5 text-xs font-mono">password</code>
                        </div>

                        <p class="text-sm text-muted-foreground">Ang Teacher Ma'am focused lang sa <span class="font-medium text-foreground">Grade Entry</span>. Pag nag-login siya, ni ang makita niya sa sidebar:</p>

                        <div class="space-y-2">
                            <ul class="space-y-1.5 text-sm text-muted-foreground">
                                <li class="flex items-start gap-2"><ChevronRight class="mt-0.5 h-3 w-3 shrink-0 text-primary" /><span><span class="font-medium text-foreground">Grade Entry</span> &mdash; makita niya ang list sang sections and subjects nga assigned sa iya. Click lang one para mag-start encode.</span></li>
                                <li class="flex items-start gap-2"><ChevronRight class="mt-0.5 h-3 w-3 shrink-0 text-primary" /><span><span class="font-medium text-foreground">Encode Midterm</span> &mdash; type-an niya ang midterm scores per student, then Save.</span></li>
                                <li class="flex items-start gap-2"><ChevronRight class="mt-0.5 h-3 w-3 shrink-0 text-primary" /><span><span class="font-medium text-foreground">Encode Finals</span> &mdash; same thing for finals scores. Ang final grade auto-compute na.</span></li>
                                <li class="flex items-start gap-2"><ChevronRight class="mt-0.5 h-3 w-3 shrink-0 text-primary" /><span><span class="font-medium text-foreground">Lock</span> &mdash; pag done na, click "Lock Grades". After ma-lock, indi na ma-edit unless mag-request siya sang unlock sa Admin.</span></li>
                            </ul>
                        </div>

                        <div class="space-y-2">
                            <p class="text-sm font-medium">My Sections &amp; My Students</p>
                            <p class="text-sm text-muted-foreground">Makita man niya ang mga sections nga naka-assign sa iya and ang student list per section.</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- 6. Student Guide -->
                <Card id="student" class="scroll-mt-20">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <CardTitle>Student Guide</CardTitle>
                                <CardDescription>Ni para sa Student &mdash; view-only lang ni Ma'am.</CardDescription>
                            </div>
                            <Badge variant="outline">Student</Badge>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="rounded-lg bg-muted px-4 py-3 text-sm text-muted-foreground">
                            <span class="font-medium text-foreground">Login:</span> <code class="rounded bg-background px-1.5 py-0.5 text-xs font-mono">student1@school.edu.ph</code> / <code class="rounded bg-background px-1.5 py-0.5 text-xs font-mono">password</code>
                        </div>

                        <p class="text-sm text-muted-foreground">Ang Student portal Ma'am is <span class="font-medium text-foreground">view-only</span> lang &mdash; wala sila pwede i-edit. May 3 ka pages lang:</p>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-lg border p-3">
                                <p class="text-sm font-medium">My Profile</p>
                                <p class="mt-1 text-sm text-muted-foreground">Makita nila ang ila personal info, LRN, address, and guardian details.</p>
                            </div>
                            <div class="rounded-lg border p-3">
                                <p class="text-sm font-medium">My Subjects</p>
                                <p class="mt-1 text-sm text-muted-foreground">Makita nila ang ila enrolled subjects, section, and teacher for the current semester.</p>
                            </div>
                            <div class="rounded-lg border p-3">
                                <p class="text-sm font-medium">My Grades</p>
                                <p class="mt-1 text-sm text-muted-foreground">Makita nila ang ila midterm and finals grades pag na-encode na sang teacher.</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Step-by-Step divider -->
                <div class="flex items-center gap-3 pt-2">
                    <Separator class="flex-1" />
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Step-by-Step</span>
                    <Separator class="flex-1" />
                </div>

                <!-- 7. Paano Mag-Enroll -->
                <Card id="enroll-steps" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle>Paano Mag-Enroll? (Step-by-Step)</CardTitle>
                        <CardDescription>Okay Ma'am, ni ang 5 steps sa enrollment wizard. Sundon mo lang.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">1</span>
                            <div>
                                <p class="font-medium">Search the Student</p>
                                <p class="text-muted-foreground">Una Ma'am, kadto ka sa <span class="font-medium text-foreground">Enrollment</span> &rarr; click mo "Enroll Student". Then search mo ang name or LRN. Kung wala pa sa system, pwede ka mag-create sang new record diri mismo.</p>
                                <p class="mt-1 text-xs italic text-muted-foreground">Tip: Try mo search "Juan" or isa ka LRN like "136547890123" para makita mo kung paano gana.</p>
                            </div>
                        </div>
                        <Separator />
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">2</span>
                            <div>
                                <p class="font-medium">Pick Grade Level, Track, and Strand</p>
                                <p class="text-muted-foreground">Next, select mo kung Grade 11 or Grade 12, then pick mo ang track (e.g., <span class="font-medium text-foreground">Academic</span> or <span class="font-medium text-foreground">TVL</span>), then pick mo ang strand sa ila.</p>
                                <p class="mt-1 text-xs italic text-muted-foreground">Example: Grade 11 &rarr; Academic Track &rarr; STEM. Or Grade 12 &rarr; TVL Track &rarr; ICT.</p>
                            </div>
                        </div>
                        <Separator />
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">3</span>
                            <div>
                                <p class="font-medium">Subjects Auto-load</p>
                                <p class="text-muted-foreground">Diri Ma'am wala ka na himuon &mdash; auto na ang system mag-load sang subjects based sa strand and grade level. Wala na kinahanglan mag-pick one by one.</p>
                                <p class="mt-1 text-xs italic text-muted-foreground">Tip: Makita mo diri ang core subjects (like Oral Communication, PE) and specialized subjects (like General Biology for STEM). Naka-configure na ni sa Curriculum.</p>
                            </div>
                        </div>
                        <Separator />
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">4</span>
                            <div>
                                <p class="font-medium">Pick a Section</p>
                                <p class="text-muted-foreground">Okay next, makita mo ang available sections and pila pa ang slots. Pick mo lang ang section nga may available capacity.</p>
                                <p class="mt-1 text-xs italic text-muted-foreground">Tip: Ang sections nga may green indicator, may slots pa. Kung full na, indi na siya ma-select.</p>
                            </div>
                        </div>
                        <Separator />
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">5</span>
                            <div>
                                <p class="font-medium">Confirm</p>
                                <p class="text-muted-foreground">Last step Ma'am! Review mo lang tanan &mdash; student info, strand, subjects, section. Kung okay na tanan, click mo "Confirm Enrollment". Amo na to!</p>
                                <p class="mt-1 text-xs italic text-muted-foreground">Tip: Double-check ang strand and section before confirming. Kung may gusto ka i-change, click lang Back para mabalik sa previous step.</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- 8. Paano Mag-Encode ng Grades -->
                <Card id="grade-steps" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle>Paano Mag-Encode sang Grades?</CardTitle>
                        <CardDescription>Ni ang flow para sa teachers Ma'am. Simple lang man ni.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">1</span>
                            <div>
                                <p class="font-medium">Go to Grade Entry</p>
                                <p class="text-muted-foreground">Click mo ang <span class="font-medium text-foreground">Grade Entry</span> sa sidebar. Makita niya diri ang iya assigned sections and subjects.</p>
                                <p class="mt-1 text-xs italic text-muted-foreground">Tip: Ang teacher makita lang ang sections and subjects nga assigned sa iya. Kung may missing, contact ang Admin or Registrar para ma-check ang assignments.</p>
                            </div>
                        </div>
                        <Separator />
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">2</span>
                            <div>
                                <p class="font-medium">Pick Section and Subject</p>
                                <p class="text-muted-foreground">Click mo ang section/subject combo nga gusto mo i-encode.</p>
                                <p class="mt-1 text-xs italic text-muted-foreground">Example: "STEM 11-A &mdash; General Biology 1" para ma-encode ang bio grades sang section na to.</p>
                            </div>
                        </div>
                        <Separator />
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">3</span>
                            <div>
                                <p class="font-medium">Type the Scores</p>
                                <p class="text-muted-foreground">I-enter ang <span class="font-medium text-foreground">Midterm</span> and <span class="font-medium text-foreground">Finals</span> scores per student. Ang final grade auto-compute na (average sang midterm and finals).</p>
                                <p class="mt-1 text-xs italic text-muted-foreground">Tip: Scores should be between 0-100. Pwede mag-encode sang Midterm una, save, then balik later for Finals.</p>
                            </div>
                        </div>
                        <Separator />
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">4</span>
                            <div>
                                <p class="font-medium">Save</p>
                                <p class="text-muted-foreground">Click <span class="font-medium text-foreground">Save</span>. Pwede siya mag-balik-balik para mag-edit basta wala pa na-lock.</p>
                                <p class="mt-1 text-xs italic text-muted-foreground">Tip: Mag-save pirme Ma'am! Indi mag-hulat nga complete na tanan bago mag-save &mdash; para wala mawala ang progress.</p>
                            </div>
                        </div>
                        <Separator />
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">5</span>
                            <div>
                                <p class="font-medium">Lock Grades</p>
                                <p class="text-muted-foreground">Pag sure na tanan tama, click mo <span class="font-medium text-foreground">"Lock Grades"</span>. After ma-lock, indi na ma-edit. Kung may error pa, mag-request sang unlock sa Admin.</p>
                                <p class="mt-1 text-xs italic text-muted-foreground">Tip: Make sure tanan students may Midterm and Finals scores na before mag-lock. Once locked, Admin lang ang pwede mag-unlock for corrections.</p>
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

                <!-- 9. Common Questions -->
                <Card id="faq" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle>Common Questions</CardTitle>
                        <CardDescription>Ni Ma'am ang mga common nga questions nga basi ma-encounter mo.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-1.5">
                            <p class="text-sm font-medium">Wala Register button? Paano mag-create sang account?</p>
                            <p class="text-sm text-muted-foreground">Wala gid Ma'am sang public registration. Admin lang ang pwede mag-create sang accounts. For security &mdash; indi pwede basta-basta mag-register ang bisan sin-o. Admin ang naga-control kung sin-o ang may access and ano nga role ang i-give (Teacher, Registrar, Student).</p>
                        </div>
                        <Separator />
                        <div class="space-y-1.5">
                            <p class="text-sm font-medium">Paano mag-reset sang password?</p>
                            <p class="text-sm text-muted-foreground">Admin ang pwede mag-reset. Kadto sa Users &rarr; click ang user &rarr; Edit &rarr; set new password. Or ang user mismo pwede mag-change sa Settings &rarr; Password.</p>
                        </div>
                        <Separator />
                        <div class="space-y-1.5">
                            <p class="text-sm font-medium">Indi ma-enroll ang student. Ngaa?</p>
                            <p class="text-sm text-muted-foreground">Check mo Ma'am kung open ang enrollment for the current semester. Admin ang naga-open/close sini. Kung closed, wala gid pwede mag-enroll.</p>
                        </div>
                        <Separator />
                        <div class="space-y-1.5">
                            <p class="text-sm font-medium">Na-lock na ang grades pero may error. Ano himuon?</p>
                            <p class="text-sm text-muted-foreground">Mag-request sa Admin para i-unlock. Admin kadto sa Grade Entry &rarr; select ang section and subject &rarr; click "Unlock". Then pwede na mag-edit ang teacher and i-lock ulit.</p>
                        </div>
                        <Separator />
                        <div class="space-y-1.5">
                            <p class="text-sm font-medium">Paano mag-import sang students via CSV?</p>
                            <p class="text-sm text-muted-foreground">Kadto sa Import &rarr; download ang CSV template &rarr; fill-up sa Excel/Sheets &rarr; upload &rarr; review ang preview &rarr; confirm.</p>
                        </div>
                        <Separator />
                        <div class="space-y-1.5">
                            <p class="text-sm font-medium">Makita bala sang student ang iya grades?</p>
                            <p class="text-sm text-muted-foreground">Huo Ma'am! Makita nila sa "My Grades" portal pag na-encode na sang teacher, bisan wala pa na-lock.</p>
                        </div>
                        <Separator />
                        <div class="space-y-1.5">
                            <p class="text-sm font-medium">Paano mag-generate sang School Forms (SF1, SF5, SF9, SF10)?</p>
                            <p class="text-sm text-muted-foreground">Kadto sa Reports &rarr; School Forms &rarr; pick ang form type and section/student &rarr; click Generate. Ma-download siya as PDF.</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Technologies divider -->
                <div class="flex items-center gap-3 pt-2">
                    <Separator class="flex-1" />
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Technical Details</span>
                    <Separator class="flex-1" />
                </div>

                <!-- 10. Technologies Used -->
                <Card id="tech" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle>Technologies Used</CardTitle>
                        <CardDescription>Ni Ma'am ang tech stack nga gin-gamit sa system.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <p class="text-sm text-muted-foreground">Ni ang mga gin-gamit namon sa pag-build sang system:</p>

                        <div class="space-y-3">
                            <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Backend (Server-side)</p>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="flex items-start gap-3 rounded-lg border p-3">
                                    <Badge variant="default" class="mt-0.5 shrink-0">Laravel 12</Badge>
                                    <p class="text-sm text-muted-foreground">PHP web framework &mdash; handles routing, database, authentication, and business logic.</p>
                                </div>
                                <div class="flex items-start gap-3 rounded-lg border p-3">
                                    <Badge variant="default" class="mt-0.5 shrink-0">PHP 8.2+</Badge>
                                    <p class="text-sm text-muted-foreground">Ang programming language sa backend &mdash; fast and modern.</p>
                                </div>
                                <div class="flex items-start gap-3 rounded-lg border p-3">
                                    <Badge variant="default" class="mt-0.5 shrink-0">MySQL</Badge>
                                    <p class="text-sm text-muted-foreground">Relational database &mdash; diri naka-save tanan nga data (students, grades, enrollment, etc.).</p>
                                </div>
                                <div class="flex items-start gap-3 rounded-lg border p-3">
                                    <Badge variant="default" class="mt-0.5 shrink-0">Inertia.js</Badge>
                                    <p class="text-sm text-muted-foreground">Bridge between Laravel and Vue &mdash; single-page app feel bisan server-rendered.</p>
                                </div>
                            </div>
                        </div>

                        <Separator />

                        <div class="space-y-3">
                            <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Frontend (Client-side)</p>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="flex items-start gap-3 rounded-lg border p-3">
                                    <Badge variant="secondary" class="mt-0.5 shrink-0">Vue.js 3</Badge>
                                    <p class="text-sm text-muted-foreground">JavaScript framework for the UI &mdash; reactive and component-based.</p>
                                </div>
                                <div class="flex items-start gap-3 rounded-lg border p-3">
                                    <Badge variant="secondary" class="mt-0.5 shrink-0">TypeScript</Badge>
                                    <p class="text-sm text-muted-foreground">Typed JavaScript &mdash; less bugs, better code quality.</p>
                                </div>
                                <div class="flex items-start gap-3 rounded-lg border p-3">
                                    <Badge variant="secondary" class="mt-0.5 shrink-0">Tailwind CSS 4</Badge>
                                    <p class="text-sm text-muted-foreground">Utility-first CSS framework &mdash; responsive and clean design.</p>
                                </div>
                                <div class="flex items-start gap-3 rounded-lg border p-3">
                                    <Badge variant="secondary" class="mt-0.5 shrink-0">Chart.js</Badge>
                                    <p class="text-sm text-muted-foreground">Charting library for dashboard visualizations (enrollment stats, section capacity).</p>
                                </div>
                            </div>
                        </div>

                        <Separator />

                        <div class="space-y-3">
                            <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Key Packages &amp; Tools</p>
                            <ul class="grid gap-1.5 text-sm text-muted-foreground sm:grid-cols-2">
                                <li class="flex items-center gap-2"><ChevronRight class="h-3 w-3 shrink-0 text-primary" /><span class="font-medium text-foreground">Spatie Permissions</span> &mdash; role-based access control</li>
                                <li class="flex items-center gap-2"><ChevronRight class="h-3 w-3 shrink-0 text-primary" /><span class="font-medium text-foreground">Laravel Fortify</span> &mdash; authentication (login, password)</li>
                                <li class="flex items-center gap-2"><ChevronRight class="h-3 w-3 shrink-0 text-primary" /><span class="font-medium text-foreground">Maatwebsite Excel</span> &mdash; CSV import/export</li>
                                <li class="flex items-center gap-2"><ChevronRight class="h-3 w-3 shrink-0 text-primary" /><span class="font-medium text-foreground">Spatie PDF</span> &mdash; PDF generation (school forms)</li>
                                <li class="flex items-center gap-2"><ChevronRight class="h-3 w-3 shrink-0 text-primary" /><span class="font-medium text-foreground">Vite</span> &mdash; frontend build tool (fast dev and production builds)</li>
                                <li class="flex items-center gap-2"><ChevronRight class="h-3 w-3 shrink-0 text-primary" /><span class="font-medium text-foreground">Nginx</span> &mdash; web server in production</li>
                            </ul>
                        </div>

                        <Separator />

                        <div class="rounded-lg bg-muted px-4 py-3 text-sm text-muted-foreground">
                            <span class="font-medium text-foreground">Architecture:</span> Ang system naga-gamit sang <span class="font-medium text-foreground">monolithic architecture</span> &mdash; Laravel (PHP) sa backend and Vue.js sa frontend, connected through Inertia.js. Naka-deploy sa DigitalOcean cloud server gamit ang Nginx and MySQL.
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
