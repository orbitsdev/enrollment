<?php

namespace App\Http\Controllers;

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\Semester;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TeacherPortalController extends Controller
{
    /**
     * Display teacher's advised sections for the active semester.
     */
    public function sections(): Response
    {
        $user = Auth::user();
        $activeSemester = Semester::where('is_active', true)->first();

        $sections = collect();

        if ($activeSemester) {
            $sections = Section::where('adviser_id', $user->id)
                ->where('semester_id', $activeSemester->id)
                ->with(['strand.track', 'semester.schoolYear'])
                ->withEnrolledCount()
                ->get();
        }

        return Inertia::render('teachers/MySections', [
            'sections' => $sections,
        ]);
    }

    /**
     * Display students enrolled in teacher's advised sections.
     */
    public function students(): Response
    {
        $user = Auth::user();
        $activeSemester = Semester::where('is_active', true)->first();

        $enrollments = collect();

        if ($activeSemester) {
            $sectionIds = Section::where('adviser_id', $user->id)
                ->where('semester_id', $activeSemester->id)
                ->pluck('id');

            $enrollments = Enrollment::whereIn('section_id', $sectionIds)
                ->where('status', EnrollmentStatus::Enrolled)
                ->with(['student', 'section.strand'])
                ->get();
        }

        return Inertia::render('teachers/MyStudents', [
            'enrollments' => $enrollments,
        ]);
    }
}
