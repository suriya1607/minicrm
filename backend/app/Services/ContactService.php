<?php

namespace App\Services;

use App\Models\Contact;
use App\Services\ActivityService;


class ContactService
{
    public function __construct(protected ActivityService $activityService) {}

    public function getContacts(array $filters, int $perPage)
    {
        $query = Contact::query()->with('assignedTo:id,name');

        foreach ($filters as $field => $value) {
            if (empty($value)) continue;

            match ($field) {
                'status'     => $query->where('status', $value),
                'created_at' => $query->whereDate('created_at', $value),
                default      => $query->where($field, 'like', "%{$value}%"),
            };
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    public function createContact(array $data): Contact
    {
        $this->activityService->log($contact, 'created', 'Contact created');

        return Contact::create($data);
    }

    public function updateContact(Contact $contact, array $data): Contact
    {
        $this->activityService->log($contact, 'updated', 'Contact updated');
        $contact->update($data);
        return $contact;
    }

    public function deleteContact(Contact $contact): void
    {
        $contact->delete();
    }
}