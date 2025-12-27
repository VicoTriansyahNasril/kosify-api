<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardingHouseResource;
use App\Models\BoardingHouse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class PublicController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $search = $request->get('q', '');
        $min = $request->get('min_price', 0);
        $max = $request->get('max_price', 999999999);
        $facilities = implode(',', $request->get('facilities', []));

        $cacheKey = "explore_kos_{$page}_{$search}_{$min}_{$max}_{$facilities}";

        $boardingHouses = Cache::remember($cacheKey, 300, function () use ($request) {
            $query = BoardingHouse::query();

            $query->with([
                'rooms' => function ($q) {
                    $q->orderBy('price', 'asc')->limit(1);
                }
            ]);

            if ($search = $request->input('q')) {
                $query->where(function (Builder $q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            }

            if ($request->has('min_price') || $request->has('max_price')) {
                $min = $request->input('min_price', 0);
                $max = $request->input('max_price', 999999999);

                $query->whereHas('rooms', function ($q) use ($min, $max) {
                    $q->whereBetween('price', [$min, $max]);
                });
            }

            if ($facilities = $request->input('facilities')) {
                foreach ($facilities as $facility) {
                    $query->whereJsonContains('facilities', $facility);
                }
            }

            return $query->latest()->paginate(12);
        });

        return BoardingHouseResource::collection($boardingHouses);
    }

    public function show(string $slug)
    {
        $boardingHouse = Cache::remember("kos_detail_{$slug}", 600, function () use ($slug) {
            return BoardingHouse::where('slug', $slug)
                ->with([
                    'owner',
                    'rooms' => function ($q) {
                        $q->with('images')->orderByRaw("FIELD(status, 'available', 'occupied', 'maintenance')");
                    }
                ])
                ->firstOrFail();
        });

        $similar = Cache::remember("kos_similar_{$boardingHouse->id}", 600, function () use ($boardingHouse) {
            return BoardingHouse::where('id', '!=', $boardingHouse->id)
                ->where(function ($q) use ($boardingHouse) {
                    $q->where('address', 'like', '%' . substr($boardingHouse->address, 0, 10) . '%') // Lokasi mirip
                        ->orWhere('category', $boardingHouse->category);
                })
                ->with(['rooms' => fn($q) => $q->orderBy('price', 'asc')->limit(1)])
                ->inRandomOrder()
                ->limit(4)
                ->get();
        });

        return (new BoardingHouseResource($boardingHouse))->additional([
            'similar' => BoardingHouseResource::collection($similar)
        ]);
    }
}