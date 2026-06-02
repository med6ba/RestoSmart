<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlatformUserController extends Controller
{
    /**
     * @var array<string, array{label: string, icon: string}>
     */
    private const ROLES = [
        'admin' => ['label' => 'Admins', 'icon' => 'shield-check'],
        'kitchen' => ['label' => 'Kitchen', 'icon' => 'chef-hat'],
        'driver' => ['label' => 'Drivers', 'icon' => 'truck'],
        'client' => ['label' => 'Clients', 'icon' => 'user'],
    ];

    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('platform.users.role', ['role' => 'admin'] + $request->query());
    }

    public function role(Request $request, string $role): View
    {
        abort_unless(array_key_exists($role, self::ROLES), 404);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'suspended'])],
            'tenant_id' => ['nullable', 'string', 'max:80'],
        ]);

        $query = User::query()
            ->withoutGlobalScopes()
            ->with('tenant')
            ->where('role', $role)
            ->when($filters['q'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when(($filters['tenant_id'] ?? null) === 'none', fn ($query) => $query->whereNull('tenant_id'))
            ->when(($filters['tenant_id'] ?? null) && ($filters['tenant_id'] ?? null) !== 'none', function ($query) use ($filters): void {
                $query->where('tenant_id', $filters['tenant_id']);
            })
            ->latest();

        return view('platform.users', [
            'roles' => $this->rolesWithCounts(),
            'selectedRole' => $role,
            'selectedRoleMeta' => self::ROLES[$role],
            'users' => $query->paginate(12)->withQueryString(),
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
        ]);
    }

    public function impersonate(Request $request, User $user): RedirectResponse
    {
        $target = User::query()->withoutGlobalScopes()->findOrFail($user->id);

        abort_if($target->is($request->user()), 422, __('You cannot impersonate your own account.'));
        abort_if($target->hasAnyRole('super'), 403, __('Super admin accounts cannot be managed from this section.'));

        $request->session()->put('impersonator_id', $request->user()->id);
        $request->session()->put('impersonated_user_id', $target->id);

        Auth::login($target);
        $request->session()->regenerate();

        return redirect($this->landingUrlFor($target))
            ->with('status', __('You are now viewing the application as :name.', ['name' => $target->name]));
    }

    public function stopImpersonating(Request $request): RedirectResponse
    {
        $impersonatorId = $request->session()->get('impersonator_id');

        abort_unless($impersonatorId, 403);

        $impersonator = User::query()
            ->withoutGlobalScopes()
            ->whereKey($impersonatorId)
            ->where('role', 'super')
            ->firstOrFail();

        Auth::login($impersonator);
        $request->session()->forget(['impersonator_id', 'impersonated_user_id']);
        $request->session()->regenerate();

        return redirect()->route('platform.users.role', 'admin')
            ->with('status', __('Returned to your super admin account.'));
    }

    /**
     * @return array<string, array{label: string, icon: string, count: int}>
     */
    private function rolesWithCounts(): array
    {
        $counts = User::query()
            ->withoutGlobalScopes()
            ->selectRaw('role, count(*) as aggregate')
            ->groupBy('role')
            ->pluck('aggregate', 'role');

        return collect(self::ROLES)
            ->map(fn (array $role, string $key): array => $role + ['count' => (int) ($counts[$key] ?? 0)])
            ->all();
    }

    private function landingUrlFor(User $user): string
    {
        if ($user->tenant_id && $user->hasAnyRole('admin')) {
            return route('tenant.admin', $user->tenant_id);
        }

        if ($user->tenant_id && $user->hasAnyRole(['kitchen', 'driver', 'client'])) {
            return route('tenant.dashboard', $user->tenant_id);
        }

        return route('dashboard');
    }
}
