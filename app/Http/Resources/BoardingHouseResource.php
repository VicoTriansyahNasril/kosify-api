<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BoardingHouseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $cheapestRoom = $this->rooms->sortBy('price')->first();
        $startPrice = $cheapestRoom ? $cheapestRoom->price : 0;

        $imageUrl = 'https://placehold.co/600x400/e2e8f0/1e293b?text=No+Image';

        if ($this->cover_image) {
            $url = Storage::url($this->cover_image);
            $imageUrl = str_starts_with($url, 'http') ? $url : rtrim(config('app.url'), '/') . $url;
        }

        $createdAt = $this->created_at;
        if (is_string($createdAt)) {
            try {
                $createdAt = Carbon::parse($createdAt);
            } catch (\Exception $e) {
                $createdAt = now();
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'address' => $this->address,
            'description' => $this->description,
            'category' => $this->category ?? 'campur',
            'facilities' => $this->facilities ?? [],
            'rules' => $this->rules ?? [],
            'image_url' => $imageUrl,
            'rooms_count' => $this->whenCounted('rooms'),
            'start_from_price' => $startPrice,
            'start_from_formatted' => 'Rp ' . number_format($startPrice, 0, ',', '.'),
            'owner_name' => $this->owner->name ?? 'Pemilik Kos',
            'owner_phone' => $this->owner->phone ?? '',
            'rooms' => RoomResource::collection($this->whenLoaded('rooms')),
            'created_at' => $createdAt ? $createdAt->toIso8601String() : now()->toIso8601String(),
            'rating_avg' => $this->rating_avg ?? 0,
        ];
    }
}