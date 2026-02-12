<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSectionRequest;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Strand;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SectionController extends Controller
{
    /**
     * Display a listing of sections with strand, semester, adviser, and enrolled count.
     */
    public function index(): Response
    {
        $query = Section::with(['strand.track', 'semester.schoolYear', 'adviser'])
            ->withEnrolledCount();

        if ($semesterId = request('semester_id')) {
            $query->bySemester((int) $semesterId);
        }

        if ($strandId = request('strand_id')) {
            $query->byStrand((int) $strandId);
        }

        if ($gradeLevel = request('grade_level')) {
            $query->byGradeLevel((int) $gradeLevel);
        }

        $sections = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('sections/Index', [
            'sections' => $sections,
            'filters' => [
                'semester_id' => request('semester_id', ''),
                'strand_id' => request('strand_id', ''),
                'grade_level' => request('grade_level', ''),
            ],
            'semesters' => fn () => Semester::with('schoolYear')->latest()->get(),
            'strands' => fn () => Strand::with('track')->active()->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Show the form for creating a new section.
     */
    public function create(): Response
    {
        return Inertia::render('sections/Create', [
            'strands' => Strand::with('track')->active()->orderBy('sort_order')->get(),
            'semesters' => Semester::with('schoolYear')->latest()->get(),
            'teachers' => User::role('teacher')->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created section in storage.
     */
    public function store(StoreSectionRequest $request): RedirectResponse
    {
        Section::create($request->validated());

        return redirect()->route('sections.index')->with('success', 'Section created successfully.');
    }

    /**
     * Display the specified section with enrolled students as roster.
     */
    public function show(Section $section): Response
    {
        $section->load([
            'strand.track',
            'semester.schoolYear',
            'adviser',
        ]);

        return Inertia::render('sections/Show', [
            'section' => $section,
            'enrollments' => Inertia::optional(fn () => $section->enrollments()->with('student')->get()),
        ]);
    }

    /**
     * Show the form for editing the specified section.
     */
    public function edit(Section $section): Response
    {
        return Inertia::render('sections/Edit', [
            'section' => $section,
            'strands' => Strand::with('track')->active()->orderBy('sort_order')->get(),
            'semesters' => Semester::with('schoolYear')->latest()->get(),
            'teachers' => User::role('teacher')->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update the specified section in storage.
     */
    public function update(StoreSectionRequest $request, Section $section): RedirectResponse
    {
        $section->update($request->validated());

        return redirect()->route('sections.index')->with('success', 'Section updated successfully.');
    }

    /**
     * Remove the specified section if no enrollments exist.
     */
    public function destroy(Section $section): RedirectResponse
    {
        if ($section->enrollments()->exists()) {
            return redirect()->route('sections.index')
                ->with('error', 'Cannot delete section with existing enrollments.');
        }

        $section->delete();

        return redirect()->route('sections.index')->with('success', 'Section deleted successfully.');
    }
}
