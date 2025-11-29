<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'hold_id',
        'total_amount',
        'status',
        'payment_reference',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public function hold(): BelongsTo
    {
        return $this->belongsTo(Hold::class);
    }

    public function markPaid(): void
    {
        $this->update(['status' => 'paid']);
    }

    public function markCancelled(): void
    {
        $this->update(['status' => 'cancelled']);
        if (!$this->hold->used) {
            $this->hold->product->increaseAvailableStock($this->hold->qty);
        }
    }
}
