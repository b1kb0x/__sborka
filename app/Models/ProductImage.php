<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'file_name',
        'alt',
        'is_primary',
        'sort_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function baseDirectory(): string
    {
        return 'products';
    }

    public function path(string $variant = 'original'): string
    {
        $prefix = match ($variant) {
            'thumbnail' => "{$this->product_id}_thumbnail_",
            'preview' => "{$this->product_id}_preview_",
            'original' => "{$this->product_id}_",
            default => throw new InvalidArgumentException("Unknown image variant [{$variant}]"),
        };

        return $this->baseDirectory() . '/' . $prefix . $this->file_name;
    }

    public function url(string $variant = 'original'): string
    {
        return Storage::disk('public')->url($this->path($variant));
    }

    public function getThumbnailUrlAttribute(): string
    {
        return $this->url('thumbnail');
    }

    public function getPreviewUrlAttribute(): string
    {
        return $this->url('preview');
    }

    public function getOriginalUrlAttribute(): string
    {
        return $this->url('original');
    }
}
