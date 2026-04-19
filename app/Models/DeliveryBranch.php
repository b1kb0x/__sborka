<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryBranch extends Model
{
    protected $fillable = [
        'delivery_city_id',
        'name',
        'address',
        'postal_code',
        'type',
        'external_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(DeliveryCity::class, 'delivery_city_id');
    }
}
