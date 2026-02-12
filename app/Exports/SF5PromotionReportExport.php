<?php

namespace App\Exports;

use App\Enums\EnrollmentStatus;
use App\Enums\GradeRemarks;
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

class SF5PromotionReportExport implements FromArray, WithTitle, ShouldAutoSize, WithStyles
{
    protected int $headerRows = 0;

    protected int $totalRows = 0;

    protected int $totalColumns = 0;

    public function __construct(
        protected Section $section,
    ) {}

    public function array(): array
    {
        $settings = SchoolSetting::pluck('value', 'key')->toArray();
        $this->section->load(['strand.track', 'semester.schoolYear', 'adviser']);

        $schoolName = $settings['school_name'] ?? 'School Name';
        $schoolId = $settings['school_id'] ?? '';
        $division = $settings['division'] ?? '';
        $region = $settings['region'] ?? '';

        $semesterLabel = $this->section->semester?->label ?? '';
        $syName = $this->section->semester?->schoolYear?->name ?? '';
        $trackName = $this->section->strand?->track?->name ?? '';
        $strandName = $this->section->strand?->name ?? '';
        $adviserName = $this->section->adviser?->name ?? '';

        $enrollments = Enrollment::where('section_id', $this->section->id)
            ->where('status', EnrollmentStatus::Enrolled)
            ->with(['student', 'grades.subject'])
            ->get()
            ->sortBy('student.last_name')
            ->values();

        // Collect all subjects for this section
        $allSubjects = $enrollments->flatMap(fn ($e) => $e->grades->pluck('subject'))
            ->unique('id')
            ->sortBy('name')
            ->values();

        $rows = [];

        // Header rows
        $rows[] = ['SCHOOL FORM 5 (SF5) - REPORT ON PROMOTION AND LEVEL OF PROFICIENCY'];
        $rows[] = [''];
        $rows[] = ['School Name:', $schoolName, '', 'School ID:', $schoolId];
        $rows[] = ['Division:', $division, '', 'Region:', $region];
        $rows[] = ['School Year:', $syName, '', 'Semester:', $semesterLabel];
        $rows[] = ['Track:', $trackName, '', 'Strand:', $strandName];
        $rows[] = ['Grade Level:', $this->section->grade_level, '', 'Section:', $this->section->name];
        $rows[] = ['Adviser:', $adviserName];
        $rows[] = [''];

        // Column headings: No., LRN, Name, then each subject, then Gen. Average, Remarks, Status
        $headingRow = ['No.', 'LRN', 'Student Name'];
        foreach ($allSubjects as $subject) {
            $headingRow[] = $subject->code ?: $subject->name;
        }
        $headingRow[] = 'Gen. Average';
        $headingRow[] = 'Remarks';
        $headingRow[] = 'Status';

        $rows[] = $headingRow;
        $this->headerRows = count($rows);
        $this->totalColumns = count($headingRow);

        // Data rows
        $number = 0;
        $promotedCount = 0;
        $retainedCount = 0;
        $totalMale = 0;
        $totalFemale = 0;

        foreach ($enrollments as $enrollment) {
            $student = $enrollment->student;
            if (!$student) {
                continue;
            }

            $row = [
                ++$number,
                $student->lrn,
                $student->full_name,
            ];

            $gradesBySubject = $enrollment->grades->keyBy('subject_id');
            $gradeValues = [];

            foreach ($allSubjects as $subject) {
                $grade = $gradesBySubject->get($subject->id);
                $finalGrade = $grade?->final_grade;
                $row[] = $finalGrade !== null ? number_format((float) $finalGrade, 2) : '';
                if ($finalGrade !== null) {
                    $gradeValues[] = (float) $finalGrade;
                }
            }

            // General Average
            $genAvg = count($gradeValues) > 0 ? array_sum($gradeValues) / count($gradeValues) : null;
            $row[] = $genAvg !== null ? number_format($genAvg, 2) : '';

            // Remarks
            $passingGrade = (float) ($settings['passing_grade'] ?? 75);
            $allPassed = $enrollment->grades->every(fn ($g) => $g->final_grade !== null && (float) $g->final_grade >= $passingGrade);
            $hasGrades = $enrollment->grades->contains(fn ($g) => $g->final_grade !== null);

            if (!$hasGrades) {
                $remarks = 'No Grades';
                $status = '-';
            } elseif ($allPassed) {
                $remarks = 'Passed';
                $status = 'Promoted';
                $promotedCount++;
            } else {
                $remarks = 'Failed';
                $status = 'Retained';
                $retainedCount++;
            }

            // Handle transferred/dropped
            if ($enrollment->status === EnrollmentStatus::Transferred) {
                $status = 'Transferred';
            } elseif ($enrollment->status === EnrollmentStatus::Dropped) {
                $status = 'Dropped';
            }

            $row[] = $remarks;
            $row[] = $status;

            $rows[] = $row;

            if ($student->gender === 'male') {
                $totalMale++;
            } else {
                $totalFemale++;
            }
        }

        // Summary
        $rows[] = [''];
        $rows[] = ['SUMMARY'];
        $rows[] = ['Total Enrolled:', $enrollments->count(), '', 'Male:', $totalMale, '', 'Female:', $totalFemale];
        $rows[] = ['Promoted:', $promotedCount, '', 'Retained:', $retainedCount];

        $this->totalRows = count($rows);

        return $rows;
    }

    public function title(): string
    {
        return 'SF5 - ' . $this->section->name;
    }

    public function styles(Worksheet $sheet): array
    {
        // Merge title row
        $lastCol = $this->getColumnLetter($this->totalColumns);
        $sheet->mergeCells("A1:{$lastCol}1");

        $styles = [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            $this->headerRows => [
                'font' => ['bold' => true, 'size' => 9],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'wrapText' => true,
                ],
            ],
        ];

        // Data row borders
        if ($this->totalRows > $this->headerRows) {
            $lastDataRow = $this->headerRows + ($this->totalRows - $this->headerRows - 4); // minus summary rows
            if ($lastDataRow > $this->headerRows) {
                $dataRange = "A{$this->headerRows}:{$lastCol}{$lastDataRow}";
                $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Center-align grade columns (D onward to the Gen. Average column)
                $gradeStartCol = 'D';
                $gradeRange = "{$gradeStartCol}" . ($this->headerRows + 1) . ":{$lastCol}{$lastDataRow}";
                $sheet->getStyle($gradeRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }

        return $styles;
    }

    private function getColumnLetter(int $columnNumber): string
    {
        $letter = '';
        while ($columnNumber > 0) {
            $columnNumber--;
            $letter = chr(65 + ($columnNumber % 26)) . $letter;
            $columnNumber = intdiv($columnNumber, 26);
        }

        return $letter;
    }
}
