<?php

namespace App\Http\Controllers;

use App\Enums\StudentStatus;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Strand;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    /**
     * Display a listing of students with search, status, and strand filters.
     */
    public function index(): Response
    {
        $query = Student::query();

        if ($search = request('search')) {
            $query->search($search);
        }

        if ($status = request('status')) {
            $query->byStatus(StudentStatus::from($status));
        }

        if ($strandId = request('strand_id')) {
            $query->byStrand((int) $strandId);
        }

        $students = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('students/Index', [
            'students' => $students,
            'filters' => [
                'search' => request('search', ''),
                'status' => request('status', ''),
                'strand_id' => request('strand_id', ''),
            ],
            'statuses' => fn () => collect(StudentStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ]),
            'strands' => fn () => Strand::with('track')->active()->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Show the form for creating a new student.
     */
    public function create(): Response
    {
        return Inertia::render('students/Create', [
            'strands' => Strand::with('track')->active()->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(StoreStudentRequest $request): RedirectResponse
    {
        Student::create($request->validated());

        return redirect()->route('students.index')->with('success', 'Student created successfully.');
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student): Response
    {
        return Inertia::render('students/Show', [
            'student' => $student,
            'history' => Inertia::defer(fn () => [
                'enrollments' => $student->enrollments()
                    ->with(['section.strand', 'semester.schoolYear', 'subjects'])
                    ->latest()
                    ->get(),
                'grades' => $student->grades()
                    ->with('subject')
                    ->get(),
            ], 'history'),
        ]);
    }

    /**
     * Show the form for editing the specified student.
     */
    public function edit(Student $student): Response
    {
        return Inertia::render('students/Edit', [
            'student' => $student,
        ]);
    }

    /**
     * Update the specified student in storage.
     */
    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $student->update($request->validated());

        return redirect()->route('students.show', $student)->with('success', 'Student updated successfully.');
    }

    /**
     * Soft status change to 'dropped' for the specified student.
     */
    public function destroy(Student $student): RedirectResponse
    {
        $student->update(['status' => StudentStatus::Dropped]);

        return redirect()->route('students.index')->with('success', 'Student status changed to dropped.');
    }
}
