<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('tenant.{tenantId}.role.{role}', function ($user, string $tenantId, string $role) {
    return (string) $user->tenant_id === $tenantId && $user->hasAnyRole($role);
});
