<?php

namespace App\Models;

use App\Traits\ModelRelations\StrandRelations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Strand extends Model
{
    use StrandRelations;

    protected $fillable = [
        'track_id',
        'name',
        'code',
        'course',
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
