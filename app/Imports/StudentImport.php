<?php

namespace App\Imports;

use App\Enums\StudentStatus;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentImport implements ToCollection, WithHeadingRow
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
                'last_name' => trim((string) ($row['last_name'] ?? '')),
                'first_name' => trim((string) ($row['first_name'] ?? '')),
                'middle_name' => trim((string) ($row['middle_name'] ?? '')) ?: null,
                'suffix' => trim((string) ($row['suffix'] ?? '')) ?: null,
                'birthdate' => trim((string) ($row['birthdate'] ?? '')),
                'gender' => strtolower(trim((string) ($row['gender'] ?? ''))),
                'address' => trim((string) ($row['address'] ?? '')) ?: null,
                'contact_number' => trim((string) ($row['contact_number'] ?? '')) ?: null,
                'guardian_name' => trim((string) ($row['guardian_name'] ?? '')) ?: null,
                'guardian_contact' => trim((string) ($row['guardian_contact'] ?? '')) ?: null,
            ];

            $validator = Validator::make($data, [
                'lrn' => ['required', 'string', 'size:12'],
                'last_name' => ['required', 'string', 'max:100'],
                'first_name' => ['required', 'string', 'max:100'],
                'middle_name' => ['nullable', 'string', 'max:100'],
                'suffix' => ['nullable', 'string', 'max:20'],
                'birthdate' => ['required', 'date'],
                'gender' => ['required', 'in:male,female'],
                'address' => ['nullable', 'string'],
                'contact_number' => ['nullable', 'string', 'max:20'],
                'guardian_name' => ['nullable', 'string', 'max:100'],
                'guardian_contact' => ['nullable', 'string', 'max:20'],
            ]);

            if ($validator->fails()) {
                $this->invalid->push([
                    'row' => $index + 2, // +2 for header + 0-index
                    'data' => $data,
                    'errors' => $validator->errors()->toArray(),
                ]);
            } else {
                // Check for duplicate LRN in DB
                if (Student::where('lrn', $data['lrn'])->exists()) {
                    $this->invalid->push([
                        'row' => $index + 2,
                        'data' => $data,
                        'errors' => ['lrn' => ['LRN already exists in the database.']],
                    ]);
                } else {
                    $this->valid->push($data);
                }
            }
        }
    }
}
