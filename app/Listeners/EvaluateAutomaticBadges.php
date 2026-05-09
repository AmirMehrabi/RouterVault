<?php

namespace App\Listeners;

use App\Events\PostLiked;
use App\Events\UserCreatedComment;
use App\Events\UserCreatedPost;
use App\Events\UserLoggedInForBadges;
use App\Events\UserRegisteredForBadges;
use App\Services\BadgeService;

class EvaluateAutomaticBadges
{
    public function __construct(protected BadgeService $badgeService) {}

    public function handle(
        UserRegisteredForBadges|UserLoggedInForBadges|UserCreatedPost|UserCreatedComment|PostLiked $event
    ): void {
        $user = $event->user;
        $context = $event->context;

        if ($event instanceof UserLoggedInForBadges) {
            $user = $this->badgeService->recordUserActivity($user);
        }

        $this->badgeService->evaluateAutomaticBadges($user, $context);
    }
}
