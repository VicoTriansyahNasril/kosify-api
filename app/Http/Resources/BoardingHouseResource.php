<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BoardingHouseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $cheapestRoom = $this->rooms->sortBy('price')->first();
        $startPrice = $cheapestRoom ? $cheapestRoom->price : 0;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'address' => $this->address,
            'description' => $this->description,
            'facilities' => $this->facilities ?? [],
            'image_url' => $this->cover_image
                ? Storage::url($this->cover_image)
                : 'https://placehold.co/600x400/e2e8f0/1e293b?text=No+Image',

            'rooms_count' => $this->whenCounted('rooms'),
            'start_from_price' => $startPrice,
            'start_from_formatted' => 'Rp ' . number_format($startPrice, 0, ',', '.'),
            'owner_name' => $this->owner->name ?? 'Pemilik Kos',
            'owner_phone' => $this->owner->phone ?? '',
            'rooms' => RoomResource::collection($this->whenLoaded('rooms')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}