<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryCity extends Model
{
    protected $fillable = [
        'delivery_region_id',
        'name',
        'external_id',
        'district_name',
        'postal_code',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(DeliveryRegion::class, 'delivery_region_id');
    }

    public function branches(): HasMany
    {
        return $this->hasMany(DeliveryBranch::class);
    }
}
