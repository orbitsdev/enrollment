<?php

namespace App\Actions\Report;

use App\Models\Enrollment;
use App\Models\SchoolSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class GenerateSF9
{
    /**
     * Generate SF9 (Report Card) PDF for a given enrollment.
     */
    public function handle(Enrollment $enrollment): Response
    {
        $enrollment->load([
            'student',
            'section.strand.track',
            'section.adviser',
            'semester.schoolYear',
            'grades.subject',
        ]);

        $schoolSettings = SchoolSetting::pluck('value', 'key')->toArray();

        $pdf = Pdf::loadView('pdf.sf9', [
            'enrollment' => $enrollment,
            'student' => $enrollment->student,
            'section' => $enrollment->section,
            'semester' => $enrollment->semester,
            'grades' => $enrollment->grades,
            'school_name' => $schoolSettings['school_name'] ?? 'School Name',
            'school_address' => $schoolSettings['school_address'] ?? '',
            'school_id' => $schoolSettings['school_id'] ?? '',
        ]);

        $pdf->setPaper('legal', 'portrait');

        $filename = 'SF9-' . $enrollment->student->lrn . '-' . now()->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }
}
