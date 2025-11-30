<?php

namespace App\Services;

use App\Models\Hold;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Jobs\ExpireHoldJob;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class HoldService
{
    public function createHold(int $productId, int $qty, int $ttlSeconds = 120): Hold
    {
        return DB::transaction(function () use ($productId, $qty, $ttlSeconds) {

            $product = Product::where('id', $productId)->lockForUpdate()->firstOrFail();

            if ($product->available_stock < $qty) {
                throw new RuntimeException('Not enough stock available.');
            }

            $product->available_stock -= $qty;
            $product->save();

            Cache::forget("product:{$product->id}:available_stock");

            $expiresAt = Carbon::now()->addSeconds($ttlSeconds);
            $hold = Hold::create([
                'product_id' => $product->id,
                'qty'        => $qty,
                'expires_at' => $expiresAt,
                'used'       => false,
            ]);

            dispatch(new ExpireHoldJob($hold->id))->delay($expiresAt);

            return $hold;
        }, 5);
    }

    public function releaseHold(Hold $hold): bool
    {
        return DB::transaction(function () use ($hold) {

            $h = Hold::where('id', $hold->id)->lockForUpdate()->first();

            if (! $h || $h->used) {
                return false;
            }

            $product = Product::where('id', $h->product_id)->lockForUpdate()->first();
            $product->available_stock += $h->qty;
            $product->save();

            $h->delete();

            Cache::forget("product:{$product->id}:available_stock");

            return true;
        }, 5);
    }
}
