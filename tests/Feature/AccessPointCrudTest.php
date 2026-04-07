<?php

namespace Tests\Feature;

use App\Models\AccessPoint;
use App\Models\Router;
use App\Models\Site;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessPointCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_page_displays_access_points_for_current_tenant(): void
    {
        [$tenant, $user] = $this->createTenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);

        AccessPoint::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'AP-Tower-01',
        ]);

        $response = $this->get(route('access-points.index'));

        $response->assertOk();
        $response->assertSee('Access Point Management');
    }

    public function test_can_create_access_point(): void
    {
        [$tenant, $user] = $this->createTenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);

        $router = Router::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Core Router',
            'ip_address' => '10.0.0.1',
        ]);

        $site = Site::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'North Tower',
        ]);

        $response = $this->post(route('access-points.store'), [
            'name' => 'AP-NORTH-01',
            'vendor' => 'Mikrotik',
            'model' => 'cAP ax',
            'router_id' => $router->id,
            'site_id' => $site->id,
            'location' => 'Tower sector A',
            'ssid' => 'SkyBase-Clients',
            'band' => 'dual',
            'status' => 'online',
            'ip_address' => '10.0.10.10',
            'mac_address' => 'AA:BB:CC:DD:EE:11',
            'firmware_version' => 'RouterOS 7.16',
            'channel' => '36',
            'frequency' => 5180,
            'tx_power' => 20,
            'connected_clients_count' => 12,
            'signal_quality' => 87,
            'cpu_usage' => 18,
            'memory_usage' => 35,
            'channel_utilization' => 42,
            'noise_floor' => -92,
            'uptime' => '2d 04h',
            'enable_monitoring' => '1',
            'enable_provisioning' => '1',
            'notes' => 'Primary sector AP',
        ]);

        $accessPoint = AccessPoint::where('tenant_id', $tenant->id)
            ->where('name', 'AP-NORTH-01')
            ->first();

        $response->assertRedirect(route('access-points.show', $accessPoint));
        $this->assertDatabaseHas('access_points', [
            'tenant_id' => $tenant->id,
            'name' => 'AP-NORTH-01',
            'router_id' => $router->id,
            'site_id' => $site->id,
            'status' => 'online',
        ]);
    }

    public function test_can_update_access_point(): void
    {
        [$tenant, $user] = $this->createTenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);

        $router = Router::factory()->create([
            'tenant_id' => $tenant->id,
            'ip_address' => '10.0.0.2',
        ]);

        $site = Site::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $accessPoint = AccessPoint::factory()->create([
            'tenant_id' => $tenant->id,
            'router_id' => $router->id,
            'site_id' => $site->id,
            'name' => 'Old AP',
            'ip_address' => '10.0.20.20',
            'mac_address' => 'AA:BB:CC:DD:EE:22',
        ]);

        $response = $this->put(route('access-points.update', $accessPoint), [
            'name' => 'Updated AP',
            'vendor' => 'Mikrotik',
            'model' => 'wAP ax',
            'router_id' => $router->id,
            'site_id' => $site->id,
            'location' => 'Updated rack',
            'ssid' => 'Updated-SSID',
            'band' => '5GHz',
            'status' => 'maintenance',
            'ip_address' => '10.0.20.20',
            'mac_address' => 'AA:BB:CC:DD:EE:22',
            'firmware_version' => 'RouterOS 7.17',
            'channel' => '44',
            'frequency' => 5220,
            'tx_power' => 18,
            'connected_clients_count' => 24,
            'signal_quality' => 74,
            'cpu_usage' => 27,
            'memory_usage' => 41,
            'channel_utilization' => 58,
            'noise_floor' => -90,
            'uptime' => '7d 09h',
            'notes' => 'Updated provisioning notes',
        ]);

        $response->assertRedirect(route('access-points.show', $accessPoint));
        $this->assertDatabaseHas('access_points', [
            'id' => $accessPoint->id,
            'name' => 'Updated AP',
            'status' => 'maintenance',
            'ssid' => 'Updated-SSID',
        ]);
    }

    public function test_cannot_access_access_point_from_another_tenant(): void
    {
        [$tenant, $user] = $this->createTenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);

        $otherTenant = Tenant::create([
            'id' => 'tenant-other',
            'name' => 'Other Tenant',
            'slug' => 'other-tenant',
            'company_name' => 'Other Tenant Ltd',
            'email' => 'other@example.com',
            'phone' => '+15551234567',
            'country' => 'US',
            'timezone' => 'UTC',
            'status' => 'active',
        ]);

        $foreignAccessPoint = AccessPoint::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);

        $response = $this->get(route('access-points.show', $foreignAccessPoint->id));

        $response->assertForbidden();
    }

    public function test_can_delete_access_point(): void
    {
        [$tenant, $user] = $this->createTenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);

        $accessPoint = AccessPoint::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->delete(route('access-points.destroy', $accessPoint));

        $response->assertRedirect(route('access-points.index'));
        $this->assertDatabaseMissing('access_points', [
            'id' => $accessPoint->id,
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
