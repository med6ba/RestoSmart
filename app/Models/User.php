<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use BelongsToTenant, HasFactory, Notifiable;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'role',
        'status',
        'available',
        'default_address',
        'password',
    ];

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super';
    }

    public function hasAnyRole(array|string $roles): bool
    {
        return in_array($this->role, (array) $roles, true);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Order::class, 'driver_id');
    }

    public function sentDeliveryMessages(): HasMany
    {
        return $this->hasMany(DeliveryMessage::class, 'sender_id');
    }

    public function receivedDeliveryMessages(): HasMany
    {
        return $this->hasMany(DeliveryMessage::class, 'receiver_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'available' => 'boolean',
        ];
    }
}
