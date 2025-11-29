<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHoldRequest;
use App\Models\Product;
use App\Services\HoldService;

class HoldController extends Controller
{
    public function store(CreateHoldRequest $request, HoldService $holdService)
    {
        $product = Product::findOrFail($request->product_id);

        $hold = $holdService->createHold($product, $request->qty);

        return response()->json([
            'hold_id' => $hold->id,
            'expires_at' => $hold->expires_at,
        ]);
    }
}
