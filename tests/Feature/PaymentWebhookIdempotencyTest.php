<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentWebhookIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_idempotent_and_before_order()
    {
        $product = Product::create([
            'name' => 'FlashItem',
            'price' => 10.00,
            'stock' => 1,
            'available_stock' => 1,
        ]);

        $holdResp = $this->postJson('/api/holds', ['product_id' => $product->id, 'qty' => 1]);
        $holdId = $holdResp->json('hold_id');

        $paymentReference = uniqid('pay_');
        $idempotencyKey = 'idem-key-1';

        $webhookResp1 = $this->postJson('/api/payments/webhook', [
            'idempotency_key' => $idempotencyKey,
            'payment_reference' => $paymentReference,
            'status' => 'success',
            'payload' => ['foo' => 'bar'],
        ]);
        $webhookResp1->assertStatus(200);

        $orderResp = $this->postJson('/api/orders', ['hold_id' => $holdId, 'payment_reference' => $paymentReference]);
        $orderResp->assertStatus(201);

        $webhookResp2 = $this->postJson('/api/payments/webhook', [
            'idempotency_key' => $idempotencyKey,
            'payment_reference' => $paymentReference,
            'status' => 'success',
        ]);
        $webhookResp2->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $orderResp->json('order_id'),
            'status' => 'paid'
        ]);
    }
}
