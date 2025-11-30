<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class HoldRepository
{
    public function createHold($productId, $qty, $expiresAt)
    {
        $id = DB::table('holds')->insertGetId([
            'product_id' => $productId,
            'qty'        => $qty,
            'expires_at' => $expiresAt,
            'used'       => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->getHoldById($id);
    }

    public function getHoldById($holdId)
    {
        return DB::table('holds')->where('id', $holdId)->first();
    }

    public function lockHoldForUpdate($holdId)
    {
        return DB::table('holds')
            ->where('id', $holdId)
            ->lockForUpdate()
            ->first();
    }

    public function markAsUsed($holdId)
    {
        return DB::table('holds')
            ->where('id', $holdId)
            ->update(['used' => true]);
    }

    public function releaseExpiredHolds()
    {
        return DB::update("
            UPDATE holds
            SET used = 1
            WHERE expires_at < NOW()
              AND used = 0
        ");
    }
}
