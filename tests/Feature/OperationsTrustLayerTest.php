<?php

namespace Tests\Feature;

use App\Jobs\RecordQueueHeartbeat;
use App\Models\ActivityLog;
use App\Models\ChangeRequest;
use App\Models\Incident;
use App\Models\Router;
use App\Models\RouterBackup;
use App\Models\SystemHeartbeat;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Operations\SystemHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OperationsTrustLayerTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_health_reports_missing_and_current_heartbeats(): void
    {
        $service = app(SystemHealthService::class);

        $missingChecks = collect($service->checks())->keyBy('key');

        $this->assertSame('critical', $missingChecks['scheduler']['status']);
        $this->assertSame('critical', $missingChecks['queue']['status']);

        SystemHeartbeat::record('scheduler');
        SystemHeartbeat::record('queue');

        $currentChecks = collect($service->checks())->keyBy('key');

        $this->assertSame('healthy', $currentChecks['scheduler']['status']);
        $this->assertSame('healthy', $currentChecks['queue']['status']);
    }

    public function test_scheduler_heartbeat_command_records_scheduler_and_dispatches_queue_probe(): void
    {
        Queue::fake();

        $this->artisan('system:heartbeat')->assertSuccessful();

        $this->assertDatabaseHas('system_heartbeats', ['service' => 'scheduler', 'status' => 'healthy']);
        Queue::assertPushed(RecordQueueHeartbeat::class);
    }

    public function test_owner_can_manage_incident_lifecycle_and_action_is_audited(): void
    {
        [$tenant, $user] = $this->createTenantUser();
        $incident = Incident::factory()->create([
            'tenant_id' => $tenant->id,
            'severity' => 'critical',
            'summary' => 'Core router configuration changed',
        ]);

        $response = $this->actingAs($user)->put(route('incidents.update', $incident), [
            'status' => 'resolved',
            'assigned_to' => $user->id,
            'resolution' => 'Reviewed and approved the intended change.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('incidents', [
            'id' => $incident->id,
            'status' => 'resolved',
            'assigned_to' => $user->id,
        ]);
        $this->assertNotNull($incident->refresh()->acknowledged_at);
        $this->assertNotNull($incident->resolved_at);
        $this->assertDatabaseHas('activity_logs', [
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'action' => 'incidents.update',
            'model_type' => Incident::class,
            'model_id' => $incident->id,
        ]);
    }

    public function test_compliance_scan_records_findings_and_can_approve_latest_backup(): void
    {
        Storage::fake('local');
        [$tenant, $user] = $this->createTenantUser();
        $router = Router::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Core Router',
            'version' => '7.20',
        ]);
        Storage::disk('local')->put('router-backups/core.rsc', '/ip service set telnet disabled=no');
        $backup = RouterBackup::factory()->create([
            'tenant_id' => $tenant->id,
            'router_id' => $router->id,
            'path' => 'router-backups/core.rsc',
            'disk' => 'local',
            'checksum' => hash('sha256', '/ip service set telnet disabled=no'),
            'status' => 'success',
        ]);

        $this->actingAs($user)
            ->post(route('compliance.scan', $router))
            ->assertRedirect();

        $this->assertDatabaseHas('compliance_findings', [
            'tenant_id' => $tenant->id,
            'router_id' => $router->id,
            'rule_key' => 'insecure_services',
            'status' => 'critical',
        ]);

        $this->post(route('compliance.baseline', $router), [
            'router_backup_id' => $backup->id,
            'label' => 'Known good production config',
        ])->assertRedirect();

        $this->assertDatabaseHas('configuration_baselines', [
            'tenant_id' => $tenant->id,
            'router_id' => $router->id,
            'router_backup_id' => $backup->id,
            'approved_by' => $user->id,
        ]);
    }

    public function test_audit_log_redacts_nested_secrets(): void
    {
        [$tenant, $user] = $this->createTenantUser();
        $incident = Incident::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)->put(route('incidents.update', $incident), [
            'status' => 'acknowledged',
            'resolution' => null,
            'telegram_bot_token' => 'must-not-be-logged',
        ]);

        $values = ActivityLog::query()->where('action', 'incidents.update')->latest()->value('new_values');

        $this->assertSame('[REDACTED]', $values['input']['telegram_bot_token']);
        $this->assertStringNotContainsString('must-not-be-logged', json_encode($values, JSON_THROW_ON_ERROR));
    }

    public function test_change_request_requires_recoverable_backup_before_approval(): void
    {
        [$tenant, $user] = $this->createTenantUser();
        $router = Router::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)->post(route('change-control.changes.store'), [
            'router_id' => $router->id,
            'title' => 'Change upstream route preference',
            'reason' => 'Move traffic to the preferred transit link.',
            'implementation_plan' => 'Update route distance, verify traffic, and restore the old distance on failure.',
        ])->assertRedirect();

        $changeRequest = ChangeRequest::query()->firstOrFail();
        $this->assertSame('submitted', $changeRequest->status);

        $this->put(route('change-control.changes.update', $changeRequest), [
            'status' => 'approved',
        ])->assertSessionHasErrors('status');

        $this->assertSame('submitted', $changeRequest->refresh()->status);

        $backup = RouterBackup::factory()->create([
            'tenant_id' => $tenant->id,
            'router_id' => $router->id,
            'status' => 'success',
            'path' => 'router-backups/pre-change.rsc',
        ]);

        $this->put(route('change-control.changes.update', $changeRequest), [
            'status' => 'approved',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('change_requests', [
            'id' => $changeRequest->id,
            'status' => 'approved',
            'approved_by' => $user->id,
            'pre_change_backup_id' => $backup->id,
        ]);
    }

    public function test_maintenance_window_dates_and_resources_are_tenant_scoped(): void
    {
        [$tenant, $user] = $this->createTenantUser();
        $router = Router::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)->post(route('change-control.maintenance.store'), [
            'name' => 'Core maintenance',
            'router_id' => $router->id,
            'starts_at' => now()->addDay()->toDateTimeString(),
            'ends_at' => now()->addDay()->addHour()->toDateTimeString(),
            'reason' => 'RouterOS maintenance',
        ])->assertRedirect();

        $this->assertDatabaseHas('maintenance_windows', [
            'tenant_id' => $tenant->id,
            'router_id' => $router->id,
            'created_by' => $user->id,
            'status' => 'scheduled',
        ]);
    }

    /**
     * @return array{Tenant, User}
     */
    protected function createTenantUser(): array
    {
        $tenant = Tenant::query()->create([
            'id' => 'tenant-operations',
            'name' => 'Operations ISP',
            'slug' => 'operations-isp',
            'company_name' => 'Operations ISP',
            'email' => 'owner@example.com',
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

        app()->instance('current_tenant', $tenant);

        return [$tenant, $user];
    }
}
