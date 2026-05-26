<?php

namespace Tests\Feature\Auth;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user->assignRole('admin');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_users_can_logout_with_get_fallback(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_tenant_user_cannot_access_admin_panel(): void
    {
        $tenant = Tenant::create([
            'name' => 'Despacho Test',
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/admin/tenants')
            ->assertForbidden();
    }

    public function test_dashboard_redirects_tenant_users_to_client_panel(): void
    {
        $tenant = Tenant::create([
            'name' => 'Despacho Test',
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('client.dashboard'));
    }

    public function test_dashboard_redirects_admin_users_to_admin_panel(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user->assignRole('admin');

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('admin.dashboard'));
    }
}
