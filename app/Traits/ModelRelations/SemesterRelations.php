<?php

namespace App\Traits\ModelRelations;

use App\Models\SchoolYear;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait SemesterRelations
{
    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }
}
