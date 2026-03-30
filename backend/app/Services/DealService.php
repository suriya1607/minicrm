<?php

namespace App\Services;

use App\Models\Deal;

class DealService
{
    public function getDeals(array $filters, int $perPage)
    {
        $query = Deal::query()
            ->with(['contact:id,name,email', 'assignedTo:id,name', 'lead:id,title']);

        foreach ($filters as $field => $value) {
            if ($value === '' || $value === null) continue;

            match ($field) {
                'stage'      => $query->where('stage', $value),
                'contact_id' => $query->where('contact_id', $value),
                'created_at' => $query->whereDate('created_at', $value),
                default      => $query->where($field, 'like', "%{$value}%"),
            };
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    public function getAllGroupedByStage(): array
    {
        $deals = Deal::with(['contact:id,name', 'assignedTo:id,name'])
            ->orderBy('id', 'desc')
            ->get();

        $stages = ['proposal', 'negotiation', 'agreement', 'won', 'lost'];
        $grouped = [];

        foreach ($stages as $stage) {
            $grouped[$stage] = $deals->where('stage', $stage)->values();
        }

        return $grouped;
    }

    public function createDeal(array $data): Deal
    {
        return Deal::create($data);
    }

    public function updateDeal(Deal $deal, array $data): Deal
    {
        $deal->update($data);
        return $deal;
    }

    public function deleteDeal(Deal $deal): void
    {
        $deal->delete();
    }

    public function moveDeal(Deal $deal, string $stage): Deal
    {
        $deal->update(['stage' => $stage]);
        return $deal;
    }
}