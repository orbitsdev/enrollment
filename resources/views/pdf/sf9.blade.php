<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>School Form 9 - Report Card</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; line-height: 1.4; color: #333; }
        .container { padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 14px; text-transform: uppercase; margin-bottom: 2px; }
        .header h2 { font-size: 12px; margin-bottom: 2px; }
        .header h3 { font-size: 11px; font-weight: normal; margin-bottom: 10px; }
        .header .form-title { font-size: 16px; font-weight: bold; border-top: 2px solid #333; border-bottom: 2px solid #333; padding: 5px 0; margin-top: 10px; }
        .info-table { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .info-table td { padding: 3px 5px; }
        .info-table .label { font-weight: bold; width: 120px; }
        .info-table .value { border-bottom: 1px solid #333; }
        .grades-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .grades-table th, .grades-table td { border: 1px solid #333; padding: 5px 8px; text-align: center; }
        .grades-table th { background-color: #f0f0f0; font-weight: bold; font-size: 9px; text-transform: uppercase; }
        .grades-table td.subject-name { text-align: left; }
        .grades-table .passed { color: #16a34a; }
        .grades-table .failed { color: #dc2626; }
        .summary { margin-top: 20px; }
        .summary-table { width: 50%; border-collapse: collapse; }
        .summary-table td { padding: 3px 5px; }
        .signature-section { margin-top: 40px; display: table; width: 100%; }
        .signature-block { display: table-cell; width: 50%; text-align: center; }
        .signature-line { border-top: 1px solid #333; width: 200px; margin: 0 auto; padding-top: 3px; }
        .footer { margin-top: 30px; text-align: center; font-size: 8px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h3>Republic of the Philippines</h3>
            <h3>Department of Education</h3>
            <h1>{{ $school_name }}</h1>
            <h3>{{ $school_address }}</h3>
            @if($school_id)
                <h3>School ID: {{ $school_id }}</h3>
            @endif
            <div class="form-title">SCHOOL FORM 9 (SF9) - LEARNER PROGRESS REPORT CARD</div>
        </div>

        <table class="info-table">
            <tr>
                <td class="label">Name:</td>
                <td class="value">{{ $student->full_name }}</td>
                <td class="label">LRN:</td>
                <td class="value">{{ $student->lrn }}</td>
            </tr>
            <tr>
                <td class="label">Grade Level:</td>
                <td class="value">{{ $section->grade_level }}</td>
                <td class="label">Section:</td>
                <td class="value">{{ $section->name }}</td>
            </tr>
            <tr>
                <td class="label">Track/Strand:</td>
                <td class="value">{{ $section->strand?->track?->name }} / {{ $section->strand?->name }}</td>
                <td class="label">School Year:</td>
                <td class="value">{{ $semester->schoolYear?->name }}</td>
            </tr>
            <tr>
                <td class="label">Semester:</td>
                <td class="value">{{ $semester->label }}</td>
                <td class="label">Adviser:</td>
                <td class="value">{{ $section->adviser?->name ?? 'N/A' }}</td>
            </tr>
        </table>

        <table class="grades-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Subject</th>
                    <th style="width: 15%;">Midterm</th>
                    <th style="width: 15%;">Finals</th>
                    <th style="width: 15%;">Final Grade</th>
                    <th style="width: 15%;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($grades as $grade)
                    <tr>
                        <td class="subject-name">{{ $grade->subject?->name ?? 'N/A' }}</td>
                        <td>{{ $grade->midterm ?? '-' }}</td>
                        <td>{{ $grade->finals ?? '-' }}</td>
                        <td>{{ $grade->final_grade ?? '-' }}</td>
                        <td class="{{ $grade->remarks?->value === 'passed' ? 'passed' : ($grade->remarks?->value === 'failed' ? 'failed' : '') }}">
                            {{ $grade->remarks?->label() ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No grades recorded.</td>
                    </tr>
                @endforelse
            </tbody>
            @php
                $gradedItems = $grades->where('final_grade', '!=', null);
                $generalAverage = $gradedItems->count() > 0 ? round($gradedItems->avg('final_grade'), 2) : null;
            @endphp
            @if($generalAverage)
                <tfoot>
                    <tr>
                        <th colspan="3" style="text-align: right;">General Average:</th>
                        <th>{{ $generalAverage }}</th>
                        <th class="{{ $generalAverage >= 75 ? 'passed' : 'failed' }}">
                            {{ $generalAverage >= 75 ? 'Passed' : 'Failed' }}
                        </th>
                    </tr>
                </tfoot>
            @endif
        </table>

        <div class="signature-section">
            <div class="signature-block">
                <br><br><br>
                <div class="signature-line">Class Adviser</div>
            </div>
            <div class="signature-block">
                <br><br><br>
                <div class="signature-line">School Principal</div>
            </div>
        </div>

        <div class="footer">
            <p>Generated on {{ now()->format('F d, Y h:i A') }}</p>
        </div>
    </div>
</body>
</html>
