<?php

namespace Tests\Feature;

use App\Models\AccessPoint;
use App\Models\AccessPointStatusChange;
use App\Models\Router;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AccessPointStatusService;
use App\Services\RouterOs\AccessPointDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AccessPointMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_command_records_a_status_transition(): void
    {
        $tenant = Tenant::create([
            'id' => 'tenant-monitoring',
            'name' => 'Monitoring Tenant',
            'slug' => 'monitoring-tenant',
            'company_name' => 'Monitoring Tenant LLC',
            'email' => 'monitoring@example.com',
            'phone' => '+15550000002',
            'country' => 'US',
            'timezone' => 'UTC',
            'status' => 'active',
        ]);

        $router = Router::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $accessPoint = AccessPoint::factory()->create([
            'tenant_id' => $tenant->id,
            'router_id' => $router->id,
            'status' => 'offline',
            'enable_monitoring' => true,
        ]);

        $service = Mockery::mock(AccessPointDataService::class);
        $service->shouldReceive('fetch')
            ->once()
            ->withArgs(fn (AccessPoint $candidate): bool => $candidate->is($accessPoint))
            ->andReturn([
                'online' => true,
                'status' => 'online',
                'reason' => null,
                'collected_at' => now()->toIso8601String(),
                'resource' => ['version' => '7.18'],
                'wireless' => ['ssid' => 'SkyBase-Clients'],
                'clients' => [['mac-address' => 'AA:BB:CC:DD:EE:FF']],
                'metrics' => [
                    'uptime' => '3d 12h',
                    'cpu_usage' => 17,
                    'memory_usage' => 38,
                    'connected_clients_count' => 1,
                    'signal_quality' => 83,
                    'firmware_version' => '7.18',
                    'ssid' => 'SkyBase-Clients',
                    'band' => '5GHz',
                    'channel' => '36',
                    'frequency' => 5180,
                    'tx_power' => 20,
                    'noise_floor' => -94,
                    'channel_utilization' => 41,
                ],
            ]);

        $this->app->instance(AccessPointDataService::class, $service);

        $this->artisan('app:check-access-point-status')
            ->expectsOutputToContain('Checking 1 access point')
            ->assertSuccessful();

        $this->assertDatabaseHas('access_points', [
            'id' => $accessPoint->id,
            'status' => 'online',
            'cpu_usage' => 17,
            'connected_clients_count' => 1,
        ]);

        $this->assertDatabaseHas('access_point_status_changes', [
            'tenant_id' => $tenant->id,
            'access_point_id' => $accessPoint->id,
            'previous_status' => 'offline',
            'current_status' => 'online',
        ]);

        $this->assertSame(1, AccessPointStatusChange::withoutGlobalScopes()->count());
    }

    public function test_show_page_uses_live_data_endpoint_for_latest_metrics(): void
    {
        [$tenant, $user] = $this->createTenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);

        $router = Router::factory()->create([
            'tenant_id' => $tenant->id,
            'api_username' => 'admin',
            'api_password' => 'secret',
        ]);

        $accessPoint = AccessPoint::factory()->create([
            'tenant_id' => $tenant->id,
            'router_id' => $router->id,
            'name' => 'Tower AP',
            'status' => 'online',
        ]);

        $service = Mockery::mock(AccessPointStatusService::class);
        $service->shouldReceive('refresh')->twice()->andReturnUsing(function (AccessPoint $candidate) use ($accessPoint, $router): array {
            $candidate->forceFill([
                'tenant_id' => $accessPoint->tenant_id,
                'router_id' => $router->id,
                'status' => 'online',
                'connected_clients_count' => 9,
                'signal_quality' => 81,
                'cpu_usage' => 22,
                'memory_usage' => 44,
                'firmware_version' => '7.18',
                'uptime' => '3d 12h',
                'last_seen_at' => now(),
            ]);

            return [
                'online' => true,
                'status' => 'online',
                'reason' => null,
                'collected_at' => now()->toIso8601String(),
                'resource' => ['version' => '7.18'],
                'wireless' => ['ssid' => 'Live-SSID'],
                'clients' => [['mac-address' => 'AA:BB:CC:DD:EE:FF']],
                'metrics' => [],
                'access_point' => $candidate->load(['router:id,name', 'site:id,name']),
            ];
        });
        $service->shouldReceive('latestStatusSummary')->twice()->andReturn([
            'status_history' => [
                [
                    'previous_status' => 'offline',
                    'current_status' => 'online',
                    'reason' => null,
                    'checked_at' => now()->toIso8601String(),
                ],
            ],
        ]);

        $this->app->instance(AccessPointStatusService::class, $service);

        $showResponse = $this->get(route('access-points.show', $accessPoint));
        $showResponse->assertOk();
        $showResponse->assertSee('Tower AP');
        $showResponse->assertSee(route('access-points.live-data', $accessPoint));

        $liveResponse = $this->getJson(route('access-points.live-data', $accessPoint));
        $liveResponse->assertOk();
        $liveResponse->assertJsonPath('access_point.connected_clients_count', 9);
        $liveResponse->assertJsonPath('access_point.cpu_usage', 22);
    }

    protected function createTenantUser(): array
    {
        $tenant = Tenant::create([
            'id' => 'tenant-002',
            'name' => 'Tenant Two',
            'slug' => 'tenant-two',
            'company_name' => 'Tenant Two LLC',
            'email' => 'tenant-two@example.com',
            'phone' => '+15550000003',
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
