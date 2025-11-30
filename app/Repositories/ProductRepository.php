<?php
namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class ProductRepository
{
    public function addProduct($data)
    {
        $id = DB::table('products')->insertGetId([
            'name' => $data['name'],
            'stock' => $data['stock'],
            'price' => $data['price'],
            'available_stock' => $data['stock'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->getProductById($id);
    }

    public function getProductById($productId)
    {
        return DB::table('products')->where('id', $productId)->first();
    }

    public function lockProductForUpdate($productId)
    {
        return DB::table('products')
            ->where('id', $productId)
            ->lockForUpdate()
            ->first();
    }

    public function decrementStock($productId, $qty)
    {
        return DB::update(
            "UPDATE products SET stock = stock - ?, available_stock = available_stock - ? WHERE id = ?",
            [$qty, $qty, $productId]
        );
    }

    public function incrementStock($productId, $qty)
    {
        return DB::update(
            "UPDATE products SET stock = stock + ?, available_stock = available_stock + ? WHERE id = ?",
            [$qty, $qty, $productId]
        );
    }

    public function getAvailableStock($productId)
    {
        return DB::selectOne("
            SELECT
                p.stock - COALESCE(SUM(h.qty), 0) AS available
            FROM products p
            LEFT JOIN holds h
                ON h.product_id = p.id
                AND h.expires_at > NOW()
                AND h.used = 0
            WHERE p.id = ?
            GROUP BY p.stock
        ", [$productId]);
    }
}
