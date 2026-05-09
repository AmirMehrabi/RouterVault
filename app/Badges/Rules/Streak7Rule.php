<?php

namespace App\Badges\Rules;

use App\Badges\Contracts\BadgeRuleInterface;
use App\Models\User;

class Streak7Rule implements BadgeRuleInterface
{
    public function check(User $user, array $context = []): bool
    {
        return (($context['event'] ?? null) === 'user_logged_in')
            && $user->activity_streak_count >= 7;
    }
}
