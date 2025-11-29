<?php
namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class HoldRepository
{
    /**
     * Get total holds placed by a user within a specific date range.
     *
     * @param int $userId
     * @param string $startDate
     * @param string $endDate
     * @return float
     */
    public function getTotalHoldsByUserAndDateRange(int $userId, string $startDate, string $endDate): float
    {
        return DB::table('holds')
            ->where('user_id', $userId)
            ->whereBetween('hold_date', [$startDate, $endDate])
            ->sum('hold_amount');
    }
}
