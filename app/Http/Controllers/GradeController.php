<?php

namespace App\Http\Controllers;

use App\Enums\EnrollmentStatus;
use App\Enums\GradeRemarks;
use App\Enums\UserRole;
use App\Models\Grade;
use App\Models\SchoolSetting;
use App\Models\Section;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class GradeController extends Controller
{
    /**
     * Display sections with subjects and grade progress.
     */
    public function index(): Response
    {
        $user = Auth::user();
        $activeSemester = \App\Models\Semester::where('is_active', true)->first();

        $query = Section::with(['strand.track'])
            ->withEnrolledCount();

        // If the semester is active, filter to active semester only
        if ($activeSemester) {
            $query->where('semester_id', $activeSemester->id);
        }

        // Teachers only see their assigned sections
        if ($user->hasRole(UserRole::Teacher->value)) {
            $query->where('adviser_id', $user->id);
        }

        $sections = $query->get()->map(function (Section $section) {
            // Get subjects for this section's strand and grade level
            $subjects = $section->strand->subjects()
                ->wherePivot('grade_level', $section->grade_level)
                ->orderBy('subject_strand.sort_order')
                ->get();

            // Add grade count info for each subject
            $subjects->each(function (Subject $subject) use ($section) {
                $enrollmentIds = $section->enrollments()
                    ->where('status', EnrollmentStatus::Enrolled)
                    ->pluck('id');

                $subject->grades_total = $enrollmentIds->count();
                $subject->grades_entered = Grade::whereIn('enrollment_id', $enrollmentIds)
                    ->where('subject_id', $subject->id)
                    ->whereNotNull('final_grade')
                    ->count();
            });

            $section->setRelation('subjects', $subjects);

            return $section;
        });

        return Inertia::render('grades/Index', [
            'sections' => $sections,
        ]);
    }

    /**
     * Display grade entry form for a section and subject.
     */
    public function entry(Section $section, Subject $subject): Response
    {
        $section->load(['strand', 'semester.schoolYear', 'adviser']);

        $enrollmentIds = $section->enrollments()
            ->where('status', EnrollmentStatus::Enrolled)
            ->pluck('id');

        // Get or create grade records for each enrolled student
        $grades = Grade::with(['enrollment.student'])
            ->where('subject_id', $subject->id)
            ->whereIn('enrollment_id', $enrollmentIds)
            ->get()
            ->sortBy('enrollment.student.last_name')
            ->values();

        // Get grade settings
        $settings = SchoolSetting::getMany(['midterm_weight', 'finals_weight', 'passing_grade']);

        return Inertia::render('grades/Entry', [
            'section' => $section,
            'subject' => $subject,
            'grades' => $grades,
            'settings' => [
                'midterm_weight' => (float) ($settings['midterm_weight'] ?? 40),
                'finals_weight' => (float) ($settings['finals_weight'] ?? 60),
                'passing_grade' => (float) ($settings['passing_grade'] ?? 75),
            ],
        ]);
    }

    /**
     * Save grades for a section and subject.
     */
    public function store(Request $request, Section $section, Subject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'grades' => ['required', 'array'],
            'grades.*.id' => ['required', 'integer', 'exists:grades,id'],
            'grades.*.midterm' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'grades.*.finals' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $settings = SchoolSetting::getMany(['midterm_weight', 'finals_weight', 'passing_grade']);
        $midtermWeight = ((float) ($settings['midterm_weight'] ?? 40)) / 100;
        $finalsWeight = ((float) ($settings['finals_weight'] ?? 60)) / 100;
        $passingGrade = (float) ($settings['passing_grade'] ?? 75);

        foreach ($validated['grades'] as $gradeData) {
            $grade = Grade::findOrFail($gradeData['id']);

            // Skip locked grades
            if ($grade->is_locked) {
                continue;
            }

            $midterm = $gradeData['midterm'];
            $finals = $gradeData['finals'];
            $finalGrade = null;
            $remarks = null;

            if ($midterm !== null && $finals !== null) {
                $finalGrade = round(($midterm * $midtermWeight) + ($finals * $finalsWeight), 2);
                $remarks = $finalGrade >= $passingGrade ? GradeRemarks::Passed : GradeRemarks::Failed;
            }

            $grade->update([
                'midterm' => $midterm,
                'finals' => $finals,
                'final_grade' => $finalGrade,
                'remarks' => $remarks,
                'encoded_by' => Auth::id(),
            ]);
        }

        return redirect()->back()->with('success', 'Grades saved successfully.');
    }

    /**
     * Lock all grades for a section and subject.
     */
    public function lock(Section $section, Subject $subject): RedirectResponse
    {
        $enrollmentIds = $section->enrollments()->pluck('id');

        Grade::where('subject_id', $subject->id)
            ->whereIn('enrollment_id', $enrollmentIds)
            ->update(['is_locked' => true]);

        return redirect()->back()->with('success', 'Grades locked successfully.');
    }

    /**
     * Unlock grades (admin only).
     */
    public function unlock(Section $section, Subject $subject): RedirectResponse
    {
        $enrollmentIds = $section->enrollments()->pluck('id');

        Grade::where('subject_id', $subject->id)
            ->whereIn('enrollment_id', $enrollmentIds)
            ->update(['is_locked' => false]);

        return redirect()->back()->with('success', 'Grades unlocked successfully.');
    }
}
