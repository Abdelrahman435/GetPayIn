<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Hold;
use Exception;

class OrderService
{
    public function createOrder(Hold $hold): Order
    {
        if ($hold->used) {
            throw new Exception("Hold already used in a previous order.");
        }

        $hold->update(['used' => true]);

        return Order::create([
            'hold_id' => $hold->id,
            'status'  => 'pending',
        ]);
    }

    public function markAsPaid(Order $order): void
    {
        $order->update(['status' => 'paid']);
    }

    public function cancel(Order $order): void
    {
        $order->update(['status' => 'cancelled']);
    }
}
