<?php

namespace App\Enums;

enum GradeRemarks: string
{
    case Passed = 'passed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Passed => 'Passed',
            self::Failed => 'Failed',
        };
    }
}
