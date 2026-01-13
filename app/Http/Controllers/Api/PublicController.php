<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardingHouseResource;
use App\Models\BoardingHouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;

class PublicController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $search = $request->input('q');
        $min = (int) $request->input('min_price', 0);
        $max = (int) $request->input('max_price', 999999999);

        $facilities = array_filter((array) $request->input('facilities', []), fn($v) => !empty($v));
        $facilitiesKey = implode(',', $facilities);

        $cacheKey = "explore_kos_v12_{$page}_{$search}_{$min}_{$max}_{$facilitiesKey}";

        $boardingHouses = Cache::remember($cacheKey, 300, function () use ($search, $min, $max, $facilities) {
            return BoardingHouse::query()
                ->with([
                    'rooms' => function ($q) {
                        $q->orderBy('price', 'asc')
                            ->limit(1)
                            ->select(['id', 'boarding_house_id', 'price', 'status']);
                    }
                ])
                ->search($search)
                ->priceBetween($min, $max)
                ->withFacilities($facilities)
                ->latest()
                ->paginate(12);
        });

        return BoardingHouseResource::collection($boardingHouses);
    }

    public function show(string $slug)
    {
        $boardingHouse = BoardingHouse::where('slug', $slug)
            ->with([
                'owner',
                'rooms.images',
                'reviews.user'
            ])
            ->firstOrFail();

        $similar = BoardingHouse::where('id', '!=', $boardingHouse->id)
            ->where('category', $boardingHouse->category)
            ->with([
                'rooms' => function ($q) {
                    $q->orderBy('price', 'asc')->limit(1);
                }
            ])
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return (new BoardingHouseResource($boardingHouse))->additional([
            'similar' => BoardingHouseResource::collection($similar)
        ]);
    }
}