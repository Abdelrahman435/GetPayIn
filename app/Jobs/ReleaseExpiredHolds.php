<?php

namespace App\Jobs;

use App\Models\Hold;
use Carbon\Carbon;

class ReleaseExpiredHoldsJob extends Job
{
    public function handle(): void
    {
        Hold::where('expires_at', '<', Carbon::now())
            ->where('used', false)
            ->delete();
    }
}
