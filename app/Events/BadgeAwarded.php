<?php

namespace App\Events;

use App\Models\Badge;
use App\Models\User;
use App\Models\UserBadge;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BadgeAwarded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public Badge $badge,
        public UserBadge $award,
    ) {}
}
