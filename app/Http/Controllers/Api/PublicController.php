<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardingHouseResource;
use App\Models\BoardingHouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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

        $cacheKey = "explore_kos_v3_{$page}_{$search}_{$min}_{$max}_{$facilitiesKey}";

        $boardingHouses = Cache::remember($cacheKey, 300, function () use ($search, $min, $max, $facilities) {
            return BoardingHouse::query()
                ->select(['id', 'user_id', 'name', 'slug', 'address', 'category', 'facilities', 'cover_image', 'created_at'])
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
        $boardingHouse = Cache::remember("kos_detail_v3_{$slug}", 600, function () use ($slug) {
            return BoardingHouse::where('slug', $slug)
                ->with([
                    'owner:id,name,phone,created_at',
                    'rooms' => function ($q) {
                        $q->with(['images:id,room_id,image_path'])
                            ->orderByRaw("FIELD(status, 'available', 'occupied', 'maintenance')")
                            ->select(['id', 'boarding_house_id', 'name', 'price', 'capacity', 'status', 'description']);
                    }
                ])
                ->firstOrFail();
        });

        $similar = Cache::remember("kos_similar_v3_{$boardingHouse->id}", 600, function () use ($boardingHouse) {
            return BoardingHouse::where('id', '!=', $boardingHouse->id)
                ->select(['id', 'name', 'slug', 'address', 'category', 'cover_image'])
                ->where(function ($q) use ($boardingHouse) {
                    $q->where('address', 'like', '%' . substr($boardingHouse->address, 0, 10) . '%')
                        ->orWhere('category', $boardingHouse->category);
                })
                ->with(['rooms' => fn($q) => $q->orderBy('price', 'asc')->limit(1)->select(['id', 'boarding_house_id', 'price'])])
                ->inRandomOrder()
                ->limit(4)
                ->get();
        });

        return (new BoardingHouseResource($boardingHouse))->additional([
            'similar' => BoardingHouseResource::collection($similar)
        ]);
    }
}