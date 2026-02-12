<?php

namespace App\Traits\ModelRelations;

use App\Models\Strand;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait SubjectRelations
{
    public function prerequisite(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'prerequisite_id');
    }

    public function dependents(): HasMany
    {
        return $this->hasMany(Subject::class, 'prerequisite_id');
    }

    public function strands(): BelongsToMany
    {
        return $this->belongsToMany(Strand::class, 'subject_strand')
            ->withPivot('grade_level', 'semester', 'sort_order')
            ->withTimestamps();
    }
}
