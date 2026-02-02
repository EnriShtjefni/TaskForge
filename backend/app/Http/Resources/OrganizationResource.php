<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
            'role' => $this->whenPivotLoaded(
                'organization_user',
                fn () => $this->pivot->role
            ),
            'projects' => ProjectResource::collection(
                $this->whenLoaded('projects')
            ),
            'members' => $this->users
                ->filter(fn ($user) => $user->pivot->role !== 'owner')
                ->map(fn ($user) => [
                    'user_id' => $user->id,
                    'name'    => $user->name,
                    'role'    => $user->pivot->role,
                ])
                ->values(),
        ];
    }
}
