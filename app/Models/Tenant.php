<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    protected $fillable = [
        'id',
        'name',
        'slug',
        'owner_email',
        'phone',
        'address',
        'status',
        'trial_ends_at',
        'current_period_ends_at',
        'plan_id',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
        'trial_ends_at' => 'datetime',
        'current_period_ends_at' => 'datetime',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    public function routePrefix(): string
    {
        return '/'.$this->id;
    }

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'slug',
            'owner_email',
            'phone',
            'address',
            'status',
            'trial_ends_at',
            'current_period_ends_at',
            'plan_id',
            'created_at',
            'updated_at',
        ];
    }
}
