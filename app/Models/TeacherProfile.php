<?php

namespace App\Models;

use App\Traits\ModelRelations\TeacherProfileRelations;
use Illuminate\Database\Eloquent\Model;

class TeacherProfile extends Model
{
    use TeacherProfileRelations;

    protected $fillable = [
        'user_id',
        'employee_id',
        'position_title',
        'appointment_status',
        'sex',
        'birthdate',
        'contact_number',
        'address',
        'highest_degree',
        'degree_course',
        'degree_major',
        'school_graduated',
        'year_graduated',
        'prc_license_number',
        'prc_validity',
        'eligibility',
        'specialization',
        'date_hired',
        'teaching_hours_per_week',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'prc_validity' => 'date',
            'date_hired' => 'date',
            'year_graduated' => 'integer',
            'teaching_hours_per_week' => 'integer',
        ];
    }
}
