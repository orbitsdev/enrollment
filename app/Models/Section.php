<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    protected $fillable = [
        'name',
        'strand_id',
        'semester_id',
        'grade_level',
        'max_capacity',
        'adviser_id',
    ];

    protected function casts(): array
    {
        return [
            'grade_level' => 'integer',
            'max_capacity' => 'integer',
        ];
    }

    /**
     * Get the strand that the section belongs to.
     */
    public function strand(): BelongsTo
    {
        return $this->belongsTo(Strand::class);
    }

    /**
     * Get the semester that the section belongs to.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Get the adviser (user) for this section.
     */
    public function adviser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adviser_id');
    }

    /**
     * Get the enrollments for this section.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Scope a query to add enrolled count (enrollments with status = enrolled).
     */
    public function scopeWithEnrolledCount(Builder $query): Builder
    {
        return $query->withCount(['enrollments as enrolled_count' => function ($q) {
            $q->where('status', EnrollmentStatus::Enrolled);
        }]);
    }

    /**
     * Scope a query to filter sections by strand.
     */
    public function scopeByStrand(Builder $query, int $strandId): Builder
    {
        return $query->where('strand_id', $strandId);
    }

    /**
     * Scope a query to filter sections by semester.
     */
    public function scopeBySemester(Builder $query, int $semesterId): Builder
    {
        return $query->where('semester_id', $semesterId);
    }

    /**
     * Scope a query to filter sections by grade level.
     */
    public function scopeByGradeLevel(Builder $query, int $gradeLevel): Builder
    {
        return $query->where('grade_level', $gradeLevel);
    }
}
