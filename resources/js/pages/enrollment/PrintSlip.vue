<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import type {
    Enrollment,
    Grade,
    Section,
    Semester,
    Strand,
    Student,
    Subject,
} from '@/types';

interface SchoolYear {
    id: number;
    name: string;
}

type EnrollmentWithRelations = Enrollment & {
    student: Student;
    section: Section & { strand: Strand };
    semester: Semester & { school_year: SchoolYear };
    subjects: Subject[];
    grades: Grade[];
};

const props = defineProps<{
    enrollment: EnrollmentWithRelations;
}>();

const page = usePage();
const appName = page.props.name as string ?? 'School Enrollment System';

const studentName = props.enrollment.student.full_name
    ?? `${props.enrollment.student.last_name}, ${props.enrollment.student.first_name}`;

const semesterLabel = props.enrollment.semester?.full_label
    ?? `Semester ${props.enrollment.semester?.number} - SY ${props.enrollment.semester?.school_year?.name}`;

function formatDate(dateStr: string | null): string {
    if (!dateStr) return '--';
    return new Date(dateStr).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

function getGradeForSubject(subjectId: number): Grade | undefined {
    return props.enrollment.grades?.find((g) => g.subject_id === subjectId);
}

function printPage() {
    window.print();
}

function goBack() {
    window.history.back();
}

onMounted(() => {
    window.print();
});
</script>

<template>
    <Head :title="`Enrollment Slip - ${studentName}`" />

    <div class="print-page">
        <!-- Header -->
        <div class="header">
            <h1 class="school-name">{{ appName }}</h1>
            <h2 class="slip-title">Enrollment Slip</h2>
            <p class="semester-label">{{ semesterLabel }}</p>
        </div>

        <hr class="divider" />

        <!-- Student Information -->
        <div class="section">
            <h3 class="section-title">Student Information</h3>
            <table class="info-table">
                <tr>
                    <td class="info-label">LRN:</td>
                    <td class="info-value">{{ enrollment.student.lrn }}</td>
                    <td class="info-label">Status:</td>
                    <td class="info-value capitalize">{{ enrollment.status }}</td>
                </tr>
                <tr>
                    <td class="info-label">Name:</td>
                    <td class="info-value">{{ studentName }}</td>
                    <td class="info-label">Gender:</td>
                    <td class="info-value capitalize">{{ enrollment.student.gender }}</td>
                </tr>
            </table>
        </div>

        <!-- Enrollment Details -->
        <div class="section">
            <h3 class="section-title">Enrollment Details</h3>
            <table class="info-table">
                <tr>
                    <td class="info-label">Strand:</td>
                    <td class="info-value">{{ enrollment.section.strand?.code }} - {{ enrollment.section.strand?.name }}</td>
                    <td class="info-label">Section:</td>
                    <td class="info-value">{{ enrollment.section.name }}</td>
                </tr>
                <tr>
                    <td class="info-label">Grade Level:</td>
                    <td class="info-value">Grade {{ enrollment.section.grade_level }}</td>
                    <td class="info-label">Date Enrolled:</td>
                    <td class="info-value">{{ formatDate(enrollment.enrolled_at ?? enrollment.created_at) }}</td>
                </tr>
            </table>
        </div>

        <!-- Subjects -->
        <div class="section">
            <h3 class="section-title">Subjects ({{ enrollment.subjects?.length ?? 0 }})</h3>
            <table class="subject-table">
                <thead>
                    <tr>
                        <th class="th-num">#</th>
                        <th class="th-code">Code</th>
                        <th class="th-name">Subject</th>
                        <th class="th-type">Type</th>
                        <th class="th-grade">Final Grade</th>
                        <th class="th-remarks">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(subject, index) in enrollment.subjects" :key="subject.id">
                        <td class="td-num">{{ index + 1 }}</td>
                        <td class="td-code">{{ subject.code }}</td>
                        <td>{{ subject.name }}</td>
                        <td class="td-type capitalize">{{ subject.type }}</td>
                        <td class="td-grade">{{ getGradeForSubject(subject.id)?.final_grade ?? '' }}</td>
                        <td class="td-remarks capitalize">{{ getGradeForSubject(subject.id)?.remarks ?? '' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer / Signatures -->
        <div class="signatures">
            <div class="sig-box">
                <div class="sig-line"></div>
                <p class="sig-label">Student's Signature</p>
            </div>
            <div class="sig-box">
                <div class="sig-line"></div>
                <p class="sig-label">Registrar's Signature</p>
            </div>
        </div>

        <p class="print-date">Printed on: {{ new Date().toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' }) }}</p>

        <!-- No-print: controls -->
        <div class="no-print controls">
            <button class="btn" @click="printPage">Print</button>
            <button class="btn btn-outline" @click="goBack">Back</button>
        </div>
    </div>
</template>

<style scoped>
.print-page {
    max-width: 800px;
    margin: 0 auto;
    padding: 32px;
    font-family: 'Arial', 'Helvetica', sans-serif;
    font-size: 13px;
    color: #111;
}

.header {
    text-align: center;
    margin-bottom: 16px;
}

.school-name {
    font-size: 20px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin: 0;
}

.slip-title {
    font-size: 16px;
    font-weight: 600;
    margin: 4px 0 2px;
}

.semester-label {
    font-size: 13px;
    color: #555;
    margin: 0;
}

.divider {
    border: none;
    border-top: 2px solid #111;
    margin: 12px 0 16px;
}

.section {
    margin-bottom: 16px;
}

.section-title {
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0 0 6px;
    padding-bottom: 4px;
    border-bottom: 1px solid #ccc;
}

.info-table {
    width: 100%;
    border-collapse: collapse;
}

.info-table td {
    padding: 3px 8px 3px 0;
    vertical-align: top;
}

.info-label {
    font-weight: 600;
    white-space: nowrap;
    width: 110px;
    color: #444;
}

.info-value {
    min-width: 140px;
}

.capitalize {
    text-transform: capitalize;
}

.subject-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 4px;
}

.subject-table th,
.subject-table td {
    border: 1px solid #ccc;
    padding: 5px 8px;
    text-align: left;
}

.subject-table thead tr {
    background-color: #f3f3f3;
}

.subject-table th {
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
}

.th-num { width: 30px; text-align: center; }
.th-code { width: 100px; }
.th-type { width: 70px; }
.th-grade { width: 80px; text-align: center; }
.th-remarks { width: 80px; text-align: center; }

.td-num { text-align: center; }
.td-code { font-family: monospace; font-size: 12px; }
.td-type { }
.td-grade { text-align: center; }
.td-remarks { text-align: center; }

.signatures {
    display: flex;
    justify-content: space-between;
    margin-top: 48px;
    gap: 64px;
}

.sig-box {
    flex: 1;
    text-align: center;
}

.sig-line {
    border-bottom: 1px solid #111;
    margin-bottom: 4px;
    height: 32px;
}

.sig-label {
    font-size: 11px;
    color: #555;
    margin: 0;
}

.print-date {
    margin-top: 24px;
    font-size: 11px;
    color: #888;
    text-align: right;
}

.controls {
    margin-top: 32px;
    text-align: center;
    display: flex;
    gap: 12px;
    justify-content: center;
}

.btn {
    padding: 8px 24px;
    font-size: 14px;
    font-weight: 600;
    border: 1px solid #111;
    border-radius: 6px;
    background: #111;
    color: #fff;
    cursor: pointer;
}

.btn:hover {
    background: #333;
}

.btn-outline {
    background: #fff;
    color: #111;
}

.btn-outline:hover {
    background: #f5f5f5;
}

@media print {
    .no-print {
        display: none !important;
    }

    .print-page {
        padding: 0;
        max-width: 100%;
    }

    .subject-table thead tr {
        background-color: #f3f3f3 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>
