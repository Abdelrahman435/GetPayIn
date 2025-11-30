<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductService;
use App\Http\Requests\ProductRequest;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $service)
    {
        $this->productService = $service;
    }

    public function store(ProductRequest $request)
    {
        $product = $this->productService->create($request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Product created successfully',
            'data'    => $product
        ], 201);
    }

    public function show(Product $product)
    {
        $availableStock = $this->productService->getAvailableStock($product);

        return response()->json([
            'id'   => $product->id,
            'name' => $product->name,
            'price'=> $product->price,
            'available_stock' => $availableStock,
        ]);
    }
}
