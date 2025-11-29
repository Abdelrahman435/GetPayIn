<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Hold;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ProductRepository;

class ProductService
{
        public function create(array $data)
    {
        $product =ProductRepository::addProduct($data);
        $this->clearCache();
        return $product;
    }

    public function getAvailableStock(Product $product): int
    {
        $activeHolds = Hold::where('product_id', $product->id)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->sum('qty');

        return $product->stock - $activeHolds;
    }

    public function increaseStock(Product $product, int $qty): void
    {
        $product->increment('stock', $qty);
    }

    public function decreaseStock(Product $product, int $qty): void
    {
        $product->decrement('stock', $qty);
    }
}
