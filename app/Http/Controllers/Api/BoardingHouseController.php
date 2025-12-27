<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BoardingHouse\StoreBoardingHouseRequest;
use App\Http\Requests\BoardingHouse\UpdateBoardingHouseRequest;
use App\Http\Resources\BoardingHouseResource;
use App\Models\BoardingHouse;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class BoardingHouseController extends Controller
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index(Request $request)
    {
        $search = $request->input('q');

        $query = $request->user()
            ->boardingHouses()
            ->withCount('rooms');

        if (!empty($search)) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        return BoardingHouseResource::collection($query->latest()->get());
    }

    public function store(StoreBoardingHouseRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['cover_image'] = $this->imageService->upload(
                $request->file('image'),
                'boarding_houses',
                1200
            );
        }

        $kos = $request->user()->boardingHouses()->create($data);

        return new BoardingHouseResource($kos);
    }

    public function show(BoardingHouse $boardingHouse)
    {
        $this->authorize('view', $boardingHouse);
        return new BoardingHouseResource($boardingHouse->loadCount('rooms'));
    }

    public function update(UpdateBoardingHouseRequest $request, BoardingHouse $boardingHouse)
    {
        $this->authorize('update', $boardingHouse);

        $data = $request->validated();

        if ($request->hasFile('image')) {
            $this->imageService->delete($boardingHouse->cover_image);
            $data['cover_image'] = $this->imageService->upload(
                $request->file('image'),
                'boarding_houses'
            );
        }

        $boardingHouse->update($data);
        return new BoardingHouseResource($boardingHouse);
    }

    public function destroy(BoardingHouse $boardingHouse)
    {
        $this->authorize('delete', $boardingHouse);

        $this->imageService->delete($boardingHouse->cover_image);

        $boardingHouse->delete();
        return response()->json(['message' => 'Boarding house deleted']);
    }
}