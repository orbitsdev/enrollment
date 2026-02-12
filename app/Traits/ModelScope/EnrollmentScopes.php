<?php

namespace App\Traits\ModelScope;

use App\Enums\EnrollmentStatus;
use Illuminate\Database\Eloquent\Builder;

trait EnrollmentScopes
{
    public function scopeCurrentSemester(Builder $query): Builder
    {
        return $query->whereHas('semester', fn (Builder $q) => $q->where('is_active', true));
    }

    public function scopeByStatus(Builder $query, EnrollmentStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeEnrolled(Builder $query): Builder
    {
        return $query->where('status', EnrollmentStatus::Enrolled);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', EnrollmentStatus::Pending);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (! $search) {
            return $query;
        }

        return $query->whereHas('student', function (Builder $q) use ($search) {
            $q->where('lrn', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('first_name', 'like', "%{$search}%");
        });
    }

    public function scopeByStrand(Builder $query, ?int $strandId): Builder
    {
        if (! $strandId) {
            return $query;
        }

        return $query->whereHas('section', fn (Builder $q) => $q->where('strand_id', $strandId));
    }

    public function scopeBySectionId(Builder $query, ?int $sectionId): Builder
    {
        if (! $sectionId) {
            return $query;
        }

        return $query->where('section_id', $sectionId);
    }
}
