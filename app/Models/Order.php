<?php

namespace App\Models;

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'email',
        'region',
        'city',
        'address',
        'comment',
        'subtotal',
        'total',
        'status',
        'fulfillment_status',
        'carrier_name',
        'tracking_number',
        'handed_to_carrier_at',
        'delivered_at',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'total' => 'integer',
        'status' => OrderStatus::class,
        'fulfillment_status' => FulfillmentStatus::class,
        'handed_to_carrier_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getFulfillmentStatusLabelAttribute(): string
    {
        return $this->fulfillment_status?->label() ?? '';
    }

    public function getCustomerStatusAttribute(): string
    {
        if (! $this->user_id) {
            return 'guest';
        }

        $user = $this->relationLoaded('user')
            ? $this->getRelation('user')
            : $this->user()->first();

        if (! $user) {
            return 'guest';
        }

        return $user->status?->value
            ?? (is_string($user->status) ? $user->status : 'guest');
    }
}
