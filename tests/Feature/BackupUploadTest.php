<?php

namespace Tests\Feature;

use App\Models\BackupToken;
use App\Models\Plan;
use App\Models\Router;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupUploadTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected Router $router;

    protected BackupToken $token;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = Plan::create([
            'name' => 'Free',
            'internal_name' => 'test_free',
            'price' => 0,
            'max_routers' => 1,
            'backup_retention_days' => 7,
            'alert_channels' => ['in_app'],
            'max_users' => 1,
            'is_saas_plan' => true,
            'status' => 'active',
        ]);

        $this->tenant = Tenant::create([
            'id' => 'test-tenant-id',
            'name' => 'Test Tenant',
            'slug' => 'test-tenant',
            'company_name' => 'Test Company',
            'email' => 'test@example.com',
            'status' => 'active',
            'saas_plan_id' => $plan->id,
        ]);

        $this->router = Router::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Router',
            'ip_address' => '192.168.1.1',
            'status' => 'offline',
        ]);

        $this->token = BackupToken::generateForRouter($this->router);
    }

    public function test_upload_requires_token(): void
    {
        $file = UploadedFile::fake()->createWithContent('test.rsc', '# routeros config');

        $this->postJson('/api/v1/backups/upload', [
            'config' => $file,
        ])->assertUnauthorized();
    }

    public function test_upload_rejects_invalid_token(): void
    {
        $file = UploadedFile::fake()->createWithContent('test.rsc', '# routeros config');

        $this->postJson('/api/v1/backups/upload', [
            'config' => $file,
        ], [
            'Authorization' => 'Bearer invalid-token',
        ])->assertUnauthorized();
    }

    public function test_upload_accepts_valid_token(): void
    {
        Storage::fake('local');

        $configContent = "/ip address\nadd address=192.168.1.1/24 interface=ether1";
        $file = UploadedFile::fake()->createWithContent('test.rsc', $configContent);

        $this->postJson('/api/v1/backups/upload', [
            'config' => $file,
        ], [
            'Authorization' => "Bearer {$this->token->token}",
        ])->assertOk()->assertJson([
            'success' => true,
            'changed' => true,
        ]);

        $this->assertDatabaseHas('router_backups', [
            'tenant_id' => $this->tenant->id,
            'router_id' => $this->router->id,
            'status' => 'success',
        ]);
    }

    public function test_upload_stores_backup_file(): void
    {
        Storage::fake('local');

        $configContent = "/ip address\nadd address=192.168.1.1/24 interface=ether1";
        $file = UploadedFile::fake()->createWithContent('test.rsc', $configContent);

        $this->postJson('/api/v1/backups/upload', [
            'config' => $file,
        ], [
            'Authorization' => "Bearer {$this->token->token}",
        ])->assertOk();

        Storage::disk('local')->assertExists('router-backups/');
    }

    public function test_upload_marks_token_as_used(): void
    {
        Storage::fake('local');

        $configContent = "/ip address\nadd address=192.168.1.1/24 interface=ether1";
        $file = UploadedFile::fake()->createWithContent('test.rsc', $configContent);

        $this->postJson('/api/v1/backups/upload', [
            'config' => $file,
        ], [
            'Authorization' => "Bearer {$this->token->token}",
        ])->assertOk();

        $this->token->refresh();
        $this->assertNotNull($this->token->last_used_at);
    }

    public function test_upload_rejects_empty_file(): void
    {
        $file = UploadedFile::fake()->createWithContent('test.rsc', '');

        $this->postJson('/api/v1/backups/upload', [
            'config' => $file,
        ], [
            'Authorization' => "Bearer {$this->token->token}",
        ])->assertStatus(500);
    }

    public function test_upload_rejects_non_rsc_file(): void
    {
        $file = UploadedFile::fake()->create('test.php', 100, 'application/php');

        $this->postJson('/api/v1/backups/upload', [
            'config' => $file,
        ], [
            'Authorization' => "Bearer {$this->token->token}",
        ])->assertUnprocessable();
    }
}
