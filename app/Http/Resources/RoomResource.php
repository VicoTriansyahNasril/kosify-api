<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class RoomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $images = $this->images->map(function ($img) {
            return [
                'id' => $img->id,
                'url' => Storage::url($img->image_path)
            ];
        });

        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => (float) $this->price,
            'price_formatted' => 'Rp ' . number_format($this->price, 0, ',', '.'),
            'status' => $this->status,
            'status_label' => ucfirst($this->status->value),
            'capacity' => $this->capacity,
            'description' => $this->description,
            'active_tenant' => $this->whenLoaded('activeTenant'),
            'images' => $images,
            'first_image' => $images->first()['url'] ?? null,
            'boarding_house_id' => $this->boarding_house_id,
        ];
    }
}