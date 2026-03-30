<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDealRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'contact_id'          => 'required|exists:contacts,id',
            'lead_id'             => 'nullable|exists:leads,id',
            'assigned_to'         => 'nullable|exists:users,id',
            'title'               => 'required|string|max:255',
            'value'               => 'nullable|numeric|min:0',
            'stage'               => 'required|in:proposal,negotiation,agreement,won,lost',
            'expected_close_date' => 'nullable|date',
            'notes'               => 'nullable|string',
        ];
    }
}