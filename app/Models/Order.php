<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'hold_id',
        'product_id',
        'qty',
        'payment_reference',
        'status',
        'total_amount',
    ];

    public function hold()
    {
        return $this->belongsTo(Hold::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function paymentLogs()
    {
        return $this->hasMany(PaymentLog::class, 'payment_reference', 'payment_reference');
    }
}
