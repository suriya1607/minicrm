<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'email'       => $this->email,
            'phone'       => $this->phone,
            'company'     => $this->company,
            'status'      => $this->status,
            'notes'       => $this->notes,
            'assigned_to' => [
                'id'   => $this->assignedTo?->id,
                'name' => $this->assignedTo?->name,
            ],
            'created_at'  => $this->created_at->format('Y-m-d'),
        ];
    }
}