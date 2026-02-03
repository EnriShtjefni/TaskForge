<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'organization_id' => $this->organization_id,
            'tasks' => TaskResource::collection(
                $this->whenLoaded('tasks')
            ),
            'organization' => [
                'id' => $this->organization->id,
                'name' => $this->organization->name,
            ],
            'members' => $this->users->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
