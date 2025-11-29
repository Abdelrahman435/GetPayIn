<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductService;

class ProductController extends Controller
{
    public function show(Product $product, ProductService $productService)
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'stock' => $productService->getAvailableStock($product),
        ];
    }
}
