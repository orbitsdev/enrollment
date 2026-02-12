<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Registrar = 'registrar';
    case Teacher = 'teacher';
    case Student = 'student';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Registrar => 'Registrar',
            self::Teacher => 'Teacher',
            self::Student => 'Student',
        };
    }
}
