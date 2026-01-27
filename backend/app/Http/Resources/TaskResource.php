<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'title'       => $this->title,
            'description' => $this->description,
            'status'      => $this->status->value,
            'assigned_to' => $this->assigned_to,
            'assignee'    => new UserResource(
                $this->whenLoaded('assignee')
            ),
            'project_id' => $this->project_id,
            'comments' => CommentResource::collection(
                $this->whenLoaded('comments')
            ),
            'created_at' => $this->created_at,
        ];
    }
}
