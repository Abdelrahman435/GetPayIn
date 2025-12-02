<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HoldConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_parallel_holds_do_not_oversell()
    {
        $product = Product::create([
            'name' => 'FlashItem',
            'price' => 10.00,
            'stock' => 5,
            'available_stock' => 5,
        ]);

        $requests = [];
        for ($i = 0; $i < 10; $i++) {
            $requests[] = $this->postJson('/api/holds', [
                'product_id' => $product->id,
                'qty' => 1,
            ]);
        }

        $success = 0;
        foreach ($requests as $req) {
            if ($req->getStatusCode() === 201) $success++;
        }

        $this->assertEquals(5, $success, "Should have only 5 successful holds");
    }
}
