<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    /**
     * Determine whether the user can view any models.
     * Admin and registrar always; teacher if student in their section; student if self.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'registrar', 'teacher', 'student']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Student $student): bool
    {
        if ($user->hasAnyRole(['admin', 'registrar'])) {
            return true;
        }

        // Teacher can view if the student is in one of their sections
        if ($user->hasRole('teacher')) {
            return $student->enrollments()
                ->whereHas('section', function ($q) use ($user) {
                    $q->where('adviser_id', $user->id);
                })
                ->exists();
        }

        // Student can view self (if student has a linked student record)
        if ($user->hasRole('student')) {
            return $student->lrn === $user->email; // Adjust based on how students are linked
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'registrar']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Student $student): bool
    {
        return $user->hasAnyRole(['admin', 'registrar']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Student $student): bool
    {
        return $user->hasRole('admin');
    }
}
