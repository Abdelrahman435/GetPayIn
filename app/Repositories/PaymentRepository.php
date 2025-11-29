<?php
namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class PaymentRepository
{
    /**
     * Get total payments made by a user within a specific date range.
     *
     * @param int $userId
     * @param string $startDate
     * @param string $endDate
     * @return float
     */
    public function getTotalPaymentsByUserAndDateRange(int $userId, string $startDate, string $endDate): float
    {
        return DB::table('payments')
            ->where('user_id', $userId)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount');
    }
}
