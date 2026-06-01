<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login', [
            'demoAccounts' => $this->demoAccounts(),
        ]);
    }

    private function demoAccounts(): array
    {
        $roleLabels = [
            'super' => 'Super Admin',
            'admin' => 'Admin',
        ];

        return collect(array_keys($roleLabels))
            ->map(function (string $role) use ($roleLabels) {
                $user = User::query()
                    ->where('role', $role)
                    ->where('status', 'active')
                    ->when($role === 'super', fn ($query) => $query->whereNull('tenant_id'))
                    ->when($role === 'admin', fn ($query) => $query->whereNotNull('tenant_id'))
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

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
