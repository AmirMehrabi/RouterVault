<?php

namespace App\Badges\Rules;

use App\Badges\Contracts\BadgeRuleInterface;
use App\Models\User;
use Carbon\CarbonImmutable;

class PopularPostRule implements BadgeRuleInterface
{
    public function check(User $user, array $context = []): bool
    {
        if (($context['event'] ?? null) !== 'post_liked') {
            return false;
        }

        $likesCount = (int) ($context['likes_count'] ?? 0);
        $threshold = (int) ($context['popular_threshold'] ?? 10);
        $postUserId = $context['post_user_id'] ?? $user->id;
        $postCreatedAt = isset($context['post_created_at'])
            ? CarbonImmutable::parse($context['post_created_at'])
            : null;
        $likedAt = isset($context['liked_at'])
            ? CarbonImmutable::parse($context['liked_at'])
            : CarbonImmutable::now();

        if ((int) $postUserId !== $user->id) {
            return false;
        }

        if (! $postCreatedAt) {
            return $likesCount >= $threshold;
        }

        return $likesCount >= $threshold && $postCreatedAt->diffInHours($likedAt) <= 24;
    }
}
