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
        if ($boardingHouse->user_id !== auth()->id()) {
            abort(403);
        }

        $rooms = $boardingHouse->rooms()
            ->with('activeTenant')
            ->latest()
            ->get();

        return RoomResource::collection($rooms);
    }

    public function store(StoreRoomRequest $request)
    {
        $boardingHouse = BoardingHouse::where('id', $request->boarding_house_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $room = $boardingHouse->rooms()->create($request->validated());

        return new RoomResource($room);
    }

    public function update(UpdateRoomRequest $request, Room $room)
    {
        if ($room->boardingHouse->user_id !== auth()->id()) {
            abort(403);
        }

        $room->update($request->validated());

        return new RoomResource($room);
    }

    public function destroy(Room $room)
    {
        if ($room->boardingHouse->user_id !== auth()->id()) {
            abort(403);
        }

        $room->delete();
        return response()->json(['message' => 'Room deleted']);
    }
}