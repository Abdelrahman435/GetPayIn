<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Hold;
use App\Models\PaymentLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use RuntimeException;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'status' => 'required|string|in:success,failed',
            'idempotency_key' => 'required|string|max:255',
        ]);

        $idempotencyKey = $validated['idempotency_key'];

        $existing = PaymentLog::find($idempotencyKey);
        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => 'Duplicate webhook ignored',
                'order_status' => $existing->order_id
                    ? optional(Order::find($existing->order_id))->status
                    : null,
            ], 200);
        }

        $log = PaymentLog::create([
            'idempotency_key' => $idempotencyKey,
            'order_id' => $validated['order_id'] ?? null,
            'status' => 'processing',
            'payload' => json_encode($request->all()),
        ]);

        DB::transaction(function () use ($validated, $log) {

            $order = Order::lockForUpdate()->find($validated['order_id']);
            if (!$order) {
                $log->status = 'failed';
                $log->processed_at = now();
                $log->save();
                return;
            }

            if ($validated['status'] === 'success') {
                $order->status = 'paid';
                $order->save();
                $log->status = 'success';
            }
            else {
                $order->status = 'cancelled';
                $order->save();

                $hold = Hold::lockForUpdate()->find($order->hold_id);
                if ($hold) {
                    $product = $hold->product()->lockForUpdate()->first();
                    if ($product) {
                        $product->available_stock += $hold->qty;
                        $product->save();
                        Cache::forget("product:{$product->id}:available_stock");
                    }
                    $hold->delete();
                }

                $log->status = 'failed';
            }

            $log->order_id = $order->id;
            $log->processed_at = now();
            $log->save();
        }, 5);

        return response()->json([
            'success' => true,
            'message' => $validated['status'] === 'success'
                            ? 'Order payment processed'
                            : 'Order payment failed, hold released',
            'order_status' => optional(Order::find($validated['order_id']))->status,
        ], 200);
    }
}
