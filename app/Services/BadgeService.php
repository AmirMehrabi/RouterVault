<?php

namespace App\Services;

use App\Badges\BadgeRuleLoader;
use App\Events\BadgeAwarded;
use App\Models\Badge;
use App\Models\User;
use App\Models\UserBadge;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BadgeService
{
    public function __construct(protected BadgeRuleLoader $badgeRuleLoader) {}

    public function awardBadge(User $user, string $badgeSlug, array $metadata = []): ?UserBadge
    {
        $badge = Badge::query()->where('slug', $badgeSlug)->firstOrFail();
        $tenantId = $user->tenant_id;

        if (! $tenantId) {
            return null;
        }

        return DB::transaction(function () use ($user, $badge, $metadata, $tenantId): ?UserBadge {
            $existingAward = UserBadge::query()
                ->forTenant($tenantId)
                ->where('user_id', $user->id)
                ->where('badge_id', $badge->id)
                ->first();

            if ($existingAward) {
                return $existingAward;
            }

            $award = UserBadge::query()->create([
                'tenant_id' => $tenantId,
                'user_id' => $user->id,
                'badge_id' => $badge->id,
                'awarded_at' => now(),
                'metadata' => $metadata,
            ]);

            BadgeAwarded::dispatch($user->fresh(['badges']), $badge, $award);

            return $award;
        });
    }

    public function hasBadge(User $user, string $badgeSlug): bool
    {
        return UserBadge::query()
            ->forTenant($user->tenant_id)
            ->where('user_id', $user->id)
            ->whereHas('badge', function ($query) use ($badgeSlug): void {
                $query->where('slug', $badgeSlug);
            })
            ->exists();
    }

    public function getUserBadges(User $user): Collection
    {
        return $user->badges()
            ->orderBy('name')
            ->get();
    }

    public function evaluateAutomaticBadges(User $user, array $context = []): void
    {
        $badges = $this->badgeRuleLoader->eligibleBadges($user, $context);

        foreach ($badges as $badge) {
            $this->awardBadge($user, $badge->slug, $context['metadata'] ?? []);
        }
    }

    public function recordUserActivity(User $user, ?CarbonImmutable $occurredAt = null): User
    {
        $occurredAt ??= CarbonImmutable::now();
        $lastActivityAt = $user->last_activity_at ? CarbonImmutable::parse($user->last_activity_at) : null;

        if (! $lastActivityAt) {
            $streak = 1;
        } elseif ($lastActivityAt->isSameDay($occurredAt)) {
            $streak = max(1, $user->activity_streak_count);
        } elseif ($lastActivityAt->copy()->addDay()->isSameDay($occurredAt)) {
            $streak = $user->activity_streak_count + 1;
        } else {
            $streak = 1;
        }

        $user->forceFill([
            'activity_streak_count' => $streak,
            'last_activity_at' => $occurredAt,
            'last_login_at' => $occurredAt,
        ])->save();

        return $user->refresh();
    }

    public function normalizePhone(string $phone): string
    {
        return Str::of($phone)
            ->replaceMatches('/[^0-9+]/', '')
            ->toString();
    }
}
