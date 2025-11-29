<?php

namespace App\Services;

use App\Models\Hold;
use App\Models\Product;
use Carbon\Carbon;

class HoldService
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function createHold(Product $product, int $qty): Hold
    {
        $available = $this->productService->getAvailableStock($product);

        if ($qty > $available) {
            throw new \Exception("Not enough stock available.");
        }

        return Hold::create([
            'product_id' => $product->id,
            'qty' => $qty,
            'expires_at' => Carbon::now()->addMinutes(5), 
        ]);
    }

    public function releaseExpiredHolds(): int
    {
        return Hold::where('used', false)
            ->where('expires_at', '<', Carbon::now())
            ->delete();
    }

    public function markUsed(Hold $hold): void
    {
        $hold->update(['used' => true]);
    }
}
