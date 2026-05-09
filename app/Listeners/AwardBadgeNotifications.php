<?php

namespace App\Listeners;

use App\Events\BadgeAwarded;
use App\Models\ActivityLog;

class AwardBadgeNotifications
{
    public function handle(BadgeAwarded $event): void
    {
        ActivityLog::query()->create([
            'tenant_id' => $event->user->tenant_id,
            'user_id' => $event->user->id,
            'action' => 'badge.awarded',
            'model_type' => get_class($event->badge),
            'model_id' => $event->badge->id,
            'new_values' => [
                'badge_slug' => $event->badge->slug,
                'badge_name' => $event->badge->name,
                'awarded_at' => $event->award->awarded_at?->toDateTimeString(),
            ],
        ]);
    }
}
