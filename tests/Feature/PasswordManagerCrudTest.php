<?php

namespace Tests\Feature;

use App\Models\AccessPoint;
use App\Models\PasswordManagerCredential;
use App\Models\Router;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordManagerCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_page_displays_password_manager_module(): void
    {
        [$tenant, $user] = $this->createTenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);

        PasswordManagerCredential::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Shared Tower Login',
        ]);

        $response = $this->get(route('password-manager.index'));

        $response->assertOk();
        $response->assertSee('Password Manager');
        $response->assertSee('Shared Tower Login');
    }

    public function test_can_create_password_manager_credential(): void
    {
        [$tenant, $user] = $this->createTenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);

        $response = $this->post(route('password-manager.store'), [
            'name' => 'Tower AP Shared Login',
            'username' => 'tower-admin',
            'password' => 'SecretPass123!',
            'notes' => 'Use for rooftop APs.',
        ]);

        $credential = PasswordManagerCredential::query()
            ->where('tenant_id', $tenant->id)
            ->where('name', 'Tower AP Shared Login')
            ->first();

        $response->assertRedirect(route('password-manager.show', $credential));
        $this->assertNotNull($credential);
        $this->assertSame('tower-admin', $credential->username);
        $this->assertSame('SecretPass123!', $credential->password);
    }

    public function test_can_assign_saved_credential_when_creating_router(): void
    {
        [$tenant, $user] = $this->createTenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);

        $credential = PasswordManagerCredential::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Core Router Login',
            'username' => 'router-admin',
            'password' => 'RouterSecret!',
        ]);

        $response = $this->post(route('routers.store'), [
            'name' => 'Core Router',
            'vendor' => 'Mikrotik',
            'model' => 'CCR2004',
            'ip_address' => '10.10.10.1',
            'api_port' => 8728,
            'ssh_port' => 22,
            'credential_source' => 'password_manager',
            'password_manager_credential_id' => $credential->id,
            'enable_monitoring' => '1',
            'enable_provisioning' => '1',
        ]);

        $router = Router::query()->where('tenant_id', $tenant->id)->where('name', 'Core Router')->first();

        $response->assertRedirect(route('routers.show', $router));
        $this->assertNotNull($router);
        $this->assertSame($credential->id, $router->password_manager_credential_id);
        $this->assertSame('router-admin', $router->fresh()->resolvedApiUsername());
    }

    public function test_can_assign_saved_credential_when_creating_access_point(): void
    {
        [$tenant, $user] = $this->createTenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);

        $router = Router::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Distribution Router',
            'ip_address' => '10.0.0.1',
        ]);

        $credential = PasswordManagerCredential::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Shared AP Login',
            'username' => 'ap-admin',
            'password' => 'ApSecret!',
        ]);

        $response = $this->post(route('access-points.store'), [
            'name' => 'AP-NORTH-02',
            'vendor' => 'Mikrotik',
            'router_id' => $router->id,
            'band' => 'dual',
            'status' => 'online',
            'ip_address' => '10.0.10.22',
            'mac_address' => 'AA:BB:CC:DD:EE:55',
            'credential_source' => 'password_manager',
            'password_manager_credential_id' => $credential->id,
        ]);

        $accessPoint = AccessPoint::query()->where('tenant_id', $tenant->id)->where('name', 'AP-NORTH-02')->first();

        $response->assertRedirect(route('access-points.show', $accessPoint));
        $this->assertNotNull($accessPoint);
        $this->assertSame($credential->id, $accessPoint->password_manager_credential_id);
        $this->assertSame('ap-admin', $accessPoint->fresh()->resolvedApiUsername());
    }

    public function test_cannot_delete_credential_while_it_is_in_use(): void
    {
        [$tenant, $user] = $this->createTenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);

        $credential = PasswordManagerCredential::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        Router::factory()->create([
            'tenant_id' => $tenant->id,
            'password_manager_credential_id' => $credential->id,
            'ip_address' => '10.9.9.9',
        ]);

        $response = $this->delete(route('password-manager.destroy', $credential));

        $response->assertRedirect(route('password-manager.show', $credential));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('password_manager_credentials', [
            'id' => $credential->id,
        ]);
    }

    protected function createTenantUser(): array
    {
        $tenant = Tenant::create([
            'id' => 'tenant-001',
            'name' => 'Tenant One',
            'slug' => 'tenant-one',
            'company_name' => 'Tenant One LLC',
            'email' => 'tenant@example.com',
            'phone' => '+15550000001',
            'country' => 'US',
            'timezone' => 'UTC',
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'owner',
            'status' => 'active',
        ]);

        return [$tenant, $user];
    }
}
