<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    public function test_guest_can_view_the_routervault_landing_page(): void
    {
        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertSee('RouterVault keeps every MikroTik config backed up')
            ->assertSee('Three steps. That\'s it.', false)
            ->assertSee('See the change, not just the backup.')
            ->assertSee('Stop hoping nothing breaks.')
            ->assertSee('assets/Images/Logos/routervault_full_color.png')
            ->assertSee('assets/Images/Logos/routervault_symbol_white.png')
            ->assertDontSee('WISPA')
            ->assertSee('id="old-vs-new"', false)
            ->assertSee('id="how-it-works"', false)
            ->assertSee('id="diffs"', false)
            ->assertSee('id="pricing"', false)
            ->assertSee(route('auth.register'));
    }

    public function test_authenticated_user_is_redirected_to_the_dashboard(): void
    {
        $user = User::factory()->make();

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertRedirect(route('dashboard'));
    }
}
