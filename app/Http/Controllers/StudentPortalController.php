<?php

namespace App\Http\Controllers;

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class StudentPortalController extends Controller
{
    /**
     * Display the student's own profile.
     */
    public function profile(): Response
    {
        $student = $this->getStudentForAuthUser();

        return Inertia::render('students/MyProfile', [
            'student' => $student,
        ]);
    }

    /**
     * Display the student's current semester subjects.
     */
    public function subjects(): Response
    {
        $student = $this->getStudentForAuthUser();
        $activeSemester = Semester::where('is_active', true)->first();

        $subjects = [];

        if ($student && $activeSemester) {
            $enrollment = Enrollment::where('student_id', $student->id)
                ->where('semester_id', $activeSemester->id)
                ->where('status', EnrollmentStatus::Enrolled)
                ->with(['section.strand', 'grades.subject'])
                ->first();

            if ($enrollment) {
                $subjects = $enrollment->grades->map(fn ($grade) => [
                    'id' => $grade->subject?->id,
                    'code' => $grade->subject?->code,
                    'name' => $grade->subject?->name,
                    'type' => $grade->subject?->type?->label(),
                    'hours' => $grade->subject?->hours,
                ])->values()->toArray();
            }
        }

        return Inertia::render('students/MySubjects', [
            'student' => $student,
            'semester' => $activeSemester,
            'subjects' => $subjects,
        ]);
    }

    /**
     * Display the student's grades.
     */
    public function grades(): Response
    {
        $student = $this->getStudentForAuthUser();

        $enrollments = [];

        if ($student) {
            $enrollments = Enrollment::where('student_id', $student->id)
                ->with([
                    'semester.schoolYear',
                    'section.strand',
                    'grades.subject',
                ])
                ->orderByDesc('created_at')
                ->get()
                ->map(fn ($enrollment) => [
                    'id' => $enrollment->id,
                    'semester' => $enrollment->semester?->full_label,
                    'section' => $enrollment->section?->name,
                    'strand' => $enrollment->section?->strand?->name,
                    'status' => $enrollment->status->label(),
                    'grades' => $enrollment->grades->map(fn ($g) => [
                        'subject_code' => $g->subject?->code,
                        'subject_name' => $g->subject?->name,
                        'midterm' => $g->midterm,
                        'finals' => $g->finals,
                        'final_grade' => $g->final_grade,
                        'remarks' => $g->remarks?->label(),
                    ])->values(),
                ])
                ->toArray();
        }

        return Inertia::render('students/MyGrades', [
            'student' => $student,
            'enrollments' => $enrollments,
        ]);
    }

    /**
     * Get the student record linked to the currently authenticated user.
     */
    protected function getStudentForAuthUser(): ?Student
    {
        return Student::where('user_id', Auth::id())->first();
    }
}
