<?php

namespace App\Http\Controllers;

use App\Enums\EnrollmentStatus;
use App\Enums\UserRole;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Track;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function __invoke(): Response
    {
        $activeSemester = Semester::where('is_active', true)->first();

        return Inertia::render('Dashboard', [
            'auth_user' => Auth::user(),

            'total_students' => fn () => Student::count(),
            'total_enrolled' => fn () => $activeSemester
                ? Enrollment::where('semester_id', $activeSemester->id)
                    ->where('status', EnrollmentStatus::Enrolled)
                    ->count()
                : 0,
            'total_sections' => fn () => $activeSemester
                ? Section::where('semester_id', $activeSemester->id)->count()
                : Section::count(),
            'total_teachers' => fn () => User::role(UserRole::Teacher->value)->count(),

            'enrollment_by_track' => fn () => $activeSemester
                ? Track::query()
                    ->get()
                    ->mapWithKeys(function ($track) use ($activeSemester) {
                        $count = Enrollment::where('status', EnrollmentStatus::Enrolled)
                            ->where('semester_id', $activeSemester->id)
                            ->whereHas('section.strand', function ($q) use ($track) {
                                $q->where('track_id', $track->id);
                            })
                            ->count();

                        return [$track->name => $count];
                    })
                    ->toArray()
                : [],

            'section_capacity' => fn () => $activeSemester
                ? Section::where('semester_id', $activeSemester->id)
                    ->withCount(['enrollments as enrolled_count' => function ($q) {
                        $q->where('status', EnrollmentStatus::Enrolled);
                    }])
                    ->get()
                    ->mapWithKeys(fn ($section) => [
                        $section->name => [
                            'enrolled' => $section->enrolled_count,
                            'max' => $section->max_capacity,
                        ],
                    ])
                    ->toArray()
                : [],

            'recent_enrollments' => fn () => Enrollment::with(['student', 'section.strand'])
                ->latest()
                ->take(10)
                ->get(),
        ]);
    }
}
