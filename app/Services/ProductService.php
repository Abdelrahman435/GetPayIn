<?php

namespace App\Services;

use App\Models\Product;

class ProductService
{
    public function getAll()
    {
        return Product::all();
    }

    public function getById($id)
    {
        return Product::findOrFail($id);
    }

    public function add(array $data)
    {
        $data['available_stock'] = $data['stock'];
        return Product::create($data);
    }
}
