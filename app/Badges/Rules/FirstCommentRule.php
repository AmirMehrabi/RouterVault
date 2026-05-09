<?php

namespace App\Badges\Rules;

use App\Badges\Contracts\BadgeRuleInterface;
use App\Models\User;

class FirstCommentRule implements BadgeRuleInterface
{
    public function check(User $user, array $context = []): bool
    {
        if (($context['event'] ?? null) !== 'comment_created') {
            return false;
        }

        $commentCount = $context['comment_count'] ?? null;

        if ($commentCount !== null) {
            return (int) $commentCount === 1;
        }

        if (method_exists($user, 'comments')) {
            return $user->comments()->count() === 1;
        }

        return false;
    }
}
