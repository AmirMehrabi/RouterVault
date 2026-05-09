<?php

namespace App\Badges\Rules;

use App\Badges\Contracts\BadgeRuleInterface;
use App\Models\User;

class EarlyAdopterRule implements BadgeRuleInterface
{
    public function check(User $user, array $context = []): bool
    {
        return (($context['event'] ?? null) === 'user_registered') && $user->id <= 100;
    }
}
