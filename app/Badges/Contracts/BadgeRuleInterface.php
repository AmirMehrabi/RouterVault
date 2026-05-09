<?php

namespace App\Badges\Contracts;

use App\Models\User;

interface BadgeRuleInterface
{
    public function check(User $user, array $context = []): bool;
}
