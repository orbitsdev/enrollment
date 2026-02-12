<?php

namespace App\Actions\Report;

use App\Models\SchoolSetting;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class GenerateSF10
{
    /**
     * Generate SF10 (Permanent Record) PDF for a given student.
     */
    public function handle(Student $student): Response
    {
        $student->load([
            'enrollments' => function ($query) {
                $query->with([
                    'section.strand.track',
                    'section.adviser',
                    'semester.schoolYear',
                    'grades.subject',
                ])->orderBy('created_at');
            },
        ]);

        $schoolSettings = SchoolSetting::pluck('value', 'key')->toArray();

        $pdf = Pdf::loadView('pdf.sf10', [
            'student' => $student,
            'enrollments' => $student->enrollments,
            'school_name' => $schoolSettings['school_name'] ?? 'School Name',
            'school_address' => $schoolSettings['school_address'] ?? '',
            'school_id' => $schoolSettings['school_id'] ?? '',
        ]);

        $pdf->setPaper('legal', 'landscape');

        $filename = 'SF10-' . $student->lrn . '-' . now()->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }
}
