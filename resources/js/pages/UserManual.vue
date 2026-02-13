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
    { id: 'about', title: 'Ano ang System?', icon: BookOpen },
    { id: 'tech', title: 'Technologies', icon: Cpu },
    { id: 'accounts', title: 'Test Accounts', icon: KeyRound },
    { id: 'admin', title: 'Admin Guide', icon: ShieldCheck },
    { id: 'registrar', title: 'Registrar Guide', icon: ClipboardList },
    { id: 'teacher', title: 'Teacher Guide', icon: GraduationCap },
    { id: 'student', title: 'Student Guide', icon: UserCheck },
    { id: 'enroll-steps', title: 'Paano Mag-Enroll?', icon: FileText },
    { id: 'grade-steps', title: 'Paano Mag-Encode?', icon: PenLine },
    { id: 'faq', title: 'Common Questions', icon: HelpCircle },
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

                <!-- 1. Ano ang System? -->
                <Card id="about" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle>Ano ang System?</CardTitle>
                        <CardDescription>Ano ini nga system kag para sa kay sin-o.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <p class="text-sm leading-relaxed text-muted-foreground">
                            Ang <span class="font-medium text-foreground">Senior High School Enrollment System</span> isa ka web-based nga system para sa <span class="font-medium text-foreground">Lake Sebu National High School</span>. Ginagamit ini para sa enrollment, pag-manage sang student records, pag-encode sang grades, kag pag-generate sang DepEd school forms.
                        </p>
                        <p class="text-sm leading-relaxed text-muted-foreground">
                            May apat ka klase sang user: <span class="font-medium text-foreground">Admin</span> (full control), <span class="font-medium text-foreground">Registrar</span> (enrollment kag records), <span class="font-medium text-foreground">Teacher</span> (grade entry), kag <span class="font-medium text-foreground">Student</span> (view-only portal).
                        </p>
                    </CardContent>
                </Card>

                <!-- 2. Technologies Used -->
                <Card id="tech" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle>Ano nga Technologies ang Gin-gamit?</CardTitle>
                        <CardDescription>Ang mga technology kag framework sa likod sang system.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <p class="text-sm text-muted-foreground">Amo ini ang mga gin-gamit sa pag-develop sang system:</p>

                        <div class="space-y-3">
                            <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Backend (Server-side)</p>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="flex items-start gap-3 rounded-lg border p-3">
                                    <Badge variant="default" class="mt-0.5 shrink-0">Laravel 12</Badge>
                                    <p class="text-sm text-muted-foreground">PHP web framework &mdash; nagahandle sang routing, database, authentication, kag business logic.</p>
                                </div>
                                <div class="flex items-start gap-3 rounded-lg border p-3">
                                    <Badge variant="default" class="mt-0.5 shrink-0">PHP 8.2+</Badge>
                                    <p class="text-sm text-muted-foreground">Ang programming language sa backend &mdash; fast kag modern.</p>
                                </div>
                                <div class="flex items-start gap-3 rounded-lg border p-3">
                                    <Badge variant="default" class="mt-0.5 shrink-0">MySQL</Badge>
                                    <p class="text-sm text-muted-foreground">Relational database &mdash; diri ginasave ang tanan nga data (students, grades, enrollment, etc.).</p>
                                </div>
                                <div class="flex items-start gap-3 rounded-lg border p-3">
                                    <Badge variant="default" class="mt-0.5 shrink-0">Inertia.js</Badge>
                                    <p class="text-sm text-muted-foreground">Bridge between Laravel kag Vue &mdash; single-page app experience bisan server-rendered.</p>
                                </div>
                            </div>
                        </div>

                        <Separator />

                        <div class="space-y-3">
                            <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Frontend (Client-side)</p>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="flex items-start gap-3 rounded-lg border p-3">
                                    <Badge variant="secondary" class="mt-0.5 shrink-0">Vue.js 3</Badge>
                                    <p class="text-sm text-muted-foreground">JavaScript framework para sa interactive UI &mdash; reactive kag component-based.</p>
                                </div>
                                <div class="flex items-start gap-3 rounded-lg border p-3">
                                    <Badge variant="secondary" class="mt-0.5 shrink-0">TypeScript</Badge>
                                    <p class="text-sm text-muted-foreground">Typed JavaScript &mdash; less bugs, better code quality.</p>
                                </div>
                                <div class="flex items-start gap-3 rounded-lg border p-3">
                                    <Badge variant="secondary" class="mt-0.5 shrink-0">Tailwind CSS 4</Badge>
                                    <p class="text-sm text-muted-foreground">Utility-first CSS framework &mdash; responsive kag clean design.</p>
                                </div>
                                <div class="flex items-start gap-3 rounded-lg border p-3">
                                    <Badge variant="secondary" class="mt-0.5 shrink-0">Chart.js</Badge>
                                    <p class="text-sm text-muted-foreground">Charting library para sa dashboard visualizations (enrollment stats, section capacity).</p>
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
                                <li class="flex items-center gap-2"><ChevronRight class="h-3 w-3 shrink-0 text-primary" /><span class="font-medium text-foreground">Vite</span> &mdash; frontend build tool (fast dev kag production builds)</li>
                                <li class="flex items-center gap-2"><ChevronRight class="h-3 w-3 shrink-0 text-primary" /><span class="font-medium text-foreground">Nginx</span> &mdash; web server sa production</li>
                            </ul>
                        </div>

                        <Separator />

                        <div class="rounded-lg bg-muted px-4 py-3 text-sm text-muted-foreground">
                            <span class="font-medium text-foreground">Architecture:</span> Ang system naga-gamit sang <span class="font-medium text-foreground">monolithic architecture</span> gamit ang Laravel (PHP) sa backend kag Vue.js sa frontend, gin-connect paagi sa Inertia.js. Gin-deploy sa DigitalOcean cloud server gamit ang Nginx kag MySQL.
                        </div>
                    </CardContent>
                </Card>

                <!-- 3. Test Accounts -->
                <Card id="accounts" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle>Mga Account para sa Testing</CardTitle>
                        <CardDescription>Gamiton ini nga accounts para ma-test ang system. Tanan nga password: <code class="rounded bg-muted px-1.5 py-0.5 text-xs font-mono">password</code></CardDescription>
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
                        <p class="mt-3 text-xs text-muted-foreground">May 5 ka teacher accounts kag 3 ka student accounts sa demo data. Ang mga listed sa ibabaw ang pinaka-common nga gamiton sa testing.</p>
                    </CardContent>
                </Card>

                <!-- Role Guides divider -->
                <div class="flex items-center gap-3 pt-2">
                    <Separator class="flex-1" />
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Role Guides</span>
                    <Separator class="flex-1" />
                </div>

                <!-- 4. Admin Guide -->
                <Card id="admin" class="scroll-mt-20">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <CardTitle>Admin Guide</CardTitle>
                                <CardDescription>Tanan nga makita kag mahimo sang Admin.</CardDescription>
                            </div>
                            <Badge variant="default">Admin</Badge>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <p class="text-sm font-medium">Dashboard</p>
                            <p class="text-sm text-muted-foreground">Pag-login mo bilang <span class="font-medium text-foreground">Admin</span>, amo ni ang makita mo sa Dashboard &mdash; enrollment stats (pila ka students ang enrolled), section capacity chart, kag recent enrollments.</p>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Users</p>
                            <p class="text-sm text-muted-foreground">I-click ang <span class="font-medium text-foreground">Users</span> para makita ang lista sang tanan nga accounts. Pwede ka mag-add sang bag-o nga user, i-edit ang details, ukon i-deactivate kung indi na active.</p>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">School Settings</p>
                            <p class="text-sm text-muted-foreground">Diri mo ma-update ang school info (school name, ID, address) kag ang grading config (passing grade, midterm/finals weight).</p>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">School Years</p>
                            <p class="text-sm text-muted-foreground">Mag-create sang bag-o nga school year (e.g., 2025-2026) &mdash; auto may 2 semesters. I-activate ang school year, tapos i-activate ang semester, kag i-open ang enrollment kung ready na.</p>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Curriculum (Tracks &amp; Strands)</p>
                            <p class="text-sm text-muted-foreground">Sa <span class="font-medium text-foreground">Curriculum</span>, makita mo ang mga tracks (Academic, TVL) kag strands (STEM, ABM, HUMSS, GAS, ICT, HE, AFA). Pwede mo i-add, i-edit, ukon i-toggle active/inactive.</p>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Subjects</p>
                            <p class="text-sm text-muted-foreground">Ang mga subjects gin-map sa strand, grade level, kag semester. May core subjects (para sa tanan nga strand) kag specialized subjects (per strand). Pwede mo i-add ukon i-edit.</p>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Students, Teachers, Sections</p>
                            <p class="text-sm text-muted-foreground">Pwede man ang Admin mag-manage sang student records (may LRN kag guardian info), teacher profiles (kag trainings), kag sections (assign adviser, set capacity).</p>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Enrollment</p>
                            <p class="text-sm text-muted-foreground">May 5-step enrollment wizard: (1) pangita-a ang student, (2) pili-a ang strand, (3) auto-load ang subjects, (4) pili-a ang section, (5) confirm.</p>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Grades</p>
                            <p class="text-sm text-muted-foreground">Makita ang grades per section. Ang Admin lang ang pwede mag-<span class="font-medium text-foreground">unlock</span> sang grades kung na-lock na sang teacher kag may corrections pa.</p>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Reports</p>
                            <p class="text-sm text-muted-foreground">Enrollment summary, class list, masterlist, grade summary, kag DepEd school forms &mdash; SF1 (School Register), SF5 (Report on Promotion), SF9 (Report Card), SF10 (Permanent Record).</p>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Import</p>
                            <p class="text-sm text-muted-foreground">Bulk upload sang students ukon grades via CSV file. Download ang template, fill-up-an, upload, review, tapos confirm.</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- 5. Registrar Guide -->
                <Card id="registrar" class="scroll-mt-20">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <CardTitle>Registrar Guide</CardTitle>
                                <CardDescription>Enrollment, student records, sections, kag reports.</CardDescription>
                            </div>
                            <Badge variant="secondary">Registrar</Badge>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <p class="text-sm text-muted-foreground">
                            Ang Registrar pareho sa Admin ang access sa enrollment workflow, pero <span class="font-medium text-foreground">wala</span> sang access sa Users, School Settings, kag School Years. Ang focus sang Registrar:
                        </p>

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Primary Responsibilities</p>
                            <ul class="space-y-1.5 text-sm text-muted-foreground">
                                <li class="flex items-start gap-2"><ChevronRight class="mt-0.5 h-3 w-3 shrink-0 text-primary" /><span><span class="font-medium text-foreground">Enrollment</span> &mdash; amo ni ang main task. Gamiton ang 5-step wizard para i-enroll ang students.</span></li>
                                <li class="flex items-start gap-2"><ChevronRight class="mt-0.5 h-3 w-3 shrink-0 text-primary" /><span><span class="font-medium text-foreground">Students</span> &mdash; add/edit student records, LRN, guardian info.</span></li>
                                <li class="flex items-start gap-2"><ChevronRight class="mt-0.5 h-3 w-3 shrink-0 text-primary" /><span><span class="font-medium text-foreground">Sections</span> &mdash; gawa sections, assign adviser, set max capacity.</span></li>
                                <li class="flex items-start gap-2"><ChevronRight class="mt-0.5 h-3 w-3 shrink-0 text-primary" /><span><span class="font-medium text-foreground">Teachers</span> &mdash; view kag update teacher profiles kag trainings.</span></li>
                                <li class="flex items-start gap-2"><ChevronRight class="mt-0.5 h-3 w-3 shrink-0 text-primary" /><span><span class="font-medium text-foreground">Reports</span> &mdash; generate enrollment summary, class list, masterlist, grade summary, kag school forms (SF1, SF5, SF9, SF10).</span></li>
                                <li class="flex items-start gap-2"><ChevronRight class="mt-0.5 h-3 w-3 shrink-0 text-primary" /><span><span class="font-medium text-foreground">Import</span> &mdash; bulk upload students/grades via CSV.</span></li>
                            </ul>
                        </div>
                    </CardContent>
                </Card>

                <!-- 6. Teacher Guide -->
                <Card id="teacher" class="scroll-mt-20">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <CardTitle>Teacher Guide</CardTitle>
                                <CardDescription>Grade entry kag pag-lock sang grades.</CardDescription>
                            </div>
                            <Badge variant="outline">Teacher</Badge>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <p class="text-sm text-muted-foreground">Ang Teacher focused lang sa <span class="font-medium text-foreground">Grade Entry</span>. Amo ni ang makita sa sidebar:</p>

                        <div class="space-y-2">
                            <ul class="space-y-1.5 text-sm text-muted-foreground">
                                <li class="flex items-start gap-2"><ChevronRight class="mt-0.5 h-3 w-3 shrink-0 text-primary" /><span><span class="font-medium text-foreground">Grade Entry</span> &mdash; makita ang lista sang sections kag subjects nga assigned sa imo. I-click para mag-encode.</span></li>
                                <li class="flex items-start gap-2"><ChevronRight class="mt-0.5 h-3 w-3 shrink-0 text-primary" /><span><span class="font-medium text-foreground">Encode Midterm</span> &mdash; i-type ang midterm scores sang kada student, tapos Save.</span></li>
                                <li class="flex items-start gap-2"><ChevronRight class="mt-0.5 h-3 w-3 shrink-0 text-primary" /><span><span class="font-medium text-foreground">Encode Finals</span> &mdash; i-type ang finals scores, auto-compute ang final grade.</span></li>
                                <li class="flex items-start gap-2"><ChevronRight class="mt-0.5 h-3 w-3 shrink-0 text-primary" /><span><span class="font-medium text-foreground">Lock</span> &mdash; pag-complete na, i-click "Lock Grades". Pagka-lock, indi na ma-edit unless mag-request sang unlock sa Admin.</span></li>
                            </ul>
                        </div>

                        <div class="space-y-2">
                            <p class="text-sm font-medium">My Sections &amp; My Students</p>
                            <p class="text-sm text-muted-foreground">Makita man ang mga sections nga gin-assign sa imo kag ang lista sang students per section.</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- 7. Student Guide -->
                <Card id="student" class="scroll-mt-20">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <CardTitle>Student Guide</CardTitle>
                                <CardDescription>View-only portal para sa students.</CardDescription>
                            </div>
                            <Badge variant="outline">Student</Badge>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <p class="text-sm text-muted-foreground">Ang Student portal <span class="font-medium text-foreground">view-only</span> lang &mdash; wala sang pwede i-edit. May tatlo ka pages:</p>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-lg border p-3">
                                <p class="text-sm font-medium">My Profile</p>
                                <p class="mt-1 text-sm text-muted-foreground">Tan-aw sang personal info, LRN, address, kag guardian details.</p>
                            </div>
                            <div class="rounded-lg border p-3">
                                <p class="text-sm font-medium">My Subjects</p>
                                <p class="mt-1 text-sm text-muted-foreground">Makita ang enrolled subjects, section, kag teacher para sa current semester.</p>
                            </div>
                            <div class="rounded-lg border p-3">
                                <p class="text-sm font-medium">My Grades</p>
                                <p class="mt-1 text-sm text-muted-foreground">Tan-aw sang midterm kag finals grades pag-encode na sang teacher.</p>
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

                <!-- 8. Paano Mag-Enroll -->
                <Card id="enroll-steps" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle>Paano Mag-Enroll? (Step-by-Step)</CardTitle>
                        <CardDescription>Ang 5 steps sa enrollment wizard.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">1</span>
                            <div>
                                <p class="font-medium">Pangita-a ang Student</p>
                                <p class="text-muted-foreground">Kadto sa <span class="font-medium text-foreground">Enrollment</span> &rarr; click "Enroll Student". I-search ang name ukon LRN. Kung wala pa, pwede mag-create sang bag-o nga student record diri mismo.</p>
                            </div>
                        </div>
                        <Separator />
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">2</span>
                            <div>
                                <p class="font-medium">Pili-a ang Grade Level kag Strand</p>
                                <p class="text-muted-foreground">Pili-a kung Grade 11 ukon Grade 12, tapos pili-a ang strand (e.g., STEM, ABM, HUMSS, ICT).</p>
                            </div>
                        </div>
                        <Separator />
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">3</span>
                            <div>
                                <p class="font-medium">Auto-load ang Subjects</p>
                                <p class="text-muted-foreground">Ang system auto mag-load sang mga subjects base sa strand kag grade level. Indi na kinahanglan mag-pili isa-isa.</p>
                            </div>
                        </div>
                        <Separator />
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">4</span>
                            <div>
                                <p class="font-medium">Pili-a ang Section</p>
                                <p class="text-muted-foreground">Makita ang available sections kag kung pila pa ang slot. Pili-a ang section nga may available capacity.</p>
                            </div>
                        </div>
                        <Separator />
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">5</span>
                            <div>
                                <p class="font-medium">Confirm</p>
                                <p class="text-muted-foreground">Review ang tanan nga details &mdash; student info, strand, subjects, section. Kung tama na, i-click "Confirm Enrollment". Done!</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- 9. Paano Mag-Encode sang Grades -->
                <Card id="grade-steps" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle>Paano Mag-Encode sang Grades?</CardTitle>
                        <CardDescription>Simple flow para sa teachers.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">1</span>
                            <div>
                                <p class="font-medium">Kadto sa Grade Entry</p>
                                <p class="text-muted-foreground">I-click ang <span class="font-medium text-foreground">Grade Entry</span> sa sidebar. Makita ang lista sang imo sections kag subjects.</p>
                            </div>
                        </div>
                        <Separator />
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">2</span>
                            <div>
                                <p class="font-medium">Pili-a ang Section kag Subject</p>
                                <p class="text-muted-foreground">I-click ang section/subject combination nga gusto mo encode-an.</p>
                            </div>
                        </div>
                        <Separator />
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">3</span>
                            <div>
                                <p class="font-medium">I-type ang Scores</p>
                                <p class="text-muted-foreground">I-enter ang <span class="font-medium text-foreground">Midterm</span> kag <span class="font-medium text-foreground">Finals</span> scores sang kada student. Ang final grade auto-compute (average sang midterm kag finals).</p>
                            </div>
                        </div>
                        <Separator />
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">4</span>
                            <div>
                                <p class="font-medium">Save</p>
                                <p class="text-muted-foreground">I-click <span class="font-medium text-foreground">Save</span>. Pwede ka mag-balik-balik para i-edit basta wala pa na-lock.</p>
                            </div>
                        </div>
                        <Separator />
                        <div class="flex items-start gap-3 text-sm">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">5</span>
                            <div>
                                <p class="font-medium">Lock Grades</p>
                                <p class="text-muted-foreground">Pag-sure na tanan tama, i-click <span class="font-medium text-foreground">"Lock Grades"</span>. Pagka-lock, indi na ma-edit. Kung may error pa, mag-request sang unlock sa Admin.</p>
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

                <!-- 10. Common Questions -->
                <Card id="faq" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle>Common Questions</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-1.5">
                            <p class="text-sm font-medium">Wala Register button? Paano mag-create sang account?</p>
                            <p class="text-sm text-muted-foreground">Wala sang public registration sa system. Ang Admin lang ang pwede mag-create sang accounts. Ini para sa security &mdash; indi pwede basta-basta mag-register ang bisan sin-o. Ang Admin ang naga-control kung sin-o ang may access kag ano nga role ang ihatag (Teacher, Registrar, Student).</p>
                        </div>
                        <Separator />
                        <div class="space-y-1.5">
                            <p class="text-sm font-medium">Paano mag-reset sang password?</p>
                            <p class="text-sm text-muted-foreground">Ang Admin ang pwede mag-reset. Kadto sa Users &rarr; i-click ang user &rarr; Edit &rarr; bag-o nga password. Ukon ang user mismo pwede mag-change sa Settings &rarr; Password.</p>
                        </div>
                        <Separator />
                        <div class="space-y-1.5">
                            <p class="text-sm font-medium">Indi ma-enroll ang student. Ngaa?</p>
                            <p class="text-sm text-muted-foreground">Check kung open ang enrollment sa current semester. Ang Admin ang naga-open/close sang enrollment. Kung closed, indi pwede mag-enroll.</p>
                        </div>
                        <Separator />
                        <div class="space-y-1.5">
                            <p class="text-sm font-medium">Na-lock na ang grades pero may error. Ano himuon?</p>
                            <p class="text-sm text-muted-foreground">Mag-request sa Admin para i-unlock. Ang Admin kadto sa Grade Entry &rarr; select section kag subject &rarr; click "Unlock". Tapos pwede na mag-edit ang teacher kag i-lock ulit.</p>
                        </div>
                        <Separator />
                        <div class="space-y-1.5">
                            <p class="text-sm font-medium">Paano mag-import sang students via CSV?</p>
                            <p class="text-sm text-muted-foreground">Kadto sa Import &rarr; download ang CSV template &rarr; fill-up sa Excel/Sheets &rarr; upload &rarr; review ang preview &rarr; confirm.</p>
                        </div>
                        <Separator />
                        <div class="space-y-1.5">
                            <p class="text-sm font-medium">Makita bala sang student ang iya grades?</p>
                            <p class="text-sm text-muted-foreground">Huo, makita sa "My Grades" portal pag-encode na sang teacher, bisan wala pa na-lock.</p>
                        </div>
                        <Separator />
                        <div class="space-y-1.5">
                            <p class="text-sm font-medium">Paano mag-generate sang School Forms (SF1, SF5, SF9, SF10)?</p>
                            <p class="text-sm text-muted-foreground">Kadto sa Reports &rarr; School Forms &rarr; pili-a ang form type kag section/student &rarr; click Generate. Ma-download ang PDF.</p>
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
