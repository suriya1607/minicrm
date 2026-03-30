<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDealRequest;
use App\Http\Resources\DealResource;
use App\Models\Deal;
use App\Services\DealService;
use Illuminate\Http\Request;

class DealController extends Controller
{
    public function __construct(protected DealService $dealService) {}

    public function index(Request $request)
    {
        // Kanban board needs all deals grouped — ?kanban=1
        if ($request->boolean('kanban')) {
            $grouped = $this->dealService->getAllGroupedByStage();
            $result  = [];
            foreach ($grouped as $stage => $deals) {
                $result[$stage] = DealResource::collection($deals);
            }
            return response()->json(['status' => true, 'data' => $result]);
        }

        $perPage = $request->get('per_page', 10);
        $filters = $request->input('filter', []);
        $deals   = $this->dealService->getDeals($filters, $perPage);

        return response()->json([
            'status'  => true,
            'message' => 'Deals fetched successfully',
            'data'    => DealResource::collection($deals),
            'meta'    => [
                'current_page' => $deals->currentPage(),
                'last_page'    => $deals->lastPage(),
                'total'        => $deals->total(),
                'per_page'     => $deals->perPage(),
            ],
        ]);
    }

    public function store(StoreDealRequest $request)
    {
        $deal = $this->dealService->createDeal($request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Deal created successfully',
            'data'    => new DealResource($deal->load(['contact', 'assignedTo', 'lead'])),
        ], 201);
    }

    public function show(Deal $deal)
    {
        return response()->json([
            'status'  => true,
            'message' => 'Deal fetched successfully',
            'data'    => new DealResource($deal->load(['contact', 'assignedTo', 'lead'])),
        ]);
    }

    public function update(StoreDealRequest $request, Deal $deal)
    {
        $updated = $this->dealService->updateDeal($deal, $request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Deal updated successfully',
            'data'    => new DealResource($updated->load(['contact', 'assignedTo', 'lead'])),
        ]);
    }

    public function destroy(Deal $deal)
    {
        $this->dealService->deleteDeal($deal);
        return response()->json(['status' => true, 'message' => 'Deal deleted successfully']);
    }

    // PATCH /deals/{deal}/move  — Kanban drag-drop
    public function move(Request $request, Deal $deal)
    {
        $data = $request->validate([
            'stage' => 'required|in:proposal,negotiation,agreement,won,lost',
        ]);

        $moved = $this->dealService->moveDeal($deal, $data['stage']);

        return response()->json([
            'status'  => true,
            'message' => 'Deal moved successfully',
            'data'    => new DealResource($moved),
        ]);
    }
}