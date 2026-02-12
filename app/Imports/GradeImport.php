<?php

namespace App\Imports;

use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GradeImport implements ToCollection, WithHeadingRow
{
    /**
     * Validated (valid) rows.
     */
    public Collection $valid;

    /**
     * Invalid rows with their errors.
     */
    public Collection $invalid;

    public function __construct()
    {
        $this->valid = collect();
        $this->invalid = collect();
    }

    /**
     * Process the collection of rows.
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $data = [
                'lrn' => trim((string) ($row['lrn'] ?? '')),
                'subject_code' => trim((string) ($row['subject_code'] ?? '')),
                'midterm' => $row['midterm'] ?? null,
                'finals' => $row['finals'] ?? null,
            ];

            $validator = Validator::make($data, [
                'lrn' => ['required', 'string', 'size:12'],
                'subject_code' => ['required', 'string'],
                'midterm' => ['nullable', 'numeric', 'min:50', 'max:100'],
                'finals' => ['nullable', 'numeric', 'min:50', 'max:100'],
            ]);

            if ($validator->fails()) {
                $this->invalid->push([
                    'row' => $index + 2,
                    'data' => $data,
                    'errors' => $validator->errors()->toArray(),
                ]);
                continue;
            }

            // Verify student exists
            $student = Student::where('lrn', $data['lrn'])->first();
            if (!$student) {
                $this->invalid->push([
                    'row' => $index + 2,
                    'data' => $data,
                    'errors' => ['lrn' => ['Student with this LRN not found.']],
                ]);
                continue;
            }

            // Verify subject exists
            $subject = Subject::where('code', $data['subject_code'])->first();
            if (!$subject) {
                $this->invalid->push([
                    'row' => $index + 2,
                    'data' => $data,
                    'errors' => ['subject_code' => ['Subject with this code not found.']],
                ]);
                continue;
            }

            $data['student_id'] = $student->id;
            $data['subject_id'] = $subject->id;
            $data['student_name'] = $student->full_name;
            $data['subject_name'] = $subject->name;

            $this->valid->push($data);
        }
    }
}
