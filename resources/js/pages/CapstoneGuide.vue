<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { ref, onMounted, onUnmounted } from 'vue';
import {
    BookOpen,
    Layers,
    Server,
    Settings,
    Database,
    GitBranch,
    Cpu,
    ArrowLeft,
    Menu,
    X,
    GraduationCap,
    Workflow,
    Download,
} from 'lucide-vue-next';

const sections = [
    { id: 'section-3-1', title: '3.1 System Overview', icon: Layers },
    { id: 'section-3-2', title: '3.2 Dev Model', icon: Workflow },
    { id: 'section-3-3', title: '3.3 Architecture', icon: Server },
    { id: 'section-3-4', title: '3.4 Design Specs', icon: Settings },
    { id: 'section-3-5', title: '3.5 Data Modeling', icon: Database },
    { id: 'section-3-6', title: '3.6 Graph Model', icon: GitBranch },
    { id: 'section-3-7', title: '3.7 Algorithms', icon: Cpu },
];

const activeSection = ref('section-3-1');
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

function downloadDiagram(containerId: string, filename: string) {
    const svg = document.querySelector(`#${containerId} > svg`);
    if (!svg) return;
    const clone = svg.cloneNode(true) as SVGElement;
    const vb = svg.getAttribute('viewBox');
    if (vb) {
        const parts = vb.split(/[\s,]+/).map(Number);
        clone.setAttribute('width', String(parts[2] * 2));
        clone.setAttribute('height', String(parts[3] * 2));
    }
    clone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
    const data = new XMLSerializer().serializeToString(clone);
    const blob = new Blob([data], { type: 'image/svg+xml;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const img = new Image();
    img.onload = () => {
        const canvas = document.createElement('canvas');
        canvas.width = img.naturalWidth;
        canvas.height = img.naturalHeight;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0);
        canvas.toBlob((b) => {
            if (!b) return;
            const a = document.createElement('a');
            a.href = URL.createObjectURL(b);
            a.download = filename;
            a.click();
            URL.revokeObjectURL(a.href);
        }, 'image/png');
        URL.revokeObjectURL(url);
    };
    img.src = url;
}
</script>

<template>
    <Head title="Chapter 3 — System Design Diagrams" />

    <div class="min-h-screen bg-background">
        <!-- Header -->
        <header class="sticky top-0 z-30 border-b bg-background/80 backdrop-blur-sm">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary">
                        <GraduationCap class="h-6 w-6 text-primary-foreground" />
                    </div>
                    <div class="hidden sm:block">
                        <p class="text-sm font-semibold leading-tight">Chapter 3 Diagrams</p>
                        <p class="text-xs leading-tight text-muted-foreground">System Design &amp; Development</p>
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
            <div v-if="mobileMenuOpen" class="fixed inset-0 z-40 bg-background/80 backdrop-blur-sm lg:hidden" @click="mobileMenuOpen = false" />
            <aside :class="['shrink-0 lg:sticky lg:top-20 lg:block lg:h-[calc(100vh-6rem)] lg:w-56 lg:overflow-y-auto', mobileMenuOpen ? 'fixed inset-y-0 left-0 z-50 block w-64 overflow-y-auto border-r bg-background p-4 shadow-xl' : 'hidden']">
                <nav class="space-y-0.5">
                    <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-muted-foreground">Diagrams</p>
                    <button v-for="section in sections" :key="section.id" class="flex w-full items-center gap-2.5 rounded-md px-3 py-1.5 text-left text-sm transition-colors" :class="activeSection === section.id ? 'bg-accent text-accent-foreground font-medium' : 'text-muted-foreground hover:bg-accent/50 hover:text-foreground'" @click="scrollToSection(section.id)">
                        <component :is="section.icon" class="h-3.5 w-3.5 shrink-0" />
                        <span>{{ section.title }}</span>
                    </button>
                </nav>
            </aside>

            <!-- Content -->
            <div class="min-w-0 flex-1 space-y-8">

                <!-- ============================================================ -->
                <!-- 3.1 USE CASE DIAGRAM -->
                <!-- ============================================================ -->
                <Card id="section-3-1" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle>3.1 Use Case Diagram</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div id="usecase-diagram" class="relative overflow-x-auto rounded-lg border bg-white p-4 dark:bg-zinc-950">
                            <button @click="downloadDiagram('usecase-diagram', 'UseCase-Diagram.png')" class="absolute right-2 top-2 z-10 inline-flex items-center gap-1.5 rounded-md border bg-white px-2.5 py-1 text-xs font-medium text-muted-foreground shadow-sm transition-colors hover:bg-accent hover:text-foreground dark:bg-zinc-900" title="Download as PNG"><Download class="h-3.5 w-3.5" /> Download</button>
                            <svg viewBox="0 0 800 520" class="mx-auto w-full max-w-3xl" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <marker id="arr" markerWidth="8" markerHeight="6" refX="8" refY="3" orient="auto"><polygon points="0 0, 8 3, 0 6" fill="#94a3b8"/></marker>
                                </defs>
                                <!-- System Boundary -->
                                <rect x="180" y="20" width="440" height="480" rx="12" fill="none" stroke="currentColor" stroke-width="1.5" stroke-dasharray="6 3" class="text-muted-foreground/40"/>
                                <text x="400" y="48" text-anchor="middle" class="fill-primary" style="font-size:13px;font-weight:700">SHS Enrollment &amp; Records Management System</text>

                                <!-- Admin actor -->
                                <circle cx="60" cy="95" r="12" fill="none" stroke="#3b82f6" stroke-width="2"/>
                                <line x1="60" y1="107" x2="60" y2="140" stroke="#3b82f6" stroke-width="2"/>
                                <line x1="40" y1="120" x2="80" y2="120" stroke="#3b82f6" stroke-width="2"/>
                                <line x1="60" y1="140" x2="40" y2="165" stroke="#3b82f6" stroke-width="2"/>
                                <line x1="60" y1="140" x2="80" y2="165" stroke="#3b82f6" stroke-width="2"/>
                                <text x="60" y="182" text-anchor="middle" fill="#3b82f6" style="font-size:11px;font-weight:700">Admin</text>

                                <!-- Registrar actor -->
                                <circle cx="60" cy="280" r="12" fill="none" stroke="#8b5cf6" stroke-width="2"/>
                                <line x1="60" y1="292" x2="60" y2="325" stroke="#8b5cf6" stroke-width="2"/>
                                <line x1="40" y1="305" x2="80" y2="305" stroke="#8b5cf6" stroke-width="2"/>
                                <line x1="60" y1="325" x2="40" y2="350" stroke="#8b5cf6" stroke-width="2"/>
                                <line x1="60" y1="325" x2="80" y2="350" stroke="#8b5cf6" stroke-width="2"/>
                                <text x="60" y="367" text-anchor="middle" fill="#8b5cf6" style="font-size:11px;font-weight:700">Registrar</text>

                                <!-- Teacher actor -->
                                <circle cx="740" cy="130" r="12" fill="none" stroke="#f59e0b" stroke-width="2"/>
                                <line x1="740" y1="142" x2="740" y2="175" stroke="#f59e0b" stroke-width="2"/>
                                <line x1="720" y1="155" x2="760" y2="155" stroke="#f59e0b" stroke-width="2"/>
                                <line x1="740" y1="175" x2="720" y2="200" stroke="#f59e0b" stroke-width="2"/>
                                <line x1="740" y1="175" x2="760" y2="200" stroke="#f59e0b" stroke-width="2"/>
                                <text x="740" y="217" text-anchor="middle" fill="#f59e0b" style="font-size:11px;font-weight:700">Teacher</text>

                                <!-- Student actor -->
                                <circle cx="740" cy="360" r="12" fill="none" stroke="#10b981" stroke-width="2"/>
                                <line x1="740" y1="372" x2="740" y2="405" stroke="#10b981" stroke-width="2"/>
                                <line x1="720" y1="385" x2="760" y2="385" stroke="#10b981" stroke-width="2"/>
                                <line x1="740" y1="405" x2="720" y2="430" stroke="#10b981" stroke-width="2"/>
                                <line x1="740" y1="405" x2="760" y2="430" stroke="#10b981" stroke-width="2"/>
                                <text x="740" y="447" text-anchor="middle" fill="#10b981" style="font-size:11px;font-weight:700">Student</text>

                                <!-- Use Cases -->
                                <ellipse cx="310" cy="85" rx="100" ry="22" fill="#3b82f6" fill-opacity="0.08" stroke="#3b82f6" stroke-width="1.5"/>
                                <text x="310" y="90" text-anchor="middle" style="font-size:11px;fill:#3b82f6">Manage Users</text>

                                <ellipse cx="310" cy="145" rx="100" ry="22" fill="#3b82f6" fill-opacity="0.08" stroke="#3b82f6" stroke-width="1.5"/>
                                <text x="310" y="150" text-anchor="middle" style="font-size:11px;fill:#3b82f6">Manage School Settings</text>

                                <ellipse cx="490" cy="85" rx="100" ry="22" fill="#3b82f6" fill-opacity="0.08" stroke="#3b82f6" stroke-width="1.5"/>
                                <text x="490" y="90" text-anchor="middle" style="font-size:11px;fill:#3b82f6">Manage Curriculum</text>

                                <ellipse cx="490" cy="145" rx="100" ry="22" fill="#3b82f6" fill-opacity="0.08" stroke="#3b82f6" stroke-width="1.5"/>
                                <text x="490" y="150" text-anchor="middle" style="font-size:11px;fill:#3b82f6">Unlock Grades</text>

                                <ellipse cx="310" cy="230" rx="100" ry="22" fill="#8b5cf6" fill-opacity="0.08" stroke="#8b5cf6" stroke-width="1.5"/>
                                <text x="310" y="235" text-anchor="middle" style="font-size:11px;fill:#8b5cf6">Enroll Students</text>

                                <ellipse cx="490" cy="230" rx="100" ry="22" fill="#8b5cf6" fill-opacity="0.08" stroke="#8b5cf6" stroke-width="1.5"/>
                                <text x="490" y="235" text-anchor="middle" style="font-size:11px;fill:#8b5cf6">Manage Sections</text>

                                <ellipse cx="310" cy="300" rx="100" ry="22" fill="#8b5cf6" fill-opacity="0.08" stroke="#8b5cf6" stroke-width="1.5"/>
                                <text x="310" y="305" text-anchor="middle" style="font-size:11px;fill:#8b5cf6">Manage Student Records</text>

                                <ellipse cx="490" cy="300" rx="100" ry="22" fill="#8b5cf6" fill-opacity="0.08" stroke="#8b5cf6" stroke-width="1.5"/>
                                <text x="490" y="305" text-anchor="middle" style="font-size:11px;fill:#8b5cf6">Generate Reports (SF1-10)</text>

                                <ellipse cx="490" cy="170" rx="100" ry="22" fill="#f59e0b" fill-opacity="0.08" stroke="#f59e0b" stroke-width="1.5"/>
                                <text x="490" y="175" text-anchor="middle" style="font-size:11px;fill:#f59e0b">Enter &amp; Lock Grades</text>

                                <ellipse cx="490" cy="380" rx="100" ry="22" fill="#10b981" fill-opacity="0.08" stroke="#10b981" stroke-width="1.5"/>
                                <text x="490" y="385" text-anchor="middle" style="font-size:11px;fill:#10b981">View My Grades</text>

                                <ellipse cx="310" cy="380" rx="100" ry="22" fill="#10b981" fill-opacity="0.08" stroke="#10b981" stroke-width="1.5"/>
                                <text x="310" y="385" text-anchor="middle" style="font-size:11px;fill:#10b981">View My Profile</text>

                                <ellipse cx="400" cy="455" rx="100" ry="22" fill="#10b981" fill-opacity="0.08" stroke="#10b981" stroke-width="1.5"/>
                                <text x="400" y="460" text-anchor="middle" style="font-size:11px;fill:#10b981">View My Subjects</text>

                                <!-- Lines -->
                                <line x1="85" y1="110" x2="210" y2="85" stroke="#3b82f6" stroke-width="1" opacity="0.5"/>
                                <line x1="85" y1="130" x2="210" y2="145" stroke="#3b82f6" stroke-width="1" opacity="0.5"/>
                                <line x1="85" y1="100" x2="390" y2="85" stroke="#3b82f6" stroke-width="1" opacity="0.5"/>
                                <line x1="85" y1="140" x2="390" y2="145" stroke="#3b82f6" stroke-width="1" opacity="0.5"/>
                                <line x1="85" y1="300" x2="210" y2="230" stroke="#8b5cf6" stroke-width="1" opacity="0.5"/>
                                <line x1="85" y1="310" x2="210" y2="300" stroke="#8b5cf6" stroke-width="1" opacity="0.5"/>
                                <line x1="85" y1="290" x2="390" y2="230" stroke="#8b5cf6" stroke-width="1" opacity="0.5"/>
                                <line x1="85" y1="305" x2="390" y2="300" stroke="#8b5cf6" stroke-width="1" opacity="0.5"/>
                                <line x1="718" y1="160" x2="590" y2="170" stroke="#f59e0b" stroke-width="1" opacity="0.5"/>
                                <line x1="718" y1="375" x2="590" y2="380" stroke="#10b981" stroke-width="1" opacity="0.5"/>
                                <line x1="718" y1="385" x2="410" y2="380" stroke="#10b981" stroke-width="1" opacity="0.5"/>
                                <line x1="718" y1="400" x2="500" y2="455" stroke="#10b981" stroke-width="1" opacity="0.5"/>
                            </svg>
                            <p class="mt-2 text-center text-xs italic text-muted-foreground">Figure 3.1 &mdash; Use Case Diagram</p>
                        </div>

                        <Separator />

                        <!-- Activity Flow -->
                        <p class="text-sm font-semibold">High-Level Activity Flow</p>
                        <div class="overflow-x-auto rounded-lg border bg-white p-6 dark:bg-zinc-950">
                            <div class="mx-auto flex max-w-2xl flex-col items-center gap-0">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-900 dark:bg-zinc-100"><div class="h-3 w-3 rounded-full bg-white dark:bg-zinc-900"></div></div>
                                <div class="h-5 w-0.5 bg-muted-foreground/30"></div>
                                <div class="w-full max-w-xs rounded-lg border-2 border-blue-500/30 bg-blue-50 px-4 py-2 text-center dark:bg-blue-950/30">
                                    <p class="text-[10px] font-bold uppercase text-blue-500">Admin</p>
                                    <p class="text-xs font-medium">Setup School Year, Semester &amp; Curriculum</p>
                                </div>
                                <div class="h-5 w-0.5 bg-muted-foreground/30"></div>
                                <div class="flex h-12 w-36 items-center justify-center rotate-45 border-2 border-amber-500/50 bg-amber-50 dark:bg-amber-950/30">
                                    <p class="text-[11px] font-medium -rotate-45 text-amber-700 dark:text-amber-400">Enrollment Open?</p>
                                </div>
                                <div class="h-5 w-0.5 bg-muted-foreground/30"></div>
                                <div class="w-full max-w-xs rounded-lg border-2 border-purple-500/30 bg-purple-50 px-4 py-2 text-center dark:bg-purple-950/30">
                                    <p class="text-[10px] font-bold uppercase text-purple-500">Registrar</p>
                                    <p class="text-xs font-medium">Search / Create Student Record</p>
                                </div>
                                <div class="h-3 w-0.5 bg-muted-foreground/30"></div>
                                <div class="w-full max-w-xs rounded-lg border-2 border-purple-500/30 bg-purple-50 px-4 py-2 text-center dark:bg-purple-950/30">
                                    <p class="text-xs font-medium">Select Grade Level &amp; Strand</p>
                                </div>
                                <div class="h-3 w-0.5 bg-muted-foreground/30"></div>
                                <div class="w-full max-w-xs rounded-lg border-2 border-purple-500/30 bg-purple-50 px-4 py-2 text-center dark:bg-purple-950/30">
                                    <p class="text-xs font-medium">Auto-Load Subjects &rarr; Assign Section</p>
                                </div>
                                <div class="h-3 w-0.5 bg-muted-foreground/30"></div>
                                <div class="w-full max-w-xs rounded-lg border-2 border-purple-500/30 bg-purple-50 px-4 py-2 text-center dark:bg-purple-950/30">
                                    <p class="text-xs font-medium">Confirm Enrollment</p>
                                </div>
                                <div class="h-5 w-0.5 bg-muted-foreground/30"></div>
                                <div class="flex flex-wrap items-center justify-center gap-2">
                                    <div class="rounded-lg border-2 border-amber-500/30 bg-amber-50 px-4 py-2 text-center dark:bg-amber-950/30">
                                        <p class="text-[10px] font-bold uppercase text-amber-600">Teacher</p>
                                        <p class="text-xs font-medium">Enter Midterm</p>
                                    </div>
                                    <span class="text-muted-foreground">&rarr;</span>
                                    <div class="rounded-lg border-2 border-amber-500/30 bg-amber-50 px-4 py-2 text-center dark:bg-amber-950/30">
                                        <p class="text-xs font-medium">Enter Finals</p>
                                    </div>
                                    <span class="text-muted-foreground">&rarr;</span>
                                    <div class="rounded-lg border-2 border-amber-500/30 bg-amber-50 px-4 py-2 text-center dark:bg-amber-950/30">
                                        <p class="text-xs font-medium">Lock Grades</p>
                                    </div>
                                </div>
                                <div class="h-5 w-0.5 bg-muted-foreground/30"></div>
                                <div class="w-full max-w-xs rounded-lg border-2 border-purple-500/30 bg-purple-50 px-4 py-2 text-center dark:bg-purple-950/30">
                                    <p class="text-[10px] font-bold uppercase text-purple-500">Registrar</p>
                                    <p class="text-xs font-medium">Generate Reports (SF1, SF5, SF9, SF10)</p>
                                </div>
                                <div class="h-5 w-0.5 bg-muted-foreground/30"></div>
                                <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-zinc-900 dark:border-zinc-100"><div class="h-6 w-6 rounded-full bg-zinc-900 dark:bg-zinc-100"></div></div>
                            </div>
                            <p class="mt-4 text-center text-xs italic text-muted-foreground">Figure 3.2 &mdash; High-Level Activity Flow</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- ============================================================ -->
                <!-- 3.2 SDLC MODEL DIAGRAM -->
                <!-- ============================================================ -->
                <Card id="section-3-2" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle>3.2 Software Development Life Cycle (Iterative Model)</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div id="sdlc-diagram" class="relative overflow-x-auto rounded-lg border bg-white p-6 dark:bg-zinc-950">
                            <button @click="downloadDiagram('sdlc-diagram', 'SDLC-Model.png')" class="absolute right-2 top-2 z-10 inline-flex items-center gap-1.5 rounded-md border bg-white px-2.5 py-1 text-xs font-medium text-muted-foreground shadow-sm transition-colors hover:bg-accent hover:text-foreground dark:bg-zinc-900" title="Download as PNG"><Download class="h-3.5 w-3.5" /> Download</button>
                            <svg viewBox="0 0 760 260" class="mx-auto w-full max-w-3xl" xmlns="http://www.w3.org/2000/svg">
                                <defs><marker id="a2" markerWidth="8" markerHeight="6" refX="8" refY="3" orient="auto"><polygon points="0 0, 8 3, 0 6" fill="#94a3b8"/></marker></defs>

                                <rect x="10" y="80" width="105" height="65" rx="8" fill="#3b82f6" fill-opacity="0.1" stroke="#3b82f6" stroke-width="1.5"/>
                                <text x="62" y="107" text-anchor="middle" style="font-size:12px;font-weight:700;fill:#3b82f6">Planning</text>
                                <text x="62" y="125" text-anchor="middle" style="font-size:9px;fill:#64748b">Requirements</text>

                                <line x1="115" y1="112" x2="138" y2="112" stroke="#94a3b8" stroke-width="1.5" marker-end="url(#a2)"/>

                                <rect x="140" y="80" width="105" height="65" rx="8" fill="#8b5cf6" fill-opacity="0.1" stroke="#8b5cf6" stroke-width="1.5"/>
                                <text x="192" y="107" text-anchor="middle" style="font-size:12px;font-weight:700;fill:#8b5cf6">Analysis</text>
                                <text x="192" y="125" text-anchor="middle" style="font-size:9px;fill:#64748b">Data modeling</text>

                                <line x1="245" y1="112" x2="268" y2="112" stroke="#94a3b8" stroke-width="1.5" marker-end="url(#a2)"/>

                                <rect x="270" y="80" width="105" height="65" rx="8" fill="#f59e0b" fill-opacity="0.1" stroke="#f59e0b" stroke-width="1.5"/>
                                <text x="322" y="107" text-anchor="middle" style="font-size:12px;font-weight:700;fill:#f59e0b">Design</text>
                                <text x="322" y="125" text-anchor="middle" style="font-size:9px;fill:#64748b">Architecture, ERD</text>

                                <line x1="375" y1="112" x2="398" y2="112" stroke="#94a3b8" stroke-width="1.5" marker-end="url(#a2)"/>

                                <rect x="400" y="80" width="105" height="65" rx="8" fill="#10b981" fill-opacity="0.1" stroke="#10b981" stroke-width="1.5"/>
                                <text x="452" y="107" text-anchor="middle" style="font-size:12px;font-weight:700;fill:#10b981">Development</text>
                                <text x="452" y="125" text-anchor="middle" style="font-size:9px;fill:#64748b">Coding</text>

                                <line x1="505" y1="112" x2="528" y2="112" stroke="#94a3b8" stroke-width="1.5" marker-end="url(#a2)"/>

                                <rect x="530" y="80" width="105" height="65" rx="8" fill="#ef4444" fill-opacity="0.1" stroke="#ef4444" stroke-width="1.5"/>
                                <text x="582" y="107" text-anchor="middle" style="font-size:12px;font-weight:700;fill:#ef4444">Testing</text>
                                <text x="582" y="125" text-anchor="middle" style="font-size:9px;fill:#64748b">UAT</text>

                                <line x1="635" y1="112" x2="648" y2="112" stroke="#94a3b8" stroke-width="1.5" marker-end="url(#a2)"/>

                                <rect x="650" y="80" width="100" height="65" rx="8" fill="#06b6d4" fill-opacity="0.1" stroke="#06b6d4" stroke-width="1.5"/>
                                <text x="700" y="107" text-anchor="middle" style="font-size:12px;font-weight:700;fill:#06b6d4">Deploy</text>
                                <text x="700" y="125" text-anchor="middle" style="font-size:9px;fill:#64748b">Production</text>

                                <!-- Iteration curve -->
                                <path d="M 700 150 Q 700 220 380 220 Q 62 220 62 150" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-dasharray="5 3" marker-end="url(#a2)"/>
                                <text x="380" y="212" text-anchor="middle" style="font-size:10px;font-style:italic;fill:#94a3b8">Iterate: Feedback &amp; Refinement</text>

                                <!-- Iteration labels top -->
                                <rect x="200" y="10" width="360" height="55" rx="8" fill="none" stroke="#e2e8f0" stroke-width="1" stroke-dasharray="4 2"/>
                                <text x="380" y="28" text-anchor="middle" style="font-size:10px;font-weight:600;fill:#64748b">Iterations</text>
                                <text x="380" y="42" text-anchor="middle" style="font-size:9px;fill:#94a3b8">1: Core Enrollment &amp; Student Records</text>
                                <text x="380" y="55" text-anchor="middle" style="font-size:9px;fill:#94a3b8">2: Grade Management &amp; Reports &nbsp;|&nbsp; 3: Analytics &amp; Graph Modeling</text>
                            </svg>
                            <p class="mt-2 text-center text-xs italic text-muted-foreground">Figure 3.3 &mdash; Iterative SDLC Model</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- ============================================================ -->
                <!-- 3.3 SYSTEM ARCHITECTURE -->
                <!-- ============================================================ -->
                <Card id="section-3-3" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle>3.3 System Architecture</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-6">

                        <!-- Layered Architecture -->
                        <p class="text-sm font-semibold">Layered Architecture Diagram</p>
                        <div class="overflow-hidden rounded-lg border bg-white dark:bg-zinc-950">
                            <div class="flex flex-col">
                                <!-- Presentation Layer -->
                                <div class="border-b bg-blue-50 px-6 py-4 dark:bg-blue-950/20">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <Badge variant="default" class="mb-1">Presentation Layer</Badge>
                                            <p class="text-xs text-muted-foreground">User Interface &amp; Client-Side Logic</p>
                                        </div>
                                        <div class="flex flex-wrap gap-2 text-xs">
                                            <span class="rounded bg-blue-100 px-2 py-0.5 font-mono dark:bg-blue-900/50">Vue.js 3</span>
                                            <span class="rounded bg-blue-100 px-2 py-0.5 font-mono dark:bg-blue-900/50">Inertia.js</span>
                                            <span class="rounded bg-blue-100 px-2 py-0.5 font-mono dark:bg-blue-900/50">Tailwind CSS</span>
                                            <span class="rounded bg-blue-100 px-2 py-0.5 font-mono dark:bg-blue-900/50">Reka UI</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-center py-1 text-xs text-muted-foreground">&darr; HTTP / Inertia Protocol &darr;</div>
                                <!-- Application Layer -->
                                <div class="border-b border-t bg-purple-50 px-6 py-4 dark:bg-purple-950/20">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <Badge variant="secondary" class="mb-1 bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300">Application Layer</Badge>
                                            <p class="text-xs text-muted-foreground">Business Logic, Controllers, Middleware</p>
                                        </div>
                                        <div class="flex flex-wrap gap-2 text-xs">
                                            <span class="rounded bg-purple-100 px-2 py-0.5 font-mono dark:bg-purple-900/50">Laravel 11</span>
                                            <span class="rounded bg-purple-100 px-2 py-0.5 font-mono dark:bg-purple-900/50">Eloquent ORM</span>
                                            <span class="rounded bg-purple-100 px-2 py-0.5 font-mono dark:bg-purple-900/50">Auth Middleware</span>
                                            <span class="rounded bg-purple-100 px-2 py-0.5 font-mono dark:bg-purple-900/50">Role RBAC</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-center py-1 text-xs text-muted-foreground">&darr; Eloquent / Query Builder &darr;</div>
                                <!-- Data Layer -->
                                <div class="bg-green-50 px-6 py-4 dark:bg-green-950/20">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <Badge variant="secondary" class="mb-1 bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300">Data Layer</Badge>
                                            <p class="text-xs text-muted-foreground">Database Storage &amp; Graph Engine</p>
                                        </div>
                                        <div class="flex flex-wrap gap-2 text-xs">
                                            <span class="rounded bg-green-100 px-2 py-0.5 font-mono dark:bg-green-900/50">MySQL 8</span>
                                            <span class="rounded bg-green-100 px-2 py-0.5 font-mono dark:bg-green-900/50">ONgDB (Graph)</span>
                                            <span class="rounded bg-green-100 px-2 py-0.5 font-mono dark:bg-green-900/50">File Storage</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p class="border-t bg-muted/30 py-2 text-center text-xs italic text-muted-foreground">Figure 3.4 &mdash; Layered Architecture Diagram</p>
                        </div>

                        <Separator />

                        <!-- Deployment Diagram -->
                        <p class="text-sm font-semibold">Deployment Diagram</p>
                        <div id="deployment-diagram" class="relative overflow-x-auto rounded-lg border bg-white p-6 dark:bg-zinc-950">
                            <button @click="downloadDiagram('deployment-diagram', 'Deployment-Diagram.png')" class="absolute right-2 top-2 z-10 inline-flex items-center gap-1.5 rounded-md border bg-white px-2.5 py-1 text-xs font-medium text-muted-foreground shadow-sm transition-colors hover:bg-accent hover:text-foreground dark:bg-zinc-900" title="Download as PNG"><Download class="h-3.5 w-3.5" /> Download</button>
                            <svg viewBox="0 0 750 320" class="mx-auto w-full max-w-3xl" xmlns="http://www.w3.org/2000/svg">
                                <defs><marker id="a3" markerWidth="8" markerHeight="6" refX="8" refY="3" orient="auto"><polygon points="0 0, 8 3, 0 6" fill="#94a3b8"/></marker></defs>

                                <!-- Client -->
                                <rect x="10" y="40" width="160" height="110" rx="10" fill="#3b82f6" fill-opacity="0.06" stroke="#3b82f6" stroke-width="1.5"/>
                                <text x="90" y="65" text-anchor="middle" style="font-size:12px;font-weight:700;fill:#3b82f6">Client Device</text>
                                <rect x="30" y="78" width="120" height="24" rx="4" fill="#dbeafe" stroke="#93c5fd" stroke-width="1"/>
                                <text x="90" y="94" text-anchor="middle" style="font-size:10px;fill:#3b82f6">Web Browser</text>
                                <rect x="30" y="108" width="120" height="24" rx="4" fill="#dbeafe" stroke="#93c5fd" stroke-width="1"/>
                                <text x="90" y="124" text-anchor="middle" style="font-size:10px;fill:#3b82f6">Vue.js SPA</text>

                                <!-- Arrow to server -->
                                <line x1="170" y1="95" x2="255" y2="95" stroke="#94a3b8" stroke-width="1.5" marker-end="url(#a3)"/>
                                <text x="212" y="88" text-anchor="middle" style="font-size:9px;fill:#94a3b8">HTTPS</text>

                                <!-- Web Server -->
                                <rect x="260" y="10" width="220" height="200" rx="10" fill="#8b5cf6" fill-opacity="0.06" stroke="#8b5cf6" stroke-width="1.5"/>
                                <text x="370" y="35" text-anchor="middle" style="font-size:12px;font-weight:700;fill:#8b5cf6">DigitalOcean Server</text>
                                <text x="370" y="48" text-anchor="middle" style="font-size:8px;fill:#94a3b8">209.97.175.112</text>

                                <rect x="280" y="58" width="180" height="24" rx="4" fill="#ede9fe" stroke="#c4b5fd" stroke-width="1"/>
                                <text x="370" y="74" text-anchor="middle" style="font-size:10px;fill:#7c3aed">Nginx (Reverse Proxy)</text>

                                <rect x="280" y="90" width="180" height="24" rx="4" fill="#ede9fe" stroke="#c4b5fd" stroke-width="1"/>
                                <text x="370" y="106" text-anchor="middle" style="font-size:10px;fill:#7c3aed">PHP 8.4-FPM</text>

                                <rect x="280" y="122" width="180" height="24" rx="4" fill="#ede9fe" stroke="#c4b5fd" stroke-width="1"/>
                                <text x="370" y="138" text-anchor="middle" style="font-size:10px;fill:#7c3aed">Laravel 11 Application</text>

                                <rect x="280" y="154" width="85" height="24" rx="4" fill="#ede9fe" stroke="#c4b5fd" stroke-width="1"/>
                                <text x="322" y="170" text-anchor="middle" style="font-size:10px;fill:#7c3aed">Supervisor</text>

                                <rect x="375" y="154" width="85" height="24" rx="4" fill="#ede9fe" stroke="#c4b5fd" stroke-width="1"/>
                                <text x="417" y="170" text-anchor="middle" style="font-size:10px;fill:#7c3aed">Queue Worker</text>

                                <!-- Arrow to DB -->
                                <line x1="370" y1="215" x2="370" y2="248" stroke="#94a3b8" stroke-width="1.5" marker-end="url(#a3)"/>
                                <text x="390" y="237" text-anchor="start" style="font-size:9px;fill:#94a3b8">TCP 3306</text>

                                <!-- Database -->
                                <rect x="260" y="250" width="110" height="55" rx="10" fill="#10b981" fill-opacity="0.06" stroke="#10b981" stroke-width="1.5"/>
                                <text x="315" y="275" text-anchor="middle" style="font-size:11px;font-weight:700;fill:#10b981">MySQL 8</text>
                                <text x="315" y="292" text-anchor="middle" style="font-size:9px;fill:#64748b">enrollment DB</text>

                                <!-- Graph DB -->
                                <rect x="390" y="250" width="110" height="55" rx="10" fill="#f59e0b" fill-opacity="0.06" stroke="#f59e0b" stroke-width="1.5"/>
                                <text x="445" y="275" text-anchor="middle" style="font-size:11px;font-weight:700;fill:#f59e0b">ONgDB</text>
                                <text x="445" y="292" text-anchor="middle" style="font-size:9px;fill:#64748b">Graph Engine</text>

                                <!-- File Storage -->
                                <rect x="560" y="60" width="160" height="80" rx="10" fill="#06b6d4" fill-opacity="0.06" stroke="#06b6d4" stroke-width="1.5"/>
                                <text x="640" y="85" text-anchor="middle" style="font-size:11px;font-weight:700;fill:#06b6d4">Storage</text>
                                <text x="640" y="102" text-anchor="middle" style="font-size:9px;fill:#64748b">CSV Imports</text>
                                <text x="640" y="115" text-anchor="middle" style="font-size:9px;fill:#64748b">Generated PDFs</text>
                                <text x="640" y="128" text-anchor="middle" style="font-size:9px;fill:#64748b">Report Files</text>

                                <line x1="480" y1="100" x2="558" y2="100" stroke="#94a3b8" stroke-width="1.5" marker-end="url(#a3)"/>
                            </svg>
                            <p class="mt-2 text-center text-xs italic text-muted-foreground">Figure 3.5 &mdash; Physical Deployment Diagram</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- ============================================================ -->
                <!-- 3.4 SEQUENCE DIAGRAM -->
                <!-- ============================================================ -->
                <Card id="section-3-4" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle>3.4 System Design Specifications</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <p class="text-sm font-semibold">Sequence Diagram &mdash; Enrollment Process</p>
                        <div id="sequence-diagram" class="relative overflow-x-auto rounded-lg border bg-white p-4 dark:bg-zinc-950">
                            <button @click="downloadDiagram('sequence-diagram', 'Sequence-Diagram.png')" class="absolute right-2 top-2 z-10 inline-flex items-center gap-1.5 rounded-md border bg-white px-2.5 py-1 text-xs font-medium text-muted-foreground shadow-sm transition-colors hover:bg-accent hover:text-foreground dark:bg-zinc-900" title="Download as PNG"><Download class="h-3.5 w-3.5" /> Download</button>
                            <svg viewBox="0 0 700 440" class="mx-auto w-full max-w-2xl" xmlns="http://www.w3.org/2000/svg">
                                <defs><marker id="a4" markerWidth="8" markerHeight="6" refX="8" refY="3" orient="auto"><polygon points="0 0, 8 3, 0 6" fill="#64748b"/></marker><marker id="a4r" markerWidth="8" markerHeight="6" refX="0" refY="3" orient="auto"><polygon points="8 0, 0 3, 8 6" fill="#64748b"/></marker></defs>

                                <!-- Lifelines -->
                                <rect x="30" y="10" width="90" height="30" rx="6" fill="#8b5cf6" fill-opacity="0.15" stroke="#8b5cf6" stroke-width="1.5"/>
                                <text x="75" y="30" text-anchor="middle" style="font-size:11px;font-weight:700;fill:#8b5cf6">Registrar</text>
                                <line x1="75" y1="40" x2="75" y2="420" stroke="#8b5cf6" stroke-width="1" stroke-dasharray="4 3"/>

                                <rect x="195" y="10" width="90" height="30" rx="6" fill="#3b82f6" fill-opacity="0.15" stroke="#3b82f6" stroke-width="1.5"/>
                                <text x="240" y="30" text-anchor="middle" style="font-size:11px;font-weight:700;fill:#3b82f6">Frontend</text>
                                <line x1="240" y1="40" x2="240" y2="420" stroke="#3b82f6" stroke-width="1" stroke-dasharray="4 3"/>

                                <rect x="370" y="10" width="90" height="30" rx="6" fill="#10b981" fill-opacity="0.15" stroke="#10b981" stroke-width="1.5"/>
                                <text x="415" y="30" text-anchor="middle" style="font-size:11px;font-weight:700;fill:#10b981">Controller</text>
                                <line x1="415" y1="40" x2="415" y2="420" stroke="#10b981" stroke-width="1" stroke-dasharray="4 3"/>

                                <rect x="555" y="10" width="90" height="30" rx="6" fill="#f59e0b" fill-opacity="0.15" stroke="#f59e0b" stroke-width="1.5"/>
                                <text x="600" y="30" text-anchor="middle" style="font-size:11px;font-weight:700;fill:#f59e0b">Database</text>
                                <line x1="600" y1="40" x2="600" y2="420" stroke="#f59e0b" stroke-width="1" stroke-dasharray="4 3"/>

                                <!-- Messages -->
                                <!-- 1 -->
                                <line x1="78" y1="70" x2="237" y2="70" stroke="#64748b" stroke-width="1.2" marker-end="url(#a4)"/>
                                <text x="155" y="64" text-anchor="middle" style="font-size:9px;fill:#64748b">1. Search student</text>

                                <!-- 2 -->
                                <line x1="243" y1="100" x2="412" y2="100" stroke="#64748b" stroke-width="1.2" marker-end="url(#a4)"/>
                                <text x="325" y="94" text-anchor="middle" style="font-size:9px;fill:#64748b">2. GET /api/students/search</text>

                                <!-- 3 -->
                                <line x1="418" y1="130" x2="597" y2="130" stroke="#64748b" stroke-width="1.2" marker-end="url(#a4)"/>
                                <text x="505" y="124" text-anchor="middle" style="font-size:9px;fill:#64748b">3. SELECT * FROM students</text>

                                <!-- 4 return -->
                                <line x1="597" y1="155" x2="243" y2="155" stroke="#64748b" stroke-width="1.2" stroke-dasharray="4 2" marker-end="url(#a4r)"/>
                                <text x="400" y="149" text-anchor="middle" style="font-size:9px;fill:#64748b">4. Student record(s)</text>

                                <!-- 5 -->
                                <line x1="78" y1="190" x2="237" y2="190" stroke="#64748b" stroke-width="1.2" marker-end="url(#a4)"/>
                                <text x="155" y="184" text-anchor="middle" style="font-size:9px;fill:#64748b">5. Select grade/strand</text>

                                <!-- 6 -->
                                <line x1="243" y1="220" x2="412" y2="220" stroke="#64748b" stroke-width="1.2" marker-end="url(#a4)"/>
                                <text x="325" y="214" text-anchor="middle" style="font-size:9px;fill:#64748b">6. GET /api/enrollment/subjects</text>

                                <!-- 7 return -->
                                <line x1="412" y1="245" x2="243" y2="245" stroke="#64748b" stroke-width="1.2" stroke-dasharray="4 2" marker-end="url(#a4r)"/>
                                <text x="325" y="239" text-anchor="middle" style="font-size:9px;fill:#64748b">7. Subject list + sections</text>

                                <!-- 8 -->
                                <line x1="78" y1="280" x2="237" y2="280" stroke="#64748b" stroke-width="1.2" marker-end="url(#a4)"/>
                                <text x="155" y="274" text-anchor="middle" style="font-size:9px;fill:#64748b">8. Confirm enrollment</text>

                                <!-- 9 -->
                                <line x1="243" y1="310" x2="412" y2="310" stroke="#64748b" stroke-width="1.2" marker-end="url(#a4)"/>
                                <text x="325" y="304" text-anchor="middle" style="font-size:9px;fill:#64748b">9. POST /enrollment</text>

                                <!-- 10 -->
                                <line x1="418" y1="340" x2="597" y2="340" stroke="#64748b" stroke-width="1.2" marker-end="url(#a4)"/>
                                <text x="505" y="334" text-anchor="middle" style="font-size:9px;fill:#64748b">10. INSERT enrollment + subjects</text>

                                <!-- 11 return -->
                                <line x1="597" y1="370" x2="243" y2="370" stroke="#64748b" stroke-width="1.2" stroke-dasharray="4 2" marker-end="url(#a4r)"/>
                                <text x="400" y="364" text-anchor="middle" style="font-size:9px;fill:#64748b">11. Enrollment confirmed</text>

                                <!-- 12 -->
                                <line x1="237" y1="400" x2="78" y2="400" stroke="#64748b" stroke-width="1.2" stroke-dasharray="4 2" marker-end="url(#a4r)"/>
                                <text x="155" y="394" text-anchor="middle" style="font-size:9px;fill:#64748b">12. Show success + slip</text>
                            </svg>
                            <p class="mt-2 text-center text-xs italic text-muted-foreground">Figure 3.6 &mdash; Sequence Diagram: Enrollment Process</p>
                        </div>

                        <Separator />

                        <!-- Module Specification -->
                        <p class="text-sm font-semibold">Module Specifications</p>
                        <div class="overflow-x-auto rounded-lg border">
                            <table class="w-full text-sm">
                                <thead><tr class="border-b bg-muted/50"><th class="px-4 py-2 text-left font-medium">Module</th><th class="px-4 py-2 text-left font-medium">Inputs</th><th class="px-4 py-2 text-left font-medium">Outputs</th></tr></thead>
                                <tbody class="text-muted-foreground">
                                    <tr class="border-b"><td class="px-4 py-2 font-medium text-foreground">Enrollment</td><td class="px-4 py-2">Student info, grade level, strand</td><td class="px-4 py-2">Enrollment record, subject load, section</td></tr>
                                    <tr class="border-b"><td class="px-4 py-2 font-medium text-foreground">Student Records</td><td class="px-4 py-2">Personal data, LRN, guardian info</td><td class="px-4 py-2">Student profile, academic history</td></tr>
                                    <tr class="border-b"><td class="px-4 py-2 font-medium text-foreground">Grade Management</td><td class="px-4 py-2">Midterm/finals scores</td><td class="px-4 py-2">Computed grades, pass/fail</td></tr>
                                    <tr class="border-b"><td class="px-4 py-2 font-medium text-foreground">Reports</td><td class="px-4 py-2">Section/student, form type</td><td class="px-4 py-2">SF1, SF5, SF9, SF10, CSV</td></tr>
                                    <tr class="border-b"><td class="px-4 py-2 font-medium text-foreground">Curriculum</td><td class="px-4 py-2">Track, strand, subject data</td><td class="px-4 py-2">Subject loading rules</td></tr>
                                    <tr><td class="px-4 py-2 font-medium text-foreground">User Management</td><td class="px-4 py-2">Credentials, role</td><td class="px-4 py-2">Access control, auth token</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                <!-- ============================================================ -->
                <!-- 3.5 ERD -->
                <!-- ============================================================ -->
                <Card id="section-3-5" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle>3.5 Entity-Relationship Diagram (ERD)</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div id="erd-diagram" class="relative overflow-x-auto rounded-lg border bg-white p-4 dark:bg-zinc-950">
                            <button @click="downloadDiagram('erd-diagram', 'ERD-Diagram.png')" class="absolute right-2 top-2 z-10 inline-flex items-center gap-1.5 rounded-md border bg-white px-2.5 py-1 text-xs font-medium text-muted-foreground shadow-sm transition-colors hover:bg-accent hover:text-foreground dark:bg-zinc-900" title="Download as PNG">
                                <Download class="h-3.5 w-3.5" /> Download
                            </button>
                            <svg viewBox="0 0 920 500" class="mx-auto w-full max-w-5xl" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <marker id="erd-crow" markerWidth="12" markerHeight="10" refX="12" refY="5" orient="auto">
                                        <line x1="0" y1="0" x2="12" y2="5" stroke="#94a3b8" stroke-width="1.5"/>
                                        <line x1="0" y1="10" x2="12" y2="5" stroke="#94a3b8" stroke-width="1.5"/>
                                        <line x1="0" y1="5" x2="12" y2="5" stroke="#94a3b8" stroke-width="1.5"/>
                                    </marker>
                                </defs>

                                <!-- ===== COLUMN 1: People ===== -->

                                <!-- users -->
                                <rect x="30" y="10" width="195" height="118" rx="6" fill="#3b82f6" fill-opacity="0.05" stroke="#3b82f6" stroke-width="1.5"/>
                                <rect x="30" y="10" width="195" height="28" rx="6" fill="#3b82f6" fill-opacity="0.15"/>
                                <text x="128" y="29" text-anchor="middle" style="font-size:12px;font-weight:700;fill:#3b82f6">users</text>
                                <text x="40" y="53" style="font-size:9px;fill:#64748b">PK  id BIGINT</text>
                                <text x="40" y="68" style="font-size:9px;fill:#64748b">    name VARCHAR</text>
                                <text x="40" y="83" style="font-size:9px;fill:#64748b">    email VARCHAR (UNIQUE)</text>
                                <text x="40" y="98" style="font-size:9px;fill:#64748b">    password VARCHAR</text>
                                <text x="40" y="113" style="font-size:9px;fill:#64748b">    is_active BOOLEAN</text>

                                <!-- students -->
                                <rect x="30" y="155" width="195" height="148" rx="6" fill="#10b981" fill-opacity="0.05" stroke="#10b981" stroke-width="1.5"/>
                                <rect x="30" y="155" width="195" height="28" rx="6" fill="#10b981" fill-opacity="0.15"/>
                                <text x="128" y="174" text-anchor="middle" style="font-size:12px;font-weight:700;fill:#10b981">students</text>
                                <text x="40" y="198" style="font-size:9px;fill:#64748b">PK  id BIGINT</text>
                                <text x="40" y="213" style="font-size:9px;fill:#64748b">FK  user_id BIGINT</text>
                                <text x="40" y="228" style="font-size:9px;fill:#64748b">    lrn VARCHAR(12) UNIQUE</text>
                                <text x="40" y="243" style="font-size:9px;fill:#64748b">    last_name VARCHAR</text>
                                <text x="40" y="258" style="font-size:9px;fill:#64748b">    first_name VARCHAR</text>
                                <text x="40" y="273" style="font-size:9px;fill:#64748b">    middle_name VARCHAR</text>
                                <text x="40" y="288" style="font-size:9px;fill:#64748b">    birthdate DATE</text>
                                <text x="40" y="303" style="font-size:9px;fill:#64748b">    gender VARCHAR</text>

                                <!-- teacher_profiles -->
                                <rect x="30" y="330" width="195" height="118" rx="6" fill="#f97316" fill-opacity="0.05" stroke="#f97316" stroke-width="1.5"/>
                                <rect x="30" y="330" width="195" height="28" rx="6" fill="#f97316" fill-opacity="0.15"/>
                                <text x="128" y="349" text-anchor="middle" style="font-size:12px;font-weight:700;fill:#f97316">teacher_profiles</text>
                                <text x="40" y="373" style="font-size:9px;fill:#64748b">PK  id BIGINT</text>
                                <text x="40" y="388" style="font-size:9px;fill:#64748b">FK  user_id BIGINT</text>
                                <text x="40" y="403" style="font-size:9px;fill:#64748b">    employee_id VARCHAR(20)</text>
                                <text x="40" y="418" style="font-size:9px;fill:#64748b">    position_title VARCHAR</text>
                                <text x="40" y="433" style="font-size:9px;fill:#64748b">    specialization VARCHAR</text>
                                <text x="40" y="448" style="font-size:9px;fill:#64748b">    date_hired DATE</text>

                                <!-- ===== COLUMN 2: Academic Structure ===== -->

                                <!-- school_years -->
                                <rect x="255" y="10" width="195" height="73" rx="6" fill="#6366f1" fill-opacity="0.05" stroke="#6366f1" stroke-width="1.5"/>
                                <rect x="255" y="10" width="195" height="28" rx="6" fill="#6366f1" fill-opacity="0.15"/>
                                <text x="353" y="29" text-anchor="middle" style="font-size:12px;font-weight:700;fill:#6366f1">school_years</text>
                                <text x="265" y="53" style="font-size:9px;fill:#64748b">PK  id BIGINT</text>
                                <text x="265" y="68" style="font-size:9px;fill:#64748b">    name VARCHAR (UNIQUE)</text>
                                <text x="265" y="83" style="font-size:9px;fill:#64748b">    is_active BOOLEAN</text>

                                <!-- semesters -->
                                <rect x="255" y="110" width="195" height="103" rx="6" fill="#6366f1" fill-opacity="0.05" stroke="#6366f1" stroke-width="1.5"/>
                                <rect x="255" y="110" width="195" height="28" rx="6" fill="#6366f1" fill-opacity="0.15"/>
                                <text x="353" y="129" text-anchor="middle" style="font-size:12px;font-weight:700;fill:#6366f1">semesters</text>
                                <text x="265" y="153" style="font-size:9px;fill:#64748b">PK  id BIGINT</text>
                                <text x="265" y="168" style="font-size:9px;fill:#64748b">FK  school_year_id BIGINT</text>
                                <text x="265" y="183" style="font-size:9px;fill:#64748b">    number TINYINT</text>
                                <text x="265" y="198" style="font-size:9px;fill:#64748b">    is_active BOOLEAN</text>
                                <text x="265" y="213" style="font-size:9px;fill:#64748b">    enrollment_open BOOLEAN</text>

                                <!-- tracks -->
                                <rect x="255" y="245" width="195" height="88" rx="6" fill="#ec4899" fill-opacity="0.05" stroke="#ec4899" stroke-width="1.5"/>
                                <rect x="255" y="245" width="195" height="28" rx="6" fill="#ec4899" fill-opacity="0.15"/>
                                <text x="353" y="264" text-anchor="middle" style="font-size:12px;font-weight:700;fill:#ec4899">tracks</text>
                                <text x="265" y="288" style="font-size:9px;fill:#64748b">PK  id BIGINT</text>
                                <text x="265" y="303" style="font-size:9px;fill:#64748b">    name VARCHAR</text>
                                <text x="265" y="318" style="font-size:9px;fill:#64748b">    code VARCHAR (UNIQUE)</text>
                                <text x="265" y="333" style="font-size:9px;fill:#64748b">    is_active BOOLEAN</text>

                                <!-- strands -->
                                <rect x="255" y="365" width="195" height="103" rx="6" fill="#ec4899" fill-opacity="0.05" stroke="#ec4899" stroke-width="1.5"/>
                                <rect x="255" y="365" width="195" height="28" rx="6" fill="#ec4899" fill-opacity="0.15"/>
                                <text x="353" y="384" text-anchor="middle" style="font-size:12px;font-weight:700;fill:#ec4899">strands</text>
                                <text x="265" y="408" style="font-size:9px;fill:#64748b">PK  id BIGINT</text>
                                <text x="265" y="423" style="font-size:9px;fill:#64748b">FK  track_id BIGINT</text>
                                <text x="265" y="438" style="font-size:9px;fill:#64748b">    name VARCHAR</text>
                                <text x="265" y="453" style="font-size:9px;fill:#64748b">    code VARCHAR (UNIQUE)</text>
                                <text x="265" y="468" style="font-size:9px;fill:#64748b">    is_active BOOLEAN</text>

                                <!-- ===== COLUMN 3: Operations ===== -->

                                <!-- sections -->
                                <rect x="480" y="10" width="195" height="133" rx="6" fill="#f59e0b" fill-opacity="0.05" stroke="#f59e0b" stroke-width="1.5"/>
                                <rect x="480" y="10" width="195" height="28" rx="6" fill="#f59e0b" fill-opacity="0.15"/>
                                <text x="578" y="29" text-anchor="middle" style="font-size:12px;font-weight:700;fill:#f59e0b">sections</text>
                                <text x="490" y="53" style="font-size:9px;fill:#64748b">PK  id BIGINT</text>
                                <text x="490" y="68" style="font-size:9px;fill:#64748b">    name VARCHAR</text>
                                <text x="490" y="83" style="font-size:9px;fill:#64748b">FK  strand_id BIGINT</text>
                                <text x="490" y="98" style="font-size:9px;fill:#64748b">FK  semester_id BIGINT</text>
                                <text x="490" y="113" style="font-size:9px;fill:#64748b">FK  adviser_id BIGINT</text>
                                <text x="490" y="128" style="font-size:9px;fill:#64748b">    grade_level TINYINT</text>
                                <text x="490" y="143" style="font-size:9px;fill:#64748b">    max_capacity INT</text>

                                <!-- enrollments -->
                                <rect x="480" y="175" width="195" height="133" rx="6" fill="#8b5cf6" fill-opacity="0.05" stroke="#8b5cf6" stroke-width="1.5"/>
                                <rect x="480" y="175" width="195" height="28" rx="6" fill="#8b5cf6" fill-opacity="0.15"/>
                                <text x="578" y="194" text-anchor="middle" style="font-size:12px;font-weight:700;fill:#8b5cf6">enrollments</text>
                                <text x="490" y="218" style="font-size:9px;fill:#64748b">PK  id BIGINT</text>
                                <text x="490" y="233" style="font-size:9px;fill:#64748b">FK  student_id BIGINT</text>
                                <text x="490" y="248" style="font-size:9px;fill:#64748b">FK  section_id BIGINT</text>
                                <text x="490" y="263" style="font-size:9px;fill:#64748b">FK  semester_id BIGINT</text>
                                <text x="490" y="278" style="font-size:9px;fill:#64748b">    status VARCHAR</text>
                                <text x="490" y="293" style="font-size:9px;fill:#64748b">    enrolled_at TIMESTAMP</text>
                                <text x="490" y="308" style="font-size:9px;fill:#64748b">    remarks TEXT</text>

                                <!-- grades -->
                                <rect x="480" y="340" width="195" height="148" rx="6" fill="#ef4444" fill-opacity="0.05" stroke="#ef4444" stroke-width="1.5"/>
                                <rect x="480" y="340" width="195" height="28" rx="6" fill="#ef4444" fill-opacity="0.15"/>
                                <text x="578" y="359" text-anchor="middle" style="font-size:12px;font-weight:700;fill:#ef4444">grades</text>
                                <text x="490" y="383" style="font-size:9px;fill:#64748b">PK  id BIGINT</text>
                                <text x="490" y="398" style="font-size:9px;fill:#64748b">FK  enrollment_id BIGINT</text>
                                <text x="490" y="413" style="font-size:9px;fill:#64748b">FK  subject_id BIGINT</text>
                                <text x="490" y="428" style="font-size:9px;fill:#64748b">    midterm DECIMAL(5,2)</text>
                                <text x="490" y="443" style="font-size:9px;fill:#64748b">    finals DECIMAL(5,2)</text>
                                <text x="490" y="458" style="font-size:9px;fill:#64748b">    final_grade DECIMAL(5,2)</text>
                                <text x="490" y="473" style="font-size:9px;fill:#64748b">    is_locked BOOLEAN</text>
                                <text x="490" y="488" style="font-size:9px;fill:#64748b">FK  encoded_by BIGINT</text>

                                <!-- ===== COLUMN 4: Curriculum ===== -->

                                <!-- subjects -->
                                <rect x="705" y="10" width="195" height="133" rx="6" fill="#06b6d4" fill-opacity="0.05" stroke="#06b6d4" stroke-width="1.5"/>
                                <rect x="705" y="10" width="195" height="28" rx="6" fill="#06b6d4" fill-opacity="0.15"/>
                                <text x="803" y="29" text-anchor="middle" style="font-size:12px;font-weight:700;fill:#06b6d4">subjects</text>
                                <text x="715" y="53" style="font-size:9px;fill:#64748b">PK  id BIGINT</text>
                                <text x="715" y="68" style="font-size:9px;fill:#64748b">    code VARCHAR (UNIQUE)</text>
                                <text x="715" y="83" style="font-size:9px;fill:#64748b">    name VARCHAR</text>
                                <text x="715" y="98" style="font-size:9px;fill:#64748b">    type VARCHAR</text>
                                <text x="715" y="113" style="font-size:9px;fill:#64748b">    hours INT</text>
                                <text x="715" y="128" style="font-size:9px;fill:#64748b">FK  prerequisite_id BIGINT</text>
                                <text x="715" y="143" style="font-size:9px;fill:#64748b">    is_active BOOLEAN</text>

                                <!-- subject_strand (pivot) -->
                                <rect x="705" y="175" width="195" height="118" rx="6" fill="#14b8a6" fill-opacity="0.05" stroke="#14b8a6" stroke-width="1.5"/>
                                <rect x="705" y="175" width="195" height="28" rx="6" fill="#14b8a6" fill-opacity="0.15"/>
                                <text x="803" y="194" text-anchor="middle" style="font-size:12px;font-weight:700;fill:#14b8a6">subject_strand</text>
                                <text x="715" y="218" style="font-size:9px;fill:#64748b">PK  id BIGINT</text>
                                <text x="715" y="233" style="font-size:9px;fill:#64748b">FK  subject_id BIGINT</text>
                                <text x="715" y="248" style="font-size:9px;fill:#64748b">FK  strand_id BIGINT</text>
                                <text x="715" y="263" style="font-size:9px;fill:#64748b">    grade_level TINYINT</text>
                                <text x="715" y="278" style="font-size:9px;fill:#64748b">    semester TINYINT</text>
                                <text x="715" y="293" style="font-size:9px;fill:#64748b">    sort_order INT</text>

                                <!-- ===== RELATIONSHIPS ===== -->

                                <!-- users 1:1 students -->
                                <line x1="128" y1="128" x2="128" y2="155" stroke="#94a3b8" stroke-width="1.5"/>
                                <text x="138" y="145" style="font-size:8px;fill:#94a3b8">1:1</text>

                                <!-- users 1:1 teacher_profiles -->
                                <path d="M 30,80 L 15,80 L 15,390 L 30,390" fill="none" stroke="#94a3b8" stroke-width="1.2"/>
                                <text x="5" y="240" style="font-size:8px;fill:#94a3b8">1:1</text>

                                <!-- school_years 1:N semesters -->
                                <line x1="353" y1="83" x2="353" y2="110" stroke="#94a3b8" stroke-width="1.5" marker-end="url(#erd-crow)"/>
                                <text x="360" y="100" style="font-size:8px;fill:#94a3b8">1:N</text>

                                <!-- tracks 1:N strands -->
                                <line x1="353" y1="333" x2="353" y2="365" stroke="#94a3b8" stroke-width="1.5" marker-end="url(#erd-crow)"/>
                                <text x="360" y="352" style="font-size:8px;fill:#94a3b8">1:N</text>

                                <!-- students 1:N enrollments -->
                                <line x1="225" y1="233" x2="480" y2="233" stroke="#94a3b8" stroke-width="1.5" marker-end="url(#erd-crow)"/>
                                <text x="340" y="226" style="font-size:8px;fill:#94a3b8">1:N</text>

                                <!-- sections 1:N enrollments -->
                                <line x1="578" y1="143" x2="578" y2="175" stroke="#94a3b8" stroke-width="1.5" marker-end="url(#erd-crow)"/>
                                <text x="585" y="162" style="font-size:8px;fill:#94a3b8">1:N</text>

                                <!-- enrollments 1:N grades -->
                                <line x1="578" y1="308" x2="578" y2="340" stroke="#94a3b8" stroke-width="1.5" marker-end="url(#erd-crow)"/>
                                <text x="585" y="327" style="font-size:8px;fill:#94a3b8">1:N</text>

                                <!-- semesters 1:N sections -->
                                <line x1="450" y1="160" x2="480" y2="98" stroke="#94a3b8" stroke-width="1.2" marker-end="url(#erd-crow)"/>
                                <text x="458" y="125" style="font-size:8px;fill:#94a3b8">1:N</text>

                                <!-- strands 1:N sections -->
                                <path d="M 450,400 L 468,400 L 468,83 L 480,83" fill="none" stroke="#94a3b8" stroke-width="1.2" marker-end="url(#erd-crow)"/>
                                <text x="458" y="240" style="font-size:8px;fill:#94a3b8">1:N</text>

                                <!-- subjects 1:N subject_strand -->
                                <line x1="803" y1="143" x2="803" y2="175" stroke="#94a3b8" stroke-width="1.5" marker-end="url(#erd-crow)"/>
                                <text x="810" y="162" style="font-size:8px;fill:#94a3b8">1:N</text>

                                <!-- subjects 1:N grades -->
                                <line x1="705" y1="100" x2="675" y2="413" stroke="#94a3b8" stroke-width="1.2" marker-end="url(#erd-crow)"/>
                                <text x="695" y="260" style="font-size:8px;fill:#94a3b8">1:N</text>

                                <!-- strands M:N subjects (via subject_strand) -->
                                <line x1="450" y1="440" x2="705" y2="248" stroke="#94a3b8" stroke-width="1" stroke-dasharray="4 3"/>
                                <text x="570" y="335" style="font-size:8px;fill:#94a3b8">M:N</text>
                            </svg>
                            <p class="mt-2 text-center text-xs italic text-muted-foreground">Figure 3.7 &mdash; Entity-Relationship Diagram (ERD) &mdash; 12 Tables</p>
                        </div>

                        <Separator />

                        <p class="text-sm font-semibold">Data Dictionary (Sample)</p>
                        <div class="overflow-x-auto rounded-lg border">
                            <table class="w-full text-sm">
                                <thead><tr class="border-b bg-muted/50"><th class="px-3 py-2 text-left font-medium">Table</th><th class="px-3 py-2 text-left font-medium">Field</th><th class="px-3 py-2 text-left font-medium">Type</th><th class="px-3 py-2 text-left font-medium">Key</th><th class="px-3 py-2 text-left font-medium">Description</th></tr></thead>
                                <tbody class="text-xs text-muted-foreground">
                                    <tr class="border-b"><td class="px-3 py-1.5 font-medium text-foreground" rowspan="4">students</td><td class="px-3 py-1.5 font-mono">id</td><td class="px-3 py-1.5">BIGINT</td><td class="px-3 py-1.5"><Badge variant="outline" class="text-[10px]">PK</Badge></td><td class="px-3 py-1.5">Auto-increment</td></tr>
                                    <tr class="border-b"><td class="px-3 py-1.5 font-mono">user_id</td><td class="px-3 py-1.5">BIGINT</td><td class="px-3 py-1.5"><Badge variant="outline" class="text-[10px]">FK</Badge></td><td class="px-3 py-1.5">Ref: users.id</td></tr>
                                    <tr class="border-b"><td class="px-3 py-1.5 font-mono">lrn</td><td class="px-3 py-1.5">VARCHAR(12)</td><td class="px-3 py-1.5"><Badge variant="outline" class="text-[10px]">UQ</Badge></td><td class="px-3 py-1.5">Learner Reference Number</td></tr>
                                    <tr class="border-b"><td class="px-3 py-1.5 font-mono">first_name</td><td class="px-3 py-1.5">VARCHAR(100)</td><td class="px-3 py-1.5"></td><td class="px-3 py-1.5">Student first name</td></tr>
                                    <tr class="border-b"><td class="px-3 py-1.5 font-medium text-foreground" rowspan="3">enrollments</td><td class="px-3 py-1.5 font-mono">id</td><td class="px-3 py-1.5">BIGINT</td><td class="px-3 py-1.5"><Badge variant="outline" class="text-[10px]">PK</Badge></td><td class="px-3 py-1.5">Auto-increment</td></tr>
                                    <tr class="border-b"><td class="px-3 py-1.5 font-mono">student_id</td><td class="px-3 py-1.5">BIGINT</td><td class="px-3 py-1.5"><Badge variant="outline" class="text-[10px]">FK</Badge></td><td class="px-3 py-1.5">Ref: students.id</td></tr>
                                    <tr class="border-b"><td class="px-3 py-1.5 font-mono">section_id</td><td class="px-3 py-1.5">BIGINT</td><td class="px-3 py-1.5"><Badge variant="outline" class="text-[10px]">FK</Badge></td><td class="px-3 py-1.5">Ref: sections.id</td></tr>
                                    <tr class="border-b"><td class="px-3 py-1.5 font-medium text-foreground" rowspan="3">grades</td><td class="px-3 py-1.5 font-mono">midterm</td><td class="px-3 py-1.5">DECIMAL(5,2)</td><td class="px-3 py-1.5"></td><td class="px-3 py-1.5">Midterm grade (0-100)</td></tr>
                                    <tr class="border-b"><td class="px-3 py-1.5 font-mono">finals</td><td class="px-3 py-1.5">DECIMAL(5,2)</td><td class="px-3 py-1.5"></td><td class="px-3 py-1.5">Finals grade (0-100)</td></tr>
                                    <tr><td class="px-3 py-1.5 font-mono">is_locked</td><td class="px-3 py-1.5">BOOLEAN</td><td class="px-3 py-1.5"></td><td class="px-3 py-1.5">Grade lock status</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                <!-- ============================================================ -->
                <!-- 3.6 GRAPH DATABASE MODEL -->
                <!-- ============================================================ -->
                <Card id="section-3-6" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle>3.6 Graph Database Model</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div id="graph-diagram" class="relative overflow-x-auto rounded-lg border bg-white p-6 dark:bg-zinc-950">
                            <button @click="downloadDiagram('graph-diagram', 'Graph-Database-Model.png')" class="absolute right-2 top-2 z-10 inline-flex items-center gap-1.5 rounded-md border bg-white px-2.5 py-1 text-xs font-medium text-muted-foreground shadow-sm transition-colors hover:bg-accent hover:text-foreground dark:bg-zinc-900" title="Download as PNG"><Download class="h-3.5 w-3.5" /> Download</button>
                            <svg viewBox="0 0 750 450" class="mx-auto w-full max-w-3xl" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <marker id="ga" markerWidth="10" markerHeight="7" refX="10" refY="3.5" orient="auto"><polygon points="0 0, 10 3.5, 0 7" fill="#94a3b8"/></marker>
                                </defs>

                                <!-- Student Node -->
                                <circle cx="120" cy="200" r="55" fill="#3b82f6" fill-opacity="0.1" stroke="#3b82f6" stroke-width="2"/>
                                <text x="120" y="193" text-anchor="middle" style="font-size:13px;font-weight:700;fill:#3b82f6">Student</text>
                                <text x="120" y="210" text-anchor="middle" style="font-size:8px;fill:#64748b">name, LRN</text>
                                <text x="120" y="222" text-anchor="middle" style="font-size:8px;fill:#64748b">grade_level, sex</text>

                                <!-- Section Node -->
                                <circle cx="370" cy="100" r="50" fill="#8b5cf6" fill-opacity="0.1" stroke="#8b5cf6" stroke-width="2"/>
                                <text x="370" y="95" text-anchor="middle" style="font-size:13px;font-weight:700;fill:#8b5cf6">Section</text>
                                <text x="370" y="112" text-anchor="middle" style="font-size:8px;fill:#64748b">name, capacity</text>

                                <!-- Strand Node -->
                                <circle cx="600" cy="100" r="50" fill="#ec4899" fill-opacity="0.1" stroke="#ec4899" stroke-width="2"/>
                                <text x="600" y="95" text-anchor="middle" style="font-size:13px;font-weight:700;fill:#ec4899">Strand</text>
                                <text x="600" y="112" text-anchor="middle" style="font-size:8px;fill:#64748b">name, code, track</text>

                                <!-- Subject Node -->
                                <circle cx="370" cy="310" r="50" fill="#10b981" fill-opacity="0.1" stroke="#10b981" stroke-width="2"/>
                                <text x="370" y="303" text-anchor="middle" style="font-size:13px;font-weight:700;fill:#10b981">Subject</text>
                                <text x="370" y="320" text-anchor="middle" style="font-size:8px;fill:#64748b">code, name, type</text>

                                <!-- Teacher Node -->
                                <circle cx="620" cy="310" r="50" fill="#f59e0b" fill-opacity="0.1" stroke="#f59e0b" stroke-width="2"/>
                                <text x="620" y="303" text-anchor="middle" style="font-size:13px;font-weight:700;fill:#f59e0b">Teacher</text>
                                <text x="620" y="320" text-anchor="middle" style="font-size:8px;fill:#64748b">name, specialization</text>

                                <!-- Semester Node -->
                                <circle cx="120" cy="400" r="45" fill="#64748b" fill-opacity="0.1" stroke="#64748b" stroke-width="2"/>
                                <text x="120" y="395" text-anchor="middle" style="font-size:13px;font-weight:700;fill:#64748b">Semester</text>
                                <text x="120" y="412" text-anchor="middle" style="font-size:8px;fill:#94a3b8">name, year</text>

                                <!-- Edges (Relationships) -->
                                <!-- Student -> ENROLLED_IN -> Section -->
                                <line x1="172" y1="175" x2="322" y2="118" stroke="#94a3b8" stroke-width="1.8" marker-end="url(#ga)"/>
                                <rect x="205" y="130" width="90" height="18" rx="3" fill="#f1f5f9" stroke="#cbd5e1" stroke-width="0.5"/>
                                <text x="250" y="143" text-anchor="middle" style="font-size:9px;font-weight:600;fill:#475569">ENROLLED_IN</text>

                                <!-- Section -> BELONGS_TO -> Strand -->
                                <line x1="420" y1="100" x2="548" y2="100" stroke="#94a3b8" stroke-width="1.8" marker-end="url(#ga)"/>
                                <rect x="448" y="84" width="86" height="18" rx="3" fill="#f1f5f9" stroke="#cbd5e1" stroke-width="0.5"/>
                                <text x="491" y="97" text-anchor="middle" style="font-size:9px;font-weight:600;fill:#475569">BELONGS_TO</text>

                                <!-- Student -> HAS_GRADE -> Subject -->
                                <line x1="165" y1="230" x2="322" y2="295" stroke="#94a3b8" stroke-width="1.8" marker-end="url(#ga)"/>
                                <rect x="198" y="253" width="82" height="18" rx="3" fill="#f1f5f9" stroke="#cbd5e1" stroke-width="0.5"/>
                                <text x="239" y="266" text-anchor="middle" style="font-size:9px;font-weight:600;fill:#475569">HAS_GRADE</text>
                                <text x="239" y="282" text-anchor="middle" style="font-size:7px;fill:#94a3b8">midterm, finals</text>

                                <!-- Teacher -> TEACHES -> Subject -->
                                <line x1="572" y1="310" x2="422" y2="310" stroke="#94a3b8" stroke-width="1.8" marker-end="url(#ga)"/>
                                <rect x="462" y="298" width="68" height="18" rx="3" fill="#f1f5f9" stroke="#cbd5e1" stroke-width="0.5"/>
                                <text x="496" y="311" text-anchor="middle" style="font-size:9px;font-weight:600;fill:#475569">TEACHES</text>

                                <!-- Subject -> PREREQUISITE_OF -> Subject (self-loop) -->
                                <path d="M 370 360 Q 420 420 370 420 Q 310 420 340 360" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-dasharray="4 2" marker-end="url(#ga)"/>
                                <text x="390" y="408" style="font-size:8px;font-weight:600;fill:#475569">PREREQUISITE_OF</text>

                                <!-- Student -> DURING -> Semester -->
                                <line x1="120" y1="255" x2="120" y2="352" stroke="#94a3b8" stroke-width="1.8" marker-end="url(#ga)"/>
                                <rect x="80" y="295" width="58" height="18" rx="3" fill="#f1f5f9" stroke="#cbd5e1" stroke-width="0.5"/>
                                <text x="109" y="308" text-anchor="middle" style="font-size:9px;font-weight:600;fill:#475569">DURING</text>

                                <!-- Subject -> UNDER_CURRICULUM -> Strand -->
                                <line x1="405" y1="272" x2="570" y2="130" stroke="#94a3b8" stroke-width="1.5" stroke-dasharray="4 2" marker-end="url(#ga)"/>
                                <text x="520" y="185" text-anchor="middle" style="font-size:8px;font-weight:600;fill:#475569">UNDER_CURRICULUM</text>
                            </svg>
                            <p class="mt-2 text-center text-xs italic text-muted-foreground">Figure 3.8 &mdash; Graph Database Model (Nodes &amp; Relationships)</p>
                        </div>

                    </CardContent>
                </Card>

                <!-- ============================================================ -->
                <!-- 3.7 ALGORITHM FLOWCHART -->
                <!-- ============================================================ -->
                <Card id="section-3-7" class="scroll-mt-20">
                    <CardHeader>
                        <CardTitle>3.7 Algorithms &amp; Analytics</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <p class="text-sm font-semibold">Enrollment Validation Algorithm Flowchart</p>
                        <div class="overflow-x-auto rounded-lg border bg-white p-6 dark:bg-zinc-950">
                            <div class="mx-auto flex max-w-sm flex-col items-center gap-0">
                                <!-- Start -->
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-900 dark:bg-zinc-100"><div class="h-4 w-4 rounded-full bg-white dark:bg-zinc-900"></div></div>
                                <div class="h-4 w-0.5 bg-muted-foreground/40"></div>

                                <!-- Step 1 -->
                                <div class="w-72 rounded-lg border-2 border-blue-400 bg-blue-50 px-4 py-2 text-center dark:bg-blue-950/30">
                                    <p class="text-xs font-semibold">Receive enrollment request</p>
                                    <p class="text-[10px] text-muted-foreground">(student_id, grade_level, strand_id)</p>
                                </div>
                                <div class="h-4 w-0.5 bg-muted-foreground/40"></div>

                                <!-- Decision 1 -->
                                <div class="flex h-16 w-44 items-center justify-center rotate-45 border-2 border-amber-500 bg-amber-50 dark:bg-amber-950/30">
                                    <p class="text-[11px] font-semibold -rotate-45 text-amber-700 dark:text-amber-400">Enrollment open?</p>
                                </div>
                                <div class="flex w-72 items-start">
                                    <div class="flex-1"></div>
                                    <div class="flex flex-col items-center">
                                        <p class="text-[10px] font-bold text-green-600">YES</p>
                                        <div class="h-4 w-0.5 bg-muted-foreground/40"></div>
                                    </div>
                                    <div class="flex flex-1 items-center">
                                        <div class="h-0.5 w-12 bg-muted-foreground/40"></div>
                                        <div class="rounded border border-red-400 bg-red-50 px-2 py-1 dark:bg-red-950/30">
                                            <p class="text-[10px] font-bold text-red-600">NO &rarr; Reject</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Decision 2 -->
                                <div class="flex h-16 w-52 items-center justify-center rotate-45 border-2 border-amber-500 bg-amber-50 dark:bg-amber-950/30">
                                    <p class="text-[10px] font-semibold -rotate-45 text-amber-700 dark:text-amber-400">Already enrolled?</p>
                                </div>
                                <div class="flex w-72 items-start">
                                    <div class="flex-1"></div>
                                    <div class="flex flex-col items-center">
                                        <p class="text-[10px] font-bold text-green-600">NO</p>
                                        <div class="h-4 w-0.5 bg-muted-foreground/40"></div>
                                    </div>
                                    <div class="flex flex-1 items-center">
                                        <div class="h-0.5 w-12 bg-muted-foreground/40"></div>
                                        <div class="rounded border border-red-400 bg-red-50 px-2 py-1 dark:bg-red-950/30">
                                            <p class="text-[10px] font-bold text-red-600">YES &rarr; Reject</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step -->
                                <div class="w-72 rounded-lg border-2 border-blue-400 bg-blue-50 px-4 py-2 text-center dark:bg-blue-950/30">
                                    <p class="text-xs font-semibold">Load subjects from curriculum</p>
                                    <p class="text-[10px] text-muted-foreground">Based on grade_level + strand</p>
                                </div>
                                <div class="h-4 w-0.5 bg-muted-foreground/40"></div>

                                <!-- Decision 3 -->
                                <div class="flex h-16 w-52 items-center justify-center rotate-45 border-2 border-amber-500 bg-amber-50 dark:bg-amber-950/30">
                                    <p class="text-[10px] font-semibold -rotate-45 text-amber-700 dark:text-amber-400">Section has capacity?</p>
                                </div>
                                <div class="flex w-72 items-start">
                                    <div class="flex-1"></div>
                                    <div class="flex flex-col items-center">
                                        <p class="text-[10px] font-bold text-green-600">YES</p>
                                        <div class="h-4 w-0.5 bg-muted-foreground/40"></div>
                                    </div>
                                    <div class="flex flex-1 items-center">
                                        <div class="h-0.5 w-12 bg-muted-foreground/40"></div>
                                        <div class="rounded border border-red-400 bg-red-50 px-2 py-1 dark:bg-red-950/30">
                                            <p class="text-[10px] font-bold text-red-600">NO &rarr; Full</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Create -->
                                <div class="w-72 rounded-lg border-2 border-green-500 bg-green-50 px-4 py-2 text-center dark:bg-green-950/30">
                                    <p class="text-xs font-semibold text-green-700 dark:text-green-400">Create enrollment record</p>
                                    <p class="text-[10px] text-muted-foreground">Assign section + attach subjects</p>
                                </div>
                                <div class="h-4 w-0.5 bg-muted-foreground/40"></div>

                                <!-- End -->
                                <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-zinc-900 dark:border-zinc-100"><div class="h-6 w-6 rounded-full bg-zinc-900 dark:bg-zinc-100"></div></div>
                            </div>
                            <p class="mt-4 text-center text-xs italic text-muted-foreground">Figure 3.9 &mdash; Enrollment Validation Algorithm Flowchart</p>
                        </div>

                        <Separator />

                        <p class="text-sm font-semibold">Graph Algorithm Complexity</p>
                        <div class="overflow-x-auto rounded-lg border">
                            <table class="w-full text-sm">
                                <thead><tr class="border-b bg-muted/50"><th class="px-4 py-2 text-left font-medium">Algorithm</th><th class="px-4 py-2 text-left font-medium">Purpose</th><th class="px-4 py-2 text-left font-medium">Complexity</th></tr></thead>
                                <tbody class="text-muted-foreground">
                                    <tr class="border-b"><td class="px-4 py-2 font-medium text-foreground">BFS / Shortest Path</td><td class="px-4 py-2">Prerequisite chain traversal</td><td class="px-4 py-2"><Badge variant="secondary">O(V + E)</Badge></td></tr>
                                    <tr class="border-b"><td class="px-4 py-2 font-medium text-foreground">Centrality Analysis</td><td class="px-4 py-2">Find bottleneck subjects</td><td class="px-4 py-2"><Badge variant="secondary">O(V * E)</Badge></td></tr>
                                    <tr class="border-b"><td class="px-4 py-2 font-medium text-foreground">Community Detection</td><td class="px-4 py-2">Cluster students by enrollment patterns</td><td class="px-4 py-2"><Badge variant="secondary">O(n log n)</Badge></td></tr>
                                    <tr><td class="px-4 py-2 font-medium text-foreground">Cycle Detection</td><td class="px-4 py-2">Validate no circular prerequisites</td><td class="px-4 py-2"><Badge variant="secondary">O(V + E)</Badge></td></tr>
                                </tbody>
                            </table>
                        </div>

                        <Separator />

                        <p class="text-sm font-semibold">Analytics Pipeline</p>
                        <div class="flex flex-wrap items-center justify-center gap-2 rounded-lg border bg-white p-6 dark:bg-zinc-950">
                            <div class="rounded-lg border-2 border-blue-400 bg-blue-50 px-4 py-3 text-center dark:bg-blue-950/30">
                                <p class="text-[10px] font-bold uppercase text-blue-500">Collect</p>
                                <p class="text-xs">Enrollment, Grades,</p>
                                <p class="text-xs">Student Records</p>
                            </div>
                            <span class="text-xl text-muted-foreground">&rarr;</span>
                            <div class="rounded-lg border-2 border-purple-400 bg-purple-50 px-4 py-3 text-center dark:bg-purple-950/30">
                                <p class="text-[10px] font-bold uppercase text-purple-500">Process</p>
                                <p class="text-xs">Graph Modeling,</p>
                                <p class="text-xs">Data Validation</p>
                            </div>
                            <span class="text-xl text-muted-foreground">&rarr;</span>
                            <div class="rounded-lg border-2 border-amber-400 bg-amber-50 px-4 py-3 text-center dark:bg-amber-950/30">
                                <p class="text-[10px] font-bold uppercase text-amber-600">Analyze</p>
                                <p class="text-xs">Centrality, Clustering,</p>
                                <p class="text-xs">Path Analysis</p>
                            </div>
                            <span class="text-xl text-muted-foreground">&rarr;</span>
                            <div class="rounded-lg border-2 border-green-400 bg-green-50 px-4 py-3 text-center dark:bg-green-950/30">
                                <p class="text-[10px] font-bold uppercase text-green-600">Visualize</p>
                                <p class="text-xs">Reports, Dashboards,</p>
                                <p class="text-xs">SF1/SF5/SF9/SF10</p>
                            </div>
                            <p class="mt-2 w-full text-center text-xs italic text-muted-foreground">Figure 3.10 &mdash; Analytics Pipeline</p>
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
                        <span>MIT 265 &mdash; Advanced Software Engineering Capstone</span>
                    </div>
                    <p class="text-xs text-muted-foreground">Notre Dame of Marbel University &bull; SY 2025-2026</p>
                </div>
            </div>
        </footer>
    </div>
</template>
