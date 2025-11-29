<?php
namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class ProductRepository
{
    public function addProduct($data)
    {
        return DB::insert(
            "INSERT INTO products (name, stock, price) VALUES (?, ?, ?)",
            [$data['name'], $data['stock'], $data['price']]
        );
    }

    public function getProductById($productId)
    {
        return DB::selectOne(
            "SELECT * FROM products WHERE id = ?",
            [$productId]
        );
    }

    public function lockProductForUpdate($productId)
    {
        return DB::selectOne(
            "SELECT * FROM products WHERE id = ? FOR UPDATE",
            [$productId]
        );
    }

    public function decrementStock($productId, $qty)
    {
        return DB::update(
            "UPDATE products SET stock = stock - ? WHERE id = ?",
            [$qty, $productId]
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

