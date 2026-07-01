<?php

namespace App\Enums;

enum OnboardingStep: string
{
    case Plan = 'plan';
    case Payment = 'payment';
    case Router = 'router';
    case Backups = 'backups';
    case Complete = 'complete';

    public function number(): int
    {
        return match ($this) {
            self::Plan => 1,
            self::Payment => 2,
            self::Router => 3,
            self::Backups => 4,
            self::Complete => 5,
        };
    }

    public static function fromLegacyStep(int $step): self
    {
        return match ($step) {
            1 => self::Plan,
            2 => self::Payment,
            3 => self::Router,
            4 => self::Backups,
            default => self::Plan,
        };
    }
}
