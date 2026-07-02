<?php

namespace Tests\Feature;

use App\Models\BackupSchedule;
use App\Models\DiffAlert;
use App\Models\DiffAlertSetting;
use App\Models\Router;
use App\Models\RouterBackup;
use App\Models\RouterBackupDiff;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Backups\RouterBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RouterVaultModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedule_creation_with_owned_routers(): void
    {
        [$tenant, $user] = $this->createTenantUser('tenant-one');
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);
        $router = Router::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->post(route('schedules.store'), $this->schedulePayload([$router->id]));

        $schedule = BackupSchedule::query()->where('tenant_id', $tenant->id)->first();
        $response->assertRedirect(route('schedules.show', $schedule));
        $this->assertDatabaseHas('backup_schedule_router', [
            'backup_schedule_id' => $schedule->id,
            'router_id' => $router->id,
        ]);
    }

    public function test_rejects_another_tenants_router_in_schedule(): void
    {
        [$tenant, $user] = $this->createTenantUser('tenant-one');
        [$otherTenant] = $this->createTenantUser('tenant-two');
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);
        $foreignRouter = Router::withoutGlobalScopes()->create(Router::factory()->make(['tenant_id' => $otherTenant->id])->toArray());

        $this->post(route('schedules.store'), $this->schedulePayload([$foreignRouter->id]))
            ->assertSessionHasErrors('router_ids.0');
    }

    public function test_backup_service_fails_on_empty_export(): void
    {
        [$tenant, $user] = $this->createTenantUser('tenant-one');
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);
        $router = Router::factory()->create(['tenant_id' => $tenant->id]);
        $service = app(RouterBackupService::class);
        $service->fakeExportUsing(fn () => '');

        $backup = $service->create($router);

        $this->assertSame('failed', $backup->status);
        $this->assertStringContainsString('empty', $backup->error_message);
    }

    public function test_backup_service_does_not_store_routeros_command_errors(): void
    {
        [$tenant, $user] = $this->createTenantUser('tenant-one');
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);
        Storage::fake('public');
        $router = Router::factory()->create(['tenant_id' => $tenant->id]);
        $service = app(RouterBackupService::class);
        $service->fakeExportUsing(fn () => 'expected end of command (line 1 column 9)');

        $backup = $service->create($router);

        $this->assertSame('failed', $backup->status);
        $this->assertNull($backup->path);
        $this->assertStringContainsString('RouterOS export failed', $backup->error_message);
        $this->assertSame([], Storage::disk('public')->allFiles('router-backups'));
    }

    public function test_first_backup_creates_no_alert(): void
    {
        [$tenant, $user] = $this->createTenantUser('tenant-one');
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);
        Storage::fake('local');
        $router = Router::factory()->create(['tenant_id' => $tenant->id]);
        $service = app(RouterBackupService::class);
        $service->fakeExportUsing(fn () => "/system identity set name=one\n");

        $backup = $service->create($router);

        $this->assertSame('success', $backup->status);
        $this->assertTrue($backup->changed);
        $this->assertDatabaseCount('diff_alerts', 0);
    }

    public function test_unchanged_backup_creates_no_alert(): void
    {
        [$tenant, $user] = $this->createTenantUser('tenant-one');
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);
        Storage::fake('local');
        $router = Router::factory()->create(['tenant_id' => $tenant->id]);
        $service = app(RouterBackupService::class);
        $service->fakeExportUsing(fn () => "/system identity set name=one\n");

        $service->create($router);
        $backup = $service->create($router);

        $this->assertFalse($backup->changed);
        $this->assertDatabaseCount('diff_alerts', 0);
    }

    public function test_changed_backup_creates_diff_alert(): void
    {
        [$tenant, $user] = $this->createTenantUser('tenant-one');
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);
        Storage::fake('local');
        $router = Router::factory()->create(['tenant_id' => $tenant->id]);
        $service = app(RouterBackupService::class);
        $service->fakeExportUsing(fn () => "/system identity set name=one\n");
        $service->create($router);
        $service->fakeExportUsing(fn () => "/system identity set name=two\n");

        $backup = $service->create($router);

        $this->assertTrue($backup->changed);
        $this->assertDatabaseHas('diff_alerts', [
            'tenant_id' => $tenant->id,
            'router_id' => $router->id,
            'router_backup_id' => $backup->id,
        ]);
    }

    public function test_ignored_section_suppresses_alert(): void
    {
        [$tenant, $user] = $this->createTenantUser('tenant-one');
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);
        Storage::fake('local');
        DiffAlertSetting::factory()->create(['tenant_id' => $tenant->id, 'ignored_sections' => ['system identity']]);
        $router = Router::factory()->create(['tenant_id' => $tenant->id]);
        $service = app(RouterBackupService::class);
        $service->fakeExportUsing(fn () => "/system identity set name=one\n");
        $service->create($router);
        $service->fakeExportUsing(fn () => "/system identity set name=two\n");

        $service->create($router);

        $this->assertDatabaseCount('diff_alerts', 0);
    }

    public function test_firewall_user_and_service_changes_are_high_severity(): void
    {
        [$tenant, $user] = $this->createTenantUser('tenant-one');
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);
        Storage::fake('local');
        $router = Router::factory()->create(['tenant_id' => $tenant->id]);
        $service = app(RouterBackupService::class);
        $service->fakeExportUsing(fn () => "/ip address add address=10.0.0.1/24\n");
        $service->create($router);
        $service->fakeExportUsing(fn () => "/ip firewall filter add action=drop\n/user add name=a\n/ip service set ssh disabled=no\n");

        $service->create($router);

        $this->assertDatabaseHas('diff_alerts', [
            'tenant_id' => $tenant->id,
            'severity' => 'high',
        ]);
    }

    public function test_user_cannot_view_another_users_backup_or_alert(): void
    {
        [$tenant, $user] = $this->createTenantUser('tenant-one');
        [$otherTenant] = $this->createTenantUser('tenant-two');
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);
        $foreignRouter = Router::withoutGlobalScopes()->create(Router::factory()->make(['tenant_id' => $otherTenant->id])->toArray());
        $previousBackup = RouterBackup::withoutGlobalScopes()->create([
            'tenant_id' => $otherTenant->id,
            'router_id' => $foreignRouter->id,
            'status' => 'success',
            'disk' => 'local',
            'path' => 'foreign-old.rsc',
            'checksum' => hash('sha256', 'old'),
        ]);
        $backup = RouterBackup::withoutGlobalScopes()->create([
            'tenant_id' => $otherTenant->id,
            'router_id' => $foreignRouter->id,
            'previous_router_backup_id' => $previousBackup->id,
            'status' => 'success',
            'disk' => 'local',
            'path' => 'foreign-new.rsc',
            'checksum' => hash('sha256', 'new'),
        ]);
        $diff = RouterBackupDiff::create([
            'router_backup_id' => $backup->id,
            'previous_router_backup_id' => $previousBackup->id,
            'added_lines' => 1,
            'removed_lines' => 0,
            'unified_diff' => "@@ -1,1 +1,1 @@\n+changed",
            'hunks' => [],
        ]);
        $alert = DiffAlert::withoutGlobalScopes()->create([
            'tenant_id' => $otherTenant->id,
            'router_id' => $foreignRouter->id,
            'router_backup_id' => $backup->id,
            'previous_router_backup_id' => $previousBackup->id,
            'router_backup_diff_id' => $diff->id,
            'severity' => 'low',
            'status' => 'unread',
            'summary' => 'Foreign alert',
        ]);

        $this->get(route('backups.show', $backup))->assertNotFound();
        $this->get(route('diff-alerts.show', $alert))->assertNotFound();
    }

    /**
     * @param  array<int, int>  $routerIds
     * @return array<string, mixed>
     */
    protected function schedulePayload(array $routerIds): array
    {
        return [
            'name' => 'Daily Core Backup',
            'is_enabled' => '1',
            'interval_value' => 1,
            'interval_unit' => 'days',
            'timezone' => 'UTC',
            'retention_count' => 30,
            'router_ids' => $routerIds,
        ];
    }

    protected function createTenantUser(string $tenantId): array
    {
        $tenant = Tenant::create([
            'id' => $tenantId,
            'name' => $tenantId,
            'slug' => $tenantId,
            'company_name' => $tenantId,
            'email' => "{$tenantId}@example.com",
            'phone' => '+15550000000',
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
