<?php

namespace App\Traits\ModelRelations;

use App\Models\Subject;
use App\Models\Track;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait StrandRelations
{
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'subject_strand')
            ->withPivot('grade_level', 'semester', 'sort_order')
            ->withTimestamps();
    }
}
