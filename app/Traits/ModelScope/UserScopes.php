<?php

namespace App\Traits\ModelScope;

use Illuminate\Database\Eloquent\Builder;

trait UserScopes
{
    public function scopeTeachers(Builder $query): Builder
    {
        return $query->role('teacher');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
