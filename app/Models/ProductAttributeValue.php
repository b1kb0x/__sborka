<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttributeValue extends Model
{
    protected $fillable = [
        'product_id',
        'product_attribute_id',
        'product_attribute_option_id',
        'value_string',
        'value_text',
        'value_number',
        'value_boolean',
    ];

    protected $casts = [
        'value_boolean' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class, 'product_attribute_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(ProductAttributeOption::class, 'product_attribute_option_id');
    }

    public function getDisplayValueAttribute(): string
    {
        if ($this->option) {
            return $this->option->value;
        }

        if ($this->value_string !== null && $this->value_string !== '') {
            return $this->value_string;
        }

        if ($this->value_text !== null && $this->value_text !== '') {
            return $this->value_text;
        }

        if ($this->value_number !== null) {
            $number = rtrim(rtrim((string) $this->value_number, '0'), '.');
            $unit = $this->attribute?->unit ? ' ' . $this->attribute->unit : '';

            return $number . $unit;
        }

        if ($this->value_boolean !== null) {
            return $this->value_boolean ? 'Да' : 'Нет';
        }

        return '';
    }
}
