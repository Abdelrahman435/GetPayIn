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

        $paymentReference = (string) Str::uuid();

        $order = Order::create([
            'hold_id'           => $hold->id,
            'product_id'        => $product->id,
            'qty'               => $hold->qty,
            'total_amount'      => bcmul((string) $product->price, (string) $hold->qty, 2),
            'payment_reference' => $paymentReference,
            'status'            => 'pending',
        ]);

        $hold->used = true;
        $hold->save();

        $pendingLogs = PaymentLog::where('payment_reference', $paymentReference)
            ->whereNull('processed_at')
            ->lockForUpdate()
            ->get();

        foreach ($pendingLogs as $log) {
            app(\App\Services\PaymentWebhookService::class)
                ::applyWebhookToOrder($log);
        }

        return $order->refresh();
    }, 5);
}
}
