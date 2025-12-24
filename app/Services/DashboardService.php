<?php

namespace App\Services;

use App\Enums\RoomStatus;
use App\Enums\TransactionStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getOwnerStats(User $user): array
    {
        // 1. Statistik Kamar (Agregat dari semua kos milik user)
        $roomStats = DB::table('rooms')
            ->join('boarding_houses', 'rooms.boarding_house_id', '=', 'boarding_houses.id')
            ->where('boarding_houses.user_id', $user->id)
            ->whereNull('rooms.deleted_at')
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as occupied
            ", [RoomStatus::OCCUPIED->value])
            ->first();

        $totalRooms = $roomStats->total ?? 0;
        $occupiedRooms = $roomStats->occupied ?? 0;

        // Hitung persentase okupansi (hindari division by zero)
        $occupancyRate = $totalRooms > 0
            ? round(($occupiedRooms / $totalRooms) * 100)
            : 0;

        // 2. Keuangan Bulan Ini
        $currentMonth = now()->format('Y-m');

        $financials = DB::table('transactions')
            ->join('rooms', 'transactions.room_id', '=', 'rooms.id')
            ->join('boarding_houses', 'rooms.boarding_house_id', '=', 'boarding_houses.id')
            ->where('boarding_houses.user_id', $user->id)
            ->whereNull('transactions.deleted_at')
            ->where('transactions.due_date', 'like', "$currentMonth%")
            ->selectRaw("
                SUM(CASE WHEN transactions.status = ? THEN amount ELSE 0 END) as revenue,
                SUM(CASE WHEN transactions.status = ? THEN amount ELSE 0 END) as potential
            ", [TransactionStatus::PAID->value, TransactionStatus::UNPAID->value])
            ->first();

        return [
            'total_rooms' => $totalRooms,
            'occupied_rooms' => $occupiedRooms,
            'occupancy_rate' => $occupancyRate,
            'revenue_this_month' => (int) ($financials->revenue ?? 0),
            'unpaid_this_month' => (int) ($financials->potential ?? 0),
        ];
    }
}