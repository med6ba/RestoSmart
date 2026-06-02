<?php

namespace App\Services;

use App\Models\BillingHistory;
use App\Models\Plan;
use App\Models\PlatformNotification;
use App\Models\RestaurantApplication;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PlatformProvisioningService
{
    public function approve(RestaurantApplication $application, ?int $planId = null, ?string $note = null): Tenant
    {
        $this->ensureAdminCanReceiveTenant($application);

        $plan = Plan::query()->find($planId ?: $application->plan_id)
            ?? Plan::query()->where('slug', 'starter')->firstOrFail();

        $tenant = Tenant::query()->firstOrCreate(
            ['id' => $application->desired_slug],
            [
                'name' => $application->restaurant_name,
                'slug' => $application->desired_slug,
                'owner_email' => $application->owner_email,
                'phone' => $application->phone,
                'address' => $application->address,
                'status' => 'trial',
                'trial_ends_at' => now()->addDays(30),
                'current_period_ends_at' => now()->addDays(30),
                'plan_id' => $plan->id,
                'data' => [
                    'approved_from_application' => $application->id,
                    'local_url' => '/'.$application->desired_slug,
                ],
            ],
        );

        DB::transaction(function () use ($application, $plan, $tenant, $note) {
            $tenant->update([
                'name' => $application->restaurant_name,
                'owner_email' => $application->owner_email,
                'plan_id' => $plan->id,
                'status' => $tenant->status === 'suspended' ? 'suspended' : 'trial',
                'trial_ends_at' => $tenant->trial_ends_at ?: now()->addDays(30),
                'current_period_ends_at' => $tenant->current_period_ends_at ?: now()->addDays(30),
            ]);

            Subscription::query()->updateOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'plan_id' => $plan->id,
                    'status' => $tenant->status,
                    'trial_started_at' => now(),
                    'trial_ends_at' => $tenant->trial_ends_at,
                    'current_period_started_at' => now(),
                    'current_period_ends_at' => $tenant->current_period_ends_at,
                ],
            );

            BillingHistory::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'status' => 'trial_credit'],
                [
                    'plan_id' => $plan->id,
                    'amount_cents' => 0,
                    'issued_at' => now(),
                ],
            );

            User::query()->updateOrCreate(
                ['email' => $application->owner_email],
                [
                    'tenant_id' => $tenant->id,
                    'name' => $application->owner_name,
                    'phone' => $application->phone,
                    'role' => 'admin',
                    'status' => 'active',
                    'available' => false,
                    'default_address' => $application->address,
                    'password' => Hash::make('password'),
                ],
            );

            $application->update([
                'status' => 'approved',
                'tenant_id' => $tenant->id,
                'decision_note' => $note,
                'decided_at' => now(),
            ]);

            PlatformNotification::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'type' => 'tenant_approved'],
                [
                    'title' => __('Restaurant approved'),
                    'body' => __(':restaurant is ready at /:tenant', ['restaurant' => $application->restaurant_name, 'tenant' => $tenant->id]),
                ],
            );
        });

        return $tenant;
    }

    private function ensureAdminCanReceiveTenant(RestaurantApplication $application): void
    {
        $existingTenant = Tenant::query()
            ->where('owner_email', $application->owner_email)
            ->where('id', '!=', $application->desired_slug)
            ->first();

        $existingAdmin = User::query()
            ->where('email', $application->owner_email)
            ->whereNotNull('tenant_id')
            ->where('tenant_id', '!=', $application->desired_slug)
            ->first();

        if ($existingTenant || $existingAdmin) {
            throw ValidationException::withMessages([
                'owner_email' => __('This admin already owns a restaurant workspace.'),
            ]);
        }
    }

    public function reject(RestaurantApplication $application, ?string $note = null): void
    {
        $application->update([
            'status' => 'rejected',
            'decision_note' => $note,
            'decided_at' => now(),
        ]);

        PlatformNotification::query()->create([
            'type' => 'application_rejected',
            'title' => __('Restaurant application rejected'),
            'body' => __(':restaurant was rejected. :note', ['restaurant' => $application->restaurant_name, 'note' => Str::limit((string) $note, 120)]),
        ]);
    }
}
