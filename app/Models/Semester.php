<?php

namespace App\Models;

use App\Traits\ModelRelations\SemesterRelations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use SemesterRelations;

    protected $fillable = [
        'school_year_id',
        'number',
        'is_active',
        'enrollment_open',
    ];

    protected $appends = [
        'label',
        'full_label',
    ];

    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'is_active' => 'boolean',
            'enrollment_open' => 'boolean',
        ];
    }

    /**
     * Scope a query to only include active semesters.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to get the current active semester.
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the semester label (e.g., "1st Semester" or "2nd Semester").
     */
    protected function label(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->number === 1 ? '1st Semester' : '2nd Semester',
        );
    }

    /**
     * Get the full semester label (e.g., "1st Semester - SY 2024-2025").
     */
    protected function fullLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->label . ' - SY ' . $this->schoolYear?->name,
        );
    }
}
