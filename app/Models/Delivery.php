<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'order_id',
        'driver_id',
        'status',
        'route_summary',
        'restaurant_latitude',
        'restaurant_longitude',
        'destination_latitude',
        'destination_longitude',
        'driver_latitude',
        'driver_longitude',
        'last_location_at',
        'assigned_at',
        'picked_up_at',
        'delivered_at',
    ];

    protected $casts = [
        'restaurant_latitude' => 'float',
        'restaurant_longitude' => 'float',
        'destination_latitude' => 'float',
        'destination_longitude' => 'float',
        'driver_latitude' => 'float',
        'driver_longitude' => 'float',
        'last_location_at' => 'datetime',
        'assigned_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
