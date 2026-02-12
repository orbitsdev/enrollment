<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>School Form 10 - Permanent Record</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 9px; line-height: 1.3; color: #333; }
        .container { padding: 15px; }
        .header { text-align: center; margin-bottom: 15px; }
        .header h1 { font-size: 13px; text-transform: uppercase; margin-bottom: 2px; }
        .header h2 { font-size: 11px; margin-bottom: 2px; }
        .header h3 { font-size: 10px; font-weight: normal; margin-bottom: 5px; }
        .header .form-title { font-size: 14px; font-weight: bold; border-top: 2px solid #333; border-bottom: 2px solid #333; padding: 4px 0; margin-top: 8px; }
        .section-title { font-size: 11px; font-weight: bold; margin: 12px 0 5px 0; padding: 3px; background-color: #f0f0f0; border: 1px solid #333; }
        .info-table { width: 100%; margin-bottom: 10px; border-collapse: collapse; }
        .info-table td { padding: 2px 4px; }
        .info-table .label { font-weight: bold; width: 100px; }
        .info-table .value { border-bottom: 1px solid #333; }
        .grades-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .grades-table th, .grades-table td { border: 1px solid #333; padding: 3px 5px; text-align: center; font-size: 8px; }
        .grades-table th { background-color: #f0f0f0; font-weight: bold; text-transform: uppercase; }
        .grades-table td.subject-name { text-align: left; font-size: 8px; }
        .grades-table .passed { color: #16a34a; }
        .grades-table .failed { color: #dc2626; }
        .semester-header { background-color: #e0e0e0; font-weight: bold; text-align: left; padding: 4px 8px; font-size: 9px; }
        .signature-section { margin-top: 30px; width: 100%; }
        .signature-section table { width: 100%; }
        .signature-section td { text-align: center; padding: 5px 20px; }
        .signature-line { border-top: 1px solid #333; padding-top: 2px; margin-top: 30px; }
        .footer { margin-top: 20px; text-align: center; font-size: 7px; color: #666; }
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
            <div class="form-title">SCHOOL FORM 10 (SF10) - LEARNER'S PERMANENT ACADEMIC RECORD</div>
        </div>

        <div class="section-title">LEARNER'S INFORMATION</div>
        <table class="info-table">
            <tr>
                <td class="label">LRN:</td>
                <td class="value">{{ $student->lrn }}</td>
                <td class="label">Name:</td>
                <td class="value">{{ $student->full_name }}</td>
            </tr>
            <tr>
                <td class="label">Birthdate:</td>
                <td class="value">{{ $student->birthdate?->format('F d, Y') }}</td>
                <td class="label">Gender:</td>
                <td class="value">{{ ucfirst($student->gender) }}</td>
            </tr>
            <tr>
                <td class="label">Address:</td>
                <td class="value" colspan="3">{{ $student->address ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Guardian:</td>
                <td class="value">{{ $student->guardian_name ?? 'N/A' }}</td>
                <td class="label">Contact:</td>
                <td class="value">{{ $student->guardian_contact ?? 'N/A' }}</td>
            </tr>
        </table>

        <div class="section-title">SCHOLASTIC RECORD</div>

        @forelse($enrollments as $enrollment)
            <table class="grades-table">
                <thead>
                    <tr>
                        <td class="semester-header" colspan="6">
                            {{ $enrollment->semester?->schoolYear?->name ?? '' }} -
                            {{ $enrollment->semester?->label ?? '' }} |
                            Grade {{ $enrollment->section?->grade_level ?? '' }} -
                            {{ $enrollment->section?->name ?? '' }} |
                            {{ $enrollment->section?->strand?->track?->name ?? '' }} /
                            {{ $enrollment->section?->strand?->name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th style="width: 35%;">Subject</th>
                        <th style="width: 13%;">Midterm</th>
                        <th style="width: 13%;">Finals</th>
                        <th style="width: 13%;">Final Grade</th>
                        <th style="width: 13%;">Remarks</th>
                        <th style="width: 13%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enrollment->grades as $grade)
                        <tr>
                            <td class="subject-name">{{ $grade->subject?->name ?? 'N/A' }}</td>
                            <td>{{ $grade->midterm ?? '-' }}</td>
                            <td>{{ $grade->finals ?? '-' }}</td>
                            <td>{{ $grade->final_grade ?? '-' }}</td>
                            <td class="{{ $grade->remarks?->value === 'passed' ? 'passed' : ($grade->remarks?->value === 'failed' ? 'failed' : '') }}">
                                {{ $grade->remarks?->label() ?? '-' }}
                            </td>
                            <td>{{ $grade->is_locked ? 'Final' : 'Draft' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No grades recorded for this semester.</td>
                        </tr>
                    @endforelse
                </tbody>
                @php
                    $gradedItems = $enrollment->grades->where('final_grade', '!=', null);
                    $avg = $gradedItems->count() > 0 ? round($gradedItems->avg('final_grade'), 2) : null;
                @endphp
                @if($avg)
                    <tfoot>
                        <tr>
                            <th colspan="3" style="text-align: right;">General Average:</th>
                            <th>{{ $avg }}</th>
                            <th colspan="2" class="{{ $avg >= 75 ? 'passed' : 'failed' }}">
                                {{ $avg >= 75 ? 'Passed' : 'Failed' }}
                            </th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        @empty
            <p style="text-align: center; padding: 20px;">No enrollment records found.</p>
        @endforelse

        <div class="signature-section">
            <table>
                <tr>
                    <td>
                        <div class="signature-line">Registrar / Authorized Person</div>
                    </td>
                    <td>
                        <div class="signature-line">School Principal</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>This is a system-generated document. Generated on {{ now()->format('F d, Y h:i A') }}</p>
        </div>
    </div>
</body>
</html>
