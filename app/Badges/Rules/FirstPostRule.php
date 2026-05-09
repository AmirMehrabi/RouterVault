<?php

namespace App\Badges\Rules;

use App\Badges\Contracts\BadgeRuleInterface;
use App\Models\User;

class FirstPostRule implements BadgeRuleInterface
{
    public function check(User $user, array $context = []): bool
    {
        if (($context['event'] ?? null) !== 'post_created') {
            return false;
        }

        $postCount = $context['post_count'] ?? null;

        if ($postCount !== null) {
            return (int) $postCount === 1;
        }

        if (method_exists($user, 'posts')) {
            return $user->posts()->count() === 1;
        }

        return false;
    }
}
