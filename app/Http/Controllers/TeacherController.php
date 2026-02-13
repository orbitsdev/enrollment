<?php

namespace App\Http\Controllers;

use App\Models\TeacherProfile;
use App\Models\TeacherTraining;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeacherController extends Controller
{
    /**
     * Display a listing of teachers.
     */
    public function index(): Response
    {
        $query = User::role('teacher')->with('teacherProfile.trainings');

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('teacherProfile', function ($q) use ($search) {
                      $q->where('employee_id', 'like', "%{$search}%");
                  });
            });
        }

        $teachers = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('teachers/Index', [
            'teachers' => fn () => $teachers,
            'filters' => [
                'search' => request('search', ''),
            ],
        ]);
    }

    /**
     * Display the specified teacher profile.
     */
    public function show(User $teacher): Response
    {
        $teacher->load(['teacherProfile.trainings']);

        return Inertia::render('teachers/Show', [
            'teacher' => $teacher,
            'profile' => $teacher->teacherProfile,
            'trainings' => $teacher->teacherProfile?->trainings()->latest('date_from')->get() ?? [],
        ]);
    }

    /**
     * Show the form for editing a teacher profile.
     */
    public function edit(User $teacher): Response
    {
        $teacher->load('teacherProfile');

        return Inertia::render('teachers/Edit', [
            'teacher' => $teacher,
            'profile' => $teacher->teacherProfile,
        ]);
    }

    /**
     * Update or create the teacher profile.
     */
    public function update(Request $request, User $teacher): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'nullable|string|max:20',
            'position_title' => 'nullable|string|max:255',
            'appointment_status' => 'nullable|string|max:255',
            'sex' => 'nullable|string|max:10',
            'birthdate' => 'nullable|date',
            'contact_number' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'highest_degree' => 'nullable|string|max:255',
            'degree_course' => 'nullable|string|max:255',
            'degree_major' => 'nullable|string|max:255',
            'school_graduated' => 'nullable|string|max:255',
            'year_graduated' => 'nullable|integer|min:1950|max:2099',
            'prc_license_number' => 'nullable|string|max:20',
            'prc_validity' => 'nullable|date',
            'eligibility' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'date_hired' => 'nullable|date',
            'teaching_hours_per_week' => 'nullable|integer|min:0|max:60',
        ]);

        $teacher->teacherProfile()->updateOrCreate(
            ['user_id' => $teacher->id],
            $validated,
        );

        return redirect()->back()->with('success', 'Teacher profile updated successfully.');
    }

    /**
     * Store a new training record.
     */
    public function storeTraining(Request $request, User $teacher): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'sponsor' => 'nullable|string|max:255',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'hours' => 'nullable|numeric|min:0|max:9999',
        ]);

        $profile = $teacher->teacherProfile()->firstOrCreate(
            ['user_id' => $teacher->id],
        );

        $profile->trainings()->create($validated);

        return redirect()->back()->with('success', 'Training record added successfully.');
    }

    /**
     * Delete a training record.
     */
    public function destroyTraining(User $teacher, TeacherTraining $training): RedirectResponse
    {
        $training->delete();

        return redirect()->back()->with('success', 'Training record removed.');
    }
}
