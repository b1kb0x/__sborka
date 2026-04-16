<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'description',
        'image_path',
        'price',
        'stock',
        'is_active',
    ];

    protected $casts = [
        'price' => 'integer',
        'stock' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function attributeValue(string $slug): ?ProductAttributeValue
    {
        if ($this->relationLoaded('attributeValues')) {
            return $this->attributeValues
                ->first(fn (ProductAttributeValue $value) => $value->attribute?->slug === $slug);
        }

        return $this->attributeValues()
            ->whereHas('attribute', fn ($query) => $query->where('slug', $slug))
            ->with(['attribute', 'option'])
            ->first();
    }

    public function attributeDisplayValue(string $slug, ?string $default = null): ?string
    {
        $value = $this->attributeValue($slug);

        return $value?->display_value ?? $default;
    }

    public function attributesForGroup(string $group): Collection
    {
        if ($this->relationLoaded('attributeValues')) {
            return $this->attributeValues
                ->filter(fn (ProductAttributeValue $value) => $value->attribute
                    && $value->attribute->display_group === $group
                    && $value->attribute->is_visible)
                ->sortBy(fn (ProductAttributeValue $value) => $value->attribute?->sort_order ?? 0)
                ->values();
        }

        return $this->attributeValues()
            ->whereHas('attribute', fn ($query) => $query
                ->where('display_group', $group)
                ->where('is_visible', true))
            ->with(['attribute', 'option'])
            ->get()
            ->sortBy(fn (ProductAttributeValue $value) => $value->attribute?->sort_order ?? 0)
            ->values();
    }
}
