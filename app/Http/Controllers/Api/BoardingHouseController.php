<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BoardingHouse\StoreBoardingHouseRequest;
use App\Http\Requests\BoardingHouse\UpdateBoardingHouseRequest;
use App\Http\Resources\BoardingHouseResource;
use App\Models\BoardingHouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BoardingHouseController extends Controller
{
    public function index(Request $request)
    {
        $kos = $request->user()
            ->boardingHouses()
            ->withCount('rooms')
            ->latest()
            ->get();

        return BoardingHouseResource::collection($kos);
    }

    public function store(StoreBoardingHouseRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('boarding_houses', 'public');
            $data['cover_image'] = $path;
        }

        $kos = $request->user()->boardingHouses()->create($data);

        return new BoardingHouseResource($kos);
    }

    public function show(BoardingHouse $boardingHouse)
    {
        if ($boardingHouse->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this property');
        }

        return new BoardingHouseResource($boardingHouse->loadCount('rooms'));
    }

    public function update(UpdateBoardingHouseRequest $request, BoardingHouse $boardingHouse)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($boardingHouse->cover_image) {
                Storage::disk('public')->delete($boardingHouse->cover_image);
            }
            $data['cover_image'] = $request->file('image')->store('boarding_houses', 'public');
        }

        $boardingHouse->update($data);
        return new BoardingHouseResource($boardingHouse);
    }

    public function destroy(BoardingHouse $boardingHouse)
    {
        if ($boardingHouse->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        if ($boardingHouse->cover_image) {
            Storage::disk('public')->delete($boardingHouse->cover_image);
        }

        $boardingHouse->delete();
        return response()->json(['message' => 'Boarding house deleted']);
    }
}