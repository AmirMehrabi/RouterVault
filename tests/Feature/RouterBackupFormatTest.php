<?php

namespace Tests\Feature;

use App\Models\Router;
use App\Models\RouterBackup;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Backups\RouterBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RouterBackupFormatTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_default_and_router_form_preferences_are_persisted(): void
    {
        [$tenant, $user] = $this->tenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);

        $response = $this->post(route('routers.store'), [
            'name' => 'Core Router',
            'vendor' => 'Mikrotik',
            'ip_address' => '192.0.2.10',
            'api_port' => 8728,
            'ssh_port' => 22,
            'enable_api' => '1',
            'enable_ssh' => '1',
            'backup_binary_enabled' => '1',
            'credential_source' => 'manual',
            'api_username' => 'admin',
            'api_password' => 'secret',
        ]);

        $router = Router::query()->firstOrFail();
        $response->assertRedirect(route('routers.show', $router));
        $this->assertFalse($router->backup_rsc_enabled);
        $this->assertTrue($router->backup_binary_enabled);
    }

    public function test_binary_backup_requires_ssh(): void
    {
        [$tenant, $user] = $this->tenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);

        $this->post(route('routers.store'), [
            'name' => 'Core Router',
            'vendor' => 'Mikrotik',
            'ip_address' => '192.0.2.11',
            'api_port' => 8728,
            'ssh_port' => 22,
            'enable_api' => '1',
            'enable_ssh' => '0',
            'backup_binary_enabled' => '1',
            'credential_source' => 'manual',
            'api_username' => 'admin',
            'api_password' => 'secret',
        ])->assertSessionHasErrors('backup_binary_enabled');
    }

    public function test_manual_backup_is_rejected_when_both_formats_are_disabled(): void
    {
        [$tenant, $user] = $this->tenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);
        $router = Router::factory()->create([
            'tenant_id' => $tenant->id,
            'backup_rsc_enabled' => false,
            'backup_binary_enabled' => false,
        ]);

        $this->postJson(route('routers.backup', $router))
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Backups are disabled for this router.');
    }

    public function test_both_formats_create_independent_artifacts(): void
    {
        Storage::fake('local');
        [$tenant, $user] = $this->tenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);
        $router = Router::factory()->create([
            'tenant_id' => $tenant->id,
            'backup_rsc_enabled' => true,
            'backup_binary_enabled' => true,
        ]);
        $service = app(RouterBackupService::class);
        $service->fakeExportUsing(fn (): string => "/system identity set name=core\n");
        $service->fakeBinaryBackupUsing(function ($router, $backup): array {
            $path = "router-backups/{$router->tenant_id}/{$router->id}/{$backup->id}/test.backup";
            Storage::disk('local')->put($path, 'binary-data');

            return [
                'path' => $path,
                'checksum' => hash('sha256', 'binary-data'),
                'size_bytes' => 11,
                'cleanup_error' => null,
            ];
        });

        $backup = $service->create($router);

        $this->assertSame('success', $backup->status);
        $this->assertCount(2, $backup->artifacts);
        $this->assertSame(['binary', 'rsc'], $backup->artifacts->pluck('type')->sort()->values()->all());
    }

    public function test_one_failed_format_produces_partial_success_and_keeps_the_other_file(): void
    {
        Storage::fake('local');
        [$tenant, $user] = $this->tenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);
        $router = Router::factory()->create([
            'tenant_id' => $tenant->id,
            'backup_rsc_enabled' => true,
            'backup_binary_enabled' => true,
        ]);
        $service = app(RouterBackupService::class);
        $service->fakeExportUsing(fn (): string => "/system identity set name=core\n");
        $service->fakeBinaryBackupUsing(fn (): never => throw new \RuntimeException('SCP failed'));

        $backup = $service->create($router);

        $this->assertSame('partial_success', $backup->status);
        $this->assertSame('success', $backup->artifacts->firstWhere('type', 'rsc')->status);
        $this->assertSame('failed', $backup->artifacts->firstWhere('type', 'binary')->status);
        Storage::disk('local')->assertExists($backup->artifacts->firstWhere('type', 'rsc')->path);
    }

    public function test_compare_workspace_identifies_base_and_comparison_snapshots(): void
    {
        Storage::fake('local');
        [$tenant, $user] = $this->tenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);
        $router = Router::factory()->create(['tenant_id' => $tenant->id]);
        $base = RouterBackup::factory()->create([
            'tenant_id' => $tenant->id,
            'router_id' => $router->id,
            'routeros_version' => '7.15',
            'disk' => 'local',
            'path' => 'router-backups/base.rsc',
        ]);
        $comparison = RouterBackup::factory()->create([
            'tenant_id' => $tenant->id,
            'router_id' => $router->id,
            'routeros_version' => '7.16',
            'disk' => 'local',
            'path' => 'router-backups/comparison.rsc',
        ]);
        Storage::disk('local')->put($base->path, "/system identity\nset name=old\n");
        Storage::disk('local')->put($comparison->path, "/system identity\nset name=new\n");

        $this->get(route('backups.compare', [
            'router_id' => $router->id,
            'old_backup_id' => $base->id,
            'new_backup_id' => $comparison->id,
        ]))
            ->assertOk()
            ->assertSee("Backup #{$comparison->id}")
            ->assertSee('compared with')
            ->assertSee("#{$base->id}")
            ->assertSee('RouterOS 7.15')
            ->assertSee('7.16');
    }

    /**
     * @return array{Tenant, User}
     */
    protected function tenantUser(): array
    {
        $tenant = Tenant::create([
            'id' => 'backup-formats',
            'name' => 'Backup Formats',
            'slug' => 'backup-formats',
            'company_name' => 'Backup Formats',
            'email' => 'owner@example.com',
            'phone' => '+15550000000',
            'country' => 'US',
            'timezone' => 'UTC',
            'status' => 'active',
        ]);

        return [$tenant, User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'owner',
            'status' => 'active',
        ])];
    }
}
