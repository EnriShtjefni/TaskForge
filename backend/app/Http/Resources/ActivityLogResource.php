<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
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
            'description' => $this->description,
            'causer' => $this->causer ? [
                'id' => $this->causer->id,
                'name' => $this->causer->name ?? null,
            ] : null,
            'subject_type' => $this->subject_type,
            'subject_id' => $this->subject_id,
            'properties' => $this->properties ?? [],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
