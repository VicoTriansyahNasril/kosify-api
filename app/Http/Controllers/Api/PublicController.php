<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardingHouseResource;
use App\Models\BoardingHouse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class PublicController extends Controller
{
    public function index(Request $request)
    {
        $query = BoardingHouse::query();

        $query->with([
            'rooms' => function ($q) {
                $q->orderBy('price', 'asc');
            }
        ]);

        if ($search = $request->input('q')) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $boardingHouses = $query->latest()->paginate(12);

        return BoardingHouseResource::collection($boardingHouses);
    }

    public function show(string $slug)
    {
        $boardingHouse = BoardingHouse::where('slug', $slug)
            ->with([
                'owner',
                'rooms' => function ($q) {
                    $q->orderByRaw("FIELD(status, 'available', 'occupied', 'maintenance')");
                }
            ])
            ->firstOrFail();

        return new BoardingHouseResource($boardingHouse);
    }
}