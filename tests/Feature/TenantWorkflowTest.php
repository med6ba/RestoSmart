<?php

namespace Tests\Feature;

use App\Models\RestaurantTable;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_routes_use_the_restaurant_slug_without_t_prefix(): void
    {
        $tenant = $this->createTenant();

        $this->assertSame('/demo', route('tenant.menu', $tenant->id, false));
        $this->assertSame('/demo/login', route('tenant.login', $tenant->id, false));

        $this->get('/demo')->assertOk();
        $this->get('/t/demo/login')->assertNotFound();
    }

    public function test_saas_login_accepts_only_super_and_admin_accounts(): void
    {
        $tenant = $this->createTenant();

        $admin = $this->createUser('admin', $tenant->id);
        $kitchen = $this->createUser('kitchen', $tenant->id);

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($admin);

        auth()->logout();

        $this->post('/login', [
            'email' => $kitchen->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_tenant_login_accepts_only_kitchen_driver_and_client_accounts(): void
    {
        $tenant = $this->createTenant();

        $kitchen = $this->createUser('kitchen', $tenant->id);
        $admin = $this->createUser('admin', $tenant->id);

        $this->post('/demo/login', [
            'email' => $kitchen->email,
            'password' => 'password',
        ])->assertRedirect(route('tenant.dashboard', $tenant->id, false));

        $this->assertAuthenticatedAs($kitchen);

        auth()->logout();

        $this->post('/demo/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_guest_redirects_match_the_split_workflow(): void
    {
        $tenant = $this->createTenant();

        $this->get('/demo/admin')->assertRedirect(route('login', absolute: false));
        $this->get('/demo/kitchen')->assertRedirect(route('tenant.login', $tenant->id, false));
    }

    public function test_admin_table_cards_download_qr_without_showing_the_menu_link(): void
    {
        $tenant = $this->createTenant();
        $admin = $this->createUser('admin', $tenant->id);
        $table = RestaurantTable::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'T001',
            'qr_token' => 'TABLE-TOKEN-001',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        $this->get('/demo/admin')
            ->assertOk()
            ->assertSee('TABLE-TOKEN-001')
            ->assertSee('Download QR')
            ->assertSee('download=1')
            ->assertSee('download="table-t001-qr.png"', false)
            ->assertDontSee('/demo?table=TABLE-TOKEN-001')
            ->assertDontSee('Open QR')
            ->assertDontSee('Open table link');

        $preview = $this->get("/demo/admin/tables/{$table->id}/qr");
        $preview->assertOk();
        $this->assertStringStartsWith('image/svg+xml', (string) $preview->headers->get('Content-Type'));
        $this->assertNull($preview->headers->get('Content-Disposition'));

        $download = $this->get("/demo/admin/tables/{$table->id}/qr?download=1");
        $download->assertOk();
        $this->assertStringStartsWith('image/png', (string) $download->headers->get('Content-Type'));
        $this->assertSame('attachment; filename="table-t001-qr.png"', $download->headers->get('Content-Disposition'));
    }

    private function createTenant(): Tenant
    {
        return Tenant::query()->create([
            'id' => 'demo',
            'name' => 'Demo Restaurant',
            'slug' => 'demo',
            'owner_email' => 'admin@demo.com',
            'status' => 'trial',
        ]);
    }

    private function createUser(string $role, ?string $tenantId = null): User
    {
        return User::factory()->create([
            'tenant_id' => $tenantId,
            'role' => $role,
            'status' => 'active',
        ]);
    }
}
