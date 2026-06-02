<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlatformPaymentController extends Controller
{
    public function index(Request $request): View
    {
        $tenants = Tenant::with(['plan', 'subscription', 'billingHistories' => function ($query) {
            $query->latest('issued_at');
        }])->latest()->paginate(15);

        return view('platform.payments', [
            'tenants' => $tenants,
        ]);
    }
}
