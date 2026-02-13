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

        return response()->json([
            'students' => $students->map(fn ($student) => [
                'id' => $student->id,
                'lrn' => $student->lrn,
                'full_name' => $student->full_name,
                'status' => $student->status,
            ]),
        ]);
    }

    /**
     * Quick-create a student from the enrollment wizard.
     */
    public function quickCreate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lrn' => ['required', 'string', 'digits:12', 'unique:students,lrn'],
            'last_name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'gender' => ['required', 'string', 'in:male,female'],
            'birthdate' => ['required', 'date'],
        ]);

        $student = Student::create([
            ...$validated,
            'status' => 'active',
        ]);

        return response()->json([
            'student' => [
                'id' => $student->id,
                'lrn' => $student->lrn,
                'full_name' => $student->full_name,
                'status' => $student->status,
            ],
        ], 201);
    }
}
