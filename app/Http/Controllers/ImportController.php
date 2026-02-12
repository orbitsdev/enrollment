<?php

namespace App\Http\Controllers;

use App\Enums\EnrollmentStatus;
use App\Enums\GradeRemarks;
use App\Enums\StudentStatus;
use App\Imports\GradeImport;
use App\Imports\StudentImport;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ImportController extends Controller
{
    /**
     * Display the import hub page.
     */
    public function index(): Response
    {
        return Inertia::render('import/Index');
    }

    /**
     * Upload and parse student Excel file for preview.
     */
    public function uploadStudents(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        $import = new StudentImport();
        Excel::import($import, $request->file('file'));

        return response()->json([
            'valid' => $import->valid->values(),
            'invalid' => $import->invalid->values(),
        ]);
    }

    /**
     * Confirm and import validated student rows.
     */
    public function confirmStudents(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'students' => ['required', 'array', 'min:1'],
            'students.*.lrn' => ['required', 'string', 'size:12'],
            'students.*.last_name' => ['required', 'string'],
            'students.*.first_name' => ['required', 'string'],
            'students.*.birthdate' => ['required', 'date'],
            'students.*.gender' => ['required', 'in:male,female'],
        ]);

        $imported = 0;
        $errors = [];

        foreach ($request->input('students') as $index => $data) {
            try {
                Student::create([
                    'lrn' => $data['lrn'],
                    'last_name' => $data['last_name'],
                    'first_name' => $data['first_name'],
                    'middle_name' => $data['middle_name'] ?? null,
                    'suffix' => $data['suffix'] ?? null,
                    'birthdate' => $data['birthdate'],
                    'gender' => $data['gender'],
                    'address' => $data['address'] ?? null,
                    'contact_number' => $data['contact_number'] ?? null,
                    'guardian_name' => $data['guardian_name'] ?? null,
                    'guardian_contact' => $data['guardian_contact'] ?? null,
                    'status' => StudentStatus::Active->value,
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = [
                    'row' => $index + 1,
                    'lrn' => $data['lrn'],
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'imported' => $imported,
            'errors' => $errors,
        ]);
    }

    /**
     * Upload and parse grade Excel file for preview.
     */
    public function uploadGrades(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        $import = new GradeImport();
        Excel::import($import, $request->file('file'));

        return response()->json([
            'valid' => $import->valid->values(),
            'invalid' => $import->invalid->values(),
        ]);
    }

    /**
     * Confirm and import validated grade rows.
     */
    public function confirmGrades(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'grades' => ['required', 'array', 'min:1'],
            'grades.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'grades.*.subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'grades.*.midterm' => ['nullable', 'numeric', 'min:50', 'max:100'],
            'grades.*.finals' => ['nullable', 'numeric', 'min:50', 'max:100'],
        ]);

        $imported = 0;
        $errors = [];

        foreach ($request->input('grades') as $index => $data) {
            try {
                // Find the enrollment for this student (most recent active one)
                $enrollment = Enrollment::where('student_id', $data['student_id'])
                    ->where('status', EnrollmentStatus::Enrolled)
                    ->latest()
                    ->first();

                if (!$enrollment) {
                    $errors[] = [
                        'row' => $index + 1,
                        'error' => 'No active enrollment found for student.',
                    ];
                    continue;
                }

                // Find or create the grade record
                $grade = Grade::where('enrollment_id', $enrollment->id)
                    ->where('subject_id', $data['subject_id'])
                    ->first();

                if ($grade) {
                    if ($grade->is_locked) {
                        $errors[] = [
                            'row' => $index + 1,
                            'error' => 'Grade is locked and cannot be modified.',
                        ];
                        continue;
                    }

                    $midterm = $data['midterm'] !== null ? (float) $data['midterm'] : $grade->midterm;
                    $finals = $data['finals'] !== null ? (float) $data['finals'] : $grade->finals;
                    $finalGrade = ($midterm !== null && $finals !== null)
                        ? round(($midterm + $finals) / 2, 2)
                        : null;

                    $grade->update([
                        'midterm' => $midterm,
                        'finals' => $finals,
                        'final_grade' => $finalGrade,
                        'remarks' => $finalGrade !== null
                            ? ($finalGrade >= 75 ? GradeRemarks::Passed : GradeRemarks::Failed)
                            : null,
                        'encoded_by' => auth()->id(),
                    ]);
                } else {
                    $midterm = $data['midterm'] !== null ? (float) $data['midterm'] : null;
                    $finals = $data['finals'] !== null ? (float) $data['finals'] : null;
                    $finalGrade = ($midterm !== null && $finals !== null)
                        ? round(($midterm + $finals) / 2, 2)
                        : null;

                    Grade::create([
                        'enrollment_id' => $enrollment->id,
                        'subject_id' => $data['subject_id'],
                        'midterm' => $midterm,
                        'finals' => $finals,
                        'final_grade' => $finalGrade,
                        'remarks' => $finalGrade !== null
                            ? ($finalGrade >= 75 ? GradeRemarks::Passed : GradeRemarks::Failed)
                            : null,
                        'encoded_by' => auth()->id(),
                    ]);
                }

                $imported++;
            } catch (\Exception $e) {
                $errors[] = [
                    'row' => $index + 1,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'imported' => $imported,
            'errors' => $errors,
        ]);
    }

    /**
     * Download an import template.
     */
    public function downloadTemplate(string $type): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        if ($type === 'students') {
            $sheet->setTitle('Students Template');
            $headers = ['lrn', 'last_name', 'first_name', 'middle_name', 'suffix', 'birthdate', 'gender', 'address', 'contact_number', 'guardian_name', 'guardian_contact'];

            foreach ($headers as $col => $header) {
                $sheet->setCellValueByColumnAndRow($col + 1, 1, $header);
            }

            // Sample row
            $sample = ['123456789012', 'Dela Cruz', 'Juan', 'Santos', '', '2008-05-15', 'male', 'Manila, Philippines', '09171234567', 'Maria Dela Cruz', '09179876543'];
            foreach ($sample as $col => $value) {
                $sheet->setCellValueByColumnAndRow($col + 1, 2, $value);
            }
        } elseif ($type === 'grades') {
            $sheet->setTitle('Grades Template');
            $headers = ['lrn', 'subject_code', 'midterm', 'finals'];

            foreach ($headers as $col => $header) {
                $sheet->setCellValueByColumnAndRow($col + 1, 1, $header);
            }

            // Sample row
            $sample = ['123456789012', 'ORAL-COM', '85', '88'];
            foreach ($sample as $col => $value) {
                $sheet->setCellValueByColumnAndRow($col + 1, 2, $value);
            }
        } else {
            abort(404, 'Template type not found.');
        }

        // Auto-size columns
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Bold headers
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);

        $filename = "{$type}-template.xlsx";
        $tempPath = storage_path("app/temp/{$filename}");

        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $filename)->deleteFileAfterSend();
    }
}
