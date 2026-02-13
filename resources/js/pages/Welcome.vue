<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { BarChart3, BookOpen, ClipboardList, GraduationCap, ShieldCheck, Users } from 'lucide-vue-next';
import { onMounted } from 'vue';

gsap.registerPlugin(ScrollTrigger);


const features = [
    {
        icon: Users,
        title: 'Student Management',
        description: 'Comprehensive student records with LRN tracking, enrollment history, and personal information management.',
    },
    {
        icon: ClipboardList,
        title: 'Enrollment Processing',
        description: 'Streamlined enrollment workflow with section assignments, strand selection, and semester management.',
    },
    {
        icon: BookOpen,
        title: 'Curriculum & Grades',
        description: 'Manage SHS tracks, strands, subjects, and grade recording for midterm and final periods.',
    },
    {
        icon: BarChart3,
        title: 'Reports & School Forms',
        description: 'Generate DepEd school forms (SF1, SF5, SF9, SF10) and enrollment summary reports.',
    },
    {
        icon: ShieldCheck,
        title: 'Role-Based Access',
        description: 'Secure access control for administrators, registrars, teachers, and students.',
    },
    {
        icon: GraduationCap,
        title: 'SHS Tracks & Strands',
        description: 'Full support for Senior High School academic tracks, strands, and specialized subjects.',
    },
];

onMounted(() => {
    const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });

    tl.from('.hero-header', { y: -20, opacity: 0, duration: 0.5 })
        .from('.hero-badge', { scale: 0.9, opacity: 0, duration: 0.6 }, '-=0.2')
        .from('.hero-title-1', { y: 30, opacity: 0, duration: 0.7 }, '-=0.3')
        .from('.hero-title-2', { y: 30, opacity: 0, duration: 0.7 }, '-=0.5')
        .from('.hero-desc', { y: 20, opacity: 0, duration: 0.6 }, '-=0.4')
        .from('.hero-cta', { y: 20, opacity: 0, duration: 0.5 }, '-=0.3');

    // Parallax effect on hero background image
    gsap.to('.hero-bg-img', {
        scrollTrigger: {
            trigger: '.hero-section',
            start: 'top top',
            end: 'bottom top',
            scrub: true,
        },
        y: 150,
        ease: 'none',
    });

    // Parallax on hero content (moves slower than scroll)
    gsap.to('.hero-content', {
        scrollTrigger: {
            trigger: '.hero-section',
            start: 'top top',
            end: 'bottom top',
            scrub: true,
        },
        y: 80,
        opacity: 0.3,
        ease: 'none',
    });

    // Feature cards with ScrollTrigger
    gsap.from('.feature-card', {
        scrollTrigger: { trigger: '.features-grid', start: 'top 80%' },
        y: 40,
        opacity: 0,
        duration: 0.6,
        stagger: 0.1,
    });

    // About section
    gsap.from('.about-text', {
        scrollTrigger: { trigger: '.about-section', start: 'top 80%' },
        x: -40,
        opacity: 0,
        duration: 0.8,
    });
    gsap.from('.about-card', {
        scrollTrigger: { trigger: '.about-section', start: 'top 80%' },
        x: 40,
        opacity: 0,
        duration: 0.8,
    });

    // Footer fade in
    gsap.from('.site-footer', {
        scrollTrigger: { trigger: '.site-footer', start: 'top 95%' },
        y: 20,
        opacity: 0,
        duration: 0.6,
    });
});
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-background">
        <!-- Header -->
        <header class="hero-header sticky top-0 z-50 border-b bg-background/80 backdrop-blur-sm">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary">
                        <GraduationCap class="h-6 w-6 text-primary-foreground" />
                    </div>
                    <div class="hidden sm:block">
                        <p class="text-sm font-semibold leading-tight">Lake Sebu National</p>
                        <p class="text-xs leading-tight text-muted-foreground">High School</p>
                    </div>
                </div>

                <nav class="flex items-center gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        href="/dashboard"
                        class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90"
                    >
                        Dashboard
                    </Link>
                    <Link
                        v-else
                        href="/login"
                        class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90"
                    >
                        Log in
                    </Link>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="hero-section relative overflow-hidden min-h-[600px]">
            <!-- Background image -->
            <div class="absolute inset-0 -top-[50px] -bottom-[50px]">
                <img
                    src="https://images.unsplash.com/photo-1580674285054-bed31e145f59?w=1920&q=80"
                    alt=""
                    class="hero-bg-img h-full w-full object-cover"
                    loading="eager"
                />
                <div class="absolute inset-0 bg-background/85 dark:bg-background/90" />
            </div>

            <!-- Content -->
            <div class="hero-content relative mx-auto max-w-7xl px-4 py-24 sm:px-6 sm:py-32 lg:px-8 lg:py-40">
                <div class="mx-auto max-w-2xl text-center">
                    <div class="hero-badge mb-8 inline-flex items-center rounded-full border bg-background/80 px-4 py-1.5 text-sm text-muted-foreground shadow-sm backdrop-blur-sm">
                        <GraduationCap class="mr-2 h-4 w-4" />
                        Senior High School Enrollment System
                    </div>

                    <h1 class="text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl">
                        <span class="hero-title-1 block">Lake Sebu National</span>
                        <span class="hero-title-2 block text-primary">High School</span>
                    </h1>

                    <p class="hero-desc mt-6 text-lg leading-8 text-muted-foreground sm:text-xl">
                        A comprehensive enrollment and student information system for managing
                        student records, class sections, grades, and DepEd school forms.
                    </p>

                    <div class="hero-cta mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <Link
                            v-if="$page.props.auth.user"
                            href="/dashboard"
                            class="inline-flex w-full items-center justify-center rounded-md bg-primary px-8 py-3 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90 sm:w-auto"
                        >
                            Go to Dashboard
                        </Link>
                        <Link
                            v-else
                            href="/login"
                            class="inline-flex w-full items-center justify-center rounded-md bg-primary px-8 py-3 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90 sm:w-auto"
                        >
                            Log in to Get Started
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="border-t bg-muted/30 py-24 sm:py-32">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">
                        Everything you need to manage enrollment
                    </h2>
                    <p class="mt-4 text-lg text-muted-foreground">
                        Built specifically for Senior High School needs with DepEd compliance in mind.
                    </p>
                </div>

                <div class="features-grid mx-auto mt-16 grid max-w-5xl grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="feature in features"
                        :key="feature.title"
                        class="feature-card relative rounded-lg border bg-background p-6 shadow-sm transition-shadow hover:shadow-md"
                    >
                        <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                            <component :is="feature.icon" class="h-5 w-5 text-primary" />
                        </div>
                        <h3 class="text-base font-semibold">{{ feature.title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
                            {{ feature.description }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Info Section -->
        <section class="about-section border-t py-24 sm:py-32">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto grid max-w-5xl grid-cols-1 items-center gap-12 lg:grid-cols-2">
                    <div class="about-text">
                        <h2 class="text-3xl font-bold tracking-tight">
                            About the School
                        </h2>
                        <p class="mt-4 leading-relaxed text-muted-foreground">
                            Lake Sebu National High School is located in the municipality of Lake Sebu, South Cotabato.
                            The school offers the Senior High School program with various academic tracks and strands,
                            preparing students for higher education and the workforce.
                        </p>
                        <p class="mt-4 leading-relaxed text-muted-foreground">
                            This enrollment system streamlines the management of student records, enrollment processing,
                            grade recording, and the generation of official DepEd school forms.
                        </p>
                        <div class="mt-8 flex flex-col gap-3 text-sm">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                </div>
                                <span class="text-muted-foreground">Lake Sebu, South Cotabato</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                                </div>
                                <span class="text-muted-foreground">Senior High School Program</span>
                            </div>
                        </div>
                    </div>
                    <div class="about-card flex items-center justify-center">
                        <div class="relative rounded-2xl border bg-muted/50 p-8 text-center shadow-sm">
                            <GraduationCap class="mx-auto h-24 w-24 text-primary/20" />
                            <h3 class="mt-4 text-xl font-bold">LSNHS</h3>
                            <p class="text-sm text-muted-foreground">Lake Sebu National High School</p>
                            <p class="mt-1 text-xs text-muted-foreground">Enrollment Management System</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="site-footer border-t bg-muted/30 py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                        <GraduationCap class="h-4 w-4" />
                        <span>Lake Sebu National High School</span>
                    </div>
                    <p class="text-xs text-muted-foreground">
                        Lake Sebu, South Cotabato
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
