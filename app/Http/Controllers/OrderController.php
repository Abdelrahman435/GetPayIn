<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateOrderRequest;
use App\Models\Hold;
use App\Models\Order;
use App\Services\PaymentWebhookService;

class OrderController extends Controller
{
    public function store(CreateOrderRequest $request)
    {
        $validated = $request->validated();

        $hold = Hold::with('product')->findOrFail($validated['hold_id']);
        $paymentReference = $validated['payment_reference'];

        $order = Order::create([
            'hold_id' => $hold->id,
            'product_id' => $hold->product_id,
            'qty'       => $hold->qty,
            'payment_reference' => $paymentReference,
            'status' => 'pending',
            'total_amount' => $hold->qty * $hold->product->price,
        ]);

        PaymentWebhookService::processPendingFor($order);
        $order->refresh();

        return response()->json([
            'order_id' => $order->id,
            'payment_reference' => $order->payment_reference,
            'status' => $order->status,
        ], 201);
    }
}
