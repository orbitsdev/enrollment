<?php

namespace App\Models;

use App\Enums\StudentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Student extends Model
{
    protected $fillable = [
        'lrn',
        'last_name',
        'first_name',
        'middle_name',
        'suffix',
        'birthdate',
        'gender',
        'address',
        'contact_number',
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
            'birthdate' => 'date',
        ];
    }

    /**
     * Get the student's enrollments.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get the student's grades through enrollments.
     */
    public function grades(): HasManyThrough
    {
        return $this->hasManyThrough(Grade::class, Enrollment::class);
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

    /**
     * Scope a query to search students by LRN, last name, or first name.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('lrn', 'like', "%{$term}%")
              ->orWhere('last_name', 'like', "%{$term}%")
              ->orWhere('first_name', 'like', "%{$term}%");
        });
    }

    /**
     * Scope a query to filter by student status.
     */
    public function scopeByStatus(Builder $query, StudentStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter students by strand via enrollments -> sections.
     */
    public function scopeByStrand(Builder $query, int $strandId): Builder
    {
        return $query->whereHas('enrollments.section', function ($q) use ($strandId) {
            $q->where('strand_id', $strandId);
        });
    }

    /**
     * Scope a query to filter students by grade level via enrollments -> sections.
     */
    public function scopeByGradeLevel(Builder $query, int $level): Builder
    {
        return $query->whereHas('enrollments.section', function ($q) use ($level) {
            $q->where('grade_level', $level);
        });
    }
}
