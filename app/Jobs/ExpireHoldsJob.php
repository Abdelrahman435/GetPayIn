<?php

namespace App\Jobs;

use App\Services\HoldService;

class ExpireHoldJob extends Job
{
    public function __construct(public $holdId) {}

    public function handle(HoldService $holdService)
    {
        $holdService->releaseHold($this->holdId);
    }
}
