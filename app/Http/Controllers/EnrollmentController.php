<?php

namespace App\Http\Controllers;

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Semester;
use App\Models\Track;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of enrollments with search, status, and semester filters.
     */
    public function index(): Response
    {
        $query = Enrollment::with(['student', 'section.strand'])
            ->latest();

        if ($search = request('search')) {
            $query->search($search);
        }

        if ($status = request('status')) {
            $query->byStatus(EnrollmentStatus::from($status));
        }

        if ($semesterId = request('semester_id')) {
            $query->where('semester_id', (int) $semesterId);
        }

        $enrollments = $query->paginate(15)->withQueryString();

        return Inertia::render('enrollment/Index', [
            'enrollments' => $enrollments,
            'filters' => [
                'search' => request('search', ''),
                'status' => request('status', ''),
                'semester_id' => request('semester_id', ''),
            ],
            'semesters' => fn () => Semester::with('schoolYear')->latest()->get(),
        ]);
    }

    /**
     * Show the enrollment creation wizard.
     */
    public function create(): Response
    {
        return Inertia::render('enrollment/Create', [
            'tracks' => Track::with('strands')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
            'activeSemester' => Semester::with('schoolYear')->where('is_active', true)->first(),
        ]);
    }

    /**
     * Store a newly created enrollment.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'strand_id' => ['required', 'integer', 'exists:strands,id'],
            'grade_level' => ['required', 'integer', 'in:11,12'],
            'semester_id' => ['required', 'integer', 'exists:semesters,id'],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
        ]);

        $enrollment = Enrollment::create([
            'student_id' => $validated['student_id'],
            'section_id' => $validated['section_id'],
            'semester_id' => $validated['semester_id'],
            'status' => EnrollmentStatus::Pending,
        ]);

        // Create grade records for each selected subject
        foreach ($validated['subject_ids'] as $subjectId) {
            Grade::create([
                'enrollment_id' => $enrollment->id,
                'subject_id' => $subjectId,
            ]);
        }

        return redirect()->route('enrollment.show', $enrollment)
            ->with('success', 'Enrollment created successfully.');
    }

    /**
     * Display the specified enrollment with subjects and grades.
     */
    public function show(Enrollment $enrollment): Response
    {
        $enrollment->load([
            'student',
            'section.strand',
            'grades.subject',
        ]);

        // Derive subjects from grades
        $subjects = $enrollment->grades->map->subject->unique('id')->values();

        return Inertia::render('enrollment/Show', [
            'enrollment' => array_merge($enrollment->toArray(), [
                'subjects' => $subjects,
            ]),
        ]);
    }

    /**
     * Update the enrollment status.
     */
    public function updateStatus(Request $request, Enrollment $enrollment): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:enrolled,dropped,transferred'],
        ]);

        $enrollment->update([
            'status' => $validated['status'],
            'enrolled_at' => $validated['status'] === 'enrolled' ? now() : $enrollment->enrolled_at,
        ]);

        return redirect()->back()->with('success', 'Enrollment status updated.');
    }

    /**
     * Print the enrollment slip.
     */
    public function printSlip(Enrollment $enrollment): Response
    {
        $enrollment->load([
            'student',
            'section.strand',
            'semester.schoolYear',
            'grades.subject',
        ]);

        $subjects = $enrollment->grades->map->subject->unique('id')->values();

        return Inertia::render('enrollment/PrintSlip', [
            'enrollment' => array_merge($enrollment->toArray(), [
                'subjects' => $subjects,
            ]),
        ]);
    }
}
