<?php

namespace App\Http\Controllers;

use App\Services\PaymentWebhookService;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $validated = $request->validate([
            'idempotency_key'   => 'required|string|max:255',
            'payment_reference' => 'required|string|max:255',
            'status'            => 'required|in:success,failed',
            'payload'           => 'nullable'
        ]);

        $result = PaymentWebhookService::processWebhook(
            $validated['idempotency_key'],
            $validated['payment_reference'],
            $validated['status'],
            $request->all()
        );

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'order_status' => $result['order_status'],
        ], 200);
    }
}
