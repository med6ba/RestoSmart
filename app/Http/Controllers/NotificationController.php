<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\PlatformNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if ($user->isSuperAdmin()) {
            $notifications = PlatformNotification::query()
                ->latest()
                ->take(10)
                ->get()
                ->map(fn ($n) => [
                    'id' => $n->id,
                    'type' => $n->type,
                    'title' => $n->title,
                    'body' => $n->body,
                    'read_at' => $n->read_at,
                    'created_at' => $n->created_at,
                ]);
        } else {
            $notifications = Notification::query()
                ->where('tenant_id', $user->tenant_id)
                ->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                          ->orWhere('role', $user->role);
                })
                ->latest()
                ->take(10)
                ->get()
                ->map(fn ($n) => [
                    'id' => $n->id,
                    'type' => $n->type,
                    'title' => $n->title,
                    'body' => $n->body,
                    'read_at' => $n->read_at,
                    'created_at' => $n->created_at,
                ]);
        }

        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => $notifications->whereNull('read_at')->count(),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $now = now();

        if ($user->isSuperAdmin()) {
            PlatformNotification::query()
                ->whereNull('read_at')
                ->update(['read_at' => $now]);
        } else {
            Notification::query()
                ->where('tenant_id', $user->tenant_id)
                ->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                          ->orWhere('role', $user->role);
                })
                ->whereNull('read_at')
                ->update(['read_at' => $now]);
        }

        return response()->json(['status' => 'success']);
    }
}
