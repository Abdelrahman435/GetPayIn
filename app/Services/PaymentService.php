<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentLog;
use Carbon\Carbon;
use Exception;

class PaymentService
{
    public function processPayment(Order $order, string $key, array $payload): string
    {
        $log = PaymentLog::firstOrCreate(
            ['idempotency_key' => $key],
            ['payload' => $payload]
        );

        if ($log->processed_at) {
            return "Payment already processed (idempotent).";
        }

        if ($order->status === 'paid') {
            return "Order already paid.";
        }

        $log->update(['processed_at' => Carbon::now()]);

        return "Payment success.";
    }
}
