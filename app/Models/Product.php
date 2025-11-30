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
        return Cache::remember($key, now()->addSeconds(5), fn() => (int)$this->available_stock);
    }
}
