<?php

namespace App\Enums;

enum SubjectType: string
{
    case Core = 'core';
    case Specialized = 'specialized';
    case Applied = 'applied';

    public function label(): string
    {
        return match ($this) {
            self::Core => 'Core',
            self::Specialized => 'Specialized',
            self::Applied => 'Applied',
        };
    }
}
