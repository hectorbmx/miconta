<?php

namespace Tests\Feature\Auth;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $plan = Plan::where('slug', 'prueba-gratis')->firstOrFail();
        Notification::fake();

        $response = $this->post('/register', [
            'tenant_name' => 'Despacho Test',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'plan_id' => $plan->id,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('verification.notice'));

        $tenant = Tenant::where('billing_email', 'test@example.com')->first();

        $this->assertNotNull($tenant);
        $this->assertSame($plan->id, $tenant->plan_id);
        $this->assertTrue(User::where('email', 'test@example.com')
            ->where('tenant_id', $tenant->id)
            ->exists());

        $user = User::where('email', 'test@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('tenant_admin'));
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_tenant_billing_email_must_be_unique(): void
    {
        $plan = Plan::where('slug', 'prueba-gratis')->firstOrFail();

        Tenant::create([
            'name' => 'Despacho existente',
            'billing_email' => 'test@example.com',
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);

        $response = $this->post('/register', [
            'tenant_name' => 'Despacho Test',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'plan_id' => $plan->id,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
