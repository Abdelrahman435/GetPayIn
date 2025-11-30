<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentWebhookRequest;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymentService;

class PaymentWebhookController extends Controller
{
    public function handle(
        PaymentWebhookRequest $request,
        PaymentService $paymentService,
        OrderService $orderService
    ) {
        $order = Order::findOrFail($request->order_id);

        $result = $paymentService->processPayment(
            $order,
            $request->idempotency_key,
            $request->data ?? []
        );

        if ($request->status === 'success') {
            $orderService->markAsPaid($order);
        } else {
            $orderService->cancel($order);
        }

        return ['message' => $result];
    }
}
