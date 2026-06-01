<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantLoginRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function login(): View
    {
        return view('tenant.auth.login', [
            'demoAccounts' => $this->demoAccounts(),
        ]);
    }

    public function authenticate(TenantLoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        return redirect()->intended(route('tenant.dashboard', tenant('id'), absolute: false));
    }

    public function register(): View
    {
        return view('tenant.auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:40'],
            'default_address' => ['nullable', 'string', 'max:700'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'default_address' => $data['default_address'] ?? null,
            'role' => 'client',
            'status' => 'active',
            'password' => Hash::make($data['password']),
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('tenant.menu', tenant('id'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('tenant.menu', tenant('id'));
    }

    private function demoAccounts(): array
    {
        $roleLabels = [
            'kitchen' => 'Kitchen',
            'driver' => 'Driver',
            'client' => 'Client',
        ];

        return collect(array_keys($roleLabels))
            ->map(function (string $role) use ($roleLabels) {
                $user = User::query()
                    ->where('role', $role)
                    ->where('status', 'active')
                    ->orderBy('id')
                    ->first();

                if (! $user) {
                    return null;
                }

                return [
                    'label' => __($roleLabels[$role]),
                    'email' => $user->email,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
