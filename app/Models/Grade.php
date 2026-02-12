<?php

namespace App\Models;

use App\Enums\GradeRemarks;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
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

    /**
     * Get the enrollment for this grade.
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Get the subject for this grade.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the user who encoded this grade.
     */
    public function encoder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }
}
