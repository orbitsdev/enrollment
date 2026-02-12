<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Strand extends Model
{
    protected $fillable = [
        'track_id',
        'name',
        'code',
        'is_active',
        'sort_order',
    ];

    protected $appends = [
        'full_name',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the track that owns the strand.
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    /**
     * Get the subjects for the strand.
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'subject_strand')
            ->withPivot('grade_level', 'semester', 'sort_order')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include active strands.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the full name (e.g., "Academic - STEM").
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->track?->name . ' - ' . $this->name,
        );
    }
}
