<?php

namespace Tests\Feature;

use App\Models\BackupSchedule;
use App\Models\DiffAlert;
use App\Models\Router;
use App\Models\RouterBackup;
use App\Models\RouterBackupDiff;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsAndDiffAlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_acknowledging_an_alert_returns_json_and_updates_timestamps(): void
    {
        [$tenant, $user] = $this->tenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);
        $router = Router::factory()->create(['tenant_id' => $tenant->id]);
        $previous = RouterBackup::factory()->create(['tenant_id' => $tenant->id, 'router_id' => $router->id]);
        $backup = RouterBackup::factory()->create(['tenant_id' => $tenant->id, 'router_id' => $router->id]);
        $diff = RouterBackupDiff::create([
            'router_backup_id' => $backup->id,
            'previous_router_backup_id' => $previous->id,
            'added_lines' => 1,
            'removed_lines' => 0,
            'unified_diff' => '+changed',
            'hunks' => [],
        ]);
        $alert = DiffAlert::create([
            'tenant_id' => $tenant->id,
            'router_id' => $router->id,
            'router_backup_id' => $backup->id,
            'previous_router_backup_id' => $previous->id,
            'router_backup_diff_id' => $diff->id,
            'severity' => 'high',
            'status' => 'unread',
            'summary' => 'Firewall changed',
            'added_lines' => 1,
            'removed_lines' => 0,
        ]);

        $this->postJson(route('diff-alerts.status', $alert), ['status' => 'acknowledged'])
            ->assertOk()
            ->assertJsonPath('alert.status', 'acknowledged');

        $alert->refresh();
        $this->assertSame('acknowledged', $alert->status);
        $this->assertNotNull($alert->acknowledged_at);
        $this->assertNotNull($alert->read_at);
    }

    public function test_alert_inbox_defaults_to_unread_and_exposes_live_interaction(): void
    {
        [$tenant, $user] = $this->tenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);

        $this->get(route('diff-alerts.index'))
            ->assertOk()
            ->assertSee("tab: 'unread'", false)
            ->assertSee('async acknowledge(id, url)', false)
            ->assertSee('Alert settings');
    }

    public function test_general_settings_keep_email_immutable_and_propagate_timezone_to_schedules(): void
    {
        [$tenant, $user] = $this->tenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);
        $schedule = BackupSchedule::factory()->create([
            'tenant_id' => $tenant->id,
            'timezone' => 'UTC',
        ]);

        $this->put(route('settings.update.general'), [
            'company_name' => 'Updated ISP',
            'phone' => '+982100000000',
            'country' => 'IR',
            'timezone' => 'Asia/Tehran',
            'email' => 'changed@example.com',
        ])->assertRedirect(route('settings.index', ['tab' => 'general']));

        $tenant->refresh();
        $this->assertSame('Updated ISP', $tenant->company_name);
        $this->assertSame('owner@example.com', $tenant->email);
        $this->assertSame('Asia/Tehran', $tenant->timezone);
        $this->assertSame('Asia/Tehran', $schedule->fresh()->timezone);
    }

    public function test_tenancy_middleware_applies_the_tenant_timezone(): void
    {
        [$tenant, $user] = $this->tenantUser();
        $tenant->update(['timezone' => 'Asia/Tehran']);
        $this->actingAs($user);

        $this->get(route('settings.index'))->assertOk();

        $this->assertSame('Asia/Tehran', config('app.timezone'));
        $this->assertSame('Asia/Tehran', date_default_timezone_get());

        config(['app.timezone' => 'UTC']);
        date_default_timezone_set('UTC');
    }

    /**
     * @return array{Tenant, User}
     */
    protected function tenantUser(): array
    {
        $tenant = Tenant::create([
            'id' => 'settings-alerts',
            'name' => 'Settings Alerts',
            'slug' => 'settings-alerts',
            'company_name' => 'Settings Alerts',
            'email' => 'owner@example.com',
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
