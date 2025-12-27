<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Room\StoreRoomRequest;
use App\Http\Requests\Room\UpdateRoomRequest;
use App\Http\Resources\RoomResource;
use App\Models\BoardingHouse;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    public function index(Request $request, BoardingHouse $boardingHouse)
    {
        $this->authorize('view', $boardingHouse);

        $query = $boardingHouse->rooms()->with(['activeTenant', 'images']);

        if ($search = $request->input('q')) {
            $query->where('name', 'like', "%{$search}%");
        }

        return RoomResource::collection($query->latest()->get());
    }

    public function store(StoreRoomRequest $request)
    {
        $boardingHouse = BoardingHouse::findOrFail($request->boarding_house_id);
        $this->authorize('update', $boardingHouse);

        $room = $boardingHouse->rooms()->create($request->validated());

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('room_images', 'public');
                $room->images()->create(['image_path' => $path]);
            }
        }

        return new RoomResource($room->load('images'));
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

        foreach ($room->images as $img) {
            Storage::disk('public')->delete($img->image_path);
        }

        $room->delete();
        return response()->json(['message' => 'Room deleted']);
    }
}