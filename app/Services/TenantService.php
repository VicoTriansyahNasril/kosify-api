<?php

namespace App\Services;

use App\Enums\RoomStatus;
use App\Models\Tenant;
use App\Models\Room;
use Illuminate\Support\Facades\DB;
use Exception;

class TenantService
{
    /**
     * Handle Tenant Check-in
     * - Create Data Tenant
     * - Update Room Status -> Occupied
     * - Wrap in Transaction (Atomic)
     */
    public function checkIn(array $data): Tenant
    {
        return DB::transaction(function () use ($data) {
            $tenant = Tenant::create($data);

            // 2. Update Status Kamar
            $room = Room::findOrFail($data['room_id']);
            $room->update(['status' => RoomStatus::OCCUPIED]);

            return $tenant;
        });
    }

    /**
     * Handle Tenant Check-out
     * - Update Room Status -> Available
     * - Soft Delete Tenant
     */
    public function checkOut(Tenant $tenant): void
    {
        DB::transaction(function () use ($tenant) {
            $tenant->room->update(['status' => RoomStatus::AVAILABLE]);
            $tenant->delete();
        });
    }
}