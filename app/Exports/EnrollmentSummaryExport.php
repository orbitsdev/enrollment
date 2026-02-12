<?php

namespace App\Exports;

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\Semester;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EnrollmentSummaryExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function __construct(
        protected ?Semester $semester = null,
    ) {}

    /**
     * Return the collection of data rows.
     */
    public function collection(): \Illuminate\Support\Collection
    {
        if (!$this->semester) {
            return collect();
        }

        $enrollments = Enrollment::where('semester_id', $this->semester->id)
            ->where('status', EnrollmentStatus::Enrolled)
            ->with('section.strand.track', 'student')
            ->get();

        return $enrollments->map(fn ($enrollment) => [
            'lrn' => $enrollment->student?->lrn,
            'student_name' => $enrollment->student?->full_name,
            'track' => $enrollment->section?->strand?->track?->name,
            'strand' => $enrollment->section?->strand?->name,
            'section' => $enrollment->section?->name,
            'grade_level' => $enrollment->section?->grade_level,
            'status' => $enrollment->status->label(),
        ]);
    }

    /**
     * Define the headings row.
     */
    public function headings(): array
    {
        return [
            'LRN',
            'Student Name',
            'Track',
            'Strand',
            'Section',
            'Grade Level',
            'Status',
        ];
    }

    /**
     * Define the sheet title.
     */
    public function title(): string
    {
        return 'Enrollment Summary';
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
