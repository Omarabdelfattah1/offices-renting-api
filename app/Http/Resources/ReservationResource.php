<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"=> $this->id,
            "user_id" => $this->user_id,
            "office_id" => $this->office_id,
            "start_date" => $this->start_date,
            "end_date" => $this->end_date,
            "status" => $this->status,
            "price" => $this->price,
            "user" => new UserResource($this->whenLoaded("user")),
            "office" => new OfficeResource($this->whenLoaded("office")),
        ];
    }
}
