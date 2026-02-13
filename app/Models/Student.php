<?php

namespace App\Models;

use App\Enums\StudentStatus;
use App\Traits\ModelRelations\StudentRelations;
use App\Traits\ModelScope\StudentScopes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use StudentRelations, StudentScopes;

    protected $fillable = [
        'lrn',
        'last_name',
        'first_name',
        'middle_name',
        'suffix',
        'birthdate',
        'gender',
        'religion',
        'learning_modality',
        'address',
        'contact_number',
        'father_name',
        'mother_name',
        'guardian_name',
        'guardian_contact',
        'guardian_relationship',
        'previous_school',
        'status',
    ];

    protected $appends = [
        'full_name',
    ];

    protected function casts(): array
    {
        return [
            'status' => StudentStatus::class,
            'birthdate' => 'date:Y-m-d',
        ];
    }

    /**
     * Get the student's full name formatted as "Last, First Middle Suffix".
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: function () {
                $name = $this->last_name . ', ' . $this->first_name;

                if ($this->middle_name) {
                    $name .= ' ' . $this->middle_name;
                }

                if ($this->suffix) {
                    $name .= ' ' . $this->suffix;
                }

                return $name;
            },
        );
    }
}
