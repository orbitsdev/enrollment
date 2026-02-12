<?php

namespace App\Traits\ModelRelations;

use App\Models\Semester;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait SchoolYearRelations
{
    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class);
    }
}
