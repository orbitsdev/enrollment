<?php

namespace App\Traits\ModelRelations;

use App\Models\Strand;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait TrackRelations
{
    public function strands(): HasMany
    {
        return $this->hasMany(Strand::class);
    }
}
