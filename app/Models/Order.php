<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use BelongsToTenant;

    public const STATUS_FLOW = [
        'received' => 'Received',
        'preparing' => 'Preparing',
        'ready' => 'Ready',
        'assigned' => 'Assigned',
        'out_for_delivery' => 'Out for delivery',
        'delivered' => 'Delivered',
        'collected' => 'Collected',
        'cancelled' => 'Cancelled',
    ];

    public const TYPE_LABELS = [
        'local' => 'Local dine-in',
        'takeaway' => 'Takeaway',
        'delivery' => 'Delivery',
        'click_collect' => 'Takeaway',
    ];

    protected $fillable = [
        'tenant_id',
        'public_code',
        'user_id',
        'driver_id',
        'customer_address_id',
        'restaurant_table_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'delivery_address',
        'type',
        'status',
        'payment_status',
        'subtotal_cents',
        'delivery_fee_cents',
        'total_cents',
        'kitchen_notes',
        'placed_at',
        'ready_at',
        'delivered_at',
        'collected_at',
    ];

    protected $casts = [
        'placed_at' => 'datetime',
        'ready_at' => 'datetime',
        'delivered_at' => 'datetime',
        'collected_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'customer_address_id');
    }

    public function restaurantTable(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function deliveryMessages(): HasMany
    {
        return $this->hasMany(DeliveryMessage::class);
    }

    public function latestDeliveryMessage(): HasOne
    {
        return $this->hasOne(DeliveryMessage::class)->latestOfMany();
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    public function formattedTotal(): string
    {
        return Money::mad($this->total_cents);
    }

    public function typeLabel(): string
    {
        return __(self::TYPE_LABELS[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type)));
    }
}
