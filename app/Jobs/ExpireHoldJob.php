<?php

namespace App\Jobs;

use App\Models\Hold;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable; 
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ExpireHoldJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $holdId;

    public function __construct(int $holdId)
    {
        $this->holdId = $holdId;
    }

    public function handle()
    {
        DB::transaction(function () {
            $hold = Hold::where('id', $this->holdId)->lockForUpdate()->first();
            if (! $hold) return;

            if ($hold->used) return;

            if ($hold->expires_at->isFuture()) return;

            $product = Product::where('id', $hold->product_id)->lockForUpdate()->first();
            if ($product) {
                $product->available_stock += $hold->qty;
                $product->save();

                Cache::forget("product:{$product->id}:available_stock");
            }

            $hold->delete();
        }, 5);
    }
}
