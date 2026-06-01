<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasAnyRole('admin')) {
            return redirect()->route('tenant.admin', tenant('id'));
        }

        if ($user->hasAnyRole('kitchen')) {
            return redirect()->route('tenant.kitchen', tenant('id'));
        }

        if ($user->hasAnyRole('driver')) {
            return redirect()->route('tenant.driver', tenant('id'));
        }

        abort_unless($user->hasAnyRole('client'), 403);

        return view('tenant.dashboard', [
            'user' => $user,
            'myOrders' => Order::query()->where('user_id', $user->id)->latest()->limit(8)->get(),
            'notifications' => Notification::query()
                ->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)->orWhere('role', $user->role);
                })
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }
}
