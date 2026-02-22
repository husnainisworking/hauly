<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'short_description',
        'price', 'compare_price', 'sku', 'stock', 'images',
        'is_active', 'is_featured', 'track_stock', 'weight',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'images' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'track_stock' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getPrimaryImageAttribute(): string
    {
        $images = $this->images ?? [];
        return $images[0] ?? 'https://placehold.co/400x400/e2e8f0/64748b?text=No+Image';
    }

    public function isOnSale(): bool
    {
        return $this->compare_price && $this->compare_price > $this->price;
    }

    public function getDiscountPercentAttribute(): int
    {
        if (!$this->isOnSale()) return 0;
        return (int) round((($this->compare_price - $this->price) / $this->compare_price) * 100);
    }

    public function isInStock(): bool
    {
        return !$this->track_stock || $this->stock > 0;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
