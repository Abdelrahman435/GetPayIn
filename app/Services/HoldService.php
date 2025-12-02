<?php

namespace App\Services;

use App\Models\Hold;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Jobs\ExpireHoldJob;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Illuminate\Support\Facades\Log;

class HoldService
{
    public function createHold(int $productId, int $qty, int $ttlSeconds = 120): Hold
    {
        $lockKey = "product:{$productId}:lock";

        return Cache::store('redis')->lock($lockKey, 5)->get(function () use ($productId, $qty, $ttlSeconds) {
            return DB::transaction(function () use ($productId, $qty, $ttlSeconds) {
                $product = Product::where('id', $productId)->lockForUpdate()->firstOrFail();

                if ($product->available_stock < $qty) {
                    Log::warning('HoldService: not enough stock', [
                        'product' => $productId,
                        'requested' => $qty,
                        'available' => $product->available_stock
                    ]);
                    throw new RuntimeException('Not enough stock available.');
                }

                $product->available_stock -= $qty;
                $product->save();
                Product::forgetStockCache($product->id);

                $expiresAt = Carbon::now()->addSeconds($ttlSeconds);
                $hold = Hold::create([
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'expires_at' => $expiresAt,
                    'used' => false,
                ]);

                ExpireHoldJob::dispatch($hold->id)->delay($expiresAt);

                return $hold;
            }, 5);
        });
    }

    public function releaseHold(Hold $hold): bool
    {
        return DB::transaction(function () use ($hold) {
            $h = Hold::where('id', $hold->id)->lockForUpdate()->first();
            if (!$h || $h->used) return false;

            $product = $h->product()->lockForUpdate()->first();
            if ($product) {
                $product->available_stock += $h->qty;
                $product->save();
                Product::forgetStockCache($product->id);
            }

            $h->delete();
            Log::info('HoldService: released hold', ['hold' => $h->id]);
            return true;
        }, 5);
    }
}
