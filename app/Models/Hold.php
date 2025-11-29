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
        return now()->greaterThan($this->expires_at);
    }

    public function markAsUsed(): void
    {
        $this->update(['used' => true]);
    }

    public function markAsReleased(): void
    {
        $this->product->increaseAvailableStock($this->qty);
    }
}
