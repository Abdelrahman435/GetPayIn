<?php

namespace App\Jobs;

use App\Models\Hold;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Product;

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
            if (!$hold) return;

            if ($hold->used) {
                Log::info('ExpireHoldJob: hold already used', ['hold' => $hold->id]);
                return;
            }

            if ($hold->expires_at && $hold->expires_at->isFuture()) {
                Log::info('ExpireHoldJob: hold not yet expired, skipping', ['hold' => $hold->id]);
                return;
            }

            $product = $hold->product()->lockForUpdate()->first();
            if ($product) {
                $product->available_stock += $hold->qty;
                $product->save();
                Product::forgetStockCache($product->id);
            }

            $hold->delete();
            Log::info('ExpireHoldJob: released hold', ['hold' => $this->holdId]);
        }, 5);
    }
}
