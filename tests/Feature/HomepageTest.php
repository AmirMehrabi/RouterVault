<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    public function test_guest_can_view_the_wispa_landing_page(): void
    {
        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertSee('Router config backups, with version history you can actually understand.')
            ->assertSee('A simple workflow for safer router changes.')
            ->assertSee('See the change, not just the backup file.')
            ->assertSee('Stop guessing what changed on your routers.')
            ->assertSee('id="features"', false)
            ->assertSee('id="how-it-works"', false)
            ->assertSee('id="use-cases"', false)
            ->assertSee('id="pricing"', false)
            ->assertSee(route('auth.register'))
            ->assertSee(route('contact-us'));
    }

    public function test_authenticated_user_is_redirected_to_the_dashboard(): void
    {
        $user = User::factory()->make();

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertRedirect(route('dashboard'));
    }
}
