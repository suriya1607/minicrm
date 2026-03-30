<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'source'      => $this->source,
            'stage'       => $this->stage,
            'notes'       => $this->notes,
            'contact'     => [
                'id'    => $this->contact?->id,
                'name'  => $this->contact?->name,
                'email' => $this->contact?->email,
            ],
            'assigned_to' => [
                'id'   => $this->assignedTo?->id,
                'name' => $this->assignedTo?->name,
            ],
            // 'has_deal'    => $this->deal()->exists(),
            'created_at'  => $this->created_at->format('Y-m-d'),
        ];
    }
}