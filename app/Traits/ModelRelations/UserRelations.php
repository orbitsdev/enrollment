<?php

namespace App\Traits\ModelRelations;

use App\Models\AuditLog;
use App\Models\Grade;
use App\Models\Section;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait UserRelations
{
    public function advisedSections(): HasMany
    {
        return $this->hasMany(Section::class, 'adviser_id');
    }

    public function encodedGrades(): HasMany
    {
        return $this->hasMany(Grade::class, 'encoded_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
