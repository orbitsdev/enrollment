<?php

namespace App\Models;

use App\Enums\SubjectType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Subject extends Model
{
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
     * Get the prerequisite subject.
     */
    public function prerequisite(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'prerequisite_id');
    }

    /**
     * Get the subjects that depend on this subject.
     */
    public function dependents(): HasMany
    {
        return $this->hasMany(Subject::class, 'prerequisite_id');
    }

    /**
     * Get the strands for the subject.
     */
    public function strands(): BelongsToMany
    {
        return $this->belongsToMany(Strand::class, 'subject_strand')
            ->withPivot('grade_level', 'semester', 'sort_order')
            ->withTimestamps();
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
