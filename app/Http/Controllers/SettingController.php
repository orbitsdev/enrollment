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
     * Batch update settings from key => value pairs.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($request->settings as $key => $value) {
            SchoolSetting::set($key, $value);
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
