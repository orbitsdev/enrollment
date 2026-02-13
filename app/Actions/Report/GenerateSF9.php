<?php

namespace App\Actions\Report;

use App\Models\Enrollment;
use App\Models\SchoolSetting;
use Spatie\LaravelPdf\Facades\Pdf;
use Symfony\Component\HttpFoundation\Response;

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

        $filename = 'SF9-' . $enrollment->student->lrn . '-' . now()->format('Ymd') . '.pdf';
        $tempPath = storage_path('app/temp/' . $filename);

        if (! is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        Pdf::view('pdf.sf9', [
            'enrollment' => $enrollment,
            'student' => $enrollment->student,
            'section' => $enrollment->section,
            'semester' => $enrollment->semester,
            'grades' => $enrollment->grades,
            'school_name' => $schoolSettings['school_name'] ?? 'School Name',
            'school_address' => $schoolSettings['school_address'] ?? '',
            'school_id' => $schoolSettings['school_id'] ?? '',
        ])
            ->withBrowsershot(function (\Spatie\Browsershot\Browsershot $browsershot) {
                if (file_exists('/usr/bin/google-chrome-stable')) {
                    $browsershot->setChromePath('/usr/bin/google-chrome-stable');
                } elseif (file_exists('/usr/bin/chromium-browser')) {
                    $browsershot->setChromePath('/usr/bin/chromium-browser');
                }
                $browsershot->noSandbox();
            })
            ->format('legal')
            ->save($tempPath);

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(true);
    }
}
