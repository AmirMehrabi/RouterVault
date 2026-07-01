<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileSettingsNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = Plan::factory()->create([
            'type' => 'saas',
            'status' => 'active',
            'is_saas_plan' => true,
            'max_routers' => 3,
            'max_users' => 3,
        ]);

        $tenant = Tenant::create([
            'id' => 'profile-tenant',
            'name' => 'Profile Tenant',
            'slug' => 'profile-tenant',
            'company_name' => 'Profile Tenant',
            'email' => 'tenant@example.com',
            'status' => 'active',
            'saas_plan_id' => $plan->id,
            'subscription_status' => 'active',
            'onboarding_completed' => true,
            'onboarding_step' => 'complete',
        ]);

        $this->user = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Original Name',
            'email' => 'profile@example.com',
            'phone' => '+1000000000',
            'password' => 'password',
            'role' => 'owner',
            'status' => 'active',
        ]);
    }

    public function test_profile_and_settings_links_are_functional(): void
    {
        $this->actingAs($this->user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee(route('settings.index'), false)
            ->assertSee(route('billing.subscription'), false);

        $this->actingAs($this->user)
            ->get(route('settings.index'))
            ->assertOk();
    }

    public function test_user_can_update_profile_and_password(): void
    {
        $this->actingAs($this->user)
            ->patch(route('profile.update'), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'phone' => '+12223334444',
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($this->user)
            ->patch(route('profile.password.update'), [
                'current_password' => 'password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertSessionHasNoErrors();

        $this->user->refresh();
        $this->assertSame('Updated Name', $this->user->name);
        $this->assertTrue(Hash::check('new-password-123', $this->user->password));
    }
}
