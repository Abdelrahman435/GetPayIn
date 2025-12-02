<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Services\HoldService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use App\Jobs\ExpireHoldJob;
use Illuminate\Support\Facades\Queue;

class HoldExpiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_hold_expires_and_releases_stock()
    {
        Queue::fake();

        $product = Product::create([
            'name' => 'ExpireItem',
            'price' => 15.00,
            'stock' => 3,
            'available_stock' => 3,
        ]);

        $service = new HoldService();
        $hold = $service->createHold($product->id, 2, 1);  

        $this->assertEquals(1, $product->fresh()->available_stock);

        Queue::assertPushed(ExpireHoldJob::class, function ($job) use ($hold) {
            return $job->holdId === $hold->id;
        });

        Carbon::setTestNow(Carbon::now()->addSeconds(2));

        $job = new ExpireHoldJob($hold->id);
        $job->handle();

        $this->assertDatabaseMissing('holds', ['id' => $hold->id]);
        $this->assertEquals(3, $product->fresh()->available_stock);
    }
}
