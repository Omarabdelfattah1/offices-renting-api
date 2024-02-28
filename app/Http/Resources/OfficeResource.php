<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'description' => $this->description,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'approval_status' => $this->approval_status,
            'hidden' => $this->hidden,
            'price_per_day' => $this->price_per_day,
            'monthly_discount' => $this->monthly_discount,
            'reservations_count' => $this->whenCounted('reservations'),
            'user' => new UserResource($this->whenLoaded('user')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'images' => ImageResource::collection($this->whenLoaded('images')),
        ];
    }
}
