<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryRegion extends Model
{
    protected $fillable = [
        'delivery_service_id',
        'name',
        'external_id',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(DeliveryService::class, 'delivery_service_id');
    }

    public function cities(): HasMany
    {
        return $this->hasMany(DeliveryCity::class);
    }
}
