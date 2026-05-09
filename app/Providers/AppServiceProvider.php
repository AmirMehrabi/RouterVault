<?php

namespace App\Providers;

use App\Events\BadgeAwarded;
use App\Events\PostLiked;
use App\Events\UserCreatedComment;
use App\Events\UserCreatedPost;
use App\Events\UserLoggedInForBadges;
use App\Events\UserRegisteredForBadges;
use App\Listeners\AwardBadgeNotifications;
use App\Listeners\EvaluateAutomaticBadges;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(BadgeAwarded::class, AwardBadgeNotifications::class);

        foreach ([
            UserRegisteredForBadges::class,
            UserLoggedInForBadges::class,
            UserCreatedPost::class,
            UserCreatedComment::class,
            PostLiked::class,
        ] as $eventClass) {
            Event::listen($eventClass, EvaluateAutomaticBadges::class);
        }
    }
}
