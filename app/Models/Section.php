<?php

namespace App\Models;

use App\Traits\ModelRelations\SectionRelations;
use App\Traits\ModelScope\SectionScopes;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use SectionRelations, SectionScopes;

    protected $fillable = [
        'name',
        'strand_id',
        'semester_id',
        'grade_level',
        'max_capacity',
        'adviser_id',
    ];

    protected function casts(): array
    {
        return [
            'grade_level' => 'integer',
            'max_capacity' => 'integer',
        ];
    }
}
