<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'description'  => $this->description,
            'priority'     => $this->priority,
            'status'       => $this->status,
            'due_date'     => $this->due_date?->format('Y-m-d H:i'),
            'completed_at' => $this->completed_at?->format('Y-m-d H:i'),
            'assigned_to'  => [
                'id'   => $this->assignedTo?->id,
                'name' => $this->assignedTo?->name,
            ],
            'taskable_type' => class_basename($this->taskable_type),
            'taskable_id'   => $this->taskable_id,
            'created_at'    => $this->created_at->format('Y-m-d'),
        ];
    }
}