<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DuplicateCheckController extends Controller
{
    /**
     * Check for duplicate students based on name and birthdate.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'birthdate' => ['required', 'date'],
        ]);

        $matches = Student::where('first_name', 'like', $request->first_name)
            ->where('last_name', 'like', $request->last_name)
            ->where('birthdate', $request->birthdate)
            ->get(['id', 'lrn', 'last_name', 'first_name', 'middle_name', 'suffix', 'birthdate', 'status']);

        return response()->json(
            $matches->map(fn ($student) => [
                'id' => $student->id,
                'lrn' => $student->lrn,
                'full_name' => $student->full_name,
                'birthdate' => $student->birthdate->format('Y-m-d'),
                'status' => $student->status,
            ])
        );
    }
}
