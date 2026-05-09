<?php

namespace Tests\Feature;

use App\Events\PostLiked;
use App\Events\UserCreatedComment;
use App\Events\UserCreatedPost;
use App\Events\UserLoggedInForBadges;
use App\Events\UserRegisteredForBadges;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BadgeService;
use Database\Seeders\BadgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BadgeSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BadgeSeeder::class);
    }

    public function test_badge_service_prevents_duplicate_awards(): void
    {
        [$tenant, $user] = $this->createTenantUser();

        $service = app(BadgeService::class);
        $service->awardBadge($user, 'verified', ['source' => 'test']);
        $service->awardBadge($user, 'verified', ['source' => 'test-again']);

        $this->assertDatabaseCount('user_badges', 1);
        $this->assertDatabaseHas('user_badges', [
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_award_verified_command_awards_badge_using_phone_number(): void
    {
        [, $user] = $this->createTenantUser([
            'phone' => '+15551112222',
        ]);

        $this->artisan('award:verified', ['phone' => ' +1 (555) 111-2222 '])
            ->expectsOutput("Verified badge awarded to {$user->name}.")
            ->assertSuccessful();

        $this->assertTrue($user->fresh()->hasBadge('verified'));
    }

    public function test_automatic_badges_are_awarded_from_registered_events(): void
    {
        [, $user] = $this->createTenantUser([
            'id' => 10,
        ]);

        UserRegisteredForBadges::dispatch($user, ['event' => 'user_registered']);
        UserCreatedPost::dispatch($user, ['event' => 'post_created', 'post_count' => 1]);
        UserCreatedComment::dispatch($user, ['event' => 'comment_created', 'comment_count' => 1]);
        PostLiked::dispatch($user, [
            'event' => 'post_liked',
            'post_user_id' => $user->id,
            'likes_count' => 10,
            'popular_threshold' => 10,
            'post_created_at' => now()->subHours(2),
            'liked_at' => now(),
        ]);

        $user = $user->fresh(['badges']);

        $this->assertTrue($user->hasBadge('early_adopter_100'));
        $this->assertTrue($user->hasBadge('first_post'));
        $this->assertTrue($user->hasBadge('first_comment'));
        $this->assertTrue($user->hasBadge('popular_post'));
    }

    public function test_login_streak_awards_seven_day_streak_badge(): void
    {
        [, $user] = $this->createTenantUser([
            'activity_streak_count' => 6,
            'last_activity_at' => now()->subDay(),
        ]);

        UserLoggedInForBadges::dispatch($user, ['event' => 'user_logged_in']);

        $user = $user->fresh(['badges']);

        $this->assertSame(7, $user->activity_streak_count);
        $this->assertTrue($user->hasBadge('streak_7'));
    }

    public function test_user_profile_shows_profile_badges_but_not_verified_in_badge_list(): void
    {
        [$tenant, $user] = $this->createTenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);

        app(BadgeService::class)->awardBadge($user, 'verified');
        app(BadgeService::class)->awardBadge($user, 'early_adopter_100');

        $response = $this->get(route('admin.tenant.users.show', $user));

        $response->assertOk();
        $response->assertSee('Badges');
        $response->assertSee('Early Adopter');
        $response->assertDontSee('Verified user badge, shown next to username.');
    }

    protected function createTenantUser(array $userOverrides = []): array
    {
        $tenant = Tenant::create([
            'id' => 'tenant-'.fake()->unique()->numerify('###'),
            'name' => 'Tenant One',
            'slug' => fake()->unique()->slug(),
            'company_name' => 'Tenant One LLC',
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->e164PhoneNumber(),
            'country' => 'US',
            'timezone' => 'UTC',
            'status' => 'active',
        ]);

        $user = User::factory()->create(array_merge([
            'tenant_id' => $tenant->id,
            'role' => 'owner',
            'status' => 'active',
        ], $userOverrides));

        return [$tenant, $user];
    }
}
