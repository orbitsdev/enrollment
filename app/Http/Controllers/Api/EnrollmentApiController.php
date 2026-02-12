<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Section;
use App\Models\Strand;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnrollmentApiController extends Controller
{
    /**
     * Get the subject load for a given strand, grade level, and semester.
     */
    public function getSubjectLoad(Request $request): JsonResponse
    {
        $request->validate([
            'strand_id' => ['required', 'integer', 'exists:strands,id'],
            'grade_level' => ['required', 'integer', 'in:11,12'],
            'semester_id' => ['required', 'integer', 'exists:semesters,id'],
        ]);

        $strand = Strand::findOrFail($request->strand_id);
        $semester = \App\Models\Semester::findOrFail($request->semester_id);

        $subjects = $strand->subjects()
            ->wherePivot('grade_level', $request->grade_level)
            ->wherePivot('semester', $semester->number)
            ->where('is_active', true)
            ->orderBy('subject_strand.sort_order')
            ->get();

        return response()->json($subjects);
    }

    /**
     * Check prerequisites for a student's selected subjects.
     */
    public function checkPrerequisites(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'subject_ids' => ['required', 'array'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
        ]);

        $student = Student::findOrFail($request->student_id);
        $issues = [];

        foreach ($request->subject_ids as $subjectId) {
            $subject = Subject::with('prerequisite')->findOrFail($subjectId);

            if ($subject->prerequisite_id) {
                // Check if the student has passed the prerequisite
                $passed = Grade::whereHas('enrollment', function ($q) use ($student) {
                    $q->where('student_id', $student->id);
                })
                    ->where('subject_id', $subject->prerequisite_id)
                    ->where('remarks', 'passed')
                    ->exists();

                if (! $passed) {
                    $issues[] = [
                        'subject_id' => $subject->id,
                        'subject_name' => $subject->name,
                        'prerequisite' => $subject->prerequisite->name,
                        'message' => "Prerequisite '{$subject->prerequisite->name}' not yet passed.",
                    ];
                }
            }
        }

        return response()->json([
            'has_issues' => count($issues) > 0,
            'issues' => $issues,
        ]);
    }

    /**
     * Get available sections for a given strand, grade level, and semester.
     */
    public function getAvailableSections(Request $request): JsonResponse
    {
        $request->validate([
            'strand_id' => ['required', 'integer', 'exists:strands,id'],
            'grade_level' => ['required', 'integer', 'in:11,12'],
            'semester_id' => ['required', 'integer', 'exists:semesters,id'],
        ]);

        $sections = Section::withEnrolledCount()
            ->where('strand_id', $request->strand_id)
            ->where('grade_level', $request->grade_level)
            ->where('semester_id', $request->semester_id)
            ->with('adviser')
            ->get()
            ->map(function (Section $section) {
                return [
                    'id' => $section->id,
                    'name' => $section->name,
                    'grade_level' => $section->grade_level,
                    'max_capacity' => $section->max_capacity,
                    'enrolled_count' => $section->enrolled_count,
                    'adviser' => $section->adviser?->name,
                    'is_full' => $section->enrolled_count >= $section->max_capacity,
                ];
            });

        return response()->json($sections);
    }
}
