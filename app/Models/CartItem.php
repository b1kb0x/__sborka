<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'user_id',
        'row_id',
        'product_id',
        'name',
        'price',
        'qty',
        'grind_type',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'product_id' => 'integer',
        'price' => 'integer',
        'qty' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
