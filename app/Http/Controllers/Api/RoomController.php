<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Room\StoreRoomRequest;
use App\Http\Requests\Room\UpdateRoomRequest;
use App\Http\Resources\RoomResource;
use App\Models\BoardingHouse;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(BoardingHouse $boardingHouse)
    {
        $this->authorize('view', $boardingHouse);

        $rooms = $boardingHouse->rooms()
            ->with('activeTenant')
            ->latest()
            ->get();

        return RoomResource::collection($rooms);
    }

    public function store(StoreRoomRequest $request)
    {
        $boardingHouse = BoardingHouse::findOrFail($request->boarding_house_id);

        $this->authorize('update', $boardingHouse);

        $room = $boardingHouse->rooms()->create($request->validated());

        return new RoomResource($room);
    }

    public function update(UpdateRoomRequest $request, Room $room)
    {
        $this->authorize('update', $room);

        $room->update($request->validated());

        return new RoomResource($room);
    }

    public function destroy(Room $room)
    {
        $this->authorize('delete', $room);

        if ($room->status === \App\Enums\RoomStatus::OCCUPIED) {
            return response()->json([
                'message' => 'Tidak dapat menghapus kamar yang sedang dihuni. Silakan check-out penyewa terlebih dahulu.'
            ], 422);
        }

        $room->delete();
        return response()->json(['message' => 'Room deleted']);
    }
}