<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class OfficeCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => OfficeResource::collection($this->collection),
            'meta' => [
                'total'=> $this->total(),
                'current_page'=> $this->currentPage(),
                'items_per_page'=> $this->perPage(),
                'total_pages' => $this->lastPage(),
            ]
        ];
    }
}
