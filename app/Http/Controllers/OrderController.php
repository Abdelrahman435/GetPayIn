<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateOrderRequest;
use App\Services\OrderService;

class OrderController extends Controller
{
    private OrderService $svc;

    public function __construct(OrderService $svc)
    {
        $this->svc = $svc;
    }

    public function store(CreateOrderRequest $request)
    {
        $holdId = $request->input('hold_id');

        try {
            $order = $this->svc->createFromHold($holdId);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'order_id' => $order->id,
            'status' => $order->status,
            'payment_reference' => $order->payment_reference,
            'total_amount' => (string)$order->total_amount,
        ], 201);
    }
}
