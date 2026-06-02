<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\BillingHistory;
use App\Models\PlatformNotification;
use App\Models\RestaurantApplication;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $originalEmail = $user->email;

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($user->hasAnyRole('admin')) {
            $this->syncAdminOwnership($user, $originalEmail);
        }

        return Redirect::to($this->profileEditUrl())->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasAnyRole('admin'), 403);

        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $tenantIds = $this->ownedTenantIds($user);

        DB::transaction(function () use ($user, $tenantIds): void {
            if ($tenantIds !== []) {
                User::query()
                    ->withoutGlobalScopes()
                    ->whereIn('tenant_id', $tenantIds)
                    ->whereKeyNot($user->id)
                    ->delete();

                Subscription::query()->whereIn('tenant_id', $tenantIds)->delete();
                BillingHistory::query()->whereIn('tenant_id', $tenantIds)->delete();
                PlatformNotification::query()->whereIn('tenant_id', $tenantIds)->delete();
                RestaurantApplication::query()->whereIn('tenant_id', $tenantIds)->delete();
                Tenant::query()->whereIn('id', $tenantIds)->delete();
            }

            RestaurantApplication::query()->where('owner_email', $user->email)->delete();
            User::query()->withoutGlobalScopes()->whereKey($user->id)->delete();
        });

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * @return array<int, string>
     */
    private function ownedTenantIds(User $user): array
    {
        return Tenant::query()
            ->where(function ($query) use ($user): void {
                $query->where('owner_email', $user->email);

                if ($user->tenant_id) {
                    $query->orWhere('id', $user->tenant_id);
                }
            })
            ->pluck('id')
            ->all();
    }

    private function syncAdminOwnership(User $user, string $originalEmail): void
    {
        Tenant::query()
            ->where(function ($query) use ($user, $originalEmail): void {
                $query->where('owner_email', $originalEmail);

                if ($user->tenant_id) {
                    $query->orWhere('id', $user->tenant_id);
                }
            })
            ->update(['owner_email' => $user->email]);

        RestaurantApplication::query()
            ->where('owner_email', $originalEmail)
            ->update([
                'owner_name' => $user->name,
                'owner_email' => $user->email,
            ]);
    }

    private function profileEditUrl(): string
    {
        if (function_exists('tenancy') && tenancy()->initialized) {
            return route('tenant.profile.edit', tenant('id'));
        }

        return route('profile.edit');
    }
}
