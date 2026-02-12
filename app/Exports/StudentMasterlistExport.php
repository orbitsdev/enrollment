<?php

namespace App\Exports;

use App\Enums\StudentStatus;
use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentMasterlistExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function __construct(
        protected ?int $strandId = null,
        protected ?string $status = null,
    ) {}

    /**
     * Return the collection of data rows.
     */
    public function collection(): \Illuminate\Support\Collection
    {
        $query = Student::query();

        if ($this->strandId) {
            $query->byStrand($this->strandId);
        }

        if ($this->status) {
            $query->byStatus(StudentStatus::from($this->status));
        }

        $students = $query->orderBy('last_name')->orderBy('first_name')->get();

        return $students->map(fn ($student) => [
            'lrn' => $student->lrn,
            'last_name' => $student->last_name,
            'first_name' => $student->first_name,
            'middle_name' => $student->middle_name,
            'suffix' => $student->suffix,
            'gender' => $student->gender,
            'birthdate' => $student->birthdate?->format('Y-m-d'),
            'address' => $student->address,
            'contact_number' => $student->contact_number,
            'guardian_name' => $student->guardian_name,
            'guardian_contact' => $student->guardian_contact,
            'status' => $student->status->label(),
        ]);
    }

    /**
     * Define the headings row.
     */
    public function headings(): array
    {
        return [
            'LRN',
            'Last Name',
            'First Name',
            'Middle Name',
            'Suffix',
            'Gender',
            'Birthdate',
            'Address',
            'Contact Number',
            'Guardian Name',
            'Guardian Contact',
            'Status',
        ];
    }

    /**
     * Define the sheet title.
     */
    public function title(): string
    {
        return 'Student Masterlist';
    }

    /**
     * Apply styles to the worksheet.
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
