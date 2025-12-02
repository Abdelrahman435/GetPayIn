<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    protected $fillable = [
        'name',
        'price',
        'stock',
        'available_stock',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'available_stock' => 'integer',
    ];

    public function holds(): HasMany
    {
        return $this->hasMany(Hold::class);
    }

    public function orders(): HasMany
    {
        return $this->hasManyThrough(Order::class, Hold::class);
    }

    public function cachedAvailableStock(): int
    {
        $key = "product:{$this->id}:available_stock";
        return (int) Cache::store('redis')->remember($key, 60, fn() => $this->available_stock);
    }

    public static function forgetStockCache(int $productId): void
    {
        Cache::store('redis')->forget("product:{$productId}:available_stock");
    }
}
