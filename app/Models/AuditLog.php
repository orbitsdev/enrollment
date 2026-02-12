<?php

namespace App\Models;

use App\Traits\ModelRelations\AuditLogRelations;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use AuditLogRelations;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }
}
