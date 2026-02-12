<?php

namespace App\Traits\ModelRelations;

use App\Models\Enrollment;
use App\Models\Semester;
use App\Models\Strand;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait SectionRelations
{
    public function strand(): BelongsTo
    {
        return $this->belongsTo(Strand::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function adviser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adviser_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }
}
