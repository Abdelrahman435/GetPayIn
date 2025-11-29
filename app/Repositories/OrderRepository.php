<?php
namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class OrderRepository
{
    /**
     * Get total orders placed by a user within a specific date range.
     *
     * @param int $userId
     * @param string $startDate
     * @param string $endDate
     * @return float
     */
    public function getTotalOrdersByUserAndDateRange(int $userId, string $startDate, string $endDate): float
    {
        return DB::table('orders')
            ->where('user_id', $userId)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->sum('total_amount');
    }
}
