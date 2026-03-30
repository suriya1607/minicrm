<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Resources\LeadResource;
use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function __construct(protected LeadService $leadService) {}

    public function index(Request $request)
    {
        $perPage  = $request->get('per_page', 10);
        $filters  = $request->input('filter', []);
        $leads    = $this->leadService->getLeads($filters, $perPage);

        return response()->json([
            'status'  => true,
            'message' => 'Leads fetched successfully',
            'data'    => LeadResource::collection($leads),
            'meta'    => [
                'current_page' => $leads->currentPage(),
                'last_page'    => $leads->lastPage(),
                'total'        => $leads->total(),
                'per_page'     => $leads->perPage(),
            ],
        ]);
    }

    public function store(StoreLeadRequest $request)
    {
        $lead = $this->leadService->createLead($request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Lead created successfully',
            'data'    => new LeadResource($lead->load(['contact', 'assignedTo'])),
        ], 201);
    }

    public function show(Lead $lead)
    {
        return response()->json([
            'status'  => true,
            'message' => 'Lead fetched successfully',
            'data'    => new LeadResource($lead->load(['contact', 'assignedTo'])),
        ]);
    }

    public function update(StoreLeadRequest $request, Lead $lead)
    {
        $updated = $this->leadService->updateLead($lead, $request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Lead updated successfully',
            'data'    => new LeadResource($updated->load(['contact', 'assignedTo'])),
        ]);
    }

    public function destroy(Lead $lead)
    {
        $this->leadService->deleteLead($lead);

        return response()->json(['status' => true, 'message' => 'Lead deleted successfully']);
    }

    // POST /leads/{lead}/convert  → creates a Deal
    public function convert(Request $request, Lead $lead)
    {
        if ($lead->deal()->exists()) {
            return response()->json(['message' => 'Lead already converted to a deal'], 422);
        }

        $data = $request->validate([
            'title'               => 'required|string|max:255',
            'value'               => 'nullable|numeric|min:0',
            'stage'               => 'required|in:proposal,negotiation,agreement,won,lost',
            'expected_close_date' => 'nullable|date',
        ]);

        $deal = $this->leadService->convertToDeal($lead, $data);

        return response()->json([
            'status'  => true,
            'message' => 'Lead converted to deal successfully',
            'deal_id' => $deal->id,
        ]);
    }
}