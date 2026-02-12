<?php

namespace App\Http\Controllers;

use App\Actions\Report\GenerateSF10;
use App\Actions\Report\GenerateSF9;
use App\Enums\EnrollmentStatus;
use App\Enums\StudentStatus;
use App\Exports\ClassListExport;
use App\Exports\EnrollmentSummaryExport;
use App\Exports\StudentMasterlistExport;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Strand;
use App\Models\Student;
use App\Models\Track;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    /**
     * Display the reports index page.
     */
    public function index(): Response
    {
        return Inertia::render('reports/Index');
    }

    /**
     * Display the enrollment summary report.
     */
    public function enrollmentSummary(Request $request): Response
    {
        $schoolYears = SchoolYear::with('semesters')->orderByDesc('id')->get();
        $semesterId = $request->input('semester_id');
        $semester = $semesterId ? Semester::find($semesterId) : Semester::where('is_active', true)->first();

        $data = [];

        if ($semester) {
            $enrollments = Enrollment::where('semester_id', $semester->id)
                ->where('status', EnrollmentStatus::Enrolled)
                ->with('section.strand.track')
                ->get();

            // By Track
            $byTrack = $enrollments->groupBy(fn ($e) => $e->section?->strand?->track?->name ?? 'Unknown')
                ->map->count()
                ->toArray();

            // By Strand
            $byStrand = $enrollments->groupBy(fn ($e) => $e->section?->strand?->name ?? 'Unknown')
                ->map->count()
                ->toArray();

            // By Grade Level
            $byGradeLevel = $enrollments->groupBy(fn ($e) => 'Grade ' . ($e->section?->grade_level ?? '?'))
                ->map->count()
                ->toArray();

            // By Status
            $allEnrollments = Enrollment::where('semester_id', $semester->id)->get();
            $byStatus = $allEnrollments->groupBy(fn ($e) => $e->status->label())
                ->map->count()
                ->toArray();

            $data = [
                'by_track' => $byTrack,
                'by_strand' => $byStrand,
                'by_grade_level' => $byGradeLevel,
                'by_status' => $byStatus,
                'total' => $enrollments->count(),
            ];
        }

        return Inertia::render('reports/EnrollmentSummary', [
            'school_years' => $schoolYears,
            'selected_semester' => $semester,
            'report_data' => $data,
            'filters' => $request->only(['semester_id']),
        ]);
    }

    /**
     * Display the class list report.
     */
    public function classList(Request $request): Response
    {
        $activeSemester = Semester::where('is_active', true)->first();
        $sections = $activeSemester
            ? Section::where('semester_id', $activeSemester->id)->with('strand')->orderBy('name')->get()
            : collect();

        $sectionId = $request->input('section_id');
        $students = [];

        if ($sectionId) {
            $students = Enrollment::where('section_id', $sectionId)
                ->where('status', EnrollmentStatus::Enrolled)
                ->with('student')
                ->get()
                ->pluck('student')
                ->sortBy('last_name')
                ->values()
                ->toArray();
        }

        return Inertia::render('reports/ClassList', [
            'sections' => $sections,
            'students' => $students,
            'selected_section_id' => $sectionId ? (int) $sectionId : null,
            'filters' => $request->only(['section_id']),
        ]);
    }

    /**
     * Display the student masterlist report.
     */
    public function masterlist(Request $request): Response
    {
        $strands = Strand::with('track')->get();

        $query = Student::query();

        if ($request->filled('strand_id')) {
            $query->byStrand((int) $request->input('strand_id'));
        }

        if ($request->filled('status')) {
            $query->byStatus(StudentStatus::from($request->input('status')));
        }

        $students = $query->orderBy('last_name')->orderBy('first_name')->paginate(50);

        return Inertia::render('reports/Masterlist', [
            'students' => $students,
            'strands' => $strands,
            'filters' => $request->only(['strand_id', 'status']),
        ]);
    }

    /**
     * Display the grade summary report.
     */
    public function gradeSummary(Request $request): Response
    {
        $activeSemester = Semester::where('is_active', true)->first();
        $sections = $activeSemester
            ? Section::where('semester_id', $activeSemester->id)->with('strand')->orderBy('name')->get()
            : collect();

        $sectionId = $request->input('section_id');
        $subjectId = $request->input('subject_id');
        $grades = [];
        $subjects = [];

        if ($sectionId) {
            $section = Section::with('strand.subjects')->find($sectionId);

            if ($section) {
                $subjects = $section->strand->subjects()
                    ->wherePivot('grade_level', $section->grade_level)
                    ->wherePivot('semester', $activeSemester?->number)
                    ->get();
            }

            $query = Grade::whereHas('enrollment', function ($q) use ($sectionId) {
                $q->where('section_id', $sectionId)
                    ->where('status', EnrollmentStatus::Enrolled);
            })->with(['enrollment.student', 'subject']);

            if ($subjectId) {
                $query->where('subject_id', $subjectId);
            }

            $grades = $query->get()
                ->groupBy('enrollment_id')
                ->map(function ($studentGrades) {
                    $enrollment = $studentGrades->first()->enrollment;

                    return [
                        'student' => $enrollment->student,
                        'grades' => $studentGrades->map(fn ($g) => [
                            'subject' => $g->subject,
                            'midterm' => $g->midterm,
                            'finals' => $g->finals,
                            'final_grade' => $g->final_grade,
                            'remarks' => $g->remarks,
                        ])->values(),
                    ];
                })
                ->values()
                ->toArray();
        }

        return Inertia::render('reports/GradeSummary', [
            'sections' => $sections,
            'subjects' => $subjects,
            'grades' => $grades,
            'filters' => $request->only(['section_id', 'subject_id']),
        ]);
    }

    /**
     * Display the school forms page.
     */
    public function schoolForms(Request $request): Response
    {
        $activeSemester = Semester::where('is_active', true)->first();
        $sections = $activeSemester
            ? Section::where('semester_id', $activeSemester->id)->with('strand')->orderBy('name')->get()
            : collect();

        $sectionId = $request->input('section_id');
        $enrollments = [];

        if ($sectionId) {
            $enrollments = Enrollment::where('section_id', $sectionId)
                ->where('status', EnrollmentStatus::Enrolled)
                ->with('student')
                ->get()
                ->sortBy('student.last_name')
                ->values()
                ->toArray();
        }

        return Inertia::render('reports/SchoolForms', [
            'sections' => $sections,
            'enrollments' => $enrollments,
            'filters' => $request->only(['section_id']),
        ]);
    }

    /**
     * Export enrollment summary to Excel.
     */
    public function exportEnrollmentSummary(Request $request): BinaryFileResponse
    {
        $semesterId = $request->input('semester_id');
        $semester = $semesterId ? Semester::find($semesterId) : Semester::where('is_active', true)->first();

        return Excel::download(
            new EnrollmentSummaryExport($semester),
            'enrollment-summary-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export class list to Excel.
     */
    public function exportClassList(Section $section): BinaryFileResponse
    {
        return Excel::download(
            new ClassListExport($section),
            'class-list-' . $section->name . '-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export student masterlist to Excel.
     */
    public function exportMasterlist(Request $request): BinaryFileResponse
    {
        return Excel::download(
            new StudentMasterlistExport(
                $request->input('strand_id'),
                $request->input('status')
            ),
            'student-masterlist-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Generate SF9 (Report Card) PDF.
     */
    public function generateSF9(Enrollment $enrollment): \Illuminate\Http\Response
    {
        $action = new GenerateSF9();

        return $action->handle($enrollment);
    }

    /**
     * Generate SF10 (Permanent Record) PDF.
     */
    public function generateSF10(Student $student): \Illuminate\Http\Response
    {
        $action = new GenerateSF10();

        return $action->handle($student);
    }
}
