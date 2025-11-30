<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Hold;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ProductRepository;

class ProductService
{
    protected $repo;

    public function __construct(ProductRepository $repo)
    {
        $this->repo = $repo;
    }

    public function create(array $data)
    {
        $product = $this->repo->addProduct($data);

        $this->clearCache();

        return $product;
    }

    public function getAvailableStock(Product $product): int
    {
        $row = $this->repo->getAvailableStock($product->id);

        return $row ? (int) $row->available : 0;
    }

    public function increaseStock(Product $product, int $qty): void
    {
        $this->repo->incrementStock($product->id, $qty);

        $this->clearCache();
    }

    public function decreaseStock(Product $product, int $qty): void
    {
        $this->repo->decrementStock($product->id, $qty);

        $this->clearCache();
    }

    private function clearCache()
    {
        Cache::forget('products_list');
    }
}
