<?php

namespace Tests\Feature;

use App\Models\AccessPoint;
use App\Models\Router;
use App\Models\Site;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WirelessClient;
use App\Services\AccessPointStatusService;
use App\Services\RouterOs\AccessPointDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class WirelessClientTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_refresh_persists_wireless_clients_and_tracks_ap_movement(): void
    {
        [$tenant, $user] = $this->createTenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);

        $router = Router::factory()->create([
            'tenant_id' => $tenant->id,
            'api_username' => 'admin',
            'api_password' => 'secret',
        ]);

        $site = Site::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'HQ',
        ]);

        $oldAccessPoint = AccessPoint::factory()->create([
            'tenant_id' => $tenant->id,
            'router_id' => $router->id,
            'site_id' => $site->id,
            'name' => 'AP-OLD',
            'ssid' => 'SkyBase-Clients',
            'band' => '5GHz',
        ]);

        $newAccessPoint = AccessPoint::factory()->create([
            'tenant_id' => $tenant->id,
            'router_id' => $router->id,
            'site_id' => $site->id,
            'name' => 'AP-NEW',
            'ssid' => 'SkyBase-Clients',
            'band' => '5GHz',
        ]);

        WirelessClient::factory()->create([
            'tenant_id' => $tenant->id,
            'access_point_id' => $oldAccessPoint->id,
            'router_id' => $router->id,
            'site_id' => $site->id,
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
            'host_name' => 'phone-1',
            'is_connected' => true,
        ]);

        $dataService = Mockery::mock(AccessPointDataService::class);
        $dataService->shouldReceive('fetch')->once()->andReturn([
            'online' => true,
            'status' => 'online',
            'reason' => null,
            'collected_at' => now()->toIso8601String(),
            'resource' => ['board-name' => 'cAP ax', 'version' => '7.18'],
            'wireless' => ['ssid' => 'SkyBase-Clients', 'band' => '5GHz', 'frequency' => 5180],
            'clients' => [
                [
                    'mac-address' => 'AA:BB:CC:DD:EE:FF',
                    'host-name' => 'phone-1',
                    'interface' => 'wlan1',
                    'signal-strength' => '-58',
                    'signal-to-noise' => '32',
                    'tx-rate' => '300Mbps',
                    'rx-rate' => '300Mbps',
                    'tx-ccq' => '89',
                    'rx-ccq' => '91',
                    'uptime' => '20m12s',
                    'last-ip' => '10.10.10.10',
                ],
            ],
            'metrics' => [
                'board_name' => 'cAP ax',
                'firmware_version' => '7.18',
                'connected_clients_count' => 1,
                'ssid' => 'SkyBase-Clients',
                'band' => '5GHz',
                'frequency' => 5180,
                'tx_power' => 20,
            ],
        ]);

        $service = app()->make(AccessPointStatusService::class, ['accessPointDataService' => $dataService]);
        $payload = $service->refresh($newAccessPoint);

        $this->assertSame('AA:BB:CC:DD:EE:FF', $payload['clients'][0]['mac_address']);
        $this->assertDatabaseHas('wireless_clients', [
            'tenant_id' => $tenant->id,
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
            'access_point_id' => $newAccessPoint->id,
            'is_connected' => true,
            'last_ip_address' => '10.10.10.10',
        ]);
        $this->assertDatabaseHas('wireless_client_movements', [
            'tenant_id' => $tenant->id,
            'from_access_point_id' => $oldAccessPoint->id,
            'to_access_point_id' => $newAccessPoint->id,
        ]);
    }

    public function test_wireless_clients_page_lists_filtered_clients(): void
    {
        [$tenant, $user] = $this->createTenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);

        $site = Site::factory()->create(['tenant_id' => $tenant->id, 'name' => 'East Site']);
        $router = Router::factory()->create(['tenant_id' => $tenant->id]);
        $accessPoint = AccessPoint::factory()->create([
            'tenant_id' => $tenant->id,
            'router_id' => $router->id,
            'site_id' => $site->id,
            'name' => 'AP-EAST',
        ]);

        WirelessClient::factory()->create([
            'tenant_id' => $tenant->id,
            'access_point_id' => $accessPoint->id,
            'router_id' => $router->id,
            'site_id' => $site->id,
            'mac_address' => '00:11:22:33:44:55',
            'host_name' => 'laptop-east',
            'is_connected' => true,
        ]);

        WirelessClient::factory()->create([
            'tenant_id' => $tenant->id,
            'mac_address' => '66:77:88:99:AA:BB',
            'host_name' => 'remote-client',
            'is_connected' => false,
        ]);

        $this->get(route('wireless-clients.index'))
            ->assertOk()
            ->assertSee('Wireless Clients');

        $this->getJson(route('wireless-clients.data', ['search' => 'laptop-east', 'connection' => 'connected']))
            ->assertOk()
            ->assertJsonCount(1, 'wireless_clients')
            ->assertJsonPath('wireless_clients.0.host_name', 'laptop-east')
            ->assertJsonPath('wireless_clients.0.access_point', 'AP-EAST');
    }

    protected function createTenantUser(): array
    {
        $tenant = Tenant::create([
            'id' => 'tenant-wireless',
            'name' => 'Tenant Wireless',
            'slug' => 'tenant-wireless',
            'company_name' => 'Tenant Wireless LLC',
            'email' => 'wireless@example.com',
            'phone' => '+15550000004',
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

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
