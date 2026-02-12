<?php

namespace App\Models;

use App\Enums\GradeRemarks;
use App\Traits\ModelRelations\GradeRelations;
use App\Traits\ModelScope\GradeScopes;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use GradeRelations, GradeScopes;

    protected $fillable = [
        'enrollment_id',
        'subject_id',
        'midterm',
        'finals',
        'final_grade',
        'remarks',
        'is_locked',
        'encoded_by',
    ];

    protected function casts(): array
    {
        return [
            'midterm' => 'decimal:2',
            'finals' => 'decimal:2',
            'final_grade' => 'decimal:2',
            'remarks' => GradeRemarks::class,
            'is_locked' => 'boolean',
        ];
    }
}
