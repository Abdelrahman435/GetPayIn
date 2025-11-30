<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHoldRequest;
use App\Services\HoldService;
use Illuminate\Http\Request;

class HoldController extends Controller
{
    private HoldService $service;

    public function __construct(HoldService $service)
    {
        $this->service = $service;
    }

    public function store(CreateHoldRequest $request)
    {
        $data = $request->validated();

        try {
            $hold = $this->service->createHold($data['product_id'], $data['qty'], 120);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'hold_id' => $hold->id,
            'expires_at' => $hold->expires_at->toISOString(),
        ], 201);
    }
}
