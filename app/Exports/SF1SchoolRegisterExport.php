<?php

namespace App\Exports;

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\SchoolSetting;
use App\Models\Section;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SF1SchoolRegisterExport implements FromArray, WithTitle, ShouldAutoSize, WithStyles
{
    protected array $rows = [];

    protected int $headerRows = 0;

    public function __construct(
        protected Section $section,
    ) {}

    public function array(): array
    {
        $settings = SchoolSetting::pluck('value', 'key')->toArray();
        $this->section->load(['strand.track', 'semester.schoolYear', 'adviser']);

        $schoolName = $settings['school_name'] ?? 'School Name';
        $schoolId = $settings['school_id'] ?? '';
        $schoolAddress = $settings['school_address'] ?? '';
        $division = $settings['division'] ?? '';
        $region = $settings['region'] ?? '';

        $semesterLabel = $this->section->semester?->label ?? '';
        $syName = $this->section->semester?->schoolYear?->name ?? '';
        $trackName = $this->section->strand?->track?->name ?? '';
        $strandName = $this->section->strand?->name ?? '';
        $adviserName = $this->section->adviser?->name ?? '';

        $enrollments = Enrollment::where('section_id', $this->section->id)
            ->where('status', EnrollmentStatus::Enrolled)
            ->with('student')
            ->get()
            ->sortBy('student.last_name')
            ->values();

        $rows = [];

        // Header rows
        $rows[] = ['SCHOOL FORM 1 (SF1) - SCHOOL REGISTER'];
        $rows[] = [''];
        $rows[] = ['School Name:', $schoolName, '', 'School ID:', $schoolId];
        $rows[] = ['School Address:', $schoolAddress, '', 'Division:', $division];
        $rows[] = ['Region:', $region];
        $rows[] = [''];
        $rows[] = ['School Year:', $syName, '', 'Semester:', $semesterLabel];
        $rows[] = ['Track:', $trackName, '', 'Strand:', $strandName];
        $rows[] = ['Grade Level:', $this->section->grade_level, '', 'Section:', $this->section->name];
        $rows[] = ['Adviser:', $adviserName];
        $rows[] = [''];

        // Column headings
        $rows[] = [
            'No.',
            'LRN',
            'Last Name',
            'First Name',
            'Middle Name',
            'Suffix',
            'Sex',
            'Birth Date',
            'Address',
            'Guardian Name',
            'Guardian Relationship',
            'Guardian Contact',
            'Contact Number',
            'Status',
        ];

        $this->headerRows = count($rows);

        // Data rows
        $number = 0;
        foreach ($enrollments as $enrollment) {
            $student = $enrollment->student;
            if (!$student) {
                continue;
            }

            $rows[] = [
                ++$number,
                $student->lrn,
                $student->last_name,
                $student->first_name,
                $student->middle_name ?? '',
                $student->suffix ?? '',
                $student->gender === 'male' ? 'M' : 'F',
                $student->birthdate?->format('m/d/Y') ?? '',
                $student->address ?? '',
                $student->guardian_name ?? '',
                $student->guardian_relationship ?? '',
                $student->guardian_contact ?? '',
                $student->contact_number ?? '',
                $enrollment->status->label(),
            ];
        }

        // Summary
        $rows[] = [''];
        $maleCount = $enrollments->filter(fn ($e) => $e->student?->gender === 'male')->count();
        $femaleCount = $enrollments->filter(fn ($e) => $e->student?->gender === 'female')->count();
        $rows[] = ['Total Enrolled:', $enrollments->count(), '', 'Male:', $maleCount, '', 'Female:', $femaleCount];

        $this->rows = $rows;

        return $rows;
    }

    public function title(): string
    {
        return 'SF1 - ' . $this->section->name;
    }

    public function styles(Worksheet $sheet): array
    {
        // Title row
        $sheet->mergeCells('A1:N1');

        $styles = [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            $this->headerRows => [
                'font' => ['bold' => true],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];

        // Bold the header info rows
        for ($i = 3; $i <= 10; $i++) {
            $styles[$i] = ['font' => ['bold' => false]];
            $sheet->getStyle("A{$i}")->getFont()->setBold(true);
            if ($i <= 9) {
                $sheet->getStyle("D{$i}")->getFont()->setBold(true);
            }
        }

        // Data row borders
        $lastDataRow = $this->headerRows + count($this->rows) - $this->headerRows;
        if ($lastDataRow > $this->headerRows) {
            $dataRange = 'A' . $this->headerRows . ':N' . $lastDataRow;
            $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        return $styles;
    }
}
