<?php

namespace App\Services;

use App\Models\Hold;
use App\Models\Order;
use App\Models\Product;
use App\Models\PaymentLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrderService
{
    public function createFromHold(int $holdId): Order
    {
        return DB::transaction(function () use ($holdId) {
            $hold = Hold::where('id', $holdId)->lockForUpdate()->firstOrFail();

            if ($hold->used) {
                throw new \RuntimeException('Hold already used.');
            }

            if ($hold->expires_at->isPast()) {
                throw new \RuntimeException('Hold expired.');
            }

            $product = Product::where('id', $hold->product_id)->lockForUpdate()->firstOrFail();

            $total = bcmul((string)$product->price, (string)$hold->qty, 2);

            $order = Order::create([
                'hold_id' => $hold->id,
                'total_amount' => $total,
                'status' => 'pending',
                'payment_reference' => (string)Str::uuid(),
            ]);

            $hold->used = true;
            $hold->save();

            $pl = PaymentLog::where('payload->payment_reference', $order->payment_reference)->first();
            if ($pl) {
                $pl->order_id = $order->id;
                $pl->save();

                if ($pl->status === 'success') {
                    $order->status = 'paid';
                    $order->save();
                } elseif ($pl->status === 'failed') {
                    $order->status = 'cancelled';
                    $order->save();
                    $product->available_stock += $hold->qty;
                    $product->save();
                }
            }

            \Illuminate\Support\Facades\Cache::forget("product:{$product->id}:available_stock");

            return $order;
        }, 5);
    }
}
