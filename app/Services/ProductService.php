<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    protected function cacheKey($request)
    {
        return 'products:' . md5(json_encode($request->query()));
    }

    public function list($request)
{
    $cacheKey = $this->cacheKey($request);

    return Cache::store('redis')->remember($cacheKey, 600, function () use ($request) {
        $products = Product::query()
            ->when($request->name, fn($q) => $q->where('name', 'like', "%{$request->name}%"))
            ->when($request->min_price, fn($q) => $q->where('price', '>=', $request->min_price))
            ->when($request->max_price, fn($q) => $q->where('price', '<=', $request->max_price))
            ->paginate($request->get('per_page', 10));

        return $products;
    });
}


}
