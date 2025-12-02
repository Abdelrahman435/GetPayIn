<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentLog;
use App\Models\Hold;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PaymentWebhookService
{
    public static function processWebhook(string $key, string $reference, string $state, array $payload)
    {
        $log = PaymentLog::firstOrCreate(
            ['idempotency_key' => $key],
            [
                'payment_reference' => $reference,
                'status' => $state === 'success' ? 'success' : 'failed',
                'payload' => json_encode($payload),
                'order_id' => null,
                'processed_at' => null,
            ]
        );

        self::applyWebhookToOrder($log);

        return [
            'message' => 'Webhook received',
            'order_status' => optional(Order::where('payment_reference', $reference)->first())->status,
        ];
    }

    protected static function applyWebhookToOrder(PaymentLog $log)
    {
        DB::transaction(function () use ($log) {
            $order = Order::where('payment_reference', $log->payment_reference)
                ->lockForUpdate()
                ->first();

            if (!$order || $log->processed_at) {
                return;
            }

            if ($log->status === 'success') {
                $order->update(['status' => 'paid']);
            } elseif ($log->status === 'failed') {
                $order->update(['status' => 'cancelled']);

                $hold = Hold::where('id', $order->hold_id)->lockForUpdate()->first();
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
        }, 5);
    }

    public static function processPendingFor(Order $order)
    {
        $logs = PaymentLog::where('payment_reference', $order->payment_reference)
            ->whereNull('processed_at')
            ->get();

        foreach ($logs as $log) {
            self::applyWebhookToOrder($log);
        }
    }
}
