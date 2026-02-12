<?php

namespace App\Traits\ModelScope;

use App\Enums\StudentStatus;
use Illuminate\Database\Eloquent\Builder;

trait StudentScopes
{
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('lrn', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('first_name', 'like', "%{$search}%");
        });
    }

    public function scopeByStatus(Builder $query, StudentStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByStrand(Builder $query, ?int $strandId): Builder
    {
        if (! $strandId) {
            return $query;
        }

        return $query->whereHas('enrollments.section', function ($q) use ($strandId) {
            $q->where('strand_id', $strandId);
        });
    }

    public function scopeByGradeLevel(Builder $query, ?int $level): Builder
    {
        if (! $level) {
            return $query;
        }

        return $query->whereHas('enrollments.section', function ($q) use ($level) {
            $q->where('grade_level', $level);
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', StudentStatus::Active);
    }
}
