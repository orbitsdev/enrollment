<?php

namespace App\Models;

use App\Enums\SubjectType;
use App\Traits\ModelRelations\SubjectRelations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use SubjectRelations;

    protected $fillable = [
        'code',
        'name',
        'type',
        'hours',
        'prerequisite_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => SubjectType::class,
            'hours' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope a query to only include active subjects.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by subject type.
     */
    public function scopeByType(Builder $query, SubjectType $type): Builder
    {
        return $query->where('type', $type);
    }
}
