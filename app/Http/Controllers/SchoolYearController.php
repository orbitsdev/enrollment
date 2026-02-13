<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use App\Models\Semester;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SchoolYearController extends Controller
{
    /**
     * Display a listing of school years with semesters.
     */
    public function index(): Response
    {
        $schoolYears = SchoolYear::with('semesters')
            ->latest()
            ->get();

        return Inertia::render('school-settings/SchoolYear', [
            'schoolYears' => $schoolYears,
        ]);
    }

    /**
     * Store a newly created school year with its two semesters.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|unique:school_years,name',
        ]);

        $schoolYear = SchoolYear::create([
            'name' => $request->name,
        ]);

        // Auto-create two semesters
        $schoolYear->semesters()->createMany([
            ['number' => 1],
            ['number' => 2],
        ]);

        return redirect()->back()->with('success', 'School year created successfully.');
    }

    /**
     * Update the specified school year name.
     */
    public function update(Request $request, SchoolYear $schoolYear): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|unique:school_years,name,' . $schoolYear->id,
        ]);

        $schoolYear->update([
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'School year updated successfully.');
    }

    /**
     * Activate the specified school year and its first semester.
     */
    public function activate(SchoolYear $schoolYear): RedirectResponse
    {
        // Ensure school year has semesters
        if ($schoolYear->semesters()->count() === 0) {
            $schoolYear->semesters()->createMany([
                ['number' => 1],
                ['number' => 2],
            ]);
        }

        // Deactivate all school years
        SchoolYear::query()->update(['is_active' => false]);

        // Deactivate all semesters
        Semester::query()->update(['is_active' => false, 'enrollment_open' => false]);

        // Activate the selected school year
        $schoolYear->update(['is_active' => true]);

        // Activate its first semester
        $schoolYear->semesters()->where('number', 1)->update(['is_active' => true]);

        return redirect()->back()->with('success', 'School year activated successfully.');
    }

    /**
     * Toggle enrollment open/close for a semester.
     */
    public function toggleEnrollment(Semester $semester): RedirectResponse
    {
        $semester->update([
            'enrollment_open' => !$semester->enrollment_open,
        ]);

        $status = $semester->enrollment_open ? 'opened' : 'closed';

        return redirect()->back()->with('success', "Enrollment {$status} successfully.");
    }

    /**
     * Activate a specific semester.
     */
    public function activateSemester(Semester $semester): RedirectResponse
    {
        // Deactivate all semesters
        Semester::query()->update(['is_active' => false]);

        // Activate the selected semester
        $semester->update(['is_active' => true]);

        return redirect()->back()->with('success', 'Semester activated successfully.');
    }
}
