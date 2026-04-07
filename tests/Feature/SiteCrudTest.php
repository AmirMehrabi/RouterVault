<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_page_displays_sites_for_current_tenant(): void
    {
        [$tenant, $user] = $this->createTenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);

        Site::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'North Tower',
        ]);

        $response = $this->get(route('sites.index'));

        $response->assertOk();
        $response->assertSee('North Tower');
    }

    public function test_index_page_links_to_topology_map(): void
    {
        [$tenant, $user] = $this->createTenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);

        $response = $this->get(route('sites.index'));

        $response->assertOk();
        $response->assertSee(route('sites.topology'));
        $response->assertSee('Topology Map');
    }

    public function test_topology_page_displays_mapped_sites_only(): void
    {
        [$tenant, $user] = $this->createTenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);

        Site::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Mapped Site',
            'latitude' => '0.3475960',
            'longitude' => '32.5825200',
            'status' => 'active',
        ]);

        Site::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Unmapped Site',
            'latitude' => null,
            'longitude' => null,
        ]);

        $response = $this->get(route('sites.topology'));

        $response->assertOk();
        $response->assertSee('Site topology map');
        $response->assertSee('Mapped Site');
        $response->assertDontSee('Unmapped Site');
    }

    public function test_can_create_site(): void
    {
        [$tenant, $user] = $this->createTenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);

        $response = $this->post(route('sites.store'), [
            'name' => 'HQ Site',
            'code' => 'HQ-001',
            'address' => '123 Main Street',
            'city' => 'Kampala',
            'state' => 'Central',
            'country' => 'Uganda',
            'contact_name' => 'Jane Doe',
            'contact_phone' => '+256700000001',
            'contact_email' => 'jane@example.com',
            'description' => 'Primary POP location',
            'latitude' => '0.3475960',
            'longitude' => '32.5825200',
            'status' => 'active',
        ]);

        $site = Site::where('tenant_id', $tenant->id)->where('code', 'HQ-001')->first();

        $response->assertRedirect(route('sites.show', $site));
        $this->assertDatabaseHas('sites', [
            'tenant_id' => $tenant->id,
            'name' => 'HQ Site',
            'code' => 'HQ-001',
            'status' => 'active',
        ]);
    }

    public function test_can_update_site(): void
    {
        [$tenant, $user] = $this->createTenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);

        $site = Site::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Old Site',
            'code' => 'OLD-001',
        ]);

        $response = $this->put(route('sites.update', $site), [
            'name' => 'Updated Site',
            'code' => 'OLD-001',
            'address' => '456 Updated Road',
            'city' => 'Nairobi',
            'state' => 'Nairobi County',
            'country' => 'Kenya',
            'contact_name' => 'John Ops',
            'contact_phone' => '+254700000002',
            'contact_email' => 'ops@example.com',
            'description' => 'Updated site details',
            'latitude' => '-1.2920659',
            'longitude' => '36.8219462',
            'status' => 'maintenance',
        ]);

        $response->assertRedirect(route('sites.show', $site));
        $this->assertDatabaseHas('sites', [
            'id' => $site->id,
            'name' => 'Updated Site',
            'status' => 'maintenance',
        ]);
    }

    public function test_cannot_access_site_from_another_tenant(): void
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

        $foreignSite = Site::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);

        $response = $this->get(route('sites.show', $foreignSite->id));

        $response->assertForbidden();
    }

    public function test_can_delete_site(): void
    {
        [$tenant, $user] = $this->createTenantUser();
        $this->actingAs($user);
        app()->instance('current_tenant', $tenant);

        $site = Site::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->delete(route('sites.destroy', $site));

        $response->assertRedirect(route('sites.index'));
        $this->assertDatabaseMissing('sites', [
            'id' => $site->id,
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
