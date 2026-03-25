<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $contactId = $this->route('contact')?->id;

        return [
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:contacts,email,' . $contactId,
            'phone'       => 'nullable|string|max:15',
            'company'     => 'nullable|string|max:255',
            'status'      => 'required|in:active,inactive,prospect',
            'notes'       => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ];
    }
}