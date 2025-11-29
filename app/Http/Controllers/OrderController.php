<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateOrderRequest;
use App\Models\Hold;
use App\Services\OrderService;

class OrderController extends Controller
{
    public function store(CreateOrderRequest $request, OrderService $orderService)
    {
        $hold = Hold::findOrFail($request->hold_id);

        $order = $orderService->createOrder($hold);

        return [
            'order_id' => $order->id,
            'status' => $order->status,
        ];
    }
}
