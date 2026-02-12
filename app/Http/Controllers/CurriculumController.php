<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Models\Strand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CurriculumController extends Controller
{
    /**
     * Display a listing of tracks with strands and subject counts.
     */
    public function index(): Response
    {
        $tracks = Track::with(['strands' => function ($query) {
            $query->withCount('subjects');
        }])
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('curriculum/Tracks', [
            'tracks' => $tracks,
        ]);
    }

    /**
     * Store a newly created track.
     */
    public function storeTrack(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:tracks,code',
        ]);

        Track::create([
            'name' => $request->name,
            'code' => $request->code,
        ]);

        return redirect()->back()->with('success', 'Track created successfully.');
    }

    /**
     * Update the specified track.
     */
    public function updateTrack(Request $request, Track $track): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:tracks,code,' . $track->id,
        ]);

        $track->update([
            'name' => $request->name,
            'code' => $request->code,
        ]);

        return redirect()->back()->with('success', 'Track updated successfully.');
    }

    /**
     * Store a newly created strand under a track.
     */
    public function storeStrand(Request $request): RedirectResponse
    {
        $request->validate([
            'track_id' => 'required|exists:tracks,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:strands,code',
        ]);

        Strand::create([
            'track_id' => $request->track_id,
            'name' => $request->name,
            'code' => $request->code,
        ]);

        return redirect()->back()->with('success', 'Strand created successfully.');
    }

    /**
     * Update the specified strand.
     */
    public function updateStrand(Request $request, Strand $strand): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:strands,code,' . $strand->id,
        ]);

        $strand->update([
            'name' => $request->name,
            'code' => $request->code,
        ]);

        return redirect()->back()->with('success', 'Strand updated successfully.');
    }

    /**
     * Toggle the active status of a track.
     */
    public function toggleTrackActive(Track $track): RedirectResponse
    {
        $track->update(['is_active' => !$track->is_active]);
        $status = $track->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "Track {$status} successfully.");
    }

    /**
     * Toggle the active status of a strand.
     */
    public function toggleStrandActive(Strand $strand): RedirectResponse
    {
        $strand->update(['is_active' => !$strand->is_active]);
        $status = $strand->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "Strand {$status} successfully.");
    }
}
