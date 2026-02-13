<?php

namespace App\Http\Controllers;

use App\Enums\SubjectType;
use App\Http\Requests\StoreSubjectRequest;
use App\Models\Strand;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SubjectController extends Controller
{
    /**
     * Display a listing of subjects with strands, filterable by type and strand.
     */
    public function index(): Response
    {
        $query = Subject::with(['strands', 'prerequisite'])->withCount('strands');

        $status = request('status', 'all');
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        if ($type = request('type')) {
            $query->where('type', $type);
        }

        if ($strandId = request('strand_id')) {
            $query->whereHas('strands', function ($q) use ($strandId) {
                $q->where('strands.id', $strandId);
            });
        }

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $subjects = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('curriculum/subjects/Index', [
            'subjects' => $subjects,
            'filters' => [
                'search' => request('search', ''),
                'type' => request('type', ''),
                'strand_id' => request('strand_id', ''),
                'status' => request('status', 'all'),
            ],
            'types' => collect(SubjectType::cases())->map(fn ($type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ]),
            'strands' => Strand::with('track')->active()->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Show the form for creating a new subject.
     */
    public function create(): Response
    {
        return Inertia::render('curriculum/subjects/Create', [
            'strands' => Strand::with('track')->active()->orderBy('sort_order')->get(),
            'subjects' => Subject::active()->orderBy('name')->get(['id', 'code', 'name']),
            'types' => collect(SubjectType::cases())->map(fn ($type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ]),
        ]);
    }

    /**
     * Store a newly created subject.
     */
    public function store(StoreSubjectRequest $request): RedirectResponse
    {
        $subject = Subject::create($request->safe()->except('strands'));

        if ($request->has('strands')) {
            $this->syncStrands($subject, $request->strands);
        }

        return redirect()->route('subjects.index')->with('success', 'Subject created successfully.');
    }

    /**
     * Show the form for editing the specified subject.
     */
    public function edit(Subject $subject): Response
    {
        $subject->load('strands');

        return Inertia::render('curriculum/subjects/Edit', [
            'subject' => $subject,
            'allStrands' => Strand::with('track')->active()->orderBy('sort_order')->get(),
            'subjects' => Subject::active()
                ->where('id', '!=', $subject->id)
                ->orderBy('name')
                ->get(['id', 'code', 'name']),
            'types' => collect(SubjectType::cases())->map(fn ($type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ]),
        ]);
    }

    /**
     * Update the specified subject.
     */
    public function update(StoreSubjectRequest $request, Subject $subject): RedirectResponse
    {
        $subject->update($request->safe()->except('strands'));

        if ($request->has('strands')) {
            $this->syncStrands($subject, $request->strands);
        }

        return redirect()->route('subjects.index')->with('success', 'Subject updated successfully.');
    }

    /**
     * Deactivate the specified subject.
     */
    public function destroy(Subject $subject): RedirectResponse
    {
        $subject->update(['is_active' => false]);

        return redirect()->route('subjects.index')->with('success', 'Subject deactivated successfully.');
    }

    /**
     * Reactivate the specified subject.
     */
    public function restore(Subject $subject): RedirectResponse
    {
        $subject->update(['is_active' => true]);

        return redirect()->route('subjects.index')->with('success', 'Subject reactivated successfully.');
    }

    /**
     * Sync strand mappings for a subject.
     */
    private function syncStrands(Subject $subject, array $strands): void
    {
        $pivotData = [];

        foreach ($strands as $strand) {
            $pivotData[$strand['strand_id']] = [
                'grade_level' => $strand['grade_level'],
                'semester' => $strand['semester'],
                'sort_order' => $strand['sort_order'] ?? 0,
            ];
        }

        $subject->strands()->sync($pivotData);
    }
}
