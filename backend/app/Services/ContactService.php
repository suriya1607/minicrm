<?php

namespace App\Services;

use App\Models\Contact;

class ContactService
{
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
        return Contact::create($data);
    }

    public function updateContact(Contact $contact, array $data): Contact
    {
        $contact->update($data);
        return $contact;
    }

    public function deleteContact(Contact $contact): void
    {
        $contact->delete();
    }
}