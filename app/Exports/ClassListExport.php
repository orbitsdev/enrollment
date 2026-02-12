<?php

namespace App\Exports;

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\Section;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClassListExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function __construct(
        protected Section $section,
    ) {}

    /**
     * Return the collection of data rows.
     */
    public function collection(): \Illuminate\Support\Collection
    {
        $enrollments = Enrollment::where('section_id', $this->section->id)
            ->where('status', EnrollmentStatus::Enrolled)
            ->with('student')
            ->get()
            ->sortBy('student.last_name');

        $number = 0;

        return $enrollments->map(fn ($enrollment) => [
            'no' => ++$number,
            'lrn' => $enrollment->student?->lrn,
            'last_name' => $enrollment->student?->last_name,
            'first_name' => $enrollment->student?->first_name,
            'middle_name' => $enrollment->student?->middle_name,
            'suffix' => $enrollment->student?->suffix,
            'gender' => $enrollment->student?->gender,
            'status' => $enrollment->status->label(),
        ]);
    }

    /**
     * Define the headings row.
     */
    public function headings(): array
    {
        return [
            'No.',
            'LRN',
            'Last Name',
            'First Name',
            'Middle Name',
            'Suffix',
            'Gender',
            'Status',
        ];
    }

    /**
     * Define the sheet title.
     */
    public function title(): string
    {
        return 'Class List - ' . $this->section->name;
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
