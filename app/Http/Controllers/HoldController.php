<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHoldRequest;
use App\Services\HoldService;

class HoldController extends Controller
{
    protected $holds;

    public function __construct(HoldService $holds)
    {
        $this->holds = $holds;
    }

    public function store(CreateHoldRequest $request)
    {
        try {
            $hold = $this->holds->createHold(
                $request->product_id,
                $request->qty
            );

            return response()->json([
                'status' => 'success',
                'data'   => $hold
            ], 201);

        } catch (\Exception $ex) {
            return response()->json([
                'status'  => 'error',
                'message' => $ex->getMessage()
            ], 422);
        }
    }
}
