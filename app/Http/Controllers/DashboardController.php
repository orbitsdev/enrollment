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

            // Deferred: stats group
            'total_students' => Inertia::defer(fn () => Student::count(), 'stats'),
            'total_enrolled' => Inertia::defer(fn () => $activeSemester
                ? Enrollment::where('semester_id', $activeSemester->id)
                    ->where('status', EnrollmentStatus::Enrolled)
                    ->count()
                : 0,
                'stats'
            ),
            'total_sections' => Inertia::defer(fn () => $activeSemester
                ? Section::where('semester_id', $activeSemester->id)->count()
                : Section::count(),
                'stats'
            ),
            'total_teachers' => Inertia::defer(
                fn () => User::role(UserRole::Teacher->value)->count(),
                'stats'
            ),

            // Deferred: charts group
            'enrollment_by_track' => Inertia::defer(function () use ($activeSemester) {
                if (!$activeSemester) {
                    return [];
                }

                return Track::query()
                    ->withCount(['strands as enrolled_count' => function ($query) use ($activeSemester) {
                        $query->whereHas('sections', function ($q) use ($activeSemester) {
                            $q->where('semester_id', $activeSemester->id);
                        })->select(\Illuminate\Support\Facades\DB::raw('count(*)'));
                    }])
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
                    ->toArray();
            }, 'charts'),

            'section_capacity' => Inertia::defer(function () use ($activeSemester) {
                if (!$activeSemester) {
                    return [];
                }

                return Section::where('semester_id', $activeSemester->id)
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
                    ->toArray();
            }, 'charts'),

            // Deferred: recent group
            'recent_enrollments' => Inertia::defer(fn () => Enrollment::with(['student', 'section.strand'])
                ->latest()
                ->take(10)
                ->get(),
                'recent'
            ),
        ]);
    }
}
