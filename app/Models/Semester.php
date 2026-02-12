<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Semester extends Model
{
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
     * Get the school year that owns the semester.
     */
    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
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
