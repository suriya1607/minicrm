<?php

namespace App\Services;

use App\Models\Lead;

class LeadService
{
    public function getLeads(array $filters, int $perPage)
    {
        $query = Lead::query()
            ->with(['contact:id,name,email', 'assignedTo:id,name']);

        foreach ($filters as $field => $value) {
            if (empty($value)) continue;

            match ($field) {
                'stage'      => $query->where('stage', $value),
                'source'     => $query->where('source', $value),
                'contact_id' => $query->where('contact_id', $value),
                'created_at' => $query->whereDate('created_at', $value),
                default      => $query->where($field, 'like', "%{$value}%"),
            };
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    public function createLead(array $data): Lead
    {
        return Lead::create($data);
    }

    public function updateLead(Lead $lead, array $data): Lead
    {
        $lead->update($data);
        return $lead;
    }

    public function deleteLead(Lead $lead): void
    {
        $lead->delete();
    }

    public function convertToDeal(Lead $lead, array $dealData): \App\Models\Deal
    {
        $lead->update(['stage' => 'qualified']);

        return $lead->deal()->create([
            'contact_id'  => $lead->contact_id,
            'lead_id'     => $lead->id,
            'assigned_to' => $lead->assigned_to,
            ...$dealData,
        ]);
    }
}