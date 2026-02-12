<?php

namespace App\Traits\ModelScope;

use App\Enums\EnrollmentStatus;
use Illuminate\Database\Eloquent\Builder;

trait SectionScopes
{
    public function scopeWithEnrolledCount(Builder $query): Builder
    {
        return $query->withCount(['enrollments as enrolled_count' => function ($q) {
            $q->where('status', EnrollmentStatus::Enrolled);
        }]);
    }

    public function scopeByStrand(Builder $query, int $strandId): Builder
    {
        return $query->where('strand_id', $strandId);
    }

    public function scopeBySemester(Builder $query, int $semesterId): Builder
    {
        return $query->where('semester_id', $semesterId);
    }

    public function scopeByGradeLevel(Builder $query, int $gradeLevel): Builder
    {
        return $query->where('grade_level', $gradeLevel);
    }

    public function scopeCurrentSemester(Builder $query): Builder
    {
        return $query->whereHas('semester', fn (Builder $q) => $q->where('is_active', true));
    }
}
