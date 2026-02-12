<?php

namespace App\Traits\ModelScope;

use Illuminate\Database\Eloquent\Builder;

trait GradeScopes
{
    public function scopeLocked(Builder $query): Builder
    {
        return $query->where('is_locked', true);
    }

    public function scopeBySubject(Builder $query, int $subjectId): Builder
    {
        return $query->where('subject_id', $subjectId);
    }
}
