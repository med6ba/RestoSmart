<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantTable extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'code',
        'qr_token',
        'sort_order',
        'is_active',
        'is_occupied',
        'occupied_order_id',
        'occupied_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_occupied' => 'boolean',
        'occupied_at' => 'datetime',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function occupiedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'occupied_order_id');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('is_occupied', false);
    }

    public function qrPayload(): string
    {
        return $this->qr_token;
    }
}
