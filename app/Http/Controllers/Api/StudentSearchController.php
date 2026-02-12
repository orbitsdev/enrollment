<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentSearchController extends Controller
{
    /**
     * Search for students by LRN or name for enrollment wizard.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['required', 'string', 'min:2'],
        ]);

        $students = Student::search($request->q)
            ->limit(10)
            ->get(['id', 'lrn', 'last_name', 'first_name', 'middle_name', 'suffix', 'status']);

        return response()->json(
            $students->map(fn ($student) => [
                'id' => $student->id,
                'lrn' => $student->lrn,
                'full_name' => $student->full_name,
                'status' => $student->status,
            ])
        );
    }
}
