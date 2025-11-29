<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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


    public function reduceAvailableStock(int $qty): bool
    {
        return $this->where('id', $this->id)
            ->where('available_stock', '>=', $qty)
            ->decrement('available_stock', $qty);
    }

    public function increaseAvailableStock(int $qty): void
    {
        $this->increment('available_stock', $qty);
    }
}
