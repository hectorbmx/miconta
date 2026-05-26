<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\TenantRoleSeeder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $plans = Plan::where('is_active', true)
            ->where('slug', 'prueba-gratis')
            ->orderBy('price')
            ->orderBy('name')
            ->get();

        return view('auth.register', compact('plans'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tenant_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class),
                Rule::unique('tenants', 'billing_email'),
            ],
            'plan_id' => [
                'required',
                Rule::exists('plans', 'id')
                    ->where('is_active', true)
                    ->where('slug', 'prueba-gratis'),
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        app(TenantRoleSeeder::class)->run();

        $user = DB::transaction(function () use ($request) {
            $tenant = Tenant::create([
                'name' => $request->tenant_name,
                'billing_email' => $request->email,
                'plan_id' => $request->plan_id,
                'status' => 'active',
                'trial_ends_at' => now()->addDays(14),
            ]);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole('tenant_admin');

            return $user;
        });

        event(new Registered($user));

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        Log::info('Enlace de activacion de cuenta generado.', [
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'email' => $user->email,
            'verification_url' => $verificationUrl,
        ]);

        Auth::login($user);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Cuenta creada. Revisa tu correo para activar la cuenta.',
                'verify_email_url' => route('verification.notice'),
            ]);
        }

        return redirect()->route('verification.notice');
    }
}
