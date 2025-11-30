<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Hold extends Model
{
    protected $fillable = [
        'product_id',
        'qty',
        'expires_at',
        'used',
    ];

    protected $casts = [
        'qty' => 'integer',
        'used' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        return $this->hasOne(Order::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function scopeActive($query)
    {
        return $query->where('used', false)->where('expires_at', '>', now());
    }
}
