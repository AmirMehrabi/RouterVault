<?php

namespace App\Badges;

use App\Badges\Contracts\BadgeRuleInterface;
use App\Models\Badge;
use App\Models\User;
use Illuminate\Support\Collection;

class BadgeRuleLoader
{
    public function automaticBadges(): Collection
    {
        return Badge::query()
            ->where('type', 'automatic')
            ->whereNotNull('rule_class')
            ->orderBy('id')
            ->get();
    }

    public function eligibleBadges(User $user, array $context = []): Collection
    {
        return $this->automaticBadges()
            ->filter(function (Badge $badge) use ($user, $context): bool {
                $rule = $this->resolveRule($badge->rule_class);

                return $rule instanceof BadgeRuleInterface
                    && $rule->check($user, $context);
            })
            ->values();
    }

    protected function resolveRule(?string $ruleClass): ?BadgeRuleInterface
    {
        if (! $ruleClass) {
            return null;
        }

        $resolvedClass = class_exists($ruleClass)
            ? $ruleClass
            : 'App\\Badges\\Rules\\'.$ruleClass;

        if (! class_exists($resolvedClass)) {
            return null;
        }

        $rule = app($resolvedClass);

        return $rule instanceof BadgeRuleInterface ? $rule : null;
    }
}
