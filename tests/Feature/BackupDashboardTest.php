<?php

namespace Tests\Feature;

use App\Jobs\ProcessBackupScheduleRun;
use App\Jobs\ProcessRouterBackup;
use App\Models\BackupRun;
use App\Models\BackupSchedule;
use App\Models\DiffAlert;
use App\Models\Router;
use App\Models\RouterBackup;
use App\Models\RouterBackupDiff;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Backups\BackupScheduleRunner;
use App\Services\Backups\RouterBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_is_scoped_and_summarizes_backup_operations(): void
    {
        [$tenant, $user] = $this->createTenantUser('tenant-one');
        [$otherTenant] = $this->createTenantUser('tenant-two');
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);

        $coveredRouter = Router::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Core Router']);
        $failedRouter = Router::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Failed Router']);
        Router::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Uncovered Router']);
        $foreignRouter = Router::withoutGlobalScopes()->create(
            Router::factory()->make(['tenant_id' => $otherTenant->id, 'name' => 'Foreign Router'])->toArray()
        );

        $schedule = BackupSchedule::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Hourly Core',
            'next_run_at' => now()->addHour(),
        ]);
        $schedule->routers()->attach($coveredRouter);

        $previous = RouterBackup::factory()->create([
            'tenant_id' => $tenant->id,
            'router_id' => $coveredRouter->id,
            'created_at' => now()->subHours(3),
        ]);
        $changedBackup = RouterBackup::factory()->create([
            'tenant_id' => $tenant->id,
            'router_id' => $coveredRouter->id,
            'previous_router_backup_id' => $previous->id,
            'changed' => true,
            'created_at' => now()->subHour(),
        ]);
        RouterBackup::factory()->create([
            'tenant_id' => $tenant->id,
            'router_id' => $failedRouter->id,
            'status' => 'failed',
            'changed' => false,
            'error_message' => 'Authentication failed',
            'created_at' => now()->subMinutes(20),
        ]);
        RouterBackup::withoutGlobalScopes()->create([
            'tenant_id' => $otherTenant->id,
            'router_id' => $foreignRouter->id,
            'status' => 'failed',
            'disk' => 'local',
            'created_at' => now(),
        ]);

        $diff = RouterBackupDiff::factory()->create([
            'router_backup_id' => $changedBackup->id,
            'previous_router_backup_id' => $previous->id,
        ]);
        DiffAlert::factory()->create([
            'tenant_id' => $tenant->id,
            'router_id' => $coveredRouter->id,
            'router_backup_id' => $changedBackup->id,
            'previous_router_backup_id' => $previous->id,
            'router_backup_diff_id' => $diff->id,
            'severity' => 'high',
            'status' => 'unread',
            'summary' => 'Firewall policy changed',
        ]);

        $response = $this->get(route('dashboard'));
        $dashboard = $response->viewData('backupDashboard');

        $response->assertOk()
            ->assertSee('Backup Operations')
            ->assertSee('Authentication failed')
            ->assertSee('Firewall policy changed')
            ->assertDontSee('Foreign Router')
            ->assertDontSee('Network visibility that reflects your live modules');
        $this->assertSame(66.7, $dashboard['stats']['success_rate']);
        $this->assertSame(1, $dashboard['stats']['covered_routers']);
        $this->assertSame(3, $dashboard['stats']['total_routers']);
        $this->assertSame(1, $dashboard['stats']['configuration_changes']);
        $this->assertSame(1, $dashboard['stats']['high_unread_alerts']);
    }

    public function test_failed_backup_retry_creates_pending_backup_and_dispatches_job(): void
    {
        Queue::fake();
        [$tenant, $user] = $this->createTenantUser('tenant-one');
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);
        $router = Router::factory()->create(['tenant_id' => $tenant->id]);
        $failedBackup = RouterBackup::factory()->create([
            'tenant_id' => $tenant->id,
            'router_id' => $router->id,
            'status' => 'failed',
        ]);

        $response = $this->from(route('dashboard'))->post(route('backups.retry', $failedBackup));

        $response->assertRedirect(route('dashboard'))->assertSessionHas('success');
        $retry = RouterBackup::query()->where('router_id', $router->id)->where('status', 'pending')->first();
        $this->assertNotNull($retry);
        Queue::assertPushed(ProcessRouterBackup::class, fn (ProcessRouterBackup $job): bool => $job->routerBackupId === $retry->id);
    }

    public function test_failed_backup_retry_returns_live_activity_payload_for_json_requests(): void
    {
        Queue::fake();
        [$tenant, $user] = $this->createTenantUser('tenant-one');
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);
        $router = Router::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Edge Router']);
        $failedBackup = RouterBackup::factory()->create([
            'tenant_id' => $tenant->id,
            'router_id' => $router->id,
            'status' => 'failed',
        ]);

        $this->postJson(route('backups.retry', $failedBackup))
            ->assertAccepted()
            ->assertJsonPath('backup.status', 'pending')
            ->assertJsonPath('backup.router_name', 'Edge Router');
    }

    public function test_duplicate_router_retry_is_rejected(): void
    {
        Queue::fake();
        [$tenant, $user] = $this->createTenantUser('tenant-one');
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);
        $router = Router::factory()->create(['tenant_id' => $tenant->id]);
        $failedBackup = RouterBackup::factory()->create([
            'tenant_id' => $tenant->id,
            'router_id' => $router->id,
            'status' => 'failed',
        ]);
        RouterBackup::factory()->create([
            'tenant_id' => $tenant->id,
            'router_id' => $router->id,
            'status' => 'pending',
        ]);

        $this->from(route('dashboard'))
            ->post(route('backups.retry', $failedBackup))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');

        Queue::assertNothingPushed();
    }

    public function test_retry_cannot_access_another_tenants_backup(): void
    {
        Queue::fake();
        [$tenant, $user] = $this->createTenantUser('tenant-one');
        [$otherTenant] = $this->createTenantUser('tenant-two');
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);
        $foreignRouter = Router::withoutGlobalScopes()->create(
            Router::factory()->make(['tenant_id' => $otherTenant->id])->toArray()
        );
        $foreignBackup = RouterBackup::withoutGlobalScopes()->create([
            'tenant_id' => $otherTenant->id,
            'router_id' => $foreignRouter->id,
            'status' => 'failed',
            'disk' => 'local',
        ]);

        $this->post(route('backups.retry', $foreignBackup))->assertNotFound();

        Queue::assertNothingPushed();
    }

    public function test_schedule_run_is_queued(): void
    {
        Queue::fake();
        [$tenant, $user] = $this->createTenantUser('tenant-one');
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);
        $schedule = BackupSchedule::factory()->create(['tenant_id' => $tenant->id]);

        $this->from(route('dashboard'))
            ->post(route('schedules.run', $schedule))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success');

        $run = BackupRun::query()->where('backup_schedule_id', $schedule->id)->first();
        $this->assertSame('queued', $run?->status);
        Queue::assertPushed(ProcessBackupScheduleRun::class, fn (ProcessBackupScheduleRun $job): bool => $job->backupRunId === $run?->id);
    }

    public function test_schedule_run_and_polling_return_live_json(): void
    {
        Queue::fake();
        [$tenant, $user] = $this->createTenantUser('tenant-one');
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);
        $schedule = BackupSchedule::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->postJson(route('schedules.run', $schedule))
            ->assertAccepted()
            ->assertJsonPath('run.status', 'queued');

        $this->getJson(route('schedules.runs', $schedule))
            ->assertOk()
            ->assertJsonPath('runs.0.id', $response->json('run.id'));
    }

    public function test_dashboard_only_displays_selected_managed_routers(): void
    {
        [$tenant, $user] = $this->createTenantUser('tenant-one');
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);
        Router::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Pinned Router', 'is_dashboard_visible' => true]);
        Router::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Hidden Router', 'is_dashboard_visible' => false]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pinned Router')
            ->assertDontSee('Hidden Router');
    }

    public function test_backup_jobs_process_prepared_records(): void
    {
        Storage::fake('local');
        [$tenant] = $this->createTenantUser('tenant-one');
        app()->instance('current_tenant', $tenant);
        $router = Router::factory()->create(['tenant_id' => $tenant->id]);
        $schedule = BackupSchedule::factory()->create(['tenant_id' => $tenant->id]);
        $schedule->routers()->attach($router);
        $backupService = app(RouterBackupService::class);
        $backupService->fakeExportUsing(fn (): string => "/system identity set name=core\n");

        $pendingBackup = RouterBackup::factory()->create([
            'tenant_id' => $tenant->id,
            'router_id' => $router->id,
            'status' => 'pending',
            'path' => null,
            'checksum' => null,
        ]);
        (new ProcessRouterBackup($pendingBackup->id))->handle($backupService);
        $this->assertSame('success', $pendingBackup->fresh()->status);

        $runner = new BackupScheduleRunner($backupService);
        $run = $runner->prepare($schedule);
        (new ProcessBackupScheduleRun($run->id))->handle($runner);
        $run->refresh();

        $this->assertSame('success', $run->status);
        $this->assertSame(1, $run->successful_backups);
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
