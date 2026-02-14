<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'event_date' => $this->event_date,
            'ticket_price' => (float) $this->ticket_price,
            'total_seats' => $this->total_seats,
            'remaining_seats' => $this->remaining_seats,
            'status' => $this->status,
            'organizer' => new UserResource($this->whenLoaded('organizer')),
            'bookings_count' => $this->bookings_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

