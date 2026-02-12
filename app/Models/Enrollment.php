<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use App\Traits\ModelRelations\EnrollmentRelations;
use App\Traits\ModelScope\EnrollmentScopes;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use EnrollmentRelations, EnrollmentScopes;

    protected $fillable = [
        'student_id',
        'section_id',
        'semester_id',
        'status',
        'remarks',
        'enrolled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => EnrollmentStatus::class,
            'enrolled_at' => 'datetime',
        ];
    }
}
