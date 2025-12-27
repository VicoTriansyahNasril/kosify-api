<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Room\StoreRoomRequest;
use App\Http\Requests\Room\UpdateRoomRequest;
use App\Http\Resources\RoomResource;
use App\Models\BoardingHouse;
use App\Models\Room;
use App\Services\ImageService;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index(Request $request, BoardingHouse $boardingHouse)
    {
        $this->authorize('view', $boardingHouse);

        $search = $request->input('q');
        $status = $request->input('status');

        $query = $boardingHouse->rooms()->with(['activeTenant:id,room_id,name', 'images']);

        if (!empty($search)) {
            $query->where('name', 'like', "%{$search}%");
        }

        if (!empty($status) && in_array($status, ['available', 'occupied', 'maintenance'])) {
            $query->where('status', $status);
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
                $path = $this->imageService->upload($image, 'room_images', 1000);
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
                'message' => 'Tidak dapat menghapus kamar yang sedang dihuni.'
            ], 422);
        }

        foreach ($room->images as $img) {
            $this->imageService->delete($img->image_path);
        }

        $room->delete();
        return response()->json(['message' => 'Room deleted']);
    }
}