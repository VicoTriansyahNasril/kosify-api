<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BoardingHouse;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewController extends Controller
{
    public function index($boardingHouseId)
    {
        $reviews = Review::where('boarding_house_id', $boardingHouseId)
            ->with('user:id,name')
            ->latest()
            ->paginate(5);

        return JsonResource::collection($reviews);
    }

    public function store(Request $request, $boardingHouseId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $kos = BoardingHouse::findOrFail($boardingHouseId);

        $review = Review::updateOrCreate(
            [
                'boarding_house_id' => $kos->id,
                'user_id' => $request->user()->id
            ],
            [
                'rating' => $request->rating,
                'comment' => $request->comment
            ]
        );

        return response()->json([
            'message' => 'Ulasan berhasil dikirim.',
            'data' => $review
        ]);
    }
}