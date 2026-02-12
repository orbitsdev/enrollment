<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case Pending = 'pending';
    case Enrolled = 'enrolled';
    case Dropped = 'dropped';
    case Transferred = 'transferred';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Enrolled => 'Enrolled',
            self::Dropped => 'Dropped',
            self::Transferred => 'Transferred',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Enrolled => 'green',
            self::Dropped => 'red',
            self::Transferred => 'blue',
        };
    }
}
