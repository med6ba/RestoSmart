<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_users_by_role(): void
    {
        $super = User::factory()->create(['role' => 'super', 'tenant_id' => null]);
        User::factory()->create(['role' => 'admin', 'name' => 'Demo Admin']);

        $this->actingAs($super)
            ->get('/users/admin')
            ->assertOk()
            ->assertSee('Administrateurs')
            ->assertSee('Demo Admin');
    }

    public function test_non_super_admin_cannot_view_platform_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/users/admin')
            ->assertForbidden();
    }

    public function test_super_admin_role_is_not_managed_from_users_section(): void
    {
        $super = User::factory()->create(['role' => 'super', 'tenant_id' => null]);
        $otherSuper = User::factory()->create(['role' => 'super', 'tenant_id' => null]);

        $this->actingAs($super)
            ->get('/users/super')
            ->assertNotFound();

        $this->actingAs($super)
            ->post(route('platform.users.impersonate', $otherSuper))
            ->assertForbidden();
    }

    public function test_super_admin_can_impersonate_and_return(): void
    {
        $tenant = Tenant::query()->create([
            'id' => 'demo',
            'name' => 'Demo Restaurant',
            'slug' => 'demo',
            'owner_email' => 'admin@demo.com',
            'status' => 'trial',
        ]);

        $super = User::factory()->create(['role' => 'super', 'tenant_id' => null]);
        $admin = User::factory()->create(['role' => 'admin', 'tenant_id' => $tenant->id]);

        $this->actingAs($super)
            ->post(route('platform.users.impersonate', $admin))
            ->assertRedirect(route('tenant.admin', $tenant->id, false))
            ->assertSessionHas('impersonator_id', $super->id)
            ->assertSessionHas('impersonated_user_id', $admin->id);

        $this->assertAuthenticatedAs($admin);

        $this->post(route('impersonation.stop'))
            ->assertRedirect(route('platform.users.role', 'admin', false))
            ->assertSessionMissing('impersonator_id')
            ->assertSessionMissing('impersonated_user_id');

        $this->assertAuthenticatedAs($super);
    }
}
