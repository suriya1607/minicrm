<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DealResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'title'               => $this->title,
            'value'               => (float) $this->value,
            'stage'               => $this->stage,
            'expected_close_date' => $this->expected_close_date?->format('Y-m-d'),
            'notes'               => $this->notes,
            'contact'             => [
                'id'    => $this->contact?->id,
                'name'  => $this->contact?->name,
                'email' => $this->contact?->email,
            ],
            'lead'       => $this->lead ? ['id' => $this->lead->id, 'title' => $this->lead->title] : null,
            'assigned_to'=> [
                'id'   => $this->assignedTo?->id,
                'name' => $this->assignedTo?->name,
            ],
            'created_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}