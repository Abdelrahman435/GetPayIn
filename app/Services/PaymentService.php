<?php

namespace App\Services;

use App\Models\PaymentLog;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PaymentService
{
    public function handleWebhook(string $idempotencyKey, array $payload, string $status): PaymentLog
    {
        $now = Carbon::now();

        try {
            $pl = PaymentLog::create([
                'idempotency_key' => $idempotencyKey,
                'order_id' => null,
                'status' => 'processing',
                'payload' => $payload,
                'processed_at' => null,
            ]);
        } catch (\Throwable $e) {
            $pl = PaymentLog::find($idempotencyKey);
            if (! $pl) {
                throw $e;
            }
            if (in_array($pl->status, ['success', 'failed', 'ignored'])) {
                return $pl;
            }
        }

        return DB::transaction(function () use ($pl, $payload, $status, $now) {
            $paymentReference = $payload['payment_reference'] ?? null;
            $order = null;
            if ($paymentReference) {
                $order = Order::where('payment_reference', $paymentReference)->lockForUpdate()->first();
            }

            $pl->payload = $payload;
            $pl->processed_at = $now;

            if (! $order) {
                $pl->status = $status === 'success' ? 'success' : 'failed';
                $pl->save();
                return $pl;
            }

            $pl->order_id = $order->id;

            if ($status === 'success') {
                $order->status = 'paid';
                $order->save();

                $pl->status = 'success';
                $pl->save();
            } else {
                $order->status = 'cancelled';
                $order->save();

                $hold = $order->hold()->lockForUpdate()->first();
                if ($hold && ! $hold->used) {
                }
                $product = $order->hold->product()->lockForUpdate()->first();
                $product->available_stock += $order->hold->qty;
                $product->save();

                $pl->status = 'failed';
                $pl->save();
            }

            return $pl;
        }, 5);
    }
}
