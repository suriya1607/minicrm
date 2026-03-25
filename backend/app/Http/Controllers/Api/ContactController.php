<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use App\Services\ContactService;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct(protected ContactService $contactService) {}

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $filters = $request->input('filter', []);

        $contacts = $this->contactService->getContacts($filters, $perPage);

        return response()->json([
            'status'  => true,
            'message' => 'Contacts fetched successfully',
            'data'    => ContactResource::collection($contacts),
            'meta'    => [
                'current_page' => $contacts->currentPage(),
                'last_page'    => $contacts->lastPage(),
                'total'        => $contacts->total(),
                'per_page'     => $contacts->perPage(),
            ],
        ]);
    }

    public function store(StoreContactRequest $request)
    {
        $contact = $this->contactService->createContact($request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Contact created successfully',
            'data'    => new ContactResource($contact),
        ], 201);
    }

    public function show(Contact $contact)
    {
        return response()->json([
            'status'  => true,
            'message' => 'Contact fetched successfully',
            'data'    => new ContactResource($contact),
        ]);
    }

    public function update(StoreContactRequest $request, Contact $contact)
    {
        $updated = $this->contactService->updateContact($contact, $request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Contact updated successfully',
            'data'    => new ContactResource($updated),
        ]);
    }

    public function destroy(Contact $contact)
    {
        $this->contactService->deleteContact($contact);

        return response()->json([
            'status'  => true,
            'message' => 'Contact deleted successfully',
        ]);
    }
}