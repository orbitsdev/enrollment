<?php

namespace App\Http\Controllers;

use App\Models\SchoolSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    /**
     * Display all school settings.
     */
    public function index(): Response
    {
        $settings = SchoolSetting::all()->pluck('value', 'key');

        return Inertia::render('school-settings/Index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Batch update settings from form data.
     */
    public function update(Request $request): RedirectResponse
    {
        $allowedKeys = [
            'school_name', 'school_id', 'school_address',
            'district', 'division', 'region',
            'passing_grade', 'midterm_weight', 'finals_weight',
            'default_capacity',
        ];

        foreach ($allowedKeys as $key) {
            if ($request->has($key)) {
                SchoolSetting::set($key, $request->input($key, ''));
            }
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
