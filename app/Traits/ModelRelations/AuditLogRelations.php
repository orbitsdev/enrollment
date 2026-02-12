<?php

namespace App\Traits\ModelRelations;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait AuditLogRelations
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
