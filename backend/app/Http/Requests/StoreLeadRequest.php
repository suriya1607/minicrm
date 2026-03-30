<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'contact_id'  => 'required|exists:contacts,id',
            'title'       => 'required|string|max:255',
            'source'      => 'required|in:website,referral,social,email,phone,other',
            'stage'       => 'required|in:new,contacted,qualified,lost',
            'notes'       => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ];
    }
}