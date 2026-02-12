<?php

namespace App\Enums;

enum StudentStatus: string
{
    case Active = 'active';
    case Transferred = 'transferred';
    case Dropped = 'dropped';
    case Graduated = 'graduated';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Transferred => 'Transferred',
            self::Dropped => 'Dropped',
            self::Graduated => 'Graduated',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::Transferred => 'blue',
            self::Dropped => 'red',
            self::Graduated => 'purple',
        };
    }
}
