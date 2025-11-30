<?php

namespace App\Services;

use App\Repositories\HoldRepository;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Jobs\ExpireHoldJob;

class HoldService
{
    protected $holds;
    protected $products;

    public function __construct(HoldRepository $holds, ProductRepository $products)
    {
        $this->holds = $holds;
        $this->products = $products;
    }

    public function createHold($productId, $qty)
    {
        return DB::transaction(function () use ($productId, $qty) {

            $product = $this->products->lockProductForUpdate($productId);

            if (!$product) {
                throw new \Exception("Product not found");
            }

            $available = $this->products->getAvailableStock($productId)->available;

            if ($available < $qty) {
                throw new \Exception("Not enough stock available");
            }
            $this->products->decrementStock($productId, $qty);

            $expiresAt = Carbon::now()->addMinutes(10);

            $hold = $this->holds->createHold($productId, $qty, $expiresAt);

            return $hold;
            dispatch(new ExpireHoldJob($hold->id))
                ->delay(now()->addMinutes(10));

        });
    }

    public function validateHold($holdId)
    {
        $hold = $this->holds->getHoldById($holdId);

        if (!$hold) {
            throw new \Exception("Hold not found");
        }

        if ($hold->used) {
            throw new \Exception("Hold already used");
        }

        if (Carbon::parse($hold->expires_at)->isPast()) {
            throw new \Exception("Hold expired");
        }

        return $hold;
    }

        public function releaseHold($holdId)
    {
        return DB::transaction(function () use ($holdId) {

            $hold = $this->holds->lockHoldForUpdate($holdId);

            if (!$hold) return;

            if ($hold->used == 1) return;

            $this->products->increaseAvailableStock(
                $hold->product_id,
                $hold->qty
            );

            $this->holds->markAsUsed($holdId);
        });
    }

}
