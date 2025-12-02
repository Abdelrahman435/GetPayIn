<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentLog;
use App\Models\Hold;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class PaymentWebhookService
{
public static function processWebhook(
    string $idempotencyKey,
    string $paymentReference,
    string $status,
    array $payload = []
) {
    $log = PaymentLog::firstOrCreate(
        ['idempotency_key' => $idempotencyKey],
        [
            'payment_reference' => $paymentReference,
            'status' => $status === 'success' ? 'success' : 'failed',
            'payload' => json_encode($payload),
            'order_id' => null,
            'processed_at' => null,
        ]
    );

    $order = Order::where('payment_reference', $paymentReference)->first();
    if ($order) {
        self::applyWebhookToOrder($log, $order);
    }

    return ['message' => 'Webhook stored'];
}


 protected static function applyWebhookToOrder(PaymentLog $log, Order $order)
{
    DB::transaction(function () use ($log, $order) {

        if (!$order || !$order->exists) {
            return;
        }

        if ($log->processed_at) {
            return;
        }

        if ($log->status === 'success') {
            $order->update(['status' => 'paid']);
        } else {
            $order->update(['status' => 'cancelled']);

            $hold = Hold::where('id', $order->hold_id)
                ->lockForUpdate()
                ->first();

            if ($hold) {
                $product = $hold->product()->lockForUpdate()->first();

                if ($product) {
                    $product->available_stock += $hold->qty;
                    $product->save();

                    Cache::forget("product:{$product->id}:available_stock");
                }

                $hold->delete();
            }
        }

        $log->update([
            'order_id' => $order->id,
            'processed_at' => now(),
        ]);
    });
}


    public static function processPendingFor(Order $order)
    {
        $logs = PaymentLog::where('payment_reference', $order->payment_reference)
            ->whereNull('processed_at')
            ->get();

        foreach ($logs as $log) {
            self::applyWebhookToOrder($log, $order);
        }
    }
}
